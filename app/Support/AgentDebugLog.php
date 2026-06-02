<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class AgentDebugLog
{
    private const LOG_FILE = 'debug-06f91d.log';
    private const SESSION_ID = '06f91d';
    private const ALERT_CACHE_KEY = 'r1_slow_request_alert_cooldown';

    public static function write(string $hypothesisId, string $location, string $message, array $data = [], string $runId = 'pre-fix'): void
    {
        // #region agent log
        try {
            $payload = [
                'sessionId' => self::SESSION_ID,
                'runId' => $runId,
                'hypothesisId' => $hypothesisId,
                'location' => $location,
                'message' => $message,
                'data' => $data,
                'timestamp' => (int) round(microtime(true) * 1000),
            ];
            file_put_contents(
                base_path(self::LOG_FILE),
                json_encode($payload, JSON_UNESCAPED_UNICODE)."\n",
                FILE_APPEND | LOCK_EX
            );
        } catch (\Throwable $e) {
            // ignore debug logging failures
        }
        // #endregion
    }

    public static function notifySlowRequestIfNeeded(int $totalMs, array $context): void
    {
        $email = trim((string) env('SLOW_REQUEST_ALERT_EMAIL', ''));
        if ($email === '') {
            return;
        }

        $thresholdMs = (int) env('SLOW_REQUEST_ALERT_MS', 60000);
        if ($totalMs < $thresholdMs) {
            return;
        }

        $cooldownSec = max(60, (int) env('SLOW_REQUEST_ALERT_COOLDOWN_SEC', 900));
        if (Cache::has(self::ALERT_CACHE_KEY)) {
            return;
        }

        try {
            $body = implode("\n", [
                'Обнаружен медленный запрос на r1riepas.lv',
                '',
                'Длительность: '.$totalMs.' ms (порог: '.$thresholdMs.' ms)',
                'Метод: '.($context['method'] ?? '?'),
                'Путь: '.($context['path'] ?? '?'),
                'HTTP статус: '.($context['status'] ?? '?'),
                'Session driver: '.($context['session_driver'] ?? '?'),
                'Фаза: '.($context['phase'] ?? 'handle'),
                '',
                'Лог: '.base_path(self::LOG_FILE),
            ]);

            Mail::raw($body, function ($message) use ($email, $totalMs) {
                $message->to($email)->subject('[R1] Медленный запрос '.$totalMs.' ms');
            });

            Cache::put(self::ALERT_CACHE_KEY, time(), $cooldownSec);
            self::write('E', 'AgentDebugLog.php:notifySlowRequest', 'alert_sent', [
                'total_ms' => $totalMs,
                'path' => $context['path'] ?? null,
            ]);
        } catch (\Throwable $e) {
            self::write('E', 'AgentDebugLog.php:notifySlowRequest', 'alert_failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
