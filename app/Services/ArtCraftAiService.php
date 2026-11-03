<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ArtCraftAiService
{
    public function __construct(
        protected LlmService $llm,
        protected AiLimitService $aiLimit
    ) {}

    public function productDescription(Provider $provider): array
    {
        $reservation = null;
        try {
            $artworks = DB::table('artworks')->where('provider_id', $provider->id)->get();
            if ($artworks->isEmpty()) return ['error' => 'no_artworks', 'message' => 'Add artworks first.'];

            $stats = [];
            foreach ($artworks as $a) {
                $stats[] = [
                    'name' => (string) $a->name,
                    'category' => (string) $a->category,
                    'artist' => (string) $a->artist_name,
                    'price' => (float) $a->price,
                    'craftsmanship' => (string) ($a->craftsmanship_level ?? 'skilled'),
                ];
            }

            $reservation = $this->aiLimit->reserve($provider, 'artcraft.description', []);
            if (!$reservation) return ['items' => [], 'summary' => 'Quota reached.', 'fallback' => true];

            $prompt = "You are a professional art catalog copywriter. For each artwork below, write ONE short description (2 sentences max, 30-50 words).\n"
                . "Highlight: material, technique, cultural significance (Nepali handicraft context). Do NOT invent details.\n"
                . "Artworks:\n" . json_encode($stats) . "\n\n"
                . "Return JSON: {\"items\":[{\"name\":\"..\",\"description\":\"..\"}],\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 2500, 0.4, true);
            $data = $this->parseJson($raw);

            if (!$data || empty($data['items'])) {
                $this->aiLimit->release($reservation);
                return ['items' => [], 'summary' => 'AI parse failed.', 'fallback' => true];
            }

            $this->aiLimit->finalize($reservation);
            return [
                'items' => $data['items'],
                'summary' => (string) ($data['summary'] ?? 'Descriptions ready.'),
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('ArtCraftAiService productDescription failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Description service unavailable.'];
        }
    }

    public function pricingAssistant(Provider $provider): array
    {
        $reservation = null;
        try {
            $artworks = DB::table('artworks')->where('provider_id', $provider->id)->get();
            if ($artworks->isEmpty()) return ['error' => 'no_artworks', 'message' => 'Add artworks first.'];

            $craftMultiplier = ['expert' => 1.5, 'skilled' => 1.25, 'apprentice' => 1.1];
            $suggestions = [];

            foreach ($artworks as $a) {
                $material = (float) ($a->material_cost ?? 0);
                $labor = (float) ($a->labor_cost ?? 0);
                $craft = (string) ($a->craftsmanship_level ?? 'skilled');
                $baseCost = $material + $labor;

                // Deterministic: base_cost × craft_multiplier × 1.3 margin
                $suggested = (int) round($baseCost * ($craftMultiplier[$craft] ?? 1.25) * 1.3);
                $currentPrice = (float) $a->price;
                $delta = $currentPrice > 0 ? round((($suggested - $currentPrice) / $currentPrice) * 100, 1) : 0;

                $status = 'ok';
                if ($delta > 20) $status = 'underpriced';
                elseif ($delta < -20) $status = 'overpriced';

                $suggestions[] = [
                    'name' => (string) $a->name,
                    'category' => (string) $a->category,
                    'current_price' => (int) $currentPrice,
                    'material_cost' => (int) $material,
                    'labor_cost' => (int) $labor,
                    'craftsmanship' => $craft,
                    'suggested_price' => $suggested,
                    'delta_pct' => $delta,
                    'status' => $status,
                    'reason' => sprintf('Base cost Rs %s × %s craft (%.2f×) × 1.30 markup = Rs %s',
                        number_format($baseCost), $craft, $craftMultiplier[$craft] ?? 1.25, number_format($suggested)),
                ];
            }

            $warn = count(array_filter($suggestions, fn($s) => $s['status'] !== 'ok')) === 0
                ? 'All prices within ±20% of deterministic estimate — no action needed.'
                : null;

            $reservation = $this->aiLimit->reserve($provider, 'artcraft.pricing', []);
            if (!$reservation) {
                return ['items' => $suggestions, 'summary' => 'Deterministic baseline (quota reached).', 'data_warning' => $warn, 'fallback' => true];
            }

            $prompt = "You are an art pricing advisor. Deterministic estimate = base_cost × craft_multiplier × 1.3 margin.\n"
                . "Artworks:\n" . json_encode($suggestions) . "\n\n"
                . "Provide ONE summary sentence on which items need price adjustment (underpriced/overpriced). "
                . "Do NOT contradict the deterministic suggestions. "
                . "Return JSON: {\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 1500, 0.3, true);
            $data = $this->parseJson($raw);

            if (!$data) {
                $this->aiLimit->release($reservation);
                return ['items' => $suggestions, 'summary' => 'Deterministic baseline (AI failed).', 'data_warning' => $warn];
            }

            $this->aiLimit->finalize($reservation);
            return [
                'items' => $suggestions,
                'summary' => (string) ($data['summary'] ?? 'Pricing analysis ready.'),
                'data_warning' => $warn,
                'assumptions' => ['formula' => 'base_cost × craft_multiplier × 1.30', 'markup' => '30% (markup, not profit margin)'],
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('ArtCraftAiService pricingAssistant failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Pricing assistant unavailable.'];
        }
    }

    protected function parseJson(string $raw): ?array
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/', '', $raw);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
}