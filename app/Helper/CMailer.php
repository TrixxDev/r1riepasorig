<?php

namespace App\Helper;

use Illuminate\Support\Facades\Config;

class CMailer {
  public $message;
  public $subject;
  public $senderAddress;
  public $senderName;
  public $recipients;
  public $BCCs;
  public $attachmentStrings;

  function __construct(){
    $this->id = -1;
//    $this->senderAddress = Config::get('app.settings.defaultsender.email');
//    $this->senderName = Config::get('app.settings.defaultsender.name');
    $this->senderAddress = 'indrikis38@gmail.com';
    $this->senderName = 'SIA R1';
    $this->recipients = array();
    $this->BCCs = array();
    $this->attachments=array();
  }

  function addRecipient($address,$name=''){
    $this->recipients[] = array($address,$name);
  }

  function addBCC($address,$name=''){
    $this->BCCs[] = array($address,$name);
  }

  function attachString($attachment,$mime,$filename){
    $this->attachmentStrings[]=array($attachment,$mime,$filename);
  }

  function send(){
    /**
     * Nodarbojamies ar faktisko sūtīšanu
     */
    $mailer = new PHPMailer();
    $mailer->IsSMTP();
//    $mailer->Host = Config::get('app.settings.email.server');
//    $mailer->Username = Config::get('app.settings.email.username');
//    $mailer->Password = Config::get('app.settings.email.password');
    $mailer->Host = 'smtp.gmail.com';
    $mailer->Username = 'indrikis38@gmail.com';
    $mailer->Password = 'edgars1423';
    $mailer->SMTPAuth = (($mailer->Username!='')||($mailer->Password!=''));
    $mailer->CharSet="UTF-8";
    //$mailer->Encoding="7bit"; // or 8bit?
    $mailer->From = $this->senderAddress;
    $mailer->FromName = $this->senderName;
    $mailer->IsHTML(false);

//    if ($this->subject=='') $this->subject=Config::get('app.settings.subject.default');
    if ($this->subject=='') $this->subject='Pieraksts';
    $mailer->Subject = $this->subject;

    //$mailer->AddBCC('admin@majam.lv');

    $mailer->Body = $this->message;

    //$mailer->AddStringAttachment($string, 'order_info.pdf', 'base64', 'application/pdf');

    $mailer->ClearAddresses();
    foreach ($this->recipients as $recipient){
      $mailer->AddAddress($recipient[0],trim($recipient[1]));
    }

    $mailer->ClearBCCs();
    foreach ($this->BCCs as $recipient){
      $mailer->AddBCC($recipient[0],trim($recipient[1]));
    }

//    $mailer->ClearAttachments();
//    foreach ($this->attachmentStrings as $attachment){
//      $mailer->AddStringAttachment($attachment[0], $attachment[2], 'base64', $attachment[1]);
//    }

    $result = $mailer->Send();

//    if (!$result){
//      audit(AUDIT_SEVERITY_WARNING,AUDIT_FACILITY_MESSAGE,-1,0,"Mail delivery failed: ".$mailer->ErrorInfo,$this);
//    } else {
//      audit(AUDIT_SEVERITY_INFO,AUDIT_FACILITY_MESSAGE,-1,0,"Mail sent",$this);
//    }
  }

  function _compare_skip(){
    return array();
  }

  /*function _compare_get_name($n,$new,$old){
    $new_val = $new->$n;
    if ($old === false){
      return $n.': '.$new_val;
    } else {
      $old_val = $old->$n;
      return $n.': '.$old_val.'->'.$new_val;
    }
  }

  function _compare_get_classname(){
    return get_class($this);
  }*/

  /*function compare($old_instance){
    $skip_attrs = $this->_compare_skip();

    $out = '';
    foreach ($this as $attribute => $value){
      if (($attribute!='id')&&($attribute!='tableName')&&($attribute!='idField')&&($attribute!='fieldPrefix')&&(substr($attribute,0,1)!='_')&&(!in_array($attribute,$skip_attrs))){
        if ($old_instance->$attribute != $value){
          // ir bijušas izmaiņas
          $out.='<span class="audit-changed">'.$this->_compare_get_name($attribute,$this,$old_instance)."</span>\n";
        } else {
          $out.='<span class="audit">'.$this->_compare_get_name($attribute,$this,false)."</span>\n";
        }
      }
    }
    return $out;
  }*/

  /**
   * @desc Salīdzina divas objektu instances un atgriež izmaiņu sarakstu kā masīvu 'lauks'=>('vecā vērtība','jaunā vērtība');
   * @param CPersistent $old_instance
   */
  /*function compareEx($old_instance){
    $skip_attrs = $this->_compare_skip();

    $out = array();
    foreach ($this as $attribute => $value){
      if (($attribute!='id')&&($attribute!='tableName')&&($attribute!='idField')&&($attribute!='fieldPrefix')&&(substr($attribute,0,1)!='_')&&(!in_array($attribute,$skip_attrs))){
        if (($old_instance===false)||($old_instance->$attribute != $value)){
          // ir bijušas izmaiņas
          if ($old_instance!==false){
            $out[$attribute] = array($old_instance->$attribute,$this->$attribute);
          } else {
            $out[$attribute] = array(false,$this->$attribute);
          }
        } else {
          // izmaiņu nav bijis - tādus laukus nefiksējam

        }
      }
    }
    return $out;
  }*/
}
?>
