<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlotBooked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queueId;
    public $date;
    public $iorder;

    public function __construct($queueId, $date, $iorder)
    {
        $this->queueId = $queueId;
        $this->date = $date;
        $this->iorder = $iorder;
    }

    public function broadcastOn()
    {
        return new Channel('slots');
    }

    public function broadcastAs()
    {
        return 'slot.booked';
    }

    public function broadcastWith()
    {
        return [
            'queue_id' => $this->queueId,
            'date' => $this->date,
            'iorder' => $this->iorder,
        ];
    }
}
