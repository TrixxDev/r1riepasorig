<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FilterCars extends Model
{

  use HasFactory;

  protected $primaryKey = 'car_id';

  public function models(): hasMany
  {
    return $this->hasMany(FilterModels::class, 'carId');
  }

}
