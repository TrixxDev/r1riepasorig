<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bigstock extends Model
{

    protected $table = 'bigtire_stock';

    protected $primaryKey = 'stock_id';

    protected $fillable = ['quantity'];

    use HasFactory;
}
