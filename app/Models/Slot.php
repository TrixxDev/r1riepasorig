<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Slot extends Model
{

    protected $primaryKey = 'slot_id';

    public $timestamps = false;

    /**
     * Stable key for matching preloaded slots to the reservation grid (queue + day + order).
     * Normalizes date strings vs Carbon and integer-ish ids so array lookups match the Blade loop.
     */
    public static function reservationGridKey($queueId, $date, $iorder): string
    {
        if ($date instanceof \DateTimeInterface) {
            $d = $date->format('Y-m-d');
        } elseif (is_string($date) && strlen($date) >= 10) {
            $d = substr($date, 0, 10);
        } else {
            $d = (string) $date;
        }

        return (int) $queueId . '|' . $d . '|' . (int) $iorder;
    }

    public function _compare_skip(){
      return array();
    }

    //TODO: Ir laiks uz kuru jāpārmet, jāpaņem rinda uz kuru tiek mests slots
    // Jāpārbauda vai sākuma laiks ir vienāds ar mazāko laiku jebkurai rindai
    // Jāizrēķina no konkrēta laika līdz kaut kādam izvēlētam laikam iorders

    public static function getSlotNumber($time, $date, $queue)
    {
      $date = Carbon::parse($date);
      $workingDay = NewWorkingDay::select('*', DB::raw('MIN(timeopen) as earliest_time'), DB::raw('MAX(timeclose) as latest_time'))->where('date', $date)->first(); // Paņemam dienu no kura slots tiek
      $nextWorkingDay = NewWorkingDay::where('date', $date)->where('queue_id', $queue)->first();

      $timeStep = $workingDay->timeStep;
      $opentime = Carbon::parse($workingDay->earliest_time);
      $bigestStartSlot = $opentime->diffInMinutes($nextWorkingDay->timeopen) / $timeStep;

      $targetTime = Carbon::parse($time);
      $timeDifference = $targetTime->diffInMinutes($opentime);

      $orderNumber = $timeDifference / $timeStep;

      if ($workingDay->earliest_time !== $nextWorkingDay->timeopen) {
        $orderNumber = $orderNumber - $bigestStartSlot;
        if ($orderNumber < 0) return 'Laiks uz kuru pārvieto ir ārpus darba laika';
      }
      $latest_time = Carbon::parse($workingDay->latest_time)->format('H:i');
      if ($latest_time <= $time) return 'Nevar ievietot slotu pēc darba laika';

      //Todo: Jāpaņem laiks uz kuru tiek pārmests slots
      // Pēc tam paņemt rindu uz kuru tiek mests slots
      // Bet vispirms vajag pārbaudīt rindas kopējo orderu skaitu
      // Ja pārvietojamā slota iorderis būs lielāks, tad atņemt $bigestStartSlot

      if (($opentime->diffInMinutes($nextWorkingDay->timeclose) / $timeStep) <= $orderNumber) {
        return 'Laiks uz kuru pārvieto ir ārpus darba laika';
      }

      return (int) $orderNumber;
    }

    public function _compare_get_name($n,$new,$old){
      return $n;
    }

    function _compare_get_values($n,$new,$old){
      $new_val = $new->$n;
      if ($old === false){
        return array($new_val,false);
      } else {
        $old_val = $old->$n;
        return array($new_val,$old_val);
      }
    }

    public function _compare_get_classname(){
      return get_class($this);
    }

    public function compare($old_instance){
      $skip_attrs = $this->_compare_skip();

      $changes = array();
//      $fillable = $this->fillable;
//      $dates = $this->getDates();
//      $available_attrs = array_merge($fillable, $dates);

//      $available_attrs[] = $this->getKeyName();


      foreach ($this->getOriginal() as $attribute => $value){
//	if (!in_array($attribute, $available_attrs)) {
//		continue;
//	}

//	if (in_array($attribute, $skip_attrs)) {
//		continue;
//	}

//	try {
//		if (!empty($old_instance->getOriginal())) {
//			$old_value = $old_instance->getOriginal()[$attribute] ?? null;

//			if ($old_value !== $value) {
//				$changes[$this->_compare_get_name($attribute, $this, $old_instance)] = 
//					$this->_compare_get_values($attribute, $this, $old_instance);
//			}
//		} else {
//			$changes[$this->_compare_get_name($attribute, $this, false)] = 
//				$this->_compare_get_values($attribute, $this, false);
//		}
//	} catch (\Exception $e) {
//		continue;
//	}
        if (!empty($old_instance->getOriginal())) {
          if ($old_instance->getOriginal()[$attribute] != $value){
            // ir bijušas izmaiņas
            $changes[$this->_compare_get_name($attribute,$this,$old_instance)] = $this->_compare_get_values($attribute,$this,$old_instance);
          } else {
            // izmaiņu nav
            $changes[$this->_compare_get_name($attribute,$this,false)] = $this->_compare_get_values($attribute,$this,false);
          }
        } else {
          $changes[$this->_compare_get_name($attribute,$this,false)] = $this->_compare_get_values($attribute,$this,false);
        }

      }
      return $changes;
    }

}
