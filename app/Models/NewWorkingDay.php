<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewWorkingDay extends Model
{
    use HasFactory;

    protected $table = 'new_workingdays';

    protected $primaryKey = 'workingday_id';
    public $timestamps = false;
}
