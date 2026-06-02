<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CarInfoTokenService;

class RequireCarInfoToken
{
    public function handle(Request $request, Closure $next)
    {
        $provided = (string) $request->headers->get('X-Car-Info-Token', '');

        if ($provided === '') {
            return $this->forbidden('missing_token');
        }

        $service = app(CarInfoTokenService::class);
        if (!$service->validate($request, $provided)) {
            return $this->forbiddenWithNewToken($request, $service, 'invalid_or_expired_token');
        }

        return $next($request);
    }

    private function forbidden(string $reason)
    {
        $payload = ['message' => 'Forbidden'];
        if (config('app.debug')) {
            $payload['reason'] = $reason;
        }

        return response()->json($payload, 403);
    }

    /**
     * Return 403 but include a fresh token so the client can retry the next request.
     */
    private function forbiddenWithNewToken(Request $request, CarInfoTokenService $service, string $reason)
    {
        $response = $this->forbidden($reason);
        $newToken = $service->issue($request);
        return $response->header('X-Car-Info-Token', $newToken);
    }
}
