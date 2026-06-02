<?php

namespace App\Broadcasting;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewSlotChannel implements ShouldBroadcastNow
{

  use Dispatchable, InteractsWithSockets;

  public $slot;
  public $slot_queue_id;
  public $slot_iorder;
  public $date;

  /**
   * Create a new channel instance.
   *
   * @return void
   */
  public function __construct($slot, $slot_queue_id, $slot_iorder, $date)
  {
      $this->slot = $slot;
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
    return 'new-slot';
  }
}
