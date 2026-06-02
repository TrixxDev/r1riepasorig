<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motobrand extends Model
{
    use HasFactory;

    protected $table = 'moto_brands';

    protected $primaryKey = 'brand_id';
}
