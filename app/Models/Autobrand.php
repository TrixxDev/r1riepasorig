<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autobrand extends Model
{
    use HasFactory;

    protected $table = 'auto_brands';

    protected $primaryKey = 'brand_id';
}
