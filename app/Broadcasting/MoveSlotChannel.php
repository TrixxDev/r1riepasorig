<?php

  namespace App\Broadcasting;

  use App\Models\User;
  use Illuminate\Broadcasting\Channel;
  use Illuminate\Foundation\Events\Dispatchable;
  use Illuminate\Broadcasting\InteractsWithSockets;
  use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

  class MoveSlotChannel implements ShouldBroadcastNow
  {

    use Dispatchable, InteractsWithSockets;

    public $status;
    public $slot;
    public $targetSlot;
    public $slot_queue_id;
    public $slot_iorder;
    public $date;

    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct($status, $slot, $targetSlot, $slot_queue_id, $slot_iorder, $date)
    {
      $this->status = $status;
      $this->slot = $slot;
      $this->targetSlot = $targetSlot;
      $this->slot_queue_id = $slot_queue_id;
      $this->slot_iorder = $slot_iorder;
      $this->date = $date;
    }

    public function broadcastOn()
    {
      return new Channel('slots');
    }

    public function broadcastAs()
    {
      return 'move-slot';
    }
  }
