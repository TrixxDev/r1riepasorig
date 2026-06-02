<?php

namespace App\Http\Middleware;

use App\Support\AgentDebugLog;
use Closure;
use Illuminate\Http\Request;

class SessionTimingDebug
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldSkipDebugLogging($request)) {
            return $next($request);
        }

        $reqT0 = (float) $request->attributes->get('_agent_debug_t0', microtime(true));
        $toSessionMs = (int) round((microtime(true) - $reqT0) * 1000);
        AgentDebugLog::write('B', 'SessionTimingDebug.php:handle', 'session_loaded', [
            'path' => $request->path(),
            'to_session_ms' => $toSessionMs,
            'session_cookie_present' => $request->hasCookie(config('session.cookie')),
            'session_keys' => count($request->session()->all()),
            'authenticated' => auth()->check(),
        ]);

        $handlerT0 = microtime(true);
        $response = $next($request);

        AgentDebugLog::write('B', 'SessionTimingDebug.php:handle', 'handler_after_session_ms', [
            'path' => $request->path(),
            'handler_ms' => (int) round((microtime(true) - $handlerT0) * 1000),
        ]);

        return $response;
    }

    protected function shouldSkipDebugLogging(Request $request): bool
    {
        if (! $request->is('api/rims/*') && ! $request->is('api/tires/*') && ! $request->is('api/moto/*') && ! $request->is('motociklu-riepas/search/api/*')) {
            return false;
        }

        return $request->isMethod('GET');
    }
}
