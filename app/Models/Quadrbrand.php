<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quadrbrand extends Model
{
    use HasFactory;

    protected $table = 'quadr_brands';

    protected $primaryKey = 'brand_id';
}
