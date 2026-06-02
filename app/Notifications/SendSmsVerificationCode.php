<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Notifications\Channels\SmsChannel;

class SendSmsVerificationCode extends Notification
{
  protected $code;

  public function __construct($code)
  {
    $this->code = $code;
  }

  public function via($notifiable)
  {
    return [SmsChannel::class];
  }

  public function toSms($notifiable)
  {
    return 'Jusu verifikacijas kods ir ' . $this->code;
  }
}