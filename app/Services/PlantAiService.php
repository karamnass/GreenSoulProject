<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlantAiService
{
    /**
     * Calls FastAPI POST /predict with multipart field name "file".
     *
     * Returns normalized payload (plant_name/confidence/recommendation/raw_response)
     * or null if AI is down/timeout/invalid response.
     */
    public function predictFromPath(string $absoluteImagePath, ?string $filename = null): ?array
    {
        $baseUrl = (string) config('services.plant_ai.url', 'http://127.0.0.1:8000');
        $timeout = (int) config('services.plant_ai.timeout', 10);

        $predictUrl = rtrim($baseUrl, '/') . '/predict';

        if (! is_file($absoluteImagePath)) {
            Log::warning('PlantAiService: image file not found', [
                'path' => $absoluteImagePath,
            ]);

            return null;
        }

        $filename = $filename ?: basename($absoluteImagePath);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->asMultipart()
                ->attach('file', fopen($absoluteImagePath, 'r'), $filename)
                ->post($predictUrl);

            if (! $response->successful()) {
                Log::warning('PlantAiService: AI non-2xx response', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $predictUrl,
                ]);

                return null;
            }

            $data = $response->json();
            if (! is_array($data)) {
                Log::warning('PlantAiService: AI response is not JSON object', [
                    'body' => $response->body(),
                    'url' => $predictUrl,
                ]);

                return null;
            }

            return [
                'plant_name' => $data['plant_name'] ?? null,
                'confidence' => isset($data['confidence']) ? (float) $data['confidence'] : null,
                'recommendation' => $data['recommendation'] ?? null,
                'raw_response' => $data,
            ];
        } catch (\Throwable $e) {
            Log::warning('PlantAiService: AI request failed', [
                'message' => $e->getMessage(),
                'url' => $predictUrl,
            ]);

            return null;
        }
    }
}
