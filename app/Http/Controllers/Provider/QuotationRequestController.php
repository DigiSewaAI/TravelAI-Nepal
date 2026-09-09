<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use App\Services\LlmService;
use App\Services\AiLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // ✅ Mail Facade
use App\Mail\QuotationMail; // ✅ Mailable Class

class QuotationRequestController extends Controller
{
    protected LlmService $llm;
    protected AiLimitService $aiLimit;

    public function __construct(LlmService $llm, AiLimitService $aiLimit)
    {
        $this->llm = $llm;
        $this->aiLimit = $aiLimit;
    }

    /**
     * List all quotation requests for the provider.
     */
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

    /**
     * Show a single quotation request with full itinerary.
     */
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

    /**
     * Generate AI quotation from the itinerary data.
     */
    public function generateQuotation(Request $request, QuotationRequest $quotationRequest)
    {
        $provider = Auth::user()->getCurrentProvider();
        if (!$provider || $quotationRequest->provider_id !== $provider->id) {
            abort(403, 'Unauthorized.');
        }

        try {
            $this->aiLimit->checkAndIncrement($provider);

            $prompt = $this->buildQuotationPrompt($quotationRequest, $provider);

            // Generate with extraction disabled, higher max_tokens (6000)
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

    /**
     * Build the AI prompt using itinerary data.
     */
    private function buildQuotationPrompt(QuotationRequest $request, $provider): string
    {
        $itinerary = $request->itinerary_data;
        $input = $request->traveler_input;

        // Extract group size from message
        $groupSize = 1;
        if ($request->message) {
            preg_match('/(\d+)\s*pax/i', $request->message, $matches);
            if (!empty($matches[1])) {
                $groupSize = (int) $matches[1];
            }
        }
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

    /**
     * Format AI response into readable quotation text.
     */
    public function formatQuotationText(array $quotationData, $provider, $quotationRequest): string
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
        
        if (isset($q['terms_and_conditions']) && is_array($q['terms_and_conditions'])) {
            $content .= "TERMS & CONDITIONS\n-------------------\n";
            foreach ($q['terms_and_conditions'] as $i => $term) {
                $content .= ($i+1) . ". " . $term . "\n";
            }
            $content .= "\n";
        }
        
        // ✅ Contact Information – provider fallback if N/A
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

    /**
     * Extract JSON from LLM response (handles markdown, extra text, etc.)
     */
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

        // Remove <think> tags and any other unwanted text
        $cleaned = preg_replace('/<think>.*?<\/think>/s', '', $content);
        $cleaned = preg_replace('/<[^>]+>/', '', $cleaned);

        // Find JSON from the first { to the last }
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

        // Try to decode the whole cleaned content
        $decoded = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        Log::error('Failed to extract JSON from quotation response', ['content' => $content]);
        throw new \Exception('Failed to extract JSON from AI response. Please adjust your prompt.');
    }

    /**
     * Send the quotation email to the traveler.
     */
    public function sendQuotationEmail(QuotationRequest $quotationRequest)
    {
        $provider = Auth::user()->getCurrentProvider();
        if (!$provider || $quotationRequest->provider_id !== $provider->id) {
            abort(403, 'Unauthorized.');
        }

        if ($quotationRequest->status !== 'completed' || empty($quotationRequest->quotation_text)) {
            return back()->with('error', 'Quotation not generated yet. Please generate quotation first.');
        }

        // Determine email address
        $email = $quotationRequest->traveler_email ?? $quotationRequest->traveler->email ?? null;

        if (!$email) {
            return back()->with('error', 'No traveler email address found.');
        }

        try {
            // Send email using Mailable
            Mail::to($email)->send(new QuotationMail($quotationRequest));
            return back()->with('success', 'Quotation email sent successfully to traveler.');
        } catch (\Exception $e) {
            Log::error('Failed to send quotation email: ' . $e->getMessage());
            return back()->with('error', 'Failed to send email. Please try again.');
        }
    }
    /**
 * Check if the current provider owns this quotation request.
 */
private function authorizeProvider(QuotationRequest $request): void
{
    $provider = Auth::user()->getCurrentProvider();
    if (!$provider || $request->provider_id !== $provider->id) {
        abort(403, 'Unauthorized.');
    }
}
/**
 * Show the quotation edit form.
 */
public function edit(QuotationRequest $quotationRequest)
{
    $this->authorizeProvider($quotationRequest);
    
    if ($quotationRequest->isQuotationSent()) {
        abort(403, 'Quotation already sent. Cannot edit.');
    }
    
    // AI draft
    $draft = $quotationRequest->quotation_data['quotation'] ?? [];
    
    // Final quotation – handle both structures
    if ($quotationRequest->quotation_final) {
        $finalWrapper = $quotationRequest->quotation_final;
        $final = $finalWrapper['quotation'] ?? [];
        
        // If cost_breakdown wrapper exists, use it; otherwise use root
        if (isset($final['cost_breakdown']) && is_array($final['cost_breakdown'])) {
            $final = $final;
        }
        // else: $final already has items at root
    } else {
        $final = $draft;
    }
    
    // Ensure items exist
    if (!isset($final['cost_breakdown']) && isset($final['items'])) {
        // Items are at root – wrap them
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
/**
 * Update the quotation with provider edits.
 */
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
    ]);
    
    // Recalculate
    $recalculated = $this->recalculateQuotation(
        $validated['items'],
        $validated['discount'] ?? 0
    );
    
    // Build final data with cost_breakdown structure
    $draft = $quotationRequest->quotation_data['quotation'] ?? [];
    $finalData = [
        'greeting' => $draft['greeting'] ?? '',
        'overview' => $draft['overview'] ?? '',
        'day_by_day_breakdown' => $draft['day_by_day_breakdown'] ?? [],
        'cost_breakdown' => $recalculated,
        'terms_and_conditions' => $validated['terms'] ?? $draft['terms_and_conditions'] ?? [],
        'special_notes' => $validated['special_notes'] ?? '',
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
/**
 * Preview the quotation as it will appear in email.
 */
public function preview(QuotationRequest $quotationRequest)
{
    $this->authorizeProvider($quotationRequest);
    
    $provider = Auth::user()->getCurrentProvider();
    
    // Use final if exists, otherwise draft
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
        'preview' => true, // Preview mode (can hide "Visit TravelAI Nepal" button if needed)
    ]);
}
/**
 * Send the final quotation to the traveler.
 */
public function send(Request $request, QuotationRequest $quotationRequest)
{
    $this->authorizeProvider($quotationRequest);
    
    if ($quotationRequest->isQuotationSent()) {
        return response()->json(['error' => 'Quotation already sent.'], 403);
    }
    
    $provider = Auth::user()->getCurrentProvider();
    
    // If no final exists, use draft as final (provider made no changes)
    if (!$quotationRequest->quotation_final) {
        $draft = $quotationRequest->quotation_data['quotation'] ?? [];
        
        // Validate draft has items
        if (empty($draft['cost_breakdown']['items'])) {
            return response()->json([
                'error' => 'No quotation data found. Please generate AI quotation first.'
            ], 400);
        }
        
        // Wrap and save as final
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
    
    // ✅ Recalculate server-side (security: trust nothing from client)
    $finalWrapper = $quotationRequest->quotation_final;
    $finalData = $finalWrapper['quotation'] ?? [];
    
    $recalculated = $this->recalculateQuotation(
        $finalData['cost_breakdown']['items'] ?? [],
        $finalData['discount'] ?? 0
    );
    $finalData['cost_breakdown']['items'] = $recalculated['items'];
    $finalData['cost_breakdown']['grand_total'] = $recalculated['grand_total'];
    $finalData['discount'] = $recalculated['discount'];
    
    // Re-wrap
    $quotationRequest->quotation_final = ['quotation' => $finalData];
    
    // Regenerate text
    $quotationRequest->quotation_text = $this->formatQuotationText(
        ['quotation' => $finalData],
        $provider,
        $quotationRequest
    );
    $quotationRequest->save();
    
    // ✅ Send email FIRST
    $email = $quotationRequest->traveler_email ?? $quotationRequest->traveler->email ?? null;
    if (!$email) {
        return response()->json(['error' => 'No traveler email address found.'], 400);
    }
    
    try {
        Mail::to($email)->send(new \App\Mail\QuotationMail($quotationRequest));
    } catch (\Exception $e) {
        \Log::error('Quotation email failed: ' . $e->getMessage(), [
            'quotation_request_id' => $quotationRequest->id,
        ]);
        return response()->json([
            'error' => 'Email sending failed. Please try again.',
            'debug' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
    
    // ✅ Only now mark as sent
$quotationRequest->quotation_status = 'sent';
$quotationRequest->sent_at = now(); // ✅ Laravel helper le Carbon instance फर्काउँछ
$quotationRequest->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Quotation sent successfully to traveler.',
    ]);
}
/**
 * Recalculate totals server-side.
 * This is a security method – never trust client-calculated totals.
 */
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