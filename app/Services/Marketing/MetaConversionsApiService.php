<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaConversionsApiService
{
    public function sendEvent(array $payload): bool
    {
        $pixelId = config('marketing.meta.pixel_id');
        $token = config('marketing.meta.capi_access_token');

        if (empty($pixelId) || empty($token)) {
            return false;
        }

        $testCode = config('marketing.meta.capi_test_event_code');
        $url = 'https://graph.facebook.com/v21.0/'.$pixelId.'/events';

        try {
            $jsonBody = ['data' => [$payload]];
            if (! empty($testCode)) {
                $jsonBody['test_event_code'] = $testCode;
            }

            $response = Http::timeout(15)
                ->acceptJson()
                ->asJson()
                ->post($url.'?access_token='.urlencode($token), $jsonBody);

            if (! $response->successful()) {
                Log::warning('Meta CAPI '.$payload['event_name'].' failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Meta CAPI '.$payload['event_name'].' exception', ['message' => $e->getMessage()]);

            return false;
        }
    }

    /** @deprecated Use sendEvent() directly */
    public function sendPurchaseEvent(array $payload): bool
    {
        return $this->sendEvent($payload);
    }

    /**
     * @param  array{event_id:string,event_time:int,value:float,currency:string,contents:array,email:?string,phone:?string,client_ip:?string,user_agent:?string}  $data
     */
    public function sendPurchaseFromOrderData(array $data): bool
    {
        $userData = $this->buildUserData($data);

        $payload = [
            'event_name' => 'Purchase',
            'event_time' => $data['event_time'],
            'event_id' => $data['event_id'],
            'action_source' => 'website',
            'user_data' => $userData,
            'custom_data' => [
                'currency' => $data['currency'],
                'value' => round($data['value'], 2),
                'contents' => $data['contents'],
                'content_type' => 'product',
            ],
        ];

        return $this->sendEvent($payload);
    }

    /**
     * @param  array{event_id:string,event_time:int,email:?string,phone:?string,client_ip:?string,user_agent:?string}  $data
     */
    public function sendScheduleEvent(array $data): bool
    {
        $userData = $this->buildUserData($data);

        $payload = [
            'event_name' => 'Schedule',
            'event_time' => $data['event_time'],
            'event_id' => $data['event_id'],
            'action_source' => 'website',
            'user_data' => $userData,
            'custom_data' => [
                'content_name' => 'e_pieraksts',
            ],
        ];

        return $this->sendEvent($payload);
    }

    protected function buildUserData(array $data): array
    {
        $userData = [];
        if (! empty($data['email'])) {
            $userData['em'] = [hash('sha256', strtolower(trim($data['email'])))];
        }
        if (! empty($data['phone'])) {
            $digits = preg_replace('/\D+/', '', (string) $data['phone']);
            if ($digits !== '') {
                $userData['ph'] = [hash('sha256', $digits)];
            }
        }
        if (! empty($data['client_ip'])) {
            $userData['client_ip_address'] = $data['client_ip'];
        }
        if (! empty($data['user_agent'])) {
            $userData['client_user_agent'] = $data['user_agent'];
        }
        if (! empty($data['fbp'])) {
            $userData['fbp'] = $data['fbp'];
        }
        if (! empty($data['fbc'])) {
            $userData['fbc'] = $data['fbc'];
        }

        return $userData;
    }
}
