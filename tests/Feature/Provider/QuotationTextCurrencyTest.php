<?php

namespace Tests\Feature\Provider;

use App\Http\Controllers\Provider\QuotationRequestController;
use App\Models\Provider;
use App\Models\QuotationRequest;
use App\Services\CurrencyService;
use Tests\TestCase;

class QuotationTextCurrencyTest extends TestCase
{
    private function makeProvider(): Provider
    {
        return new Provider(['name' => 'Everest Travels']);
    }

    private function makeQuotationRequest(float $budgetNpr): QuotationRequest
    {
        return new QuotationRequest([
            'traveler_name'  => 'Test Traveler',
            'traveler_input' => [
                'budget'      => (string) $budgetNpr,
                'days'        => 14,
                'destination' => 'EBC',
            ],
        ]);
    }

    private function baseQuotationData(float $grandTotal = 3900.0, string $currency = 'USD'): array
    {
        return [
            'quotation' => [
                'greeting' => 'Namaste',
                'overview' => 'Overview',
                'cost_breakdown' => [
                    'items' => [
                        [
                            'description' => 'Guide',
                            'quantity'    => 1,
                            'per_person'  => $grandTotal,
                            'total'       => $grandTotal,
                        ],
                    ],
                    'currency'    => $currency,
                    'grand_total' => $grandTotal,
                ],
                'contact_information' => [
                    'email' => 'x@x.com', 'phone' => '0',
                    'address' => 'KTM', 'website' => 'https://x.com',
                ],
                'day_by_day_breakdown' => [
                    ['day' => 1, 'route' => 'A → B', 'description' => 'Desc', 'services_included' => []],
                ],
                'terms_and_conditions' => ['Valid 30 days.'],
            ],
        ];
    }

    private function controller(): QuotationRequestController
    {
        // Bypass constructor — formatQuotationText uses no $this dependencies
        return (new \ReflectionClass(QuotationRequestController::class))
            ->newInstanceWithoutConstructor();
    }

    /** @test */
    public function t1_budget_label_shows_npr_with_usd_equivalent(): void
    {
        $this->mock(CurrencyService::class, function ($mock) {
            $mock->shouldReceive('getLiveUsdRate')->andReturn(152.6);
        });

        $text = $this->controller()->formatQuotationText(
            $this->baseQuotationData(),
            $this->makeProvider(),
            $this->makeQuotationRequest(152600.0)
        );

        $this->assertStringContainsString('NPR 152,600.00', $text);
        $this->assertStringContainsString('approx. USD 1,000.00', $text);
    }

    /** @test */
    public function t2_over_budget_math_is_correct(): void
    {
        $this->mock(CurrencyService::class, function ($mock) {
            $mock->shouldReceive('getLiveUsdRate')->andReturn(152.6);
        });

        $text = $this->controller()->formatQuotationText(
            $this->baseQuotationData(3900.0),
            $this->makeProvider(),
            $this->makeQuotationRequest(152600.0) // = USD 1000
        );

        // 3900 - 1000 = 2900 → 290% over
        $this->assertStringContainsString('over budget', $text);
        $this->assertStringNotContainsString('under budget', $text);
        $this->assertStringContainsString('+USD 2,900.00', $text);
        $this->assertStringContainsString('290%', $text);
    }

    /** @test */
    public function t3_under_budget_math_is_correct(): void
    {
        $this->mock(CurrencyService::class, function ($mock) {
            $mock->shouldReceive('getLiveUsdRate')->andReturn(152.6);
        });

        $text = $this->controller()->formatQuotationText(
            $this->baseQuotationData(3900.0),
            $this->makeProvider(),
            $this->makeQuotationRequest(763000.0) // = USD 5000
        );

        // 3900 - 5000 = -1100 → 22% under
        $this->assertStringContainsString('under budget', $text);
        $this->assertStringNotContainsString('over budget', $text);
        $this->assertStringContainsString('-USD 1,100.00', $text);
        $this->assertStringContainsString('22%', $text);
    }

    /** @test */
    public function t4_rate_unavailable_skips_comparison_gracefully(): void
    {
        $this->mock(CurrencyService::class, function ($mock) {
            $mock->shouldReceive('getLiveUsdRate')->andReturn(0.0);
        });

        $text = $this->controller()->formatQuotationText(
            $this->baseQuotationData(),
            $this->makeProvider(),
            $this->makeQuotationRequest(152600.0)
        );

        $this->assertStringContainsString('NPR 152,600.00', $text);
        $this->assertStringNotContainsString('over budget', $text);
        $this->assertStringNotContainsString('under budget', $text);
        $this->assertStringNotContainsString('Difference:', $text);
    }
}