<?php

namespace App\Models;

use http\Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Audit extends Model
{
  protected $table = 'audits';

  public function user()
  {
    return $this->belongsTo(User::class, 'audit_uid', 'id');
  }

  public function getInstanceObjectAttribute()
  {
    if (empty($this->audit_instance)) {
      return null;
    }
    
    $decoded = @json_decode($this->audit_instance);
    if (json_last_error() === JSON_ERROR_NONE) {
      return $decoded;
    }
    
    $instance = @unserialize($this->audit_instance);
    return $instance !== false ? $instance : null;
  }

  public function getBacktraceArrayAttribute()
  {
    if (empty($this->audit_backtrace)) {
      return [];
    }
    
    $decoded = @json_decode($this->audit_backtrace, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
      return $decoded;
    }
    
    $backtrace = @unserialize($this->audit_backtrace);
    return is_array($backtrace) ? $backtrace : [];
  }

  public function getFormattedEventAttribute()
  {
    $event = trim($this->audit_event);
    $instance = $this->instance_object;

    if (!$instance) {
      return $event;
    }

    if ($this->audit_classname === 'App\Models\Slot') {
      if (isset($instance->takenby)) {
        $takenby = json_decode($instance->takenby);
        if ($takenby && isset($takenby->cancelId)) {
          $time = substr_replace(substr($takenby->cancelId, -4), ':', 2, 0);
          return $event . ' (' . $instance->date . ' ' . $time . ')';
        }
      }
      
      if (stripos($this->audit_url, 'cancel') !== false && isset($instance->date)) {
        $time = substr_replace(substr($this->audit_url, -4), ':', 2, 0);
        return $event . ' (' . $instance->date . ' ' . $time . ')';
      }
    }

    return $event;
  }

  public function getUserDisplayAttribute()
  {
    if ($this->user) {
      return $this->user->fullName . ' (' . $this->audit_uid . ')';
    }
    return 'Nezināms (' . $this->audit_uid . ')';
  }

  public static function get_severity_image($severity): string
  {
    switch ($severity){
      case AUDIT_SEVERITY_CRITICAL: return 'error.gif';
      case AUDIT_SEVERITY_WARNING: return 'asterisk.gif';
      case AUDIT_SEVERITY_INFO: return 'info.gif';
      case AUDIT_SEVERITY_ERROR: return 'error.gif';
      case AUDIT_SEVERITY_DEBUG: return 'debug.gif';
    }
  }

  public static function get_facility_name($id): string
  {
    switch ($id){
      case AUDIT_FACILITY_LOGIN: return 'Autorizācijas apakšsistēma';
      case AUDIT_FACILITY_DB: return 'Datubāzes apakšsistēma';
      case AUDIT_FACILITY_MESSAGE: return 'Ziņu apakšsistēma';
      case AUDIT_FACILITY_SYSCORE: return 'Sistēmas kodols';
      case AUDIT_FACILITY_USER: return 'Lietotāju apakšsistēma';

      case AUDIT_FACILITY_DOCUMENT: return 'Datu objekts';
      default: return $id;
    }
  }

  public static function audit($severity, $facility, $item, $subitem, $event, $instance = false)
  {
    $ip = @$_SERVER['REMOTE_ADDR'];

    $userId = 0;
    if (Auth::check()) {
      $userId = Auth::user()->id;
    }

    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $backtrace = json_encode($backtrace, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $url = @$_SERVER['REQUEST_URI'];
    if ($url == '') {
      $url = '';
    }

    $instance_data = '';
    $instance_class = '';

    if ($instance !== false) {
      if ($instance instanceof \Throwable) {
        $backtrace = json_encode($instance->getTrace(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $instance_data = $instance->__toString();
        $instance_class = get_class($instance);
      } else {
        $instance_data = json_encode($instance, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $instance_class = get_class($instance);
      }
    }

    $data = [
      'audit_time' => now(),
      'audit_uid' => $userId,
      'audit_event' => $event,
      'audit_ip' => $ip,
      'audit_severity' => $severity,
      'audit_item' => $item,
      'audit_facility' => $facility,
      'audit_subitem' => $subitem,
      'audit_backtrace' => $backtrace,
      'audit_classname' => $instance_class,
      'audit_instance' => $instance_data,
      'audit_url' => $url
    ];

    return DB::table('audits')->insert($data);
  }

}

