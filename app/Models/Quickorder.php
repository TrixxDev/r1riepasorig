<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quickorder extends Model
{
    use HasFactory;

    protected $table = 'xml_orders';

    protected $primaryKey = 'order_id';

    public function _compare_skip(){
      return array();
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
      foreach ($this->getOriginal() as $attribute => $value){
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
