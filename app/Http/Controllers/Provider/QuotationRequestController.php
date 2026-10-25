<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use App\Services\LlmService;
use App\Services\AiLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotationMail;
use App\Exceptions\AiQuotaExceededException;

class QuotationRequestController extends Controller
{
    protected LlmService $llm;
    protected AiLimitService $aiLimit;

    public function __construct(LlmService $llm, AiLimitService $aiLimit)
    {
        $this->llm = $llm;
        $this->aiLimit = $aiLimit;
    }

    public function index()
    {
        $provider = Auth::user()->getCurrentProvider();
        if (!$provider) {
            abort(403, 'Provider not found.');
        }

        $requests = QuotationRequest::where('provider_id', $provider->id)
            ->with(['traveler', 'plannerResult'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingCount = $requests->where('status', 'pending')->count();

        return view('provider.quotation-requests.index', compact('requests', 'pendingCount'));
    }

    public function show(QuotationRequest $quotationRequest)
    {
        $provider = Auth::user()->getCurrentProvider();
        if (!$provider || $quotationRequest->provider_id !== $provider->id) {
            abort(403, 'Unauthorized.');
        }

        if ($quotationRequest->status === 'pending') {
            $quotationRequest->update(['status' => 'viewed']);
            $quotationRequest->refresh();
        }

        return view('provider.quotation-requests.show', compact('quotationRequest'));
    }

    public function generateQuotation(Request $request, QuotationRequest $quotationRequest)
    {
        $provider = Auth::user()->getCurrentProvider();
        if (!$provider || $quotationRequest->provider_id !== $provider->id) {
            abort(403, 'Unauthorized.');
        }

        $reservation = null;

        try {
            $reservation = $this->aiLimit->reserve(
                $provider,
                'provider.quotation-requests.generate',
                [
                    'quotation_request_id' => $quotationRequest->id,
                    'provider_id'          => $provider->id,
                ]
            );

            $prompt = $this->buildQuotationPrompt($quotationRequest, $provider);

            $response = $this->llm->generateItineraryParallel(
                prompt:    $prompt,
                locale:    'en',
                model:     null,
                extract:   true,
                maxTokens: 8000,
            );

            $quotationData = $this->extractQuotationJson($response);

            $itineraryDays = $quotationRequest->itinerary_data['days'] ?? [];
            $dayBreakdown = [];
            foreach ($itineraryDays as $day) {
                $services = [];
                if (!empty($day['items']) && is_array($day['items'])) {
                    foreach ($day['items'] as $item) {
                        $services[] = $item['title'] ?? 'Service';
                    }
                }
                $dayBreakdown[] = [
                    'day'                => $day['day_number'] ?? 0,
                    'route'              => $day['title'] ?? '',
                    'description'        => $day['description'] ?? '',
                    'services_included'  => $services,
                ];
            }

            if (empty($quotationData['quotation']['day_by_day_breakdown'])) {
                $quotationData['quotation']['day_by_day_breakdown'] = $dayBreakdown;
            }

            $quotationText = $this->formatQuotationText($quotationData, $provider, $quotationRequest);

            $quotationRequest->update([
                'status'          => 'completed',
                'quotation_data'  => $quotationData,
                'quotation_text'  => $quotationText,
            ]);

            if ($quotationRequest->traveler) {
                try {
                    $quotationRequest->traveler->notify(
                        new \App\Notifications\QuotationReadyNotification($quotationRequest)
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send notification: ' . $e->getMessage());
                }
            }

            $this->aiLimit->finalize($reservation);

            return response()->json([
                'success'   => true,
                'quotation' => $quotationText,
            ]);

        } catch (AiQuotaExceededException $e) {
            Log::info('AI quota exceeded', [
                'provider_id' => $provider->id,
                'endpoint'    => 'provider.quotation-requests.generate',
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 429);

        } catch (\Exception $e) {
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
                'error'       => $e->getMessage(),
                'request_id'  => $quotationRequest->id,
                'provider_id' => $provider->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    private function buildQuotationPrompt(QuotationRequest $request, $provider): string
    {
        $itinerary = $request->itinerary_data;
        $input = $request->traveler_input;

        $groupSize = 1;

        foreach (['pax', 'group_size', 'travelers'] as $paxKey) {
            if (!empty($input[$paxKey]) && is_numeric($input[$paxKey])) {
                $groupSize = (int) $input[$paxKey];
                break;
            }
        }

        if ($groupSize === 1) {
            $maxQty = 0;
            foreach (($itinerary['days'] ?? []) as $day) {
                foreach (($day['items'] ?? []) as $item) {
                    $q = (int) ($item['quantity'] ?? 0);
                    if ($q > $maxQty) {
                        $maxQty = $q;
                    }
                }
            }
            if ($maxQty > 1) {
                $groupSize = $maxQty;
            }
        }

        if ($groupSize === 1 && $request->message) {
            preg_match('/(\d+)\s*(?:pax|people|persons?|travelers?)/i', $request->message, $matches);
            if (!empty($matches[1])) {
                $groupSize = (int) $matches[1];
            }
        }

        $daysText = '';
        if (isset($itinerary['days'])) {
            foreach ($itinerary['days'] as $day) {
                $daysText .= "Day {$day['day_number']}: {$day['title']}\n";
                if (!empty($day['description'])) {
                    $daysText .= "  Description: {$day['description']}\n";
                }
                if (isset($day['items'])) {
                    foreach ($day['items'] as $item) {
                        $daysText .= "  - {$item['title']}" . (!empty($item['description']) ? ": {$item['description']}" : '') . "\n";
                    }
                }
                $daysText .= "\n";
            }
        }

        $services = $provider->services()->where('status', 'active')->pluck('name')->join(', ') ?: 'Various services available';

        $providerContactEmail = $provider->contact_email ?? '';
        $providerContactPhone = $provider->contact_phone ?? '';
        $providerWebsite      = $provider->website ?? '';
        $providerAddress      = $provider->address ?? '';

        return <<<PROMPT
Generate a professional quotation for a traveler based on the following itinerary.

TRAVELER REQUEST:
- Destination: {$input['destination']}
- Days: {$input['days']}
- Budget: \${$input['budget']}
- Travel Style: {$input['travel_style']}
- Group Size: {$groupSize} pax (IMPORTANT: Calculate all costs based on this group size)

PROVIDER: {$provider->name}
AVAILABLE SERVICES: {$services}

ITINERARY:
{$daysText}

Provide:
1. A warm greeting (1-2 sentences)
2. Service overview for {$groupSize} pax (2-3 sentences)
3. Day-by-day narrative breakdown (2-3 sentences per day, vivid, atmospheric)
4. Cost breakdown with items (per person, quantity, total) in USD
5. Terms and conditions (3-5 items)
6. Contact information

CRITICAL INSTRUCTIONS:
- Output ONLY a valid JSON object. No markdown, no thinking, no extra text.
- All numbers must be numeric.
- Grand total must equal the sum of all item totals. Calculate honestly based on realistic Nepal trekking costs — do NOT match the budget.

Return ONLY this JSON structure:
{
  "quotation": {
    "greeting": "A warm greeting to the traveler",
    "overview": "Service overview for {$groupSize} pax",
    "day_by_day_breakdown": [
      {
        "day": 1,
        "route": "Starting point → Ending point",
        "description": "2-3 vivid, atmospheric sentences about today's experience. Reference ONLY real places from the itinerary.",
        "services_included": ["Service A", "Service B"]
      }
    ],
    "cost_breakdown": {
      "currency": "USD",
      "items": [
        {"description": "Trekking Guide & Porter Service", "per_person": 250, "quantity": 1, "total": 250},
        {"description": "Accommodation", "per_person": 200, "quantity": 1, "total": 200},
        {"description": "Meals", "per_person": 180, "quantity": 1, "total": 180},
        {"description": "Trekking Permits", "per_person": 50, "quantity": 1, "total": 50},
        {"description": "Transportation", "per_person": 80, "quantity": 1, "total": 80},
        {"description": "Emergency Support", "per_person": 40, "quantity": 1, "total": 40}
      ],
      "grand_total": 800
    },
    "terms_and_conditions": ["Term 1", "Term 2", "Term 3"],
        "contact_information": {
      "email": "{$providerContactEmail}",
      "phone": "{$providerContactPhone}",
      "website": "{$providerWebsite}",
      "address": "{$providerAddress}"
    }
  }
}
PROMPT;
    }

    public function formatQuotationText(array $quotationData, $provider, $quotationRequest): string
    {
        $travelerName = $quotationRequest->traveler_name ?? $quotationRequest->traveler->name ?? 'Traveler';

        $q = $quotationData['quotation'] ?? $quotationData;
        $grandTotal = 0;

        $content = "📄 Quotation for {$travelerName}\n\n";
        $content .= "Provider: {$provider->name}\n";
        $content .= "Generated: " . now()->toDateTimeString() . "\n";
        $content .= str_repeat('=', 50) . "\n\n";

        if (isset($q['greeting'])) {
            $content .= $q['greeting'] . "\n\n";
        }

        if (isset($q['overview']) || isset($q['service_overview'])) {
            $ov = $q['overview'] ?? $q['service_overview'];
            $content .= "SERVICE OVERVIEW\n----------------\n";
            $content .= (is_string($ov) ? $ov : ($ov['description'] ?? 'N/A')) . "\n\n";
        }

        if (isset($q['day_by_day_breakdown']) && is_array($q['day_by_day_breakdown'])) {
            $content .= "DAY-BY-DAY BREAKDOWN\n--------------------\n";
            foreach ($q['day_by_day_breakdown'] as $day) {
                $dayNum = $day['day'] ?? '?';
                $route = $day['route'] ?? '';

                $route = preg_replace('/^Day\s*\d+\s*[:：]\s*/i', '', $route);
                $route = trim($route);

                $content .= "Day {$dayNum}: {$route}\n";

                if (!empty($day['description']) && is_string($day['description'])) {
                    $content .= "  " . trim($day['description']) . "\n";
                }

                if (!empty($day['services_included']) && is_array($day['services_included'])) {
                    foreach ($day['services_included'] as $service) {
                        $content .= "  - {$service}\n";
                    }
                } elseif (!empty($day['services']) && is_array($day['services'])) {
                    foreach ($day['services'] as $key => $value) {
                        $content .= "  {$key}: {$value}\n";
                    }
                }
                $content .= "\n";
            }
        }

        if (isset($q['cost_breakdown'])) {
            $p = $q['cost_breakdown'];
            $currency = $p['currency'] ?? 'USD';
            $content .= "COST BREAKDOWN\n--------------\n";

            $total = 0;
            $items = $p['items'] ?? [];

            foreach ($items as $item) {
                $amount = $item['total'] ?? 0;
                if ($amount == 0 && isset($item['per_person'])) {
                    $amount = $item['per_person'];
                }
                $total += $amount;

                $description = $item['description'] ?? 'Item';
                if (isset($item['per_person']) && isset($item['quantity'])) {
                    $description .= " ({$item['per_person']} x {$item['quantity']} pax)";
                } elseif (isset($item['per_person'])) {
                    $description .= " (Per Person: {$currency} " . number_format($item['per_person'], 2) . ")";
                }

                $content .= sprintf(
                    "%s: %s %s\n",
                    $description,
                    $currency,
                    number_format($amount, 2)
                );
            }

            $grandTotal = $p['grand_total'] ?? $p['total'] ?? 0;
            if ($grandTotal == 0 && $total > 0) {
                $grandTotal = $total;
            }

            $content .= sprintf("GRAND TOTAL: %s %s\n\n", $currency, number_format($grandTotal, 2));
        }

        // ✅ NEW: Budget Comparison Section (FIX-CURRENCY-03)
        $travelerBudget = $quotationRequest->traveler_input['budget'] ?? null;
        if ($travelerBudget && is_numeric($travelerBudget) && $travelerBudget > 0) {
            
            $currency   = $currency ?? 'USD';
            $budgetNpr  = (float) $travelerBudget;
            
            // CURRENCY-CONVERSION-02: Use snapshot rate if available, else fallback
            $usdRate = (float) ($quotationRequest->exchange_rate_snapshot ?? app(\App\Services\CurrencyService::class)->getLiveUsdRate());
            
            $finalTotal = (float) $grandTotal;

            $content .= "BUDGET COMPARISON\n";
            $content .= "-----------------\n";

            if ($usdRate > 0) {
                $budgetUsd   = round($budgetNpr / $usdRate, 2);
                $difference  = $finalTotal - $budgetUsd;
                $percentDiff = $budgetUsd > 0 ? round(($difference / $budgetUsd) * 100, 1) : 0;

                $content .= sprintf("Traveler's Budget: NPR %s (approx. USD %s)\n",
                    number_format($budgetNpr, 2),
                    number_format($budgetUsd, 2));
                $content .= sprintf("Our Quotation:     %s %s\n",
                    $currency,
                    number_format($finalTotal, 2));

                if ($finalTotal > $budgetUsd) {
                    $content .= sprintf("Difference:        +%s %s (%s%% over budget)\n\n",
                        $currency, number_format($difference, 2), $percentDiff);
                } else {
                    $savings = abs($difference);
                    $content .= sprintf("Difference:        -%s %s (%s%% under budget)\n\n",
                        $currency, number_format($savings, 2), abs($percentDiff));
                }

                $providerNote = trim($q['provider_budget_note'] ?? '');

                if (!empty($providerNote)) {
                    $content .= "📝 " . $providerNote . "\n\n";
                } else {
                    if ($finalTotal > $budgetUsd) {
                        if ($percentDiff <= 10) {
                            $content .= "📌 NOTE: Our quotation is slightly above your stated budget. We can discuss\n";
                            $content .= "   minor adjustments or payment flexibility to accommodate your needs.\n\n";
                        } elseif ($percentDiff <= 25) {
                            $content .= "📌 NOTE: Our quotation exceeds your budget by " . $percentDiff . "%. This reflects the\n";
                            $content .= "   quality and completeness of our package. We are open to discussing:\n";
                            $content .= "   • Alternative accommodation options\n";
                            $content .= "   • Adjusted service inclusions\n";
                            $content .= "   • Flexible payment terms\n\n";
                        } else {
                            $content .= "📌 NOTE: Our quotation significantly exceeds your budget (" . $percentDiff . "%). To\n";
                            $content .= "   accommodate your budget, we can customize this package by:\n";
                            $content .= "   • Using budget-tier accommodations\n";
                            $content .= "   • Reducing certain inclusions\n";
                            $content .= "   • Adjusting the itinerary scope\n";
                            $content .= "   Please contact us to discuss a tailored option.\n\n";
                        }
                    } else {
                        $savings = abs($difference);
                        if ($savings == 0) {
                            $content .= "✅ GOOD NEWS: Our quotation matches your budget exactly. We look forward to\n";
                            $content .= "   providing you with an excellent trekking experience.\n\n";
                        } elseif ($percentDiff >= -15) {
                            $content .= "✅ GOOD NEWS: Our quotation fits comfortably within your budget. We will\n";
                            $content .= "   provide the full package as described, with high-quality service.\n\n";
                        } else {
                            $content .= "✅ EXCELLENT NEWS: Our quotation is well within your budget (saving you {$currency} " . number_format($savings, 2) . ").\n";
                            $content .= "   We can offer this complete package at the quoted price, or discuss\n";
                            $content .= "   upgrading certain services using the available budget difference.\n\n";
                        }
                    }
                }
            } else {
                $content .= sprintf("Traveler's Budget: NPR %s\n", number_format($budgetNpr, 2));
                $content .= sprintf("Our Quotation:     %s %s\n\n",
                    $currency,
                    number_format($finalTotal, 2));
            }
        }
        // ✅ KTM-STAY-DISCLAIMER-05
        $content .= "IMPORTANT NOTE\n--------------\n";
        $content .= "Please note that this quotation covers the trekking package only. Kathmandu accommodation (hotel) and meals are not included. We can arrange them for you at an additional cost if needed.\n\n";
        if (isset($q['terms_and_conditions']) && is_array($q['terms_and_conditions'])) {
            $content .= "TERMS & CONDITIONS\n-------------------\n";
            $i = 1;
            foreach ($q['terms_and_conditions'] as $term) {
                $term = trim($term);
                if (empty($term)) continue;
                $content .= ($i++) . ". " . $term . "\n";
            }
            $content .= "\n";
        }

        $c = $q['contact_information'] ?? [];
        $email = ($c['email'] ?? 'N/A') !== 'N/A' ? $c['email'] : ($provider->contact_email ?? 'N/A');
        $phone = ($c['phone'] ?? 'N/A') !== 'N/A' ? $c['phone'] : ($provider->contact_phone ?? 'N/A');
        $website = ($c['website'] ?? 'N/A') !== 'N/A' ? $c['website'] : ($provider->website ?? 'N/A');
        $address = ($c['address'] ?? 'N/A') !== 'N/A' ? $c['address'] : ($provider->address ?? 'N/A');

        $content .= "CONTACT US\n----------\n";
        $content .= "Email: {$email}\n";
        $content .= "Phone: {$phone}\n";
        $content .= "Website: {$website}\n";
        $content .= "Address: {$address}\n";

        return $content;
    }

    private function extractQuotationJson($content): array
    {
        if (is_array($content)) {
            if (isset($content['quotation'])) {
                return $content;
            }
            return ['quotation' => $content];
        }

        if (!is_string($content)) {
            throw new \Exception('Invalid content type for JSON extraction.');
        }

        $cleaned = preg_replace('/<think>.*?<\/think>/s', '', $content);
        $cleaned = preg_replace('/```json\s*/i', '', $cleaned);
        $cleaned = preg_replace('/```\s*/', '', $cleaned);
        $cleaned = trim($cleaned);

        $decoded = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return isset($decoded['quotation']) ? $decoded : ['quotation' => $decoded];
        }

        if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
            $json = $matches[0];

            $open = substr_count($json, '{');
            $close = substr_count($json, '}');
            if ($open > $close) {
                $json .= str_repeat('}', $open - $close);
            }

            $openB = substr_count($json, '[');
            $closeB = substr_count($json, ']');
            if ($openB > $closeB) {
                $json .= str_repeat(']', $openB - $closeB);
            }

            $json = preg_replace('/,\s*}/', '}', $json);
            $json = preg_replace('/,\s*]/', ']', $json);

            $decoded = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return isset($decoded['quotation']) ? $decoded : ['quotation' => $decoded];
            }
        }

        Log::warning('Failed to extract JSON, using fallback', [
            'content_preview' => substr($content, 0, 500),
            'content_length' => strlen($content),
        ]);

        $greeting = 'Dear Traveler, thank you for your request.';
        $overview = 'We are pleased to provide this quotation for your trek.';

        if (preg_match('/"greeting"\s*:\s*"([^"]+)"/', $cleaned, $m)) {
            $greeting = $m[1];
        }
        if (preg_match('/"overview"\s*:\s*"([^"]+)"/', $cleaned, $m)) {
            $overview = $m[1];
        }

        return [
            'quotation' => [
                'greeting' => $greeting,
                'overview' => $overview,
                'day_by_day_breakdown' => [],
                'cost_breakdown' => [
                    'currency' => 'USD',
                    'items' => [],
                    'grand_total' => 0,
                ],
                'terms_and_conditions' => [
                    'Quotation valid for 30 days.',
                    '50% deposit required to confirm booking.',
                ],
                'contact_information' => [],
            ]
        ];
    }

    public function sendQuotationEmail(QuotationRequest $quotationRequest)
    {
        $provider = Auth::user()->getCurrentProvider();
        if (!$provider || $quotationRequest->provider_id !== $provider->id) {
            abort(403, 'Unauthorized.');
        }

        if ($quotationRequest->status !== 'completed' || empty($quotationRequest->quotation_text)) {
            return back()->with('error', 'Quotation not generated yet. Please generate quotation first.');
        }

        $email = $quotationRequest->traveler_email ?? $quotationRequest->traveler->email ?? null;

        if (!$email) {
            return back()->with('error', 'No traveler email address found.');
        }

        try {
            Mail::to($email)->send(new QuotationMail($quotationRequest));
            return back()->with('success', 'Quotation email sent successfully to traveler.');
        } catch (\Exception $e) {
            Log::error('Failed to send quotation email: ' . $e->getMessage());
            return back()->with('error', 'Failed to send email. Please try again.');
        }
    }

    private function authorizeProvider(QuotationRequest $request): void
    {
        $provider = Auth::user()->getCurrentProvider();
        if (!$provider || $request->provider_id !== $provider->id) {
            abort(403, 'Unauthorized.');
        }
    }

    public function edit(QuotationRequest $quotationRequest)
    {
        $this->authorizeProvider($quotationRequest);

        if ($quotationRequest->isQuotationSent()) {
            abort(403, 'Quotation already sent. Cannot edit.');
        }

        $draft = $quotationRequest->quotation_data['quotation'] ?? [];

        if ($quotationRequest->quotation_final) {
            $finalWrapper = $quotationRequest->quotation_final;
            $final = $finalWrapper['quotation'] ?? [];

            if (isset($final['cost_breakdown']) && is_array($final['cost_breakdown'])) {
                $final = $final;
            }
        } else {
            $final = $draft;
        }

        if (!isset($final['cost_breakdown']) && isset($final['items'])) {
            $final['cost_breakdown'] = [
                'items' => $final['items'],
                'grand_total' => $final['grand_total'] ?? 0,
                'currency' => $final['currency'] ?? 'USD',
            ];
            unset($final['items']);
        }

        return view('provider.quotation-requests.edit', compact(
            'quotationRequest', 'draft', 'final'
        ));
    }

    public function update(Request $request, QuotationRequest $quotationRequest)
    {
        $this->authorizeProvider($quotationRequest);

        if ($quotationRequest->isQuotationSent()) {
            return response()->json(['error' => 'Quotation already sent.'], 403);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.per_person' => 'required|numeric|min:0',
            'items.*.quantity' => 'nullable|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'terms' => 'nullable|array',
            'terms.*' => 'nullable|string|max:500',
            'special_notes' => 'nullable|string|max:1000',
            'provider_budget_note' => 'nullable|string|max:2000',
        ]);

        $recalculated = $this->recalculateQuotation(
            $validated['items'],
            $validated['discount'] ?? 0
        );

        $draft = $quotationRequest->quotation_data['quotation'] ?? [];
        $finalData = [
            'greeting' => $draft['greeting'] ?? '',
            'overview' => $draft['overview'] ?? '',
            'day_by_day_breakdown' => $draft['day_by_day_breakdown'] ?? [],
            'cost_breakdown' => $recalculated,
            'terms_and_conditions' => $validated['terms'] ?? $draft['terms_and_conditions'] ?? [],
            'special_notes' => $validated['special_notes'] ?? '',
            'provider_budget_note' => $validated['provider_budget_note'] ?? '',
            'contact_information' => [
                'email' => $quotationRequest->provider->contact_email ?? 'N/A',
                'phone' => $quotationRequest->provider->contact_phone ?? 'N/A',
                'website' => $quotationRequest->provider->website ?? 'N/A',
                'address' => $quotationRequest->provider->address ?? 'N/A',
            ],
        ];

        $quotationWrapper = ['quotation' => $finalData];

        $quotationRequest->quotation_final = $quotationWrapper;
        $quotationRequest->quotation_status = 'edited';
        $quotationRequest->edited_at = now();
        $quotationRequest->edited_by = auth()->id();
        $quotationRequest->quotation_text = $this->formatQuotationText(
            $quotationWrapper,
            $quotationRequest->provider,
            $quotationRequest
        );
        $quotationRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Quotation updated successfully.',
            'quotation_text' => $quotationRequest->quotation_text,
        ]);
    }

    public function preview(QuotationRequest $quotationRequest)
    {
        $this->authorizeProvider($quotationRequest);

        $provider = Auth::user()->getCurrentProvider();

        if ($quotationRequest->quotation_final) {
            $finalData = $quotationRequest->quotation_final;
            $quotationText = $quotationRequest->quotation_text;
        } else {
            $draft = $quotationRequest->quotation_data['quotation'] ?? [];
            $finalData = ['quotation' => $draft];
            $quotationText = $this->formatQuotationText(
                $finalData,
                $provider,
                $quotationRequest
            );
        }

        return view('emails.quotation', [
            'quotationRequest' => $quotationRequest,
            'quotationText' => $quotationText,
            'provider' => $provider,
            'travelerName' => $quotationRequest->traveler_name ?? 'Traveler',
            'preview' => true,
        ]);
    }

    public function send(Request $request, QuotationRequest $quotationRequest)
    {
        $this->authorizeProvider($quotationRequest);

        if ($quotationRequest->isQuotationSent()) {
            return back()->with('error', 'Quotation already sent.');
        }

        $provider = Auth::user()->getCurrentProvider();

        if (!$quotationRequest->quotation_final) {
            $draft = $quotationRequest->quotation_data['quotation'] ?? [];

            if (empty($draft['cost_breakdown']['items'])) {
                return back()->with('error', 'No quotation data found. Please generate AI quotation first.');
            }

            $finalData = $this->recalculateQuotation(
                $draft['cost_breakdown']['items'] ?? [],
                0
            );
            $finalData['greeting'] = $draft['greeting'] ?? '';
            $finalData['overview'] = $draft['overview'] ?? '';
            $finalData['day_by_day_breakdown'] = $draft['day_by_day_breakdown'] ?? [];
            $finalData['terms_and_conditions'] = $draft['terms_and_conditions'] ?? [];
            $finalData['contact_information'] = $draft['contact_information'] ?? [];

            $quotationRequest->quotation_final = ['quotation' => $finalData];
            $quotationRequest->quotation_status = 'reviewed';
            $quotationRequest->quotation_text = $this->formatQuotationText(
                ['quotation' => $finalData],
                $provider,
                $quotationRequest
            );
            $quotationRequest->save();
        }

        $finalWrapper = $quotationRequest->quotation_final;
        $finalData = $finalWrapper['quotation'] ?? [];

        $recalculated = $this->recalculateQuotation(
            $finalData['items'] ?? $finalData['cost_breakdown']['items'] ?? [],
            $finalData['discount'] ?? 0
        );
        $finalData['cost_breakdown'] = [
            'currency'    => $finalData['cost_breakdown']['currency'] ?? 'USD',
            'items'       => $recalculated['items'],
            'subtotal'    => $recalculated['subtotal'] ?? 0,
            'grand_total' => $recalculated['grand_total'],
        ];
        $finalData['discount'] = $recalculated['discount'];

        $quotationRequest->quotation_final = ['quotation' => $finalData];
        $quotationRequest->quotation_text = $this->formatQuotationText(
            ['quotation' => $finalData],
            $provider,
            $quotationRequest
        );
        $quotationRequest->save();

        $email = $quotationRequest->traveler_email ?? $quotationRequest->traveler->email ?? null;
        if (!$email) {
            return back()->with('error', 'No traveler email address found.');
        }

        try {
            Mail::to($email)->send(new \App\Mail\QuotationMail($quotationRequest));
        } catch (\Exception $e) {
            Log::error('Quotation email failed: ' . $e->getMessage());
            return back()->with('error', 'Email sending failed. Please try again.');
        }

        $quotationRequest->quotation_status = 'sent';
        $quotationRequest->sent_at = now();
        $quotationRequest->save();

        return back()->with('success', 'Quotation sent successfully to traveler.');
    }

    private function recalculateQuotation(array $items, float $discount = 0): array
    {
        $total = 0;
        $currency = 'USD';

        foreach ($items as &$item) {
            $quantity = $item['quantity'] ?? 1;
            $perPerson = (float) $item['per_person'];
            $total = (float) ($perPerson * $quantity);

            $item['total'] = round($total, 2);
            $item['quantity'] = (int) $quantity;
            $item['per_person'] = round($perPerson, 2);
        }

        $subtotal = array_sum(array_column($items, 'total'));
        $grandTotal = round($subtotal - $discount, 2);
        if ($grandTotal < 0) $grandTotal = 0;

        return [
            'items' => $items,
            'currency' => $currency,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'grand_total' => $grandTotal,
        ];
    }
}