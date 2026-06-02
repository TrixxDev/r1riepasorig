<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workingday extends Model
{

    protected $primaryKey = 'workingday_id';
    public $timestamps = false;

    public function fillWorkingHours(){
      // pagaidām neko nedara...
      $this->weekday = date('N', strtotime($this->date.' 00:00:00'));
      $id = $this->queue_id;
      $d = $this->date;
      $date = date('Y-m-d', strtotime('-7 days', strtotime($d.' 00:00:00')));

      $weekday = $this->weekday;

      $workingDay = Self::where('queue_id', $id)->where('date', $date)->where('weekday', $weekday)->first();
      //      $workingDay = Self::where('queue_id', $id)->where('date', '<', $d)->where('weekday', $weekday)->first();

      if ($workingDay !== NULL){
        $this->slotSize = $workingDay->slotSize;
        $this->secondaryAvailable = $workingDay->secondaryAvailable;
        $this->closetime = $workingDay->closetime;
        $this->opentime = $workingDay->opentime;
        $this->is_visible = $workingDay->is_visible;
        return true;
      } else {
        return false;
      }
    }

    public function isVisible(){
      return ($this->toArray()['is_visible']!=0) && ($this->opentime!=$this->closetime);
    }

    public function isHalf() {
      return ($this->toArray()['secondaryAvailable']!=0);
    }

}
