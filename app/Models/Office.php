<?php

namespace App\Models;

use App\Helper\Tires;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{

    protected $primaryKey = 'office_id';

    public function __construct()
    {
      $this->_queues = false;
      $this->_workingDays = false;
    }

    function loadQueues(){
      $list = Queue::where('office_id', $this->office_id)->orderBy('iorder', 'ASC')->get();
      $this->_queues = $list;
    }

    public function loadMobileQueues() {
      $list = Queue::where('office_id', $this->office_id)->orderBy('queue_id', 'ASC')->get();
      $this->_queues = $list;
    }

    public function loadWorkingDays($date) {
      $queues = [];
      $list = [];
      foreach ($this->_queues->toArray() as $queue) {
        array_push($queues, $queue['queue_id']);
      }
      $lists = Workingday::where('is_visible', 1)->whereRaw('queue_id IN(' . implode(', ', $queues) . ')')->where('date', $date)->get();
      foreach ($lists as $queue) {
        array_push($list, $queue);
      }
      $this->_workingDays = $list;
    }

    public static function intervalByTime($time){
      if (strpos($time, ':') !== false){
        @list($hours, $minutes, $seconds) = explode(':', $time);

        $minutes = $hours * 60 + $minutes;
        $slotNum = floor($minutes / 10);

        return $slotNum;

      }

    }

    public static function timeByInterval($iorder,$padding=true){
      $minutes = $iorder * 10;
      $hours = floor($minutes / 60);
      $minutes = $minutes - ($hours*60);
      if ($padding){
        $time = Tires::zero_pad($hours,2).':'.Tires::zero_pad($minutes,2);
      } else {
        $time = $hours.':'.Tires::zero_pad($minutes,2);
      }
      return $time;
    }

    function getOpenTime($date){
      $minTime = 0;
      foreach ($this->_queues as $queue){
        if ($queue->isVisible($date)){
          if ($minTime==0) {
            $minTime=Queue::intervalByTime($queue->getOpenTime($date));
          } else {
            $minTime= min($minTime,Queue::intervalByTime($queue->getOpenTime($date)));
          }
        }
      }
      return $minTime;
    }

    public function getCloseTime($date){
      $maxTime = 0;
      foreach ($this->_queues as $queue){
        if ($queue->isVisible($date)){
          $maxTime= max($maxTime,Queue::intervalByTime($queue->getCloseTime($date)));
        }
      }
      return $maxTime;
    }

    /**
     * Rindu skaits, ko rāda klientiem (publiskā pieraksta lapa).
     */
    public function clientVisibleQueueCount(): int
    {
        return Queue::where('office_id', $this->office_id)
            ->where(function ($q) {
                $q->where('is_public', 1)->orWhereNull('is_public');
            })
            ->count();
    }

    /**
     * Kopējais klientiem redzamo rindu skaits visos birojos (Bootstrap kolonnu platumam).
     */
    public static function clientVisibleQueueCountSum(): int
    {
        $n = Queue::where(function ($q) {
            $q->where('is_public', 1)->orWhereNull('is_public');
        })->count();
        return max(1, $n);
    }

}
