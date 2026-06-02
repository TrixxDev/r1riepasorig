<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlotReleased implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queueId;
    public $date;
    public $iorder;
    public $reason; // 'cancelled', 'expired', 'booked'

    public function __construct($queueId, $date, $iorder, $reason = 'cancelled')
    {
        $this->queueId = $queueId;
        $this->date = $date;
        $this->iorder = $iorder;
        $this->reason = $reason;
    }

    public function broadcastOn()
    {
        return new Channel('slots');
    }

    public function broadcastAs()
    {
        return 'slot.released';
    }

    public function broadcastWith()
    {
        return [
            'queue_id' => $this->queueId,
            'date' => $this->date,
            'iorder' => $this->iorder,
            'reason' => $this->reason,
        ];
    }
}
