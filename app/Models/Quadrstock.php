<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quadrstock extends Model
{
    use HasFactory;

    protected $table = 'quadr_stock';

    protected $primaryKey = 'stock_id';

    protected $fillable = ['quantity'];
}
