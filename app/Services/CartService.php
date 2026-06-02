<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CartService
{
    private const CACHE_TTL = 60; // seconds
    private const COOKIE_TTL = 43200; // 30 days

    public function getCount(): int
    {
        try {
            return $this->calculateCartCount();
        } catch (\Exception $e) {
            Log::error('Error calculating cart count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    private function calculateCartCount(): int
    {
        $cart = session()->get('cart', ['products' => []]);
        if (!isset($cart['products']) || !is_array($cart['products'])) {
            return 0;
        }

        return array_sum(array_column($cart['products'], 'quantity'));
    }

    private function getCacheKey(): string
    {
        $identifier = Auth::id() ?? Cookie::get('persistent_session_id');
        return "cart_count_{$identifier}";
    }

    public function updateCartCount(): void
    {
        Cache::forget($this->getCacheKey());
    }
} 