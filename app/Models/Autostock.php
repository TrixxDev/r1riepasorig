<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autostock extends Model
{
    use HasFactory;

    protected $table = 'auto_stock';

    protected $primaryKey = 'stock_id';

    protected $fillable = ['quantity'];
}
