<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rimstock extends Model
{
  use HasFactory;

  protected $table = 'rim_stock';

  protected $primaryKey = 'stock_id';

  protected $fillable = ['quantity'];
}
