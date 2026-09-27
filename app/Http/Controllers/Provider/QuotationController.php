<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Provider;
use App\Services\LlmService;
use App\Services\AiLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exceptions\AiQuotaExceededException;

class QuotationController extends Controller
{
    protected LlmService $llm;
    protected AiLimitService $aiLimit;

    public function __construct(LlmService $llm, AiLimitService $aiLimit)
    {
        $this->llm = $llm;
        $this->aiLimit = $aiLimit;
    }

    public function create()
    {
        $provider = Auth::user()->getCurrentProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $services = Service::where('provider_id', $provider->id)
                           ->where('status', 'active')
                           ->get();

        $usage = $this->aiLimit->getUsage($provider);

        return view('provider.quotation.create', compact('services', 'usage'));
    }

        public function generate(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

                $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'service_id' => 'required|exists:services,id',
            'notes' => 'nullable|string',
            // Phase 5C: Trip details (required for better AI quote)
            'days' => 'required|integer|min:1|max:30',
            'pax' => 'required|integer|min:1|max:20',
            'start_date' => 'required|date|after_or_equal:today',
            'accommodation' => 'required|in:budget,standard,luxury',
        ]);

        $reservation = null;

        try {
            // FIX-12: crash-safe reservation (short transaction)
            $reservation = $this->aiLimit->reserve(
                $provider,
                'provider.quotation.generate',
                [
                    'provider_id'   => $provider->id,
                    'service_id'    => $validated['service_id'] ?? null,
                    'customer_name' => $validated['customer_name'],
                ]
            );

            $prompt = $this->buildQuotationPrompt($provider, $validated);

            // FIX-12: LLM call OUTSIDE any DB transaction
            $response = $this->llm->generateItinerary($prompt, 'en');

            $quotation = $this->formatQuotation($response, $provider, $validated);

            // FIX-12: mark reservation completed (short transaction)
            $this->aiLimit->finalize($reservation);

            return response()->json([
                'success' => true,
                'quotation' => $quotation,
            ]);

        } catch (AiQuotaExceededException $e) {
            Log::info('AI quota exceeded', [
                'provider_id' => $provider->id,
                'endpoint'    => 'provider.quotation.generate',
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 429);

        } catch (\Exception $e) {
            // FIX-12: release reservation on failure (short transaction)
            if ($reservation) {
                try {
                    $this->aiLimit->release($reservation);
                } catch (\Throwable $releaseError) {
                    Log::error('Failed to release AI reservation', [
                        'reservation_id' => $reservation->id,
                        'error'          => $releaseError->getMessage(),
                    ]);
                }
            }

            Log::error('Quotation generation failed', [
                'error' => $e->getMessage(),
                'provider_id' => $provider->id,
            ]);

                        return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    private function buildQuotationPrompt($provider, $data): string
    {
                $serviceModel  = $data['service_id'] ? Service::find($data['service_id']) : null;
        $serviceName   = $serviceModel->name ?? 'N/A';
        $servicePrice  = (float) ($serviceModel->price ?? 0);
        $serviceCurrency = strtoupper((string) ($serviceModel->currency ?? 'USD'));

                        $days = (int) ($data['days'] ?? 0);
        $pax = (int) ($data['pax'] ?? 0);
        $startDate = $data['start_date'] ?? 'Not specified';
        $accommodation = $data['accommodation'] ?? 'standard';

        $baseTotal = $servicePrice * $pax;
        $taxAmount = $baseTotal * 0.13;
        $grandTotal = $baseTotal + $taxAmount;

        return "Generate a professional quotation for a customer named '{$data['customer_name']}'.

Provider: {$provider->name}
Service: {$serviceName}
Trip duration: {$days} days
Group size: {$pax} pax
Start date: {$startDate}
Accommodation preference: {$accommodation}
Additional notes: " . ($data['notes'] ?? 'None') . "

🔴 CRITICAL — SERVICE BASE PRICE (FROM PROVIDER DB — DO NOT DEVIATE):
   Per-person price: USD {$servicePrice}
   Group size: {$pax} pax
   BASE TOTAL = USD {$baseTotal} (this is the EXACT subtotal to use)
   Tax (13% VAT) = USD {$taxAmount}
   Grand Total = USD {$grandTotal}
   Currency: {$serviceCurrency}

Please provide:
1. A warm greeting
2. Service overview (USE EXACT VALUES from input above, NEVER write N/A):
      - duration: EXACTLY {$days} days
   - participants: EXACTLY {$pax}
   - description: 2-3 sentences describing the trek
3. Pricing breakdown — MUST anchor to BASE TOTAL = USD {$baseTotal}:
   - Split the base total into these EXACT items (fixed percentages):
     * {serviceName} Package (Base Price): 60% of base
     * Accommodation ({$accommodation} level): 15%
     * Meals (3 meals/day × {$days} days): 10%
     * Guide & Porter Services: 10%
     * Permits & National Park Fees: 3%
     * Transport / Flights: 2%
   - Sum of all items MUST equal USD {$baseTotal} (subtotal)
   - For each item:
     * description: specific + meaningful (no generic Item/Package labels)
     * unit_price = item total ÷ {$pax}
     * quantity = {$pax}
     * total = unit_price × {$pax}
   - currency = USD
   - subtotal = USD {$baseTotal}
   - tax = USD {$taxAmount} (13% VAT — pre-computed)
   - grand_total = USD {$grandTotal}
   - DO NOT invent different prices. Use the base total above EXACTLY.
4. Terms and conditions (at least 3 items)
5. Contact information (email, phone, website, address)

Return as a JSON object with key 'quotation' containing all these details. Do not wrap in markdown or code blocks. Just the JSON.";
    }

    private function formatQuotation($aiResponse, $provider, $data): array
    {
        // ✅ सही Service Name लिने
        $serviceName = 'N/A';
        if (!empty($data['service_id'])) {
            $service = Service::find($data['service_id']);
            if ($service) {
                $serviceName = $service->name;
            }
        }

        // If AI response is an array (JSON), build a formatted text quotation
        if (is_array($aiResponse)) {
            // If the response has a 'quotation' key (structured JSON from model)
            if (isset($aiResponse['quotation'])) {
                $quotationData = $aiResponse['quotation'];

                // ✅ Build the FULL formatted quotation (header included)
                $content = "📄 Quotation for {$data['customer_name']}\n\n";
                $content .= "Provider: {$provider->name}\n";
                $content .= "Service: {$serviceName}\n"; // ✅ सही Service Name
                $content .= "Generated: " . now()->toDateTimeString() . "\n";
                $content .= str_repeat('=', 50) . "\n\n";

                // Greeting
                if (isset($quotationData['greeting'])) {
                    $content .= $quotationData['greeting'] . "\n\n";
                }

                // Service Overview
                if (isset($quotationData['service_overview'])) {
                    $overview = $quotationData['service_overview'];
                    $content .= "SERVICE OVERVIEW\n";
                    $content .= "----------------\n";
                                        // Tier 1-EXT: parser-level fallback (LLM non-determinism safety net)
                    $durationValue = $overview['duration'] ?? 'N/A';
                    if ($durationValue === 'N/A' || trim((string) $durationValue) === '') {
                        $durationValue = ((int) ($data['days'] ?? 0)) . ' days';
                    }
                    $participantsValue = $overview['participants'] ?? 'N/A';
                    if ($participantsValue === 'N/A' || trim((string) $participantsValue) === '') {
                        $participantsValue = (string) ((int) ($data['pax'] ?? 0));
                    }
                    $content .= "Duration: " . $durationValue . "\n";
                    $content .= "Participants: " . $participantsValue . "\n";
                    $content .= "Description: " . ($overview['description'] ?? 'N/A') . "\n\n";
                }

                                // Pricing Breakdown
                if (isset($quotationData['pricing_breakdown'])) {
                    $pricing = $quotationData['pricing_breakdown'];
                    $currency = $pricing['currency'] ?? 'USD';

                    // Tier 1-EXT-2 + EXT-3: compute canonical values FIRST
                    $subtotal   = (float) ($pricing['subtotal'] ?? 0);
                    $grandTotal = (float) ($pricing['grand_total'] ?? 0);

                    $taxAmount = (float) ($pricing['tax'] ?? 0);
                    if ($taxAmount <= 0 && $grandTotal > $subtotal) {
                        $taxAmount = round($grandTotal - $subtotal, 2);
                    }

                    // Tier 1-EXT-2: parser-level numeric sanitize (LLM non-determinism)
                    $expectedTax = round($subtotal * 0.13, 2);
                    if ($expectedTax > 0 && ($taxAmount < $expectedTax * 0.9 || $taxAmount > $expectedTax * 1.1)) {
                        $taxAmount = $expectedTax;
                    }
                    $expectedGrand = round($subtotal + $taxAmount, 2);
                    if (abs($grandTotal - $expectedGrand) > 1.0) {
                        $grandTotal = $expectedGrand;
                    }

                    // Tier 1-EXT-3: R25 — scale items so sum = subtotal
                    $items = $pricing['items'] ?? [];
                    $itemsSum = 0;
                    foreach ($items as $it) {
                        $itemsSum += (float) ($it['total'] ?? 0);
                    }
                    if ($itemsSum > 0 && abs($itemsSum - $subtotal) > 1.0) {
                        $scale = $subtotal / $itemsSum;
                        foreach ($items as &$it) {
                            $it['total'] = round((float) ($it['total'] ?? 0) * $scale, 2);
                            $qty = max((int) ($it['quantity'] ?? 1), 1);
                            $it['unit_price'] = round($it['total'] / $qty, 2);
                        }
                        unset($it);
                    }

                    // Display
                    $content .= "PRICING BREAKDOWN\n";
                    $content .= "-----------------\n";

                    foreach ($items as $idx => $item) {
                        $desc = trim((string) ($item['description'] ?? ''));
                        if ($desc === '' || strcasecmp($desc, 'Item') === 0 || strcasecmp($desc, 'Package') === 0) {
                            $desc = $serviceName . ' (Component ' . ($idx + 1) . ')';
                        }
                        $content .= sprintf(
                            "%s: %s %s (x%d) = %s %s\n",
                            $desc,
                            $currency,
                            number_format($item['unit_price'] ?? 0, 2),
                            (int) ($item['quantity'] ?? 1),
                            $currency,
                            number_format($item['total'] ?? 0, 2)
                        );
                    }

                    $content .= sprintf("Subtotal: %s %s\n", $currency, number_format($subtotal, 2));
                    if ($taxAmount > 0) {
                        $content .= sprintf("Tax (13%% VAT): %s %s\n", $currency, number_format($taxAmount, 2));
                    }
                    $content .= sprintf("GRAND TOTAL: %s %s\n\n", $currency, number_format($grandTotal, 2));
                }

                // Terms & Conditions
                if (isset($quotationData['terms_and_conditions']) && is_array($quotationData['terms_and_conditions'])) {
                    $content .= "TERMS & CONDITIONS\n";
                    $content .= "-------------------\n";
                    foreach ($quotationData['terms_and_conditions'] as $i => $term) {
                        $content .= ($i+1) . ". " . $term . "\n";
                    }
                    $content .= "\n";
                }

                // Contact Information
                if (isset($quotationData['contact_information'])) {
                    $contact = $quotationData['contact_information'];
                    $content .= "CONTACT US\n";
                    $content .= "----------\n";
                    $content .= "Email: " . ($contact['email'] ?? 'N/A') . "\n";
                    $content .= "Phone: " . ($contact['phone'] ?? 'N/A') . "\n";
                    $content .= "Website: " . ($contact['website'] ?? 'N/A') . "\n";
                    $content .= "Address: " . ($contact['address'] ?? 'N/A') . "\n";
                }

                return [
                    'provider_name' => $provider->name,
                    'customer_name' => $data['customer_name'],
                    'customer_email' => $data['customer_email'] ?? 'N/A',
                    'customer_phone' => $data['customer_phone'] ?? 'N/A',
                    'service_name' => $serviceName, // ✅ सही Service Name
                    'content' => $content, // ✅ पूरा Formatted Quotation
                    'generated_at' => now()->toDateTimeString(),
                ];
            }

            // Fallback: if response has a 'content' key, use it
            if (isset($aiResponse['content'])) {
                $content = $aiResponse['content'];
            } else {
                // If JSON doesn't have a structure we recognize, display it as is
                $content = json_encode($aiResponse, JSON_PRETTY_PRINT);
            }
        } else {
            $content = $aiResponse;
        }

        return [
            'provider_name' => $provider->name,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? 'N/A',
            'customer_phone' => $data['customer_phone'] ?? 'N/A',
            'service_name' => $serviceName,
            'content' => $content,
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}