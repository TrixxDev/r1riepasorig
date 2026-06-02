<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictToDomain
{
    public function handle(Request $request, Closure $next)
    {
        $allowedHosts = [];
        $appUrlHost = parse_url(config('app.url'), PHP_URL_HOST);
        if ($appUrlHost) {
            $allowedHosts[] = $appUrlHost;
        }

        $originHost = $this->getHeaderHost($request->headers->get('Origin'));
        $refererHost = $this->getHeaderHost($request->headers->get('Referer'));

        if ($originHost && in_array($originHost, $allowedHosts, true)) {
            return $next($request);
        }

        if (!$originHost && $refererHost && in_array($refererHost, $allowedHosts, true)) {
            return $next($request);
        }

        $payload = ['message' => 'Forbidden'];
        if (config('app.debug')) {
            $payload['reason'] = 'origin_or_referer_not_allowed';
        }
        return response()->json($payload, 403);

    }

    private function getHeaderHost(?string $headerValue): ?string
    {
        if (!$headerValue) {
            return null;
        }

        return parse_url($headerValue, PHP_URL_HOST);
    }
}
