<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rimbrand extends Model
{
    use HasFactory;

    protected $table = 'rim_brands';

    protected $primaryKey = 'brand_id';
}
