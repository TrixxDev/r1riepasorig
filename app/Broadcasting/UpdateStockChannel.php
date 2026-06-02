<?php

namespace App\Broadcasting;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class UpdateStockChannel implements ShouldBroadcastNow
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tire;
    public $stock;
    public $totalStock;
    public $dot;

    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct($tire, $stock, $totalStock, $dot)
    {
        $this->tire = $tire;
        $this->stock = $stock;
        $this->totalStock = $totalStock;
        $this->dot = $dot;
    }

    public function broadcastOn(): PrivateChannel
    {
      return new PrivateChannel('stocks');
    }

    public function broadcastAs()
    {
      return 'update-stock';
    }
}
