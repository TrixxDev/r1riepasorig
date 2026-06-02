<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autotread extends Model
{
    use HasFactory;

    protected $table = 'auto_treads';

    protected $primaryKey = 'tread_id';

    public function getImageAttribute()
    {
      $fileName = '/storage/app/public/auto/tread/' . $this->tread_id . '.png';
      if (file_exists(dirname(__DIR__, 2) . $fileName)) {
        return $fileName;
      } else {
        return false;
      }
    }

    public function tires() {
        return $this->hasMany(Autotire::class, 'make_id', 'tread_id');
    }

    public function tireCount() {
        return $this->tires()->selectRaw('make_id, count(*) as tire_count')->groupBy('make_id');
    }

}
