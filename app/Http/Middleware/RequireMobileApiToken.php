<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Bearer token for internal workshop mobile app (set MOBILE_API_TOKEN in .env).
 */
class RequireMobileApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $expected = (string) config('services.mobile.api_token');
        if ($expected === '') {
            return response()->json(['message' => 'Mobile API is not configured'], 503);
        }

        $authHeader = (string) $request->headers->get('Authorization', '');
        $token = '';
        if (stripos($authHeader, 'Bearer ') === 0) {
            $token = trim(substr($authHeader, 7));
        }

        if ($token === '' || ! hash_equals($expected, $token)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
