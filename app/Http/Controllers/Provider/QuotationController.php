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
            'service_id' => 'nullable|exists:services,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->aiLimit->checkAndIncrement($provider);
            $prompt = $this->buildQuotationPrompt($provider, $validated);
            
            // ✅ Quotation को लागि 'qwen/qwen3.6-27b' Model प्रयोग गर्ने
            $response = $this->llm->generateItinerary($prompt, 'en', 'qwen/qwen3.6-27b');
            
            $quotation = $this->formatQuotation($response, $provider, $validated);

            return response()->json([
                'success' => true,
                'quotation' => $quotation,
            ]);

        } catch (\Exception $e) {
            Log::error('Quotation generation failed', [
                'error' => $e->getMessage(),
                'provider_id' => $provider->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function buildQuotationPrompt($provider, $data): string
    {
        $serviceName = $data['service_id'] 
            ? Service::find($data['service_id'])->name ?? 'N/A'
            : 'N/A';

        return "Generate a professional quotation for a customer named '{$data['customer_name']}'.
        
Provider: {$provider->name}
Service: {$serviceName}
Additional notes: " . ($data['notes'] ?? 'None') . "

Please provide:
1. A warm greeting
2. Service overview (duration, participants, description)
3. Pricing breakdown (currency, items with unit price, quantity, total, subtotal, tax, grand total)
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
                    foreach (($pricing['items'] ?? []) as $item) {
                        $content .= sprintf(
                            "%s: %s %s (x%d) = %s %s\n",
                            $item['description'] ?? 'Item',
                            $currency,
                            number_format($item['unit_price'] ?? 0, 2),
                            $item['quantity'] ?? 1,
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