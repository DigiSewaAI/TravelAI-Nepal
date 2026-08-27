<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use App\Services\LlmService;
use App\Services\AiLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        try {
            $this->aiLimit->checkAndIncrement($provider);

            $prompt = $this->buildQuotationPrompt($quotationRequest, $provider);

            // ✅ Generate with extraction disabled, higher max_tokens (6000)
            $response = $this->llm->generateItinerary($prompt, 'en', 'qwen/qwen3.6-27b', false, 6000);

            $rawContent = is_array($response) && isset($response['content']) ? $response['content'] : (string) $response;

            $quotationData = $this->extractQuotationJson($rawContent);

            $quotationText = $this->formatQuotationText($quotationData, $provider, $quotationRequest);

            $quotationRequest->update([
                'status' => 'completed',
                'quotation_data' => $quotationData,
                'quotation_text' => $quotationText,
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

            return response()->json([
                'success' => true,
                'quotation' => $quotationText,
            ]);

        } catch (\Exception $e) {
            Log::error('Quotation generation failed', [
                'error' => $e->getMessage(),
                'request_id' => $quotationRequest->id,
                'provider_id' => $provider->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function buildQuotationPrompt(QuotationRequest $request, $provider): string
    {
        $itinerary = $request->itinerary_data;
        $input = $request->traveler_input;

        // 🔥 Extract group size from message or traveler input
        $groupSize = 1; // Default
        if ($request->message) {
            preg_match('/(\d+)\s*pax/i', $request->message, $matches);
            if (!empty($matches[1])) {
                $groupSize = (int) $matches[1];
            }
        }
        // Alternative: if message has "pax" in different format
        if ($groupSize === 1 && $request->message) {
            preg_match('/(\d+)\s*people/i', $request->message, $matches);
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

        return "Generate a professional quotation for a traveler based on the following itinerary.

TRAVELER REQUEST:
- Destination: " . ($input['destination'] ?? 'N/A') . "
- Days: " . ($input['days'] ?? 'N/A') . "
- Budget: $" . ($input['budget'] ?? 'N/A') . "
- Travel Style: " . ($input['travel_style'] ?? 'N/A') . "
- Interests: " . implode(', ', $input['interests'] ?? []) . "
- **Group Size: {$groupSize} pax** (IMPORTANT: Calculate all costs based on this group size)

PROVIDER: {$provider->name}
AVAILABLE SERVICES: {$services}

ITINERARY:
{$daysText}

Please provide:
1. A warm greeting to the traveler
2. Overview of how you can fulfill this itinerary for a group of {$groupSize} people
3. Day-by-day breakdown of services you will provide (accommodation, meals, guide, transport, permits, etc.)
4. **Cost breakdown** with:
   - **Per person cost** for each item
   - **Total cost** for each item ({$groupSize} x per person)
   - **Grand total** (sum of all total costs)
   - Use USD currency
5. Terms and conditions
6. Contact information

**IMPORTANT: Output ONLY a valid JSON object with key 'quotation'. Do NOT include any thinking process, explanations, or markdown. Your entire response must be a single valid JSON object.

The JSON structure should be:
{
  \"quotation\": {
    \"greeting\": \"...\",
    \"overview\": \"...\",
    \"day_by_day_breakdown\": [...],
    \"cost_breakdown\": {
      \"currency\": \"USD\",
      \"items\": [
        {\"description\": \"...\", \"per_person\": 100, \"total\": 100}
      ],
      \"grand_total\": 100
    },
    \"terms_and_conditions\": [...],
    \"contact_information\": {...}
  }
}

Ensure the grand_total is the sum of all item totals.**";
    }

    private function formatQuotationText(array $quotationData, $provider, $quotationRequest): string
    {
        $travelerName = $quotationRequest->traveler_name ?? $quotationRequest->traveler->name ?? 'Traveler';
        
        $q = $quotationData['quotation'] ?? $quotationData;
        
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
        
        if (isset($q['day_by_day_breakdown'])) {
            $content .= "DAY-BY-DAY BREAKDOWN\n--------------------\n";
            foreach ($q['day_by_day_breakdown'] as $day) {
                $content .= "Day {$day['day']}: {$day['route']}\n";
                if (isset($day['services'])) {
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
                // ✅ Support both 'total' and 'per_person' formats
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
            
            // ✅ If grand_total is missing or 0, calculate from items
            $grandTotal = $p['grand_total'] ?? $p['total'] ?? 0;
            if ($grandTotal == 0 && $total > 0) {
                $grandTotal = $total;
            }
            
            $content .= sprintf("GRAND TOTAL: %s %s\n\n", $currency, number_format($grandTotal, 2));
        }
        
        if (isset($q['terms_and_conditions']) && is_array($q['terms_and_conditions'])) {
            $content .= "TERMS & CONDITIONS\n-------------------\n";
            foreach ($q['terms_and_conditions'] as $i => $term) {
                $content .= ($i+1) . ". " . $term . "\n";
            }
            $content .= "\n";
        }
        
        if (isset($q['contact_information'])) {
            $c = $q['contact_information'];
            $content .= "CONTACT US\n----------\n";
            $content .= "Email: " . ($c['email'] ?? 'N/A') . "\n";
            $content .= "Phone: " . ($c['phone'] ?? 'N/A') . "\n";
            $content .= "Website: " . ($c['website'] ?? 'N/A') . "\n";
            $content .= "Address: " . ($c['address'] ?? 'N/A') . "\n";
        }
        
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

        // 1. Remove <think> tags and any other unwanted text
        $cleaned = preg_replace('/<think>.*?<\/think>/s', '', $content);
        $cleaned = preg_replace('/<[^>]+>/', '', $cleaned);

        // 2. Find JSON from the first { to the last }
        if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
            $json = $matches[0];
            // Fix potential unclosed braces
            $open = substr_count($json, '{');
            $close = substr_count($json, '}');
            if ($open > $close) {
                $json .= str_repeat('}', $open - $close);
            }
            $decoded = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // 3. Try to decode the whole cleaned content
        $decoded = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        Log::error('Failed to extract JSON from quotation response', ['content' => $content]);
        throw new \Exception('Failed to extract JSON from AI response. Please adjust your prompt.');
    }
}