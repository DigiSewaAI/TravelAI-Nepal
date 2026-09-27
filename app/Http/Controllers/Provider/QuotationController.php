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
        $serviceName = $data['service_id']
            ? Service::find($data['service_id'])->name ?? 'N/A'
            : 'N/A';

                $days = (int) ($data['days'] ?? 0);
        $pax = (int) ($data['pax'] ?? 0);
        $startDate = $data['start_date'] ?? 'Not specified';
        $accommodation = $data['accommodation'] ?? 'standard';

        return "Generate a professional quotation for a customer named '{$data['customer_name']}'.

Provider: {$provider->name}
Service: {$serviceName}
Trip duration: {$days} days
Group size: {$pax} pax
Start date: {$startDate}
Accommodation preference: {$accommodation}
Additional notes: " . ($data['notes'] ?? 'None') . "

Please provide:
1. A warm greeting
2. Service overview (USE EXACT VALUES from input above, NEVER write N/A):
      - duration: EXACTLY {$days} days
   - participants: EXACTLY {$pax}
   - description: 2-3 sentences describing the trek
3. Pricing breakdown — MUST have MULTIPLE meaningful items (NEVER one generic line):
   - Provide 4-6 separate items minimum
   - Suggested categories (use the ones that fit this service):
     * Trek package / Base price
     * Accommodation ({$accommodation} level)
     * Meals (3 meals/day × {$days} days)
     * Guide & Porter services
     * Permits & National Park fees
     * Transport / Flights
   - Each item MUST have:
          * description: specific + human-readable (e.g., ABC Trek Package, 14 days)
       NEVER use generic labels like Item, Package, or Service
     * unit_price: USD value (number)
     * quantity: {$pax} for per-person items, 1 for group items
     * total: unit_price × quantity
   - currency: USD
   - subtotal = sum of all item totals
   - tax = 13% VAT of subtotal (Nepal standard)
   - grand_total = subtotal + tax
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
                    $content .= "Duration: " . ($overview['duration'] ?? 'N/A') . "\n";
                    $content .= "Participants: " . ($overview['participants'] ?? 'N/A') . "\n";
                    $content .= "Description: " . ($overview['description'] ?? 'N/A') . "\n\n";
                }

                // Pricing Breakdown
                if (isset($quotationData['pricing_breakdown'])) {
                    $pricing = $quotationData['pricing_breakdown'];
                    $content .= "PRICING BREAKDOWN\n";
                    $content .= "-----------------\n";
                    $currency = $pricing['currency'] ?? 'USD';
                                        foreach (($pricing['items'] ?? []) as $idx => $item) {
                        $desc = trim((string) ($item['description'] ?? ''));
                        if ($desc === '' || strcasecmp($desc, 'Item') === 0 || strcasecmp($desc, 'Package') === 0) {
                            // Phase 5C-EXT: fallback — meaningful description (never bare "Item")
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
                    $content .= sprintf("Subtotal: %s %s\n", $currency, number_format($pricing['subtotal'] ?? 0, 2));
                    if (($pricing['tax'] ?? 0) > 0) {
                        $content .= sprintf("Tax: %s %s\n", $currency, number_format($pricing['tax'], 2));
                    }
                    $content .= sprintf("GRAND TOTAL: %s %s\n\n", $currency, number_format($pricing['grand_total'] ?? 0, 2));
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