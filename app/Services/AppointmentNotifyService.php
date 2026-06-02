<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-side notification to Node.js WebSocket backend.
 * Secret stays on server — browser never sees it.
 */
class AppointmentNotifyService
{
    /**
     * Notify appointments table that a record was created/updated.
     * Sends HTTP POST to Node.js with secret; Node broadcasts via WebSocket.
     *
     * @param string $date Y-m-d
     * @param int $columnId queue_id
     * @param int $slotIndex iorder
     * @return bool true if notified (or skipped), false on error
     */
    public function notifyRecordCreated(string $date, int $columnId, int $slotIndex): bool
    {
        $url = config('services.appointment.notify_url');
        $secret = config('services.appointment.socket_secret');

        if (empty($url) || empty($secret)) {
            return true; // skip silently if not configured
        }

        $endpoint = rtrim($url, '/') . '/api/notify-record';

        try {
            $response = Http::timeout(5)->post($endpoint, [
                'date' => $date,
                'columnId' => $columnId,
                'slotIndex' => $slotIndex,
                'secret' => $secret,
            ]);

            if (!$response->successful()) {
                Log::warning('[AppointmentNotify] HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::warning('[AppointmentNotify] Request failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
