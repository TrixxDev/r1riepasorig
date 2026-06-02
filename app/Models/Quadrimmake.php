<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quadrimmake extends Model
{

  protected $table = 'quadrim_makes';

  protected $primaryKey = 'make_id';

    public function tires() {
        return $this->hasMany(Quadrim::class, 'make_id', 'tread_id');
    }

    public function tireCount() {
        return $this->tires()->selectRaw('make_id, count(*) as tire_count')->groupBy('make_id');
    }

}
