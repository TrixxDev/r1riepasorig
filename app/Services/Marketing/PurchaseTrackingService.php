<?php

namespace App\Services\Marketing;

use App\Helper\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseTrackingService
{
    /** @var MetaConversionsApiService */
    protected $metaCapi;

    public function __construct(MetaConversionsApiService $metaCapi)
    {
        $this->metaCapi = $metaCapi;
    }

    /**
     * @param  object  $order  Row from orders_
     */
    public function buildClientPayload($order): array
    {
        $details = Utility::decode_info($order->order_details);
        $currency = 'EUR';
        $value = (float) ($order->total_price ?? 0);
        $contents = $this->buildContents($details);
        $eventId = 'purchase_'.$order->id;

        $emailSha256 = null;
        if (! empty($order->email)) {
            $emailSha256 = hash('sha256', strtolower(trim($order->email)));
        }

        $payload = [
            'event_id' => $eventId,
            'value' => round($value, 2),
            'currency' => $currency,
            'contents' => $contents,
            'transaction_id' => (string) ($order->order_number ?? $order->id),
            'enhanced_conversion_email_sha256' => $emailSha256,
        ];

        $adsId = trim((string) config('marketing.google_ads.conversion_id', ''), " \t\n\r\0\x0B\"'");
        $purchaseLabel = trim((string) config('marketing.google_ads.conversion_label', ''), " \t\n\r\0\x0B\"'");
        if ($adsId !== '' && $purchaseLabel !== '') {
            $payload['google_ads_send_to'] = $adsId.'/'.$purchaseLabel;
        }

        return $payload;
    }

    /**
     * @param  object  $order
     */
    public function dispatchServerPurchase($order, Request $request): void
    {
        $details = Utility::decode_info($order->order_details);
        $contents = $this->buildContents($details);
        $value = (float) ($order->total_price ?? 0);

        $phone = '';
        if (isset($order->phone_country_code, $order->phone_number)) {
            $phone = preg_replace('/\D+/', '', (string) $order->phone_country_code.$order->phone_number);
        }

        $this->metaCapi->sendPurchaseFromOrderData([
            'event_id' => 'purchase_'.$order->id,
            'event_time' => time(),
            'value' => $value,
            'currency' => 'EUR',
            'contents' => $contents,
            'email' => $order->email ?? null,
            'phone' => $phone ?: null,
            'client_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'fbp' => $request->cookie('_fbp'),
            'fbc' => $request->cookie('_fbc'),
        ]);
    }

    /**
     * @param  mixed  $details  decoded order_details
     */
    protected function buildContents($details): array
    {
        $out = [];
        if (! $details || ! isset($details->products)) {
            return $out;
        }

        $products = $details->products;
        if (is_array($products)) {
            foreach ($products as $key => $item) {
                $out[] = $this->contentRow($key, $item);
            }
        } elseif (is_object($products)) {
            foreach ($products as $key => $item) {
                $out[] = $this->contentRow($key, $item);
            }
        }

        return $out;
    }

    /**
     * @param  string|int  $key  tire id from cart key
     * @param  mixed  $item
     */
    protected function contentRow($key, $item): array
    {
        $qty = isset($item->quantity) ? (int) $item->quantity : 1;
        $price = isset($item->price) ? (float) $item->price : 0.0;
        $id = (string) $key;
        if ($id === '' && isset($item->id)) {
            $id = (string) $item->id;
        }

        return [
            'id' => $id !== '' ? $id : 'item_'.md5(json_encode($item)),
            'quantity' => max(1, $qty),
            'item_price' => round($price, 2),
        ];
    }
}
