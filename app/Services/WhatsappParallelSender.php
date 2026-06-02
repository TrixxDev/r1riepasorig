<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Secondary WhatsApp provider (JSON API with X-API-Key).
 * Recipient JID/phone is chosen by the caller (FillSlotBookingNotifications, ShopController).
 */
class WhatsappParallelSender
{
    /**
     * Resolves test_mode from config. Strings like "false" from a stale config cache are treated as off.
     */
    public static function isTestMode(): bool
    {
        $v = config('services.whatsapp_parallel.test_mode', true);

        return (bool) filter_var($v, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Parallel HTTP API: needs explicit enabled + non-empty API key. Uses filter_var for cached string booleans.
     */
    public static function isParallelApiEnabled(): bool
    {
        $cfg = config('services.whatsapp_parallel', []);
        $key = $cfg['api_key'] ?? '';
        if (! is_string($key) || trim($key) === '') {
            return false;
        }

        return (bool) filter_var($cfg['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * `WHATSAPP_PARALLEL_TEST_TO` from config. Never use blind (string) cast: (string) true === "1" in PHP.
     */
    public static function testToStringFromConfig($raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }
        if (is_bool($raw)) {
            WhatsappErrorLogger::write('error', 'whatsapp_parallel.test_to is boolean in config (wrong merge or typo). Set a string in .env, e.g. WHATSAPP_PARALLEL_TEST_TO=+37128334474 — never the word "true" alone.', [
                'test_to_type' => 'bool',
            ]);

            return '';
        }
        $s = trim((string) $raw);
        if ($s === '1') {
            WhatsappErrorLogger::write('error', 'whatsapp_parallel.test_to is the literal "1" (typo or bad env). Use full E.164, e.g. +37128334474.', [
                'test_to' => $s,
            ]);

            return '';
        }

        return $s;
    }

    /**
     * API expects E.164 (+[country][number]) or WhatsApp group JID: digits@g.us
     * Normalizes: strips spaces, adds leading + for common LV form 371XXXXXXXX
     */
    public static function normalizeAndValidate(string $to): ?string
    {
        $t = preg_replace('/\s+/', '', trim($to));
        if ($t === '') {
            return null;
        }

        if (strpos($t, '@g.us') !== false) {
            return preg_match('/^\d+@g\.us$/', $t) ? $t : null;
        }

        if (isset($t[0]) && $t[0] === '+') {
            return preg_match('/^\+[1-9]\d{1,14}$/', $t) ? $t : null;
        }

        if (preg_match('/^371[0-9]{8}$/', $t)) {
            return '+'.$t;
        }

        return null;
    }

    /**
     * @return null if nothing was sent; true on HTTP 2xx; false on error response or exception
     */
    public function send(string $to, string $text): ?bool
    {
        $cfg = config('services.whatsapp_parallel', []);
        if (! self::isParallelApiEnabled()) {
            return null;
        }

        if ($to === '') {
            WhatsappErrorLogger::write('warning', 'WhatsappParallelSender: empty `to` recipient, skip', [
                'send_url' => $cfg['send_url'] ?? null,
            ]);

            return null;
        }

        $toRaw = $to;
        $to = self::normalizeAndValidate($to);
        if ($to === null) {
            $tm = self::isTestMode();
            WhatsappErrorLogger::write('error', 'WhatsappParallelSender: invalid `to` (E.164 +... or digits@g.us). See context: if is_test_mode true, fix WHATSAPP_PARALLEL_TEST_TO; if false, fix services.whatsapp_parallel wpp_group_* (or WHATSAPP_WPP_JID_* in .env).', [
                'to_raw' => $toRaw,
                'to_raw_length' => strlen($toRaw),
                'is_test_mode' => $tm,
                'config_test_mode' => config('services.whatsapp_parallel.test_mode'),
                'config_test_to' => config('services.whatsapp_parallel.test_to'),
                'hint' => 'CLI tinker and php-fpm can differ: run `php artisan config:clear` and restart php-fpm + queue workers.',
            ]);

            return false;
        }

        $url = $cfg['send_url'] ?? '';
        if ($url === '') {
            WhatsappErrorLogger::write('error', 'WhatsappParallelSender: services.whatsapp_parallel.send_url is empty', []);

            return null;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-API-Key' => $cfg['api_key'],
                ])
                ->post($url, [
                    'to' => $to,
                    'type' => 'text',
                    'content' => [
                        'text' => $text,
                    ],
                ]);

            if (! $response->successful()) {
                WhatsappErrorLogger::write('error', 'WhatsappParallelSender: API non-success', [
                    'url' => $url,
                    'to' => $to,
                    'is_test_mode' => self::isTestMode(),
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            WhatsappErrorLogger::exception($e, 'WhatsappParallelSender: HTTP request failed', [
                'url' => $url,
                'to' => $to,
            ]);

            return false;
        }
    }
}
