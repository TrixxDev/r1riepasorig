<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CarInfoTokenService
{
    private const COOKIE_NAME = 'car_info_token_state';
    private const TOKEN_TTL_SECONDS = 600;

    /**
     * Issue a short-lived, single-use token bound to the current session.
     * Format: 32-hex (128-bit) random string.
     */
    public function issue(Request $request): string
    {
        $token = bin2hex(random_bytes(16)); // 32-hex
        $state = json_encode(['t' => $token, 'ts' => time()], JSON_UNESCAPED_SLASHES);

        // Cookie will be encrypted/signed by EncryptCookies middleware.
        // Keep it HttpOnly; SameSite Lax is sufficient for same-site XHR.
        Cookie::queue(cookie(
            self::COOKIE_NAME,
            $state,
            (int) ceil(self::TOKEN_TTL_SECONDS / 60),
            '/',
            null,
            true,
            true,
            false,
            'Lax'
        ));

        return $token;
    }

    /**
     * Validate and consume a token (single-use).
     */
    public function validate(Request $request, string $token): bool
    {
        $token = trim($token);
        if ($token === '' || strlen($token) !== 32 || !ctype_xdigit($token)) {
            return false;
        }

        $raw = (string) $request->cookie(self::COOKIE_NAME, '');
        if ($raw === '') {
            return false;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['t'], $decoded['ts'])) {
            return false;
        }

        $issuedToken = (string) $decoded['t'];
        $ts = $decoded['ts'];
        if (!hash_equals($issuedToken, $token) || !is_int($ts)) {
            return false;
        }

        $now = time();
        if ($ts > $now || ($now - $ts) > self::TOKEN_TTL_SECONDS) {
            return false;
        }

        // Optional "single-use": invalidate immediately by overwriting cookie with empty state.
        // Next request must obtain a fresh token (via /pieraksts or rotated token response).
        Cookie::queue(cookie(
            self::COOKIE_NAME,
            '',
            -1,
            '/',
            null,
            true,
            true,
            false,
            'Lax'
        ));

        return true;
    }
}

