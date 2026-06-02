<?php

namespace App\Notifications\Channels;

use App\Models\Audit;
use Illuminate\Notifications\Notification;
use Exception;

class SmsChannel
{
  public function send($notifiable, Notification $notification)
  {
    if (! $phoneNumber = $notifiable->routeNotificationFor('sms')) {
      return;
    }

    $apiKey = '867459d28d672949f49d8f6df81a67d286ea96f9';
    $phone = $phoneNumber;
    $message = $notification->toSms($notifiable);

    header("Content-type: text/html; charset=UTF-8");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");

    $target = $phone;

    $sendString = '["'.$target.'","'.$message.'"]';
    $sendString = '['.$sendString.']';

    $object = json_decode($sendString);

    $postdata = http_build_query(
      array(
        'APIKey' => $apiKey,
        'Command' => 'GetSenders'
      )
    );

    $opts = array('http' =>
      array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata
      )
    );

    $context = stream_context_create($opts);
    $result = file_get_contents('https://traffic.sales.lv/API:0.14/', false, $context);

    $data = json_decode($result);
    $error = @$data->Error;

    if ($error==''){
      $sender = $data->Senders[0];	// paļaujamies uz to, ka ir vismaz viens atļautais sūtītājs!
    } else {
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SMS, Verifikācija: Neautorizēta IP!');
      throw new Exception("SMS: Neautorizēta IP!");
    }

    if ($sender=='') {
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SMS, Verifikācija: Nav pieejams neviens sūtītājs!');
      throw new Exception("SMS: Nav pieejams neviens sūtītājs!");
    }

    $postdata = http_build_query(
      array(
        'APIKey' => $apiKey,
        'Command' => 'SendMultiple',
        'Sender' => $sender,
        'Concatenated'=>'1',
        'Unicode'=>'1',
        'Content' => $sendString,
      )
    );
    $opts = array('http' =>
      array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata
      )
    );

    $context = stream_context_create($opts);
    $result = file_get_contents('https://traffic.sales.lv/API:0.14/', false, $context);

    if ($result) {
      dd($result);
    } else {

    }
  }
}
