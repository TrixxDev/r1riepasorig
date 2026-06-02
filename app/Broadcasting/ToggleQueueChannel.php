<?php

  namespace App\Broadcasting;

  use Illuminate\Broadcasting\Channel;
  use Illuminate\Foundation\Events\Dispatchable;
  use Illuminate\Broadcasting\InteractsWithSockets;
  use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
  use Illuminate\Queue\SerializesModels;

  class ToggleQueueChannel implements ShouldBroadcastNow
  {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $date;
    public $queue_id;
    public $show;

    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct($date, $queue_id, $show)
    {
      $this->date = $date;
      $this->queue_id = $queue_id;
      $this->show = $show;
    }

    public function broadcastOn()
    {
      return new Channel('queue');
    }

    public function broadcastAs()
    {
      return 'toggleQueue';
    }
  }
