<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queueslot extends Model
{

    public $queue_id;
    public $date;		// datums, kurā ir attiecīgais slots
    public $iorder;		// intervāla numurs rindā (diennaktī!)
    public $status;		// atvērts/slēgts
    public $status2;	// Sekundārā klienta atvērts/slēgts
    public $takenBy;	// Klienta informācija (JSON objekts)
    public $takenBy2;	// Sekundārā klienta informācija (rindām, kam ir vienā slotā iespējams pierakstīt divus)
    public $comment;
    public $createTime;
    public $createUser;
    public $editTime;
    public $editUser;

    public function __construct(){
        parent::__construct();
        $this->createTime = '';
        $this->status = SLOT_STATUS_FREE;
        $this->status2 = SLOT_STATUS_FREE;
    }

}
