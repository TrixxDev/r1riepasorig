<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Services\CartService;

class CartUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct()
    {
        app(CartService::class)->updateCartCount();
    }
} 