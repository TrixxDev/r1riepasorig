<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helper\Tires;

class Queue extends Model
{

    protected $primaryKey = 'queue_id';

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public $_slots;		// ielādētie sloti, Slots tipa objektu saraksts
    public $_takenSlots;
    public $_workingDays;
    public $notificationEmail;

    public function __construct()
    {
      $this->_slots = [];				// ielādētie sloti, CQueueSlot tipa objektu saraksts
      $this->_workingDays = [];		// ielādētie darba laiki, CWorkingDay tipa objektu saraksts
    }

    public function isAvailableForPublicBooking(): bool
    {
        if (! array_key_exists('is_public', $this->attributes) || $this->attributes['is_public'] === null) {
            return true;
        }

        return (int) $this->attributes['is_public'] === 1;
    }

    public function scopeForPublicSite($query)
    {
        return $query->where(function ($q) {
            $q->where('is_public', 1)->orWhereNull('is_public');
        });
    }

    public function isVisible($date){
      $day=$this->_workingDays[$date];
      return ($day->is_visible)!=0;
//      return ($day->isVisible());
    }

    public function loadWorkingDay($date,$allowCreate=false){
      $id = $this->queue_id;
      $workingday = Workingday::where('queue_id', $id)->where('date', $date)->get();

      if (count($workingday) > 0) {
        $day = $workingday[0];
      } else {
        $day = new Workingday();
        $day->timestamps = false;
        $day->queue_id = $this->queue_id;
        $day->date = $date;

        if (!$day->fillWorkingHours($this)){
          $day->secondaryAvailable = 0;
          $day->slotSize = 2;
          $day->opentime = $this->opentime;
          $day->closetime = $this->closetime;
          $day->is_visible = 1;
        }

        if ($allowCreate) $day->save();
      }
      $this->_workingDays[$date] = $day;
      return $day;
    }

    public function loadSlots($date,$allowCreate=false){
      //$query = new CQuery();
      $list = Slot::where('queue_id', $this->queue_id)->where('date', $date)->get();

      //$this->_slots = array();
      //$slotCount = ceil(1440 / $this->slotSize);
      $slotCount = $this->getWorkingDayLength($date);

      foreach ($list as $object){
        $this->_slots[$date][$object->iorder] = $object;
      }


      //$takenBy = json_encode(new CBookingForm());
      $takenBy = '';
      for ($i = 0; $i<$slotCount;$i++){
        if (!isset($this->_slots[$date][$i])) {
          $slot = new Slot;
          $slot->timestamps = false;
          $slot->queue_id = $this->queue_id;
          $slot->date = $date;
          $slot->iorder = $i;
          $slot->status = 0;
          $slot->status2 = 0;
          $slot->takenby = $takenBy;
          $slot->takenby2 = $takenBy;
          if ($allowCreate) $slot->save();
          $this->_slots[$date][$i] = $slot;
        }
      }

    }

    public function getSlots($date, $first = false){
      if (empty($first)) {
        $_queues = [];
        $queues = Queue::select('queue_id')->where('office_id', $this->office_id)->get();
        foreach ($queues as $queue) {
          $_queues[] = $queue->queue_id;
        }
        $list = Slot::where('date', $date)->where('status', 0)->whereIn('queue_id', $_queues)->orderBy('queue_id', 'ASC')->get();
      } else {
        $list = Slot::where('date', $date)->where('status', 1)->where('queue_id', $first)->get();
      }

      foreach ($list as $object){
        $this->_slots[$date][$object->slot_id] = $object;
      }

      $takenBy = '';

      foreach ($this->_slots[$date] as $key => $i) {
        if(!isset($this->_slots[$date][$i->slot_id])) {
          $slot = new Slot();
          $slot->timestamps = false;
          $slot->queue_id = $this->queue_id;
          $slot->date = $date;
          $slot->iorder = $key;
          $slot->status = '';
          $slot->status2 = '';
          $slot->takenby = $takenBy;
          $slot->takenby2 = $takenBy;
          $this->_slots[$date][$i->slot_id] = $slot;
        }
      }

    }

    public function loadWeekDays($startdate,$days=false){
      $id = $this->queue_id;
      $date = $startdate;

      if ($days===false){
        $workingDayList = Workingday::where('queue_id', $id)->where('date', '>=', $date)->where('weekday', date('N', strtotime($startdate.' 00:00:00')))->get();
      } else {
        $workingDayList = Workingday::where('queue_id', $id)->where('date', '>=', $date)->whereIn('weekday', $days)->get();
      }


      foreach ($workingDayList as $workingDay){
        $this->_workingDays[$workingDay->date] = $workingDay;
      }
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

    public function getOpenTime($date){
        $day=$this->_workingDays[$date];
        return $day['opentime'];
    }

    public function getCloseTime($date){
        $day=$this->_workingDays[$date];
        return $day['closetime'];
    }

    public function getWorkingDayLength($date){
        $start = self::intervalByTime($this->getOpenTime($date));
        $end = self::intervalByTime($this->getCloseTime($date));

        if ($start === NULL) return true;
        if ($end === NULL) return true;

        $length = floor(($end-$start) / $this->_workingDays[$date]['slotSize']);
        return $length;
    }

    public function getSlotNumberByInterval($date,$interval){
        $day = $this->_workingDays[$date];
        $start = self::intervalByTime($day['opentime']);
        $interval = $interval - $start;
        if ($interval < 0) return false;
        $slotNum = floor($interval / $this->_workingDays[$date]->slotSize);
        if ($slotNum < 0) return false;
        if ($slotNum >= $this->getWorkingDayLength($date)) return false;
        return $slotNum;
    }

    public function getSlotStartInterval($date,$slotNumber){
        $day = $this->_workingDays[$date];
        $start = self::intervalByTime($day['opentime']);
        return $start + $slotNumber * $this->_workingDays[$date]->slotSize;
    }

    public function getSlotTime($date, $slotNumber) {
        return $this->getSlotStartInterval($date, $slotNumber);
    }

    function getSlotStartTime($date,$slotNumber,$padding=true){
      $startTime = $this->getSlotStartInterval($date, $slotNumber);
      return self::timeByInterval($startTime,$padding);
    }

    function getSecondarySlotStartInterval($date,$slotNumber){
      $day=$this->_workingDays[$date];
      $start = self::intervalByTime($day->opentime);
      return $start+$slotNumber*$this->_workingDays[$date]->slotSize+($this->_workingDays[$date]->slotSize/2);
    }

    function getSlotStartTime2($date,$slotNumber,$padding=true){
      $startTime = $this->getSecondarySlotStartInterval($date, $slotNumber);
      return self::timeByInterval($startTime,$padding);
    }

    function getSlotEndTime($date,$slotNumber,$padding=true){
      $startTime = $this->getSlotStartInterval($date, $slotNumber)+$this->_workingDays[$date]->slotSize;
      return self::timeByInterval($startTime,$padding);
    }

    function getSlotEndTime2($date,$slotNumber,$padding=true){
      $startTime = $this->getSlotStartInterval2($date, $slotNumber)+($this->_workingDays[$date]->slotSize/2);
      return self::timeByInterval($startTime,$padding);
    }

    public function isIntervalBeginning($date,$interval){
        $slotNum = $this->getSlotNumberByInterval($date,$interval);
        return $this->getSlotStartInterval($date,$slotNum) == $interval;
    }

    function moveSlots($date,$delta=0){

      $list = Slot::where('queue_id', $this->queue_id)->where('date', $date)->orderBy('iorder', 'ASC')->get();
      $list2 = Slot::where('queue_id', $this->queue_id)->where('date', $date)->orderBy('iorder', 'ASC')->take(15)->get();
      $workingDay = Workingday::where('queue_id', $this->queue_id)->where('date', $date)->first();


      //if ($delta == 1) {
      //  if ($workingDay->secondaryAvailable == 0 && $workingDay->slotSize == 2) {
      //    echo json_encode(['status' => 0, 'status_text' => 'Pilnā rinda jau ir ieslēgta!']);
      //  }
      //} else {
      //  if ($workingDay->secondaryAvailable == 1 && $workingDay->slotSize == 4) {
      //    echo json_encode(['status' => 0, 'status_text' => 'Pusrinda jau ir ieslēgta!']);
      //  }
      //}


      if ($delta == 1) {
        foreach ($list2 as $object) {
          $arr = [
            'status' => $object->status,
            'status2' => $object->status2,
            'takenby' => $object->takenby,
            'takenby2' => $object->takenby2,
            'cancel_id' => $object->cancel_id ?? null,
            'comment' => $object->comment,
            'createtime' => $object->createtime,
            'createtime2' => $object->createtime2,
            'createuser' => $object->createuser,
            'createuser2' => $object->createuser2,
            'edittime' => $object->edittime,
            'edittime2' => $object->edittime2,
            'edituser' => $object->edituser,
            'edituser2' => $object->edituser2,
            'is_mobile' => $object->is_mobile,
            'is_mobile2' => $object->is_mobile2,
          ];
          $id = $object->iorder * 2;
          $object->where('queue_id', $this->queue_id)->where('date', $date)->where('iorder', $id)->update($arr);
          if ($object->iorder % 2 != 0) {
          $arr = [
            'status' => '',
            'takenby' => '',
            'cancel_id' => null,
            'createtime' => '',
            'createuser' => '',
            'edittime' => '',
            'edituser' => '',
            'is_mobile' => 0,
          ];
            Slot::where('queue_id', $this->queue_id)->where('date', $date)->where('iorder', $object->iorder)->update($arr);
            $object->status2 = 0;
            $object->takenby2 = '';
            $object->createtime2 = null;
            $object->createuser2 = -1;
            $object->edittime2 = null;
            $object->edituser2 = -1;
            $object->is_mobile2 = 0;
            $object->save();
          }
        }
        foreach ($list2 as $object) {
          if ($object->iorder % 2 == 0) {
            $slot1 = Slot::where('queue_id', $this->queue_id)->where('date', $date)->where('iorder', $object->iorder)->first();
            $arr = [
              'status' => $slot1->status2,
              'takenby' => $slot1->takenby2,
              'cancel_id' => null,
              'createtime' => $slot1->createtime2,
              'createuser' => $slot1->createuser2,
              'edittime' => $slot1->edittime2,
              'edituser' => $slot1->edituser2,
              'is_mobile' => $slot1->is_mobile2,
            ];
//            dd($arr);
            Slot::where('queue_id', $this->queue_id)->where('date', $date)->where('iorder', $object->iorder + 1)->update($arr);
            $slot1->status2 = 0;
            $slot1->takenby2 = '';
            $slot1->createtime2 = null;
            $slot1->createuser2 = -1;
            $slot1->edittime2 = null;
            $slot1->edituser2 = -1;
            $slot1->is_mobile2 = 0;
            $slot1->save();
          }
        }
      } else {
        foreach ($list as $object) {
          if ($object->iorder % 2 == 0) {
            $slot1 = Slot::where('queue_id', $this->queue_id)->where('date', $date)->where('iorder', $object->iorder + 1)->first();
            $object->status2 = (!empty($slot1->status)) ? $slot1->status : 0;
            $object->takenby2 = (!empty($slot1->takenby)) ? $slot1->takenby : '';
            $object->createtime2 = (!empty($slot1->createtime)) ? $slot1->createtime : null;
            $object->createuser2 = (!empty($slot1->createuser)) ? $slot1->createuser : -1;
            $object->edittime2 = (!empty($slot1->edittime)) ? $slot1->edittime : null;
            $object->edituser2 = (!empty($slot1->edituser)) ? $slot1->edituser : -1;
            $object->is_mobile2 = (!empty($slot1->is_mobile)) ? $slot1->is_mobile : 0;
            $arr = [
              'status' => $object->status,
              'status2' => $object->status2,
              'takenby' => $object->takenby,
              'takenby2' => $object->takenby2,
              'cancel_id' => $object->cancel_id ?? null,
              'comment' => $object->comment,
              'createtime' => $object->createtime,
              'createtime2' => $object->createtime2,
              'createuser' => $object->createuser,
              'createuser2' => $object->createuser2,
              'edittime' => $object->edittime,
              'edittime2' => $object->edittime2,
              'edituser' => $object->edituser,
              'edituser2' => $object->edituser2,
              'is_mobile' => $object->is_mobile,
              'is_mobile2' => $object->is_mobile2,
            ];
            if ($object->iorder != 0) {
              $id = $object->iorder / 2;
            } else {
              $id = 0;
            }
            $object->where('queue_id', $this->queue_id)->where('date', $date)->where('iorder', $id)->update($arr);
            if (!empty($slot1->takenby)) {
              $slot1->takenby = '';
              $slot1->cancel_id = null;
              $slot1->createtime = null;
              $slot1->createuser = -1;
              $slot1->edittime = null;
              $slot1->edituser = -1;
              $slot1->is_mobile = 0;
              $slot1->save();
            }
          }
        }
      }
    }

    public function parseNotification($text, $date, $slotNum, $takenBy, $time){
      $_weekDays = array(
        1=>'pirmdien',
        2=>'otrdien',
        3=>'trešdien',
        4=>'ceturtdien',
        5=>'piektdien',
        6=>'sestdien',
        7=>'svētdien',
      );

      $office = Office::findOrFail($this->office_id);

      $dateStamp = strtotime($date);
      $dayOfWeek = $_weekDays[date('N', $dateStamp)];
      $dateFmt = date('d.m.Y', $dateStamp);

      switch ($takenBy->service){
        case 0:{
          $purpose = '';
          $purposeLong = '';
          break;
        }
        case 1:{
          $purpose = 'riepu nomaiņa';
          $purposeLong = 'Jūs vēlaties samainīt riepas vai riteņus, kuri Jums būs līdzi';
          break;
        }
        case 2:{
          $purpose = 'riepu nomaiņa';
          $purposeLong = 'Jūs vēlaties samainīt riepas vai riteņus, kuri glabājas pie mums';
          break;
        }
        case 3:{
          $purpose = 'riepu nomaiņa';
          $purposeLong = 'Jūs vēlaties samainīt riepas vai riteņus, kurus vēlaties pie mums nopirkt';
          break;
        }
	case 4:{
	  $purpose = 'riepas remonts';
	  $purposeLong = 'Jūs vēlaties saremontēt riepu';
	  break;
	}
        case 6:{
          $purpose = 'kondicioniera uzpilde';
          $purposeLong = 'Jūs vēlaties uzpildīt kondicionieri';
          break;
        }
        case 8:{
          $purpose = 'riepu nomaiņa';
          $purposeLong = '';
          break;
        }
	case 9:{
	  $purpose = 'riepu nomaiņa';
	  $purposeLong = 'Jūs vēlaties samainīt riepas vai riteņus motociklam, kurus vēlaties pie mums nopirkt';
	  break;
	}
      }

      $url = env('SCHEDULE_URL');

      $outText = str_replace('%TIME%',$time,$text);
      $outText = str_replace('%DATE%',$dateFmt,$outText);
      $outText = str_replace('%URL%', $url, $outText);
      $outText = str_replace('%DAY%',ucfirst($dayOfWeek),$outText);
      $outText = str_replace('%DATE_LONG%',$dayOfWeek.', '.$dateFmt,$outText);
      $outText = str_replace('%OFFICE%',$office->title,$outText);
      $outText = str_replace('%CARMAKE%',$takenBy->car_brand,$outText);
      $outText = str_replace('%CARMODEL%',$takenBy->car_model,$outText);
      $outText = str_replace('%PURPOSE%',$purpose,$outText);
      $outText = str_replace('%PURPOSE_LONG%',$purposeLong,$outText);
      if (isset($takenBy->cancelId)) {
        $outText = str_replace('%CANCELID%',$takenBy->cancelId,$outText);
      }

      return $outText;
    }

}

