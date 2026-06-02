<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quadrimbrand extends Model
{
    use HasFactory;

    protected $table = 'quadrim_brands';

    protected $primaryKey = 'brand_id';
}
