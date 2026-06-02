<?php

  namespace App\Broadcasting;

  use Illuminate\Broadcasting\Channel;
  use Illuminate\Foundation\Events\Dispatchable;
  use Illuminate\Broadcasting\InteractsWithSockets;
  use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
  use Illuminate\Queue\SerializesModels;

  class ChangeQueueChannel implements ShouldBroadcastNow
  {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $date;
    public $queue_id;
    public $workingDayVisible;
    public $show;

    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct($date, $queue_id, $workingDayVisible, $show)
    {
      $this->date = $date;
      $this->queue_id = $queue_id;
      $this->workingDayVisible = $workingDayVisible;
      $this->show = $show;
    }

    public function broadcastOn()
    {
      return new Channel('queue');
    }

    public function broadcastAs()
    {
      return 'changeQueueHalf';
    }
  }
