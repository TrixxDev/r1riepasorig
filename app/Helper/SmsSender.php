<?php

namespace App\Helper;

use App\Models\Audit;
use App\Models\Office;
use App\Models\Queue;
use App\Models\Quickorder;
use App\Models\Slot;
use Exception;

class SmsSender {

  private const SMS_API_KEY = '867459d28d672949f49d8f6df81a67d286ea96f9';
  private const SMS_API_URL = 'https://traffic.sales.lv/API:0.14/';

  private function makeApiRequest($params) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, self::SMS_API_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
      throw new \Exception("CURL Error: " . $error);
    }
    
    return $result;
  }

  public function isValidPhoneNumber($phone, $normalLength = 8) {
    //izvācam atstarpes
    $phone = str_replace(' ','',$phone);
    // Izvācam visu, atskaitot +, - un .
    $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
    // Remove "-" from number
    $phone_to_check = str_replace("-", "", $filtered_phone_number);
    // Check the lenght of number
    // This can be customized if you want phone number from a specific country
    if (strlen($phone_to_check) < $normalLength || strlen($phone_to_check) > ($normalLength+4)) {
      return false;
    } else {
      return $phone_to_check;
    }
  }

  public function sendOrderSMS($data, $orderId)
  {
    header("Content-type: text/html; charset=UTF-8");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");

    $target = $data['mobile_number'];
    switch ($data['location']) {
      case 'URS': {
        $office = Office::where('office_id', 1)->first();
        break;
      }
      case 'KRS': {
        $office = Office::where('office_id', 2)->first();
        break;
      }
    }
//    $smsText = 'Pasūtījums ar numuru - ' . $orderId . ' ir apstiprināts';
    $smsText = 'Jusu pasutijuma numurs ' . $orderId . ', sanemsana - ' . str_replace('ā', 'a', $office->shipping) . '. Ar darba laikiem iespejams iepazities - www.r1riepas.lv';

    $sendString = '["'.$target.'","'.$smsText.'"]';

    $sendString = '['.$sendString.']';
    //$sendString .= '["28344474","'.$smsText.'"]';

    $object = json_decode($sendString);
    //dd($sendString, $object);

//https://traffic.sales.lv/API:0.14/

//    $sendSMS = P('reservation.sms.enabled',0);
//
//    if (!$sendSMS){
//      audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, 0,0, $sendString);
//      die;
//    }
//$sendString = '[[28344474, "Hello"]]';

    $postdata = http_build_query(
      array(
        'APIKey' => '867459d28d672949f49d8f6df81a67d286ea96f9',
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
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SMS, Ātrais pasūtījums: Neautorizēta IP!');
      throw new Exception("SMS: Neautorizēta IP!");
    }

    if ($sender=='') {
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SMS, Ātrais pasūtījums: Nav pieejams neviens sūtītājs!');
      throw new Exception("SMS: Nav pieejams neviens sūtītājs!");
    }

    $postdata = http_build_query(
      array(
        'APIKey' => '867459d28d672949f49d8f6df81a67d286ea96f9',
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


//    audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SENT SMS: '.$result);

    if ($result) {
      if (stripos($orderId, 'u-') !== false) {
        $orderId = str_replace('U-', '', $orderId);
      } else {
        $orderId = str_replace('K-', '', $orderId);
      }
      $order = Quickorder::where('order_id', $orderId)->first();
      $order->sms_sended = 1;
      $order->save();
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,$order->order_id,0,'SMS, Ātrais pasūtījums: Īsziņa veiksmīgi nosūtīta!', $order);
      return json_encode(['success' => 'Īsziņa veiksmīgi nosūtīta']);
    } else {
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,$order->order_id,0,'SMS, Ātrais pasūtījums: Neizdevās aizsūtīt īsziņu!');
      return json_encode(['danger' => 'Īsziņa nav nosūtīta']);
    }
  }

  /**
   * @param  bool  $outputHttpHeaders  false when running after HTTP response (e.g. fillSlot afterResponse)
   */
  public function sendSchedule($data, $smsText, $slot, bool $outputHttpHeaders = true)
  {
    if ($outputHttpHeaders) {
      header("Content-type: text/html; charset=UTF-8");
      header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
      header("Cache-Control: no-cache");
      header("Pragma: no-cache");
    }

    $target = $data['phone_number'];

    $sendString = '["'.$target.'","'.$smsText.'"]';

    $sendString = '['.$sendString.']';
    //$sendString .= '["28344474","'.$smsText.'"]';

    $object = json_decode($sendString);
    //dd($sendString, $object);

//https://traffic.sales.lv/API:0.14/

//    $sendSMS = P('reservation.sms.enabled',0);
//
//    if (!$sendSMS){
//      audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, 0,0, $sendString);
//      die;
//    }
//$sendString = '[[28344474, "Hello"]]';

    $postdata = http_build_query(
      array(
        'APIKey' => '867459d28d672949f49d8f6df81a67d286ea96f9',
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
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SMS, Pieraksts: Neautorizēta IP!');
      throw new Exception("SMS: Neautorizēta IP!");
    }

    if ($sender=='') {
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SMS, Pieraksts: Nav pieejams neviens sūtītājs!');
      throw new Exception("SMS: Nav pieejams neviens sūtītājs!");
    }

    $postdata = http_build_query(
      array(
        'APIKey' => '867459d28d672949f49d8f6df81a67d286ea96f9',
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


//    audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SENT SMS: '.$result);

    if ($result) {
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,$slot->slot_id,0,'SMS, Pieraksts: Īsziņa veiksmīgi nosūtīta!', $slot);
    } else {
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,$slot->slot_id,0,'SMS, Pieraksts: Neizdevās aizsūtīt īsziņu!');
    }
  }

  public function getSlotTime($date, $queue_id, $iorder) {
    $workingDay = \App\Models\Workingday::where('date', $date)->where('queue_id', $queue_id)->first();
    if (!$workingDay) return null;
    $timeStep = $workingDay->timeStep ?? 15;
    $start = \Carbon\Carbon::createFromTimeString($workingDay->timeopen);
    $slotTime = $start->copy()->addMinutes($iorder * $timeStep);
    // Проверка: не выходит ли время за пределы рабочего дня
    $end = \Carbon\Carbon::createFromTimeString($workingDay->timeclose);
    if ($slotTime < $start || $slotTime > $end) return null;
    return $slotTime->format('H:i');
  }

  public function send() {
    try {
      header("Content-type: text/html; charset=UTF-8");
      header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
      header("Cache-Control: no-cache");
      header("Pragma: no-cache");

      $date = date('Y-m-d',strtotime("+1 day"));
      echo 'Scheduling for: '.$date.'<br/>';

      $messages = [];
      $slots = Slot::where('date', $date)->where('status', SLOT_STATUS_TAKEN)->get();

      if ($slots->isEmpty()) {
        Audit::audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, 0, 0, 'SMS, Pieraksts: Nav ierakstu uz rītdienu');
        return;
      }

      foreach ($slots as $slot) {
        $form = json_decode($slot->takenby);
        if (!$form || !isset($form->phone_number)) {
          Audit::audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, $slot->slot_id, 0, 'SMS, Pieraksts: Nav telefona numura');
          continue;
        }

        $queue = Queue::where('queue_id', $slot->queue_id)->first();
        if (!$queue) {
          Audit::audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, $slot->slot_id, 0, 'SMS, Pieraksts: Nav atrasta rinda');
          continue;
        }

        // Получаем корректное время слота по iorder
        $time = $this->getSlotTime($slot->date, $slot->queue_id, $slot->iorder);

        $smsText = $queue->parseNotification($queue->notificationSMS, $date, $slot->iorder, $form, $time);
        $target = $this->isValidPhoneNumber($form->phone_number);
        
        if ($target) {
          $messages[] = [(string) $target, $smsText];
          echo 'SMS: ' . $form->phone_number . ' (' . $target . ') :' . nl2br($smsText) . '<br/>' . "\n";
        } else {
          Audit::audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, $slot->slot_id, 0, 'SMS, Pieraksts: Nederīgs telefona numurs');
        }
      }

      if (empty($messages)) {
        Audit::audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, 0, 0, 'SMS, Pieraksts: Nav derīgu numuru nosūtīšanai');
        return;
      }

      $sendString = json_encode($messages, JSON_UNESCAPED_UNICODE);
      if ($sendString === false) {
        Audit::audit(AUDIT_SEVERITY_ERROR, AUDIT_FACILITY_MESSAGE, 0, 0, 'SMS, Pieraksts: Neizdevās sagatavot satura JSON');
        return;
      }
      
      $result = $this->makeApiRequest([
        'APIKey' => self::SMS_API_KEY,
        'Command' => 'GetSenders'
      ]);

      if ($result) {
        $response = json_decode($result);
        if (isset($response->Error)) {
          Audit::audit(AUDIT_SEVERITY_ERROR, AUDIT_FACILITY_MESSAGE, 0, 0, 'SMS, Pieraksts: Kļūda API: ' . $response->Error);
        } else {
          $sender = $response->Senders[0] ?? '';
          if (empty($sender)) {
            Audit::audit(AUDIT_SEVERITY_ERROR, AUDIT_FACILITY_MESSAGE, 0, 0, 'SMS, Pieraksts: Nav pieejams neviens sūtītājs!');
            return;
          }

          $result = $this->makeApiRequest([
            'APIKey' => self::SMS_API_KEY,
            'Command' => 'SendMultiple',
            'Sender' => $sender,
            'Concatenated' => '1',
            'Unicode' => '1',
            'Content' => $sendString
          ]);

          if ($result) {
            $response = json_decode($result);
            if (isset($response->Error)) {
              Audit::audit(AUDIT_SEVERITY_ERROR, AUDIT_FACILITY_MESSAGE, 0, 0, 'SMS, Pieraksts: Kļūda API: ' . $response->Error);
            } else {
              Audit::audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, 0, 0, 'SMS, Pieraksts: Īsziņas veiksmīgi nosūtītas!');
            }
          } else {
            Audit::audit(AUDIT_SEVERITY_ERROR, AUDIT_FACILITY_MESSAGE, 0, 0, 'SMS, Pieraksts: Neizdevās nosūtīt īsziņas!');
          }
        }
      }

    } catch (\Exception $e) {
      Audit::audit(AUDIT_SEVERITY_ERROR, AUDIT_FACILITY_MESSAGE, 0, 0, 'SMS, Pieraksts: Kļūda: ' . $e->getMessage());
    }
  }

  /*public function send() {
    header("Content-type: text/html; charset=UTF-8");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");

    $date = date('Y-m-d',strtotime("+1 day"));

    echo 'Scheduling for: '.$date.'<br/>';

    $sendString = '';

    $slots = Slot::where('date', $date)->where('status', SLOT_STATUS_TAKEN)->get();

    foreach ($slots as $slot) {
      $form = json_decode($slot->takenby);

      $queue = Queue::where('queue_id', $slot->queue_id)->first();

      $time = '';
      if (isset($form->cancelId) && !empty($form->cancelId)) {
        $time = substr($form->cancelId, -4);
        $time = $this->insertColon($time);
      }

      $smsText = $queue->parseNotification($queue->notificationSMS, $date, $slot->iorder, $form, $time);
      $target = $this->isValidPhoneNumber($form->phone_number);
      echo 'SMS: ' . $form->phone_number . ' (' . $target . ') :' . nl2br($smsText) . '<br/>' . "\n";
      if ($target) {
        if ($sendString != '') $sendString .= ",";
        $sendString .= '["' . $target . '","' . $smsText . '"]';
      }
    }
    $sendString = '['.$sendString.']';
    //
    $sendString .= '["28344474","'.$smsText.'"]';


    $object = json_decode($sendString);
    //dd($sendString, $object);

//https://traffic.sales.lv/API:0.14/

//    $sendSMS = P('reservation.sms.enabled',0);
//
//    if (!$sendSMS){
//      audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, 0,0, $sendString);
//      die;
//    }
//$sendString = '[[28344474, "Hello"]]';

    $postdata = http_build_query(
      array(
        'APIKey' => '867459d28d672949f49d8f6df81a67d286ea96f9',
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
      dd($error);
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SMS, Pieraksts: Neautorizēta IP!');
    }

    if ($sender=='') {
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SMS, Pieraksts: Nav pieejams neviens sūtītājs!');
    }

    $postdata = http_build_query(
      array(
        'APIKey' => '867459d28d672949f49d8f6df81a67d286ea96f9',
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


//    audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE,0,0,'SENT SMS: '.$result);

    if ($result) {
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE, 0,0,'SMS, Pieraksts: Īsziņas veiksmīgi nosūtītas!');
    } else {
      Audit::audit(AUDIT_SEVERITY_DEBUG,AUDIT_FACILITY_MESSAGE, 0,0,'SMS, Pieraksts: Neizdevās nosūtīt īsziņas!');
    }

    dd($result);
  }*/

  public function insertColon($number)
  {
    // Get the length of the string.
    $length = strlen($number);

    // If the length of the string is less than 3, then there is no need to insert a colon.
    if ($length < 3) {
      return false;
    } else {
      // Insert a colon at the second character of the string.
      return substr($number, 0, 2) . ":" . substr($number, 2);
    }
  }

}
