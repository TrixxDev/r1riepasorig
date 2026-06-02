<?php

namespace App\Services;

use Throwable;

/**
 * WhatsApp integration diagnostics — dedicated log file, not the default laravel.log.
 * Path: storage/logs/whatsapp_errors.log
 */
final class WhatsappErrorLogger
{
    public static function logFilePath(): string
    {
        return storage_path('logs/whatsapp_errors.log');
    }

    public static function write(string $level, string $message, array $context = []): void
    {
        $line = sprintf(
            "[%s] %s: %s%s\n",
            now()->format('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context !== [] ? ' '.json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );
        try {
            file_put_contents(self::logFilePath(), $line, FILE_APPEND | LOCK_EX);
        } catch (Throwable $e) {
            // last resort: avoid breaking the booking flow
        }
    }

    public static function exception(Throwable $e, string $message, array $context = []): void
    {
        $context['exception'] = $e->getMessage();
        $context['file'] = $e->getFile().':'.$e->getLine();
        $trace = $e->getTraceAsString();
        if (strlen($trace) > 4000) {
            $trace = substr($trace, 0, 4000).'…';
        }
        $context['trace'] = $trace;
        self::write('error', $message, $context);
    }
}
