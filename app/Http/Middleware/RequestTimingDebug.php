<?php

namespace App\Http\Middleware;

use App\Support\AgentDebugLog;
use Closure;
use Illuminate\Http\Request;

class RequestTimingDebug
{
    public function handle(Request $request, Closure $next)
    {
        $t0 = microtime(true);
        $request->attributes->set('_agent_debug_t0', $t0);

        if ($this->shouldSkipDebugLogging($request)) {
            return $next($request);
        }

        AgentDebugLog::write('D', 'RequestTimingDebug.php:handle:start', 'request_start', [
            'method' => $request->method(),
            'path' => $request->path(),
            'session_driver' => config('session.driver'),
        ]);

        $response = $next($request);

        $ms = (int) round((microtime(true) - $t0) * 1000);
        AgentDebugLog::write('D', 'RequestTimingDebug.php:handle:end', 'request_end', [
            'method' => $request->method(),
            'path' => $request->path(),
            'total_ms' => $ms,
            'status' => $response->getStatusCode(),
        ]);

        if ($ms >= 3000) {
            AgentDebugLog::write('D', 'RequestTimingDebug.php:handle:slow', 'slow_request', [
                'path' => $request->path(),
                'total_ms' => $ms,
            ]);
        }

        AgentDebugLog::notifySlowRequestIfNeeded($ms, [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'session_driver' => config('session.driver'),
        ]);

        return $response;
    }

    public function terminate($request, $response): void
    {
        if ($request instanceof Request && $this->shouldSkipDebugLogging($request)) {
            return;
        }

        $reqT0 = (float) $request->attributes->get('_agent_debug_t0', microtime(true));
        $terminateMs = (int) round((microtime(true) - $reqT0) * 1000);
        AgentDebugLog::write('B', 'RequestTimingDebug.php:terminate', 'after_response_sent', [
            'path' => $request->path(),
            'terminate_ms' => $terminateMs,
        ]);

        AgentDebugLog::notifySlowRequestIfNeeded($terminateMs, [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'session_driver' => config('session.driver'),
            'phase' => 'terminate',
        ]);
    }

    protected function shouldSkipDebugLogging(Request $request): bool
    {
        if (! $request->is('api/rims/*') && ! $request->is('api/tires/*') && ! $request->is('api/moto/*') && ! $request->is('motociklu-riepas/search/api/*')) {
            return false;
        }

        return $request->isMethod('GET');
    }
}
