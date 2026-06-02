<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $expectedKey = (string) config('services.schedulebull.client_api_key');
        if ($expectedKey === '') {
            return response()->json(['message' => 'API key is not configured'], 500);
        }

        $authHeader = (string) $request->headers->get('Authorization', '');
        $apiKeyHeader = (string) $request->headers->get('X-Api-Key', '');

        $token = '';
        if (stripos($authHeader, 'Bearer ') === 0) {
            $token = trim(substr($authHeader, 7));
        } elseif ($apiKeyHeader !== '') {
            $token = trim($apiKeyHeader);
        }

        if ($token === '' || !hash_equals($expectedKey, $token)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
