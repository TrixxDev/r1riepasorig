<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bigtread extends Model
{

    protected $table = 'bigtire_treads';

    protected $primaryKey = 'tread_id';
    public $timestamps = false;

  use HasFactory;


    public function brand()
    {
        return $this->belongsTo(Bigbrand::class, 'brand_id', 'brand_id');
    }

    public function tires() {
        return $this->hasMany(Bigtire::class, 'make_id', 'tread_id');
    }

    public function tireCount() {
        return $this->tires()->selectRaw('make_id, count(*) as tire_count')->groupBy('make_id');
    }
}
