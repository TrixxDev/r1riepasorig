<?php

  namespace App\Http\Controllers;

  use App\Models\Audit;
  use App\Mail\Mail;
  use Illuminate\Support\Facades\Mail as EMail;

  class EmailController extends Controller
  {

    public $message;
    public $subject;
    public $senderAddress;
    public $senderName;
    public $recipients;
    public $BCCs;
    public $attachmentStrings;

    public function __construct(){
      $this->id = -1;
      $this->senderAddress = config('mail.from.address') ?: env('MAIL_USERNAME');
      $this->senderName = config('mail.from.name') ?: env('MAIL_FROM_NAME');
      $this->recipients = [];
      $this->BCCs = [];
      $this->attachments=[];
      $this->attachmentStrings=[];
    }

    public function addRecipient($address,$name=''){
      $this->recipients[] = array('email' => $address, 'name' => $name);
    }

    public function addBCC($address,$name=''){
      $this->BCCs[] = array('email' => $address, 'name' => $name);
    }

    public function attachString($attachment,$mime,$filename){
      $this->attachmentStrings[]=array('data' => $attachment, 'mime' => $mime, 'name' => $filename);
    }

    public function send()
    {
      try {
        EMail::send([], [], function ($message) {

          foreach ($this->recipients as $recipient){
            $message->to($recipient['email'],trim($recipient['name']));
          }

          foreach ($this->BCCs as $recipient){
            $message->bcc($recipient['email'],trim($recipient['name']));
          }
          $message->from($this->senderAddress, $this->senderName);
          $message->subject($this->subject);
          $message->setBody($this->message, 'text/html');

          foreach ($this->attachmentStrings as $attachment) {
            $message->attachData($attachment['data'], $attachment['name'], [
              'mime' => $attachment['mime']
            ]);
          }
        });
        
        Audit::audit(AUDIT_SEVERITY_INFO, AUDIT_FACILITY_MESSAGE, -1, 0, "Mail sent", $this);
        return true;
      } catch (\Exception $e) {
        Audit::audit(AUDIT_SEVERITY_WARNING, AUDIT_FACILITY_MESSAGE, -1, 0, "Mail delivery failed: " . $e->getMessage(), $this);
        return false;
      }
    }

    public function _compare_skip(){
      return array();
    }

    public function _compare_get_name($n,$new,$old){
      return $n;
    }

    function _compare_get_values($n,$new,$old){
      $new_val = $new->$n;
      if ($old === false){
        return array($new_val,false);
      } else {
        $old_val = $old->$n;
        return array($new_val,$old_val);
      }
    }

    public function _compare_get_classname(){
      return get_class($this);
    }

    public function compare($old_instance){
      $skip_attrs = $this->_compare_skip();

      $changes = array();
      foreach ($this as $attribute => $value){
        if (!empty($old_instance)) {
          if ($old_instance->$attribute != $value){
            // ir bijušas izmaiņas
            $changes[$this->_compare_get_name($attribute,$this,$old_instance)] = $this->_compare_get_values($attribute,$this,$old_instance);
          } else {
            // izmaiņu nav
            $changes[$this->_compare_get_name($attribute,$this,false)] = $this->_compare_get_values($attribute,$this,false);
          }
        } else {
          $changes[$this->_compare_get_name($attribute,$this,false)] = $this->_compare_get_values($attribute,$this,false);
        }

      }

      return $changes;
    }
  }


