<?php

namespace App\Http\Controllers;

use App\Helper\SmsSender;
use App\Helper\Tires;
use App\Models\Audit;
use App\Models\Autostock;
use App\Models\Autotire;
use App\Models\Moto;
use App\Models\Motostock;
use App\Models\Office;
use App\Models\Quickorder;
use App\Services\AccrualOrderVerificationService;
use App\Models\Service;
use App\Models\Slot;
use App\Models\User;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Redirect;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{

    public static $connection;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
      Self::$connection = false;
    }

    public function checkSession(Request $request) {

      if ($request->isMethod('post')) {
        $user = User::findOrFail(Auth::user()->id);
        $minutesToAdd = gmdate('i', env('session_lifetime'));

        $userTime = \Carbon\Carbon::now()->addMinutes($minutesToAdd)->format('Y-m-d H:i');

        $user->timestamps = false;
        $user->lastActivityTime = $userTime;
        $user->save();
        return 1;
      }

      if (Auth::check()) {

        $user = User::findOrFail(Auth::user()->id);
        $currentTime = date('Y-m-d H:i', time());

        $timeLeft = \Carbon\Carbon::parse($user->lastActivityTime)->subMinutes(3)->format('Y-m-d H:i');

        if ($currentTime === $timeLeft) {
          return 1;
        } else {
          return 0;
        }

      }
    }

    public function dragNdrop(Request $request) {
      return view('testings');
    }

    public function login(Request $request) {

	$form = (object) ['ownerEmail' => 'indrikis38@gmail.com'];

      $details = [
          'car' => 1,
          'make' => 1,
          'purpose' => 1,
          'office' => 1,
          'day' => 1,
          'date' => 1,
          'time' => 1,
          'longPurpose' => 1
        ];

      Mail::to($form->ownerEmail)->send(new \App\Mail\Mail($details));

      if ($request->post()) {

        $field = (str_contains($request->username, '@') || !$request->username) ? 'email' : 'username';
        $request->merge([$field => $request->username]);

        $request->validate([
          $field => 'required|string',
          'password' => 'required|string',
        ]);

        $credentials = array(
          $field => $request->$field,
          "password" => $request->password,
        );

        if (Auth::attempt($credentials)) {
          return 1;
        }

        return 0;

      }
      return view('testings');
    }

    public function changeArticles(Request $request)
    {

      if ($request->post()){

        $out = '';
        $count = 0;

        $data = $request->articles;
        $rows = explode("\n", trim($data));

        foreach ($rows as $idx=>$row){
          $row = trim($row);
          if (($idx>-1)&&($row!='')) {
            $fields = explode("\t", $row);

            $article = $fields[0];
            $i3Article = $fields[1];

            $itype = 'i3';

            $tire = Moto::where('article', $article)->first();
            if (!$tire) continue;
            $stock = Motostock::where('tire_id', $tire->tire_id)->where('itype', $itype)->first();
            if (!$stock) {
              $tire->addSecondaryArticle($i3Article, 'i3');
              $out .= 'Nav atrasts ieraksts ar ID - ' . $tire->tire_id . '<br>';
              $count++;
              continue;
            }
            $stock->article = $i3Article;
            $stock->save();
          }
        }

        return $out . 'Nav atrasti - ' . $count . ' ieraksti';

      }

      return '<form method="post">' . @csrf_field() . '<textarea name="articles" id="" cols="30" rows="10"></textarea><button type="submit">Aiziet</button></form>';

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function contacts()
    {
        return view('main.contacts');
    }

    public function terms()
    {
        return view('main.terms');
    }

//    public function about()
//    {
//        return view('pages.internet-veikals');
//    }

    public function moto_terms()
    {
        return view('main.moto_terms');
    }

    public function services()
    {
        return view('main.services');
    }

//    public function conditioner()
//    {
//        return view('pages.kondicionieris');
//    }

    public function pages(Request $request, $page)
    {

      $db = DB::table('pages')->where('route', $page)->first();

      if ($db === null) {
        return Redirect::to(route('home'));
      }

      return view('pages.' . $page);
    }

    public function accrualOrder(Request $request)
    {

      Self::$connection = @ftp_connect(env('ACCRUAL_IP'), 21, 5);
      if (!Self::$connection) {
        return json_encode(['danger' => 'Nesanāk savienoties ar Accrual serveri (FTP connect).']);
      }

      @ftp_set_option(Self::$connection, FTP_TIMEOUT_SEC, 10);

      if (!@ftp_login(Self::$connection, 'r1_web', 'RA5bgdGc')){
        @ftp_close(Self::$connection);
        Self::$connection = null;
        return json_encode(['danger' => 'Nesanāk autorizēties Accrual serverī (FTP login).']);
      }

      if (!@ftp_pasv(Self::$connection, true)) {
        @ftp_close(Self::$connection);
        Self::$connection = null;
        return json_encode(['danger' => 'Nesanāk ieslēgt FTP pasīvo režīmu (PASV).']);
      }

      function uploadFTP($local_file, $remote_file){
        if (!is_file($local_file)) {
          return ['ok' => false, 'reason' => 'missing_local_file'];
        }

        $uploaded = @ftp_put(HomeController::$connection, $remote_file, $local_file, FTP_BINARY);
        @ftp_close(HomeController::$connection);
        HomeController::$connection = null;
        if (!$uploaded) {
          return ['ok' => false, 'reason' => 'ftp_put_failed'];
        }

        return ['ok' => true];
      }

      $location = $request->info['location'];
      switch ($location) {
        case 'URS':
          $location = 'Noliktava';
          $location_prefix = 'U';
          break;
        case 'KRS':
          $location = 'Veikals';
          $location_prefix = 'K';
          break;
        default:
          break;
      }
      $summa = $request->info['total'];
      $summa = round((float)$summa, 0);
      $summa_pvn = $summa - ($summa / 1.21);
      $summa_pvn = number_format((float)$summa_pvn, 2, '.', '');

      $articles = explode('$', $request->info['article']);

      $articleArray = $articles;
      $qtyArray = $request->info['qty'];
      $priceArray = $request->info['price'];

      if (is_array($articleArray) && count($articleArray) > 1 || is_array($qtyArray) || is_array($priceArray)) {
        for ($i = 0; $i < count($articleArray); $i++) {
          $ieraksti[$i] = [
            $articleArray[$i],
            $qtyArray[$i],
            $priceArray[$i]
          ];
        }
      } else {
        $ieraksti[0] = [
          $articleArray[0],
          $qtyArray,
          $priceArray
        ];
      }

//      $prod = $request->info['prod'];
//      $quantity = $request->info['qty'];
//      $price = $request->info['price'];
//      $price_pvn = ($price / 1.21);
//      $price_pvn = number_format((float)$price_pvn, 2, '.', '');
      $comment = $request->info['comments'];
      $user = $request->info['user'];
      $documentType = $request->info['document_type'] ?? 'order';
      $isPrepayment = $documentType === 'prepayment';
      $pzType = $isPrepayment ? 8 : 6;
      $partnerId = (int) ($request->info['partner_id'] ?? 0);

      if ($isPrepayment && $partnerId === 0) {
        return json_encode(['danger' => 'Rēķinam priekšapmaksai jānorāda klients.']);
      }

      $partnerName = '';
      $partnerRegNr = '';
      $partnerAddress = '';
      if ($partnerId > 0) {
        $partnerService = app(\App\Services\AccrualPartnerService::class);
        $partner = $partnerService->findById($partnerId);
        if ($partner) {
          $partnerName = $partner['name'] ?? '';
          $partnerRegNr = $partner['regnr'] ?? '';
          $partnerAddress = $partner['address'] ?? '';
        }
      }

      $xmlEscape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
      };

      $mobile = 0;
      $mobile_number = '';
      if (isset($request->info['mobile'])) {
        $mobile = $request->info['mobile'];
        $mobile_number = ($mobile == 1) ? '' . $request->info['mobile_number'] : '';
      }

      @$montage = $request->info['montage'];
      @$montage_price = round($request->info['price_montage']);
      @$montage_price_pvn = $montage_price / 1.21;

      @$safe = $request->info['safe'];
      @$safe_price = $request->info['price_safe'];
      @$safe_price_pvn = $safe_price / 1.21;

      $xml_order = new QuickOrder;
      $xml_order->office = $request->info['location'];
      $xml_order->item_article = '';
      $xml_order->quantity = 0;
      $xml_order->price_per_one = 0;
      $xml_order->total_price = 0;
      $xml_order->fitting = $montage;
      $xml_order->fitting_price = $montage_price_pvn;
      $xml_order->safe = $safe;
      $xml_order->safe_price = $safe_price_pvn;
      $xml_order->phone = $mobile;
      $xml_order->phone_number = $mobile_number;
      $xml_order->admin_id = $user;
      $xml_order->sms_sended = 0;
      $xml_order->created_at = date("Y-m-d H:i:s");
      $xml_order->updated_at = date("Y-m-d H:i:s");
      $xml_order->save();
      $order = $xml_order;
      $xml_order = $order->order_id;

      $number = str_pad($xml_order, 4, '0', STR_PAD_LEFT);
      $number = $location_prefix . '-' . $number;

      $xml_string = '<?xml version="1.0" encoding="UTF-8"?>';
      $xml_string .= '<AccrualPZ>';
      $xml_string .= '<PZHeader>';
      $xml_string .= '<Struktura>' . $xmlEscape($location) . '</Struktura>';
      $xml_string .= '<Type>' . $pzType . '</Type>';
      $xml_string .= '<WEB>' . $xml_order . '</WEB>';
      $xml_string .= '<Datums>' . date('d.m.Y') . '</Datums>';
      if ($isPrepayment) {
        if ($partnerId > 0) {
          $xml_string .= '<PartnerId>' . $partnerId . '</PartnerId>';
        }
        $xml_string .= '<PartnNosaukums>' . $xmlEscape($partnerName) . '</PartnNosaukums>';
        if ($partnerRegNr !== '') {
          $xml_string .= '<RegNr>' . $xmlEscape($partnerRegNr) . '</RegNr>';
        }
        if ($partnerAddress !== '') {
          $xml_string .= '<JurAdrese>' . $xmlEscape($partnerAddress) . '</JurAdrese>';
        }
        $xml_string .= '<ApmVeidsId>2</ApmVeidsId>';
      }
      $xml_string .= '<PVNSumma>' . $summa_pvn . '</PVNSumma>';
      $xml_string .= '<Summa>' . round($summa - $summa_pvn, 2) . '</Summa>';
      $xml_string .= '<GalaSumma>' . round($summa, 0) . '</GalaSumma>';
      $xml_string .= '<Valuta>EUR</Valuta>';
      if ($comment != '') {
        $xml_string .= '<Piezimes>' . $xmlEscape($number . ' ' . $comment . ' T.' . $mobile_number) . '</Piezimes>';
      } else {
        $xml_string .= '<Piezimes>' . $xmlEscape($number . ' ' . $mobile_number) . '</Piezimes>';
      }
      $xml_string .= '<SasPerson>' . $xmlEscape($user) . '</SasPerson>';
      $xml_string .= '</PZHeader>';
      $xml_string .= '<Ieraksti>';
      foreach ($ieraksti as $ieraksts) {
        $xml_string .= '<Ieraksts>';
        $xml_string .= '<Artikuls>' . $xmlEscape($ieraksts[0]) . '</Artikuls>';
//      $xml_string .= '<Nosaukums>' . $prod . '</Nosaukums>';
        $xml_string .= '<Mervieniba>gab</Mervieniba>';
        $price_pvn = ($ieraksts[2] / 1.21);
        $price_pvn = number_format((float)$price_pvn, 2, '.', '');
        $xml_string .= '<Cena>' . $price_pvn . '</Cena>';
        $xml_string .= '<Daudzums>' . $ieraksts[1] . '.000</Daudzums>';
        $xml_string .= '<Summa>' . $ieraksts[2] . '</Summa>';
        $xml_string .= '<Nodoklis>PVN 21%</Nodoklis>';
        $xml_string .= '<Likme>21.00</Likme>';
        $xml_string .= '</Ieraksts>';
      }
      if ($montage == 1) {
        $xml_string .= '<Ieraksts>';
        $xml_string .= '<Artikuls>04</Artikuls>';
        $xml_string .= '<Mervieniba>kompl.</Mervieniba>';
        $xml_string .= '<Cena>' . $montage_price_pvn . '</Cena>';
        $xml_string .= '<Daudzums>1.000</Daudzums>';
        $xml_string .= '<Summa>' . $montage_price . '</Summa>';
        $xml_string .= '<Nodoklis>PVN 21%</Nodoklis>';
        $xml_string .= '<Likme>21.00</Likme>';
        $xml_string .= '</Ieraksts>';
      }
      if ($safe == 1) {
        $xml_string .= '<Ieraksts>';
        $xml_string .= '<Artikuls>18</Artikuls>';
        $xml_string .= '<Mervieniba>kompl.</Mervieniba>';
        $xml_string .= '<Cena>' . $safe_price_pvn . '</Cena>';
        $xml_string .= '<Daudzums>1.000</Daudzums>';
        $xml_string .= '<Summa>' . $safe_price . '</Summa>';
        $xml_string .= '<Nodoklis>PVN 21%</Nodoklis>';
        $xml_string .= '<Likme>21.00</Likme>';
        $xml_string .= '</Ieraksts>';
      }
      $xml_string .= '</Ieraksti>';
      $xml_string .= '</AccrualPZ>';


      $dom = new DOMDocument();
      $dom->preserveWhiteSpace = FALSE;
      $dom->loadXML($xml_string);
      $dom->formatOutput = TRUE;

      $xml_string = $dom->saveXML();

      $xmlDir = public_path('storage/xml');
      if (!is_dir($xmlDir)) {
        @mkdir($xmlDir, 0755, true);
      }

      $filePrefix = $isPrepayment ? 'prieksrekins' : 'pasutijums';
      $localFileName = $filePrefix . $xml_order . '.xml';
      $remoteFileName = $filePrefix . $xml_order . '.xml';
      $file = $xmlDir . '/' . $localFileName;

      file_put_contents($file, $xml_string);

      $uploadResult = uploadFTP($file, $remoteFileName);

      if (file_exists($file)) {
        unlink($file);
      }

      if (!($uploadResult['ok'] ?? false)) {
        $uploadReason = $uploadResult['reason'] ?? 'unknown';
        $docLabel = $isPrepayment ? 'Rēķina priekšapmaksai' : 'Pasūtījuma';
        if ($uploadReason === 'missing_local_file') {
          return json_encode(['danger' => $docLabel . ' XML fails nav atrasts pirms FTP sūtīšanas.']);
        }
        return json_encode(['danger' => $docLabel . ' XML nav nosūtīts uz Accrual (FTP upload, reason: ' . $uploadReason . ').']);
      }

      $verifier = app(AccrualOrderVerificationService::class);
      $verifyResult = $verifier->waitForCreation((int) $order->order_id, $pzType, (float) $summa);
      if (!($verifyResult['confirmed'] ?? false)) {
        $docLabel = $isPrepayment ? 'Rēķina priekšapmaksai' : 'Pasūtījuma';
        $verifyError = $verifyResult['error'] ?? 'timeout';
        if ($verifyError === 'db_connection') {
          return json_encode(['danger' => $docLabel . ' nav apstiprināts: neizdevās savienoties ar Accrual datubāzi.']);
        }

        return json_encode([
          'danger' => $docLabel . ' XML ir nosūtīts, bet Accrual datubāzē ieraksts vēl nav apstiprināts (WEB='
            . (int) $order->order_id . '). Mēģiniet vēlreiz pēc brīža.',
        ]);
      }

      $accrualHeader = $verifyResult['header'] ?? [];
      $accrualPzLabel = is_array($accrualHeader) && $accrualHeader !== []
        ? $verifier->accrualDocumentLabel($accrualHeader)
        : '';

      $auditMessage = $isPrepayment
        ? 'Izveidots jauns rēķins priekšapmaksai'
        : 'Izveidots jauns ātrais pasūtījums';
      Audit::audit(AUDIT_SEVERITY_DEBUG, AUDIT_FACILITY_MESSAGE, $order->order_id, 0, $auditMessage, $order);

      if ($isPrepayment) {
        $successExtra = $accrualPzLabel !== '' ? '<br>Accrual: <b>' . htmlspecialchars($accrualPzLabel, ENT_QUOTES, 'UTF-8') . '</b>' : '';

        return json_encode([
          'success' => 'Rēķins priekšapmaksai ir apstiprināts Accrual!<br><b>' . htmlspecialchars($partnerName, ENT_QUOTES, 'UTF-8') . '</b><br><b>' . $number . '</b>' . $successExtra,
          'orderId' => $number,
          'documentType' => 'prepayment',
          'previewUrl' => url('/prepayment-invoice/' . rawurlencode($number) . '?' . http_build_query(array_filter([
            'partner' => $partnerName,
            'total' => $summa,
          ]))),
        ]);
      }

      $successExtra = $accrualPzLabel !== '' ? '<br>Accrual: <b>' . htmlspecialchars($accrualPzLabel, ENT_QUOTES, 'UTF-8') . '</b>' : '';

      return json_encode([
        'success' => 'Pasūtījums ir pieņemts Accrual!<br><b>' . $number . '</b>' . $successExtra,
        'orderId' => $number,
        'documentType' => 'order',
      ]);

    }

    public function sendOrderSMS(Request $request)
    {
      $sms = new SmsSender();
      $sms->sendOrderSMS($request->info, $request->orderId);
    }

    public function fastOrder() {
//      $param = (object) request()->input();

//      , compact('param', 'links')
      return view('/testing3');
    }

    public function getLinks(Request $request) {
      $model = new SyncController();
      foreach ($request->articles as $article) {
        $links[$article] = $model->getStockLinks($article);
      }
      return $links;
    }

    public function changeName($array, $oldName, $newName)
    {
      if(!array_key_exists($oldName, $array))
      {
        return $array;
      }

      $names = array_keys($array);

      $names[array_search($oldName, $names)] = $newName;

      return array_combine($names, $array);
    }

    public function checkForTaken($args){
      return count(array_filter($args,function($v){return $v !== null;})) === 0;
    }

    public function getPrevQueue($queueList, $iorder, $date)
    {
      $queues = [];
      foreach ($queueList as $queue) {
        $slot = Slot::where('date', $date)->where('iorder', $iorder)->where('queue_id', $queue->queue_id)->first();
        if ($slot) {
          if ($slot->status == 0) {
            $queues[$queue->queue_id] = $slot;
          } else {
            $queues[$queue->queue_id] = null;
          }
        } else {
          $queues[$queue->queue_id] = null;
        }
      }
      return $queues;
    }

    public function getLastNonNullValue($array) {
      $array = array_reverse($array);
      return array_filter($array, function($slot) {
        if ($slot) {
          return $slot->status != 1;
        } else {
          return null;
        }
      });
    }


  public function queuetest(Request $request)
  {

    if ($request->post()) {
      $office = Office::where('office_id', $request->office_id)->first();

      $days = [];

      $date = date('Y-m-d');
      $visibleDays = 8;
      $todayDate = strtotime($date);

      $office->loadMobileQueues();
      foreach ($office->_queues as $queue) {
        $queue->loadWorkingDay($date, false);
        $queue->loadSlots($date, false);
        $slotSizes[] = $queue->_workingDays[$date]->slotSize;
        $workingDays[] = $date;
        for ($i = 1; $i < $visibleDays; $i++) {
          $ndate = date('Y-m-d', strtotime("+{$i} days", $todayDate));
          $queue->loadWorkingDay($ndate, true);
          $queue->loadSlots($ndate, true);
          $workingDays[] = $ndate;
        }
      }

      $workingDays = array_unique($workingDays);

      $_weekDays = array(
        1 => 'Pirmdiena',
        2 => 'Otrdiena',
        3 => 'Trešdiena',
        4 => 'Ceturtdiena',
        5 => 'Piektdiena',
        6 => 'Sestdiena',
        7 => 'Svētdiena',
      );

      $tires = new Tires();
      $timeStep = $tires->arrayGCD($slotSizes);

      $services = Service::orderBy('service_id', 'ASC')->get();

        $out = '<div class="w"><div><div class="reservation">';
        for ($day = 0; $day < $visibleDays; $day++) {

          $date = $workingDays[$day];
          $dayOfWeek = $_weekDays[date('N', strtotime($date.' 00:00:00'))];
          $dateFmt = date('d.m.Y', strtotime($date.' 00:00:00'));
          $today = date('Y-m-d');

          $office->_openQueues = 0;
          foreach ($office->_queues as $queue){
            if ($queue->isVisible($date)) $office->_openQueues++;
          }

          if ($office->_openQueues > 0) {
            $out .= '<h3>' . $office->title . ' | ' . $dayOfWeek . ' ' . $dateFmt . '</h3>';
          } else {
            $out .= '';
          }

          $out .= '<div class="time-list" data-date="' . $date . '" style="margin-left:8px;">';

          $openTime = 0;
          $closeTime = -1;

          if ($openTime==0){
            $openTime = $office->getOpenTime($date);
          } else {
            $t = $office->getOpenTime($date);
            if ($t>0){
              $openTime = min($openTime, $t);
            }
          }
          $closeTime = max($closeTime, $office->getCloseTime($date));

          for ($i=$openTime;$i<$closeTime;$i+=$timeStep) {
            foreach ($office->_queues as $queue) {
              if ($queue->isIntervalBeginning($date,$i)) {
                $office->loadWorkingDays($date);
                $slots[$queue->getSlotNumberByInterval($date, $i)] = [
                  'time' => Office::timeByInterval($i),
                  'slots' => $this->getPrevQueue($office->_workingDays, $queue->getSlotNumberByInterval($date, $i), $date),
                  'date' => $date];
              }
            }
          }

          if (!empty($slots)) {
            foreach ($slots as $slot_iorder => $slot_info){
              if ($date == $slot_info['date']) {
                $freeSlot = $this->getLastNonNullValue($slot_info['slots']);
                if (!empty($freeSlot)) {
                  $slot = $freeSlot[array_key_first($freeSlot)];
                  $slot_id = 'data-slot_id=' . $slot->slot_id;
                  if (stripos($slot->comment, '% darbam') !== false) {
                    $slotText = str_replace('!', '', $slot->comment);
                    $slotText = '<span>' . $slotText . '</span>';
                    $availability = 'discount available';
                    $discount = true;
                  } else {
                    $slotText = 'Brīvs';
                    $slotText = '<span>' . $slotText . '</span>';
                    $availability = 'available';
                    $discount = false;
                  }
                } else {
                  $slotText = 'Aizņemts';
                  $slot_id = '';
                  $availability = 'unavailable';
                  $discount = false;
                }
                $out .= '<div class="time-slot">';
                $out .= '<div ' . $slot_id . ' class="' . $availability . ' slot active">' . $slot_info['time'] . '<br>' . $slotText . '</div>';
                $out .= '<div class="dots">';
                if (!empty($slot_info['slots'])) {
                  foreach ($slot_info['slots'] as $slot) {
                    if ($slot !== null) {
                      if (stripos($slot->comment, '% darbam') !== false) {
                        $out .= '<span class="dot-availability text-center">
                        <span class="dot orange" data-toggle="tooltip" data-html="true" title="Atlaide">
                          <span class="sort-order">orange</span>
                        </span>
                      </span>';
                      } else {
                        $out .= '<span class="dot-availability text-center">
                        <span class="dot green" data-toggle="tooltip" data-html="true" title="Brīvs">
                          <span class="sort-order">green</span>
                        </span>
                      </span>';
                      }
                    } else {
                      $out .= '<span class="dot-availability text-center">
                      <span class="dot red" data-toggle="tooltip" data-html="true" title="Aizņemts">
                        <span class="sort-order">red</span>
                      </span>
                    </span>';
                    }
                  }
                } else {
                  $out .= '<span class="dot-availability text-center">
                  <span class="dot transparent" style="" data-toggle="tooltip" data-html="true" title="Aizņemts">
                    <span class="sort-order">transparent</span>
                  </span>
                </span>';
                }
                $out .= '</div></div>';
              }
            }
          }

          $out .= '</div>';

        }
        $out .= '</div></div>';

        return $out;
    }


    return view('queuetest');

    }
}

