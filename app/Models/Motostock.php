<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motostock extends Model
{
    use HasFactory;

    protected $table = 'moto_stock';

    protected $primaryKey = 'stock_id';

    protected $fillable = ['quantity'];

}
