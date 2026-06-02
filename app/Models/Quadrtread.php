<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quadrtread extends Model
{
    use HasFactory;

    protected $table = 'quadr_treads';

    protected $primaryKey = 'tread_id';

    public function tires() {
        return $this->hasMany(Quadr::class, 'make_id', 'tread_id');
    }

    public function tireCount() {
        return $this->tires()->selectRaw('make_id, count(*) as tire_count')->groupBy('make_id');
    }

}
