<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlotReserved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queueId;
    public $date;
    public $iorder;
    public $reservedBy;
    public $reservedUntil;
    public $sessionId;

    public function __construct($queueId, $date, $iorder, $reservedBy, $reservedUntil, $sessionId)
    {
        $this->queueId = $queueId;
        $this->date = $date;
        $this->iorder = $iorder;
        $this->reservedBy = $reservedBy;
        $this->reservedUntil = $reservedUntil;
        $this->sessionId = $sessionId;
    }

    public function broadcastOn()
    {
        return new Channel('slots');
    }

    public function broadcastAs()
    {
        return 'slot.reserved';
    }

    public function broadcastWith()
    {
        return [
            'queue_id' => $this->queueId,
            'date' => $this->date,
            'iorder' => $this->iorder,
            'reserved_by' => $this->reservedBy,
            'reserved_until' => $this->reservedUntil,
            'session_id' => $this->sessionId,
        ];
    }
}
