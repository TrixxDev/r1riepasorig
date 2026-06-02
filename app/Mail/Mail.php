<?php

namespace App\Mail;

use App\Models\Audit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail as EMail;

class Mail extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  /**
   * The HTML message content.
   *
   * @var string
   */
  public string $htmlContent;

  /**
   * Create a new message instance.
   *
   * @param string $htmlContent
   */
  public function __construct(string $htmlContent)
  {
    $this->htmlContent = $htmlContent;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->from(config('mail.from.address') ?: env('MAIL_USERNAME'), config('mail.from.name') ?: env('MAIL_FROM_NAME'))
                ->bcc(['karlis@r1riepas.lv', 'indrikis38@gmail.com'])
                ->subject(env('MAIL_SUBJECT'))
                ->html($this->htmlContent);
  }

  /**
   * Send the message.
   *
   * @param $mailer
   * @return void
   */
  public function send($mailer)
  {
    parent::send($mailer);

    if (count(EMail::failures()) > 0) {
      Audit::audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, -1,0, 'Mail delivery failed: ' . implode(',', EMail::failures()));
    } else {
      Audit::audit(AUDIT_SEVERITY_INFO,AUDIT_FACILITY_MESSAGE,-1,0,"Mail sent");
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

