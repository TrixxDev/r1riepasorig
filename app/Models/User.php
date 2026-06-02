<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Auth;

class User extends Authenticatable
{
  use HasFactory, Notifiable, HasRoles;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'surname',
    'email',
    'phone_number',
    'sms_verification_code',
    'password',
    'new_password',
    'company_name',
    'company_vat',
    'company_address',
    'company_postcode',
    'company_city',
  ];

  /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getFullNameAttribute() {
        return ucfirst($this->name) . ' ' . ucfirst($this->surname);
    }

    public function routeNotificationForSms()
    {
      return $this->phone_number;
    }

    // Method to check if the user has company information
    public function hasCompany()
    {
      return !empty($this->company_name) || !empty($this->company_vat) ||
        !empty($this->company_address) || !empty($this->company_postcode) ||
        !empty($this->company_city);
    }

}
