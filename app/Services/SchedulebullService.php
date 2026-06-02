<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SchedulebullService
{
    public function carInfo(string $vnr): array
    {
        $normalizedVnr = strtoupper(trim($vnr));
        $baseUrl = config('services.schedulebull.base_url');
        $apiKey = config('services.schedulebull.key');
        $cacheTtlSeconds = (int) config('services.schedulebull.cache_ttl_seconds', 21600);

        if (!$baseUrl || !$apiKey) {
            \Log::warning('Schedulebull car-info: SCHEDULEBULL_API_KEY or SCHEDULEBULL_BASE_URL not set in .env');
            return [
                'ok' => false,
                'status' => 500,
                'message' => 'Upstream API is not configured',
            ];
        }

        $cacheKey = 'schedulebull:car-info:' . $normalizedVnr;
        if (Cache::has($cacheKey)) {
            return [
                'ok' => true,
                'data' => Cache::get($cacheKey),
            ];
        }

        $response = Http::timeout(10)->get($baseUrl, [
            'key' => $apiKey,
            'q' => 'csdd/carInfo',
            'vnr' => $normalizedVnr,
        ]);

        if (!$response->ok()) {
            return [
                'ok' => false,
                'status' => 502,
                'message' => 'Upstream API error',
            ];
        }

        Cache::put($cacheKey, $response->json(), $cacheTtlSeconds);

        return [
            'ok' => true,
            'data' => $response->json(),
        ];
    }
}
