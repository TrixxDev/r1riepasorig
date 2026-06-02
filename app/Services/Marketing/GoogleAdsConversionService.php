<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-side Google Ads conversion uploads via the REST API.
 * Uses Enhanced Conversions for Web (upload click conversions with user identifiers).
 *
 * Required config keys (config/marketing.php → google_ads):
 *   - customer_id        (format: 1234567890, no dashes)
 *   - api_developer_token
 *   - oauth_client_id
 *   - oauth_client_secret
 *   - oauth_refresh_token
 *   - purchase_conversion_action
 *   - booking_conversion_action
 */
class GoogleAdsConversionService
{
    protected ?string $accessToken = null;

    /**
     * Upload a purchase conversion.
     *
     * @param  array{transaction_id:string,value:float,currency:string,email:?string,phone:?string,gclid:?string,conversion_time:?string}  $data
     */
    public function sendPurchaseConversion(array $data): bool
    {
        $action = config('marketing.google_ads.purchase_conversion_action');

        return $this->uploadConversion($action, $data);
    }

    /**
     * Upload a booking (Schedule) conversion.
     *
     * @param  array{transaction_id:string,email:?string,phone:?string,gclid:?string,conversion_time:?string}  $data
     */
    public function sendBookingConversion(array $data): bool
    {
        $action = config('marketing.google_ads.booking_conversion_action');

        return $this->uploadConversion($action, $data);
    }

    protected function uploadConversion(?string $conversionAction, array $data): bool
    {
        $customerId = config('marketing.google_ads.customer_id');

        if (empty($customerId) || empty($conversionAction)) {
            return false;
        }

        $token = $this->getAccessToken();
        if (! $token) {
            return false;
        }

        $conversionTime = $data['conversion_time'] ?? now()->format('Y-m-d H:i:sP');

        $conversion = [
            'conversion_action' => "customers/{$customerId}/conversionActions/{$conversionAction}",
            'conversion_date_time' => $conversionTime,
            'order_id' => $data['transaction_id'] ?? null,
        ];

        if (! empty($data['value'])) {
            $conversion['conversion_value'] = round($data['value'], 2);
            $conversion['currency_code'] = $data['currency'] ?? 'EUR';
        }

        if (! empty($data['gclid'])) {
            $conversion['gclid'] = $data['gclid'];
        }

        $userIdentifiers = [];
        if (! empty($data['email'])) {
            $userIdentifiers[] = [
                'hashed_email' => hash('sha256', strtolower(trim($data['email']))),
            ];
        }
        if (! empty($data['phone'])) {
            $digits = '+'.preg_replace('/\D+/', '', (string) $data['phone']);
            $userIdentifiers[] = [
                'hashed_phone_number' => hash('sha256', $digits),
            ];
        }
        if ($userIdentifiers !== []) {
            $conversion['user_identifiers'] = $userIdentifiers;
        }

        $url = "https://googleads.googleapis.com/v18/customers/{$customerId}:uploadClickConversions";

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token,
                    'developer-token' => config('marketing.google_ads.api_developer_token'),
                ])
                ->asJson()
                ->acceptJson()
                ->post($url, [
                    'conversions' => [$conversion],
                    'partialFailure' => true,
                ]);

            if (! $response->successful()) {
                Log::warning('Google Ads conversion upload failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $body = $response->json();
            if (! empty($body['partialFailureError'])) {
                Log::warning('Google Ads conversion partial failure', [
                    'error' => $body['partialFailureError'],
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Google Ads conversion exception', ['message' => $e->getMessage()]);

            return false;
        }
    }

    protected function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $clientId = config('marketing.google_ads.oauth_client_id');
        $clientSecret = config('marketing.google_ads.oauth_client_secret');
        $refreshToken = config('marketing.google_ads.oauth_refresh_token');

        if (empty($clientId) || empty($clientSecret) || empty($refreshToken)) {
            return null;
        }

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $this->accessToken = $response->json('access_token');

                return $this->accessToken;
            }

            Log::warning('Google Ads OAuth token refresh failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Google Ads OAuth exception', ['message' => $e->getMessage()]);
        }

        return null;
    }
}
