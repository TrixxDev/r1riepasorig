<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders_';
    protected $casts = [
	'phone_number' => 'string',
	'phone_country_code' => 'string'
    ];

    public function _compare_skip()
    {
        return [];
    }

    public function _compare_get_name($n, $new, $old)
    {
        return $n;
    }

    public function _compare_get_values($n, $new, $old)
    {
        $new_val = $new->$n;
        if ($old === false) {
            return [$new_val, false];
        } else {
            $old_val = $old->$n;
            return [$new_val, $old_val];
        }
    }

    public function _compare_get_classname()
    {
        return 'Order';
    }

    public function compare($old_instance)
    {
        $skip_attrs = $this->_compare_skip();
        $changes = [];
        
        foreach ($this->getAttributes() as $attribute => $value) {
            if (!empty($old_instance) && method_exists($old_instance, 'getAttributes')) {
                $oldAttrs = $old_instance->getAttributes();
                if (isset($oldAttrs[$attribute]) && $oldAttrs[$attribute] != $value) {
                    $changes[$this->_compare_get_name($attribute, $this, $old_instance)] = $this->_compare_get_values($attribute, $this, $old_instance);
                } else {
                    $changes[$this->_compare_get_name($attribute, $this, false)] = $this->_compare_get_values($attribute, $this, false);
                }
            } else {
                $changes[$this->_compare_get_name($attribute, $this, false)] = $this->_compare_get_values($attribute, $this, false);
            }
        }
        
        return $changes;
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edituser');
    }
}

