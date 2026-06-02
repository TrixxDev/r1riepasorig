<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ShopController extends Controller
{

  public $status_enum = [
    1 => 'Nav pabeigts/Nav informācijas',
    2 => 'Jauns',
    9 => 'Procesā',
    3 => 'Gaidām apmaksu',
    4 => 'Gaidām preci',
    8 => 'Gaida piegādi',
    6 => 'Prece nav pieejama',
    7 => 'Klients atteicās',
    10 => 'Kļūdains pasūtījums',
    11 => 'Klients nav sazvanāms',
    5 => 'Pabeigts'
];

  public $pay_enum = [
    0 => '',
    1 => 'Apmaksa saņemšanas brīdī',
    2 => 'Bankas pārskaitījums',
    3 => 'Tiešsaistes apmaksa'
  ];

  public $status_class_map = [
    1 => 'pending',        // Nav pabeigts/Nav informācijas
    2 => 'new',            // Jauns
    9 => 'processing',     // Procesā
    3 => 'waiting',        // Gaidām apmaksu
    4 => 'waiting',        // Gaidām preci
    8 => 'waiting',        // Gaida piegādi
    6 => 'unavailable',    // Prece nav pieejama
    7 => 'cancelled',      // Klients atteicās
    10 => 'error',         // Kļūdains pasūtījums
    11 => 'unreachable',   // Klients nav sazvanāms
    5 => 'completed'       // Pabeigts
  ];

  public $filteredStatus;
  public $filteredEditor;

  public function __construct(Request $request)
  {

    $this->filteredStatus = ($request->input('admin-order-status-select')) ? $request->input('admin-order-status-select') : 0;
    $this->filteredEditor = ($request->input('admin-order-editor-select')) ? $request->input('admin-order-editor-select') : 0;
    View::share('filteredStatus', $this->filteredStatus);
    View::share('filteredEditor', $this->filteredEditor);
  }

  public function orderLogs()
  {
    $logFile = storage_path('logs/order_tracking.log');
    
    if (!file_exists($logFile)) {
      return view('admin.shop.order_logs', [
        'logs' => [],
        'error' => 'Log file not found'
      ]);
    }
    
    $groupedLogs = [];
    $fileContent = file_get_contents($logFile);

    if ($fileContent !== false) {
      $lines = array_reverse(explode("\n", trim($fileContent)));

      foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
          continue;
        }

        $timestamp = null;
        if (preg_match('/^\[(.*?)\]/', $line, $matches)) {
          $timestamp = $matches[1];
          $message = trim(substr($line, strlen($matches[0])));
        } else {
          $message = $line;
        }

        $context = [];
        if (strpos($message, ' | Context: ') !== false) {
          [$message, $contextJson] = explode(' | Context: ', $message, 2);
          $decoded = json_decode($contextJson, true);
          if (is_array($decoded)) {
            $context = $decoded;
          }
        }

        $orderId = $context['order_id'] ?? ($context['orderid'] ?? null);
        $orderNumber = $context['order_number'] ?? null;

        if ($orderNumber === null && isset($context['orderid'])) {
          $orderNumber = $context['orderid'];
        }

        if ($orderNumber !== null) {
          $groupKey = 'order_number:' . $orderNumber;
        } elseif ($orderId !== null) {
          $groupKey = 'order_id:' . $orderId;
        } else {
          $groupKey = 'system';
        }

        if (!isset($groupedLogs[$groupKey])) {
          $groupedLogs[$groupKey] = [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'customer_email' => $context['customer_email'] ?? null,
            'entries' => [],
          ];
        } else {
          if (!$groupedLogs[$groupKey]['order_id'] && $orderId !== null) {
            $groupedLogs[$groupKey]['order_id'] = $orderId;
          }

          if (!$groupedLogs[$groupKey]['customer_email'] && !empty($context['customer_email'])) {
            $groupedLogs[$groupKey]['customer_email'] = $context['customer_email'];
          }
        }

        $severity = 'info';
        if (stripos($message, 'error') !== false) {
          $severity = 'error';
        } elseif (stripos($message, 'success') !== false || stripos($message, 'completed') !== false) {
          $severity = 'success';
        }

        $groupedLogs[$groupKey]['entries'][] = [
          'message' => $message,
          'timestamp' => $timestamp,
          'context' => $context,
          'severity' => $severity,
        ];
      }
    }

    return view('admin.shop.order_logs', [
      'groupedLogs' => $groupedLogs,
    ]);
  }

  public function orders(Request $request)
  {
    $status_enum = $this->status_enum;
    $pay_enum = $this->pay_enum;
    $users = \App\Models\User::select('id', 'name', 'surname')->get();

    if($request->post() || $this->filteredStatus || $this->filteredEditor) {
      $orders = Order::with('editor')
      ->where('order_status', '!=', 1)
      ->when($this->filteredStatus, function($query) {
        $query->where('order_status', $this->filteredStatus);
      })->when($this->filteredEditor, function($query) {
        $query->where('edituser', $this->filteredEditor);
      })
      // Filtrējam pasūtījumus bez kontaktinformācijas
      // ВРЕМЕННО ОТКЛЮЧЕН ФИЛЬТР - показываем все заказы
      // ->where(function($query) {
      //     $query->where(function($q) {
      //         $q->whereNotNull('phone_number')
      //           ->where('phone_number', '!=', '');
      //     })
      //     ->orWhere(function($q) {
      //         $q->whereNotNull('email')
      //           ->where('email', '!=', '');
      //     });
      // })
      ->orderBy('order_number', 'desc')->get();

      // Aprēķinam pareizās summas katram pasūtījumam
      foreach($orders as $order) {
        $item_sum = 0;
        
        // Iegūstam datus no serializētā lauka order_details
        $orderDetails = json_decode($order->order_details);
        if (!$orderDetails || !isset($orderDetails->products)) continue;
        
        // Aprēķinam preču summu
        foreach ($orderDetails->products as $product) {
          $item_sum += $product->price * $product->quantity;
        }

        // Piemērojam atlaidi, ja tāda ir
        if ($order->promo_code) {
          $promo = Promo::where('promo_code', $order->promo_code)->first();
          if ($promo) {
            if ($promo->discount_type === 'percentage') {
              $discount = $item_sum * ($promo->discount_value / 100);
            } else {
              $discount = $promo->discount_value;
            }
            $item_sum = round($item_sum - $discount);
          }
        }

        // Pievienojam piegādes vai montāžas izmaksas
        if ($order->delivery_price > 0) {
          $item_sum += $order->delivery_price;
        } else if ($order->mounting_price > 0) {
          $item_sum += $order->mounting_price;
        }

        $order->total_sum = $item_sum;
      }

      $orders = new \Illuminate\Pagination\LengthAwarePaginator(
        $orders->forPage($request->input('page', 1), 100),
        $orders->count(),
        100,
        $request->input('page', 1),
        ['path' => $request->url(), 'query' => $request->query()]
      );

      return view('admin.shop.index', compact('orders', 'status_enum', 'pay_enum', 'users'))->with(['getStatusClass' => [$this, 'getStatusClass'], 'status_class_map' => $this->status_class_map]);
    }

    $orders = Order::with('editor')
      ->where('order_status', '!=', 1)
      ->orderBy('order_number', 'desc')
      // Filtrējam pasūtījumus bez kontaktinformācijas
      // ВРЕМЕННО ОТКЛЮЧЕН ФИЛЬТР - показываем все заказы
      // ->where(function($query) {
      //     $query->where(function($q) {
      //         $q->whereNotNull('phone_number')
      //           ->where('phone_number', '!=', '');
      //     })
      //     ->orWhere(function($q) {
      //         $q->whereNotNull('email')
      //           ->where('email', '!=', '');
      //     });
      // })
      ->get();
    
    // Aprēķinam pareizās summas katram pasūtījumam
    foreach($orders as $order) {
      $item_sum = 0;
      
      // Iegūstam datus no serializētā lauka order_details
      $orderDetails = json_decode($order->order_details);
      if (!$orderDetails || !isset($orderDetails->products)) continue;
      
      // Aprēķinam preču summu
      foreach ($orderDetails->products as $product) {
        $item_sum += $product->price * $product->quantity;
      }

      // Piemērojam atlaidi, ja tāda ir
      if ($order->promo_code) {
        $promo = Promo::where('promo_code', $order->promo_code)->first();
        if ($promo) {
          if ($promo->discount_type === 'percentage') {
            $discount = $item_sum * ($promo->discount_value / 100);
          } else {
            $discount = $promo->discount_value;
          }
          $item_sum = round($item_sum - $discount);
        }
      }

      // Pievienojam piegādes vai montāžas izmaksas
      if ($order->delivery_price > 0) {
        $item_sum += $order->delivery_price;
      } else if ($order->mounting_price > 0) {
        $item_sum += $order->mounting_price;
      }

      $order->total_sum = $item_sum;
    }

    $orders = new \Illuminate\Pagination\LengthAwarePaginator(
      $orders->forPage($request->input('page', 1), 100),
      $orders->count(),
      100,
      $request->input('page', 1),
      ['path' => $request->url(), 'query' => $request->query()]
    );

    return view('admin.shop.index', compact('orders', 'status_enum', 'pay_enum', 'users'))->with(['getStatusClass' => [$this, 'getStatusClass'], 'status_class_map' => $this->status_class_map]);
  }

  public function orders_print(Request $request)
  {
    $status_enum = $this->status_enum;
    $pay_enum = $this->pay_enum;
    $orders = [];

    if (isset($request->orders_from, $request->orders_to)) {
      $dateFrom = Carbon::parse($request->orders_from)->startOfDay();
      $dateTo = Carbon::parse($request->orders_to)->endOfDay();

      $orders = Order::whereBetween('created_at', [$dateFrom, $dateTo])
        // Filtrējam pasūtījumus bez kontaktinformācijas
        // ВРЕМЕННО ОТКЛЮЧЕН ФИЛЬТР - показываем все заказы
        // ->where(function($query) {
        //     $query->where(function($q) {
        //         $q->whereNotNull('phone_number')
        //           ->where('phone_number', '!=', '');
        //     })
        //     ->orWhere(function($q) {
        //         $q->whereNotNull('email')
        //           ->where('email', '!=', '');
        //     });
        // })
        ->orderBy('order_number', 'desc')
        ->get();
    } else {
      // Iegūstam visus pasūtījumus, ja dati nav norādīti
      $orders = Order::orderBy('order_number', 'desc')
        // Filtrējam pasūtījumus bez kontaktinformācijas
        // ВРЕМЕННО ОТКЛЮЧЕН ФИЛЬТР - показываем все заказы
        // ->where(function($query) {
        //     $query->where(function($q) {
        //         $q->whereNotNull('phone_number')
        //           ->where('phone_number', '!=', '');
        //     })
        //     ->orWhere(function($q) {
        //         $q->whereNotNull('email')
        //           ->where('email', '!=', '');
        //     });
        // })
        ->get();
    }

    $spreadsheet = new Spreadsheet();

    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Pasūtījumi - ' . date('Y-m-d'));

    $sheet->setCellValue('A1', 'Pasūtījuma datums');
    $sheet->setCellValue('B1', 'Klients');
    $sheet->setCellValue('C1', 'Klienta nr.');
    $sheet->setCellValue('D1', 'Klienta e-pasts.');
    $sheet->setCellValue('E1', 'Akcijas');
    $sheet->setCellValue('F1', 'Preču daudzums');
    $sheet->setCellValue('G1', 'Summa');
    $sheet->setCellValue('H1', 'Apmaksas veids');
    $sheet->setCellValue('I1', 'Pasūtījuma statuss');
    $sheet->setCellValue('J1', 'Menedžeris');
    $sheet->setCellValue('K1', 'Preču grupas');
    $sheet->setCellValue('L1', 'Piegādes adrese');
    $sheet->setCellValue('M1', 'Promo kods');

    $b = 2;

    foreach ($orders as $order) {
      $item_count = 0;
      $item_sum = 0;
      
      // Iegūstam datus no serializētā lauka order_details
      $orderDetails = json_decode($order->order_details);
      if (!$orderDetails || !isset($orderDetails->products)) continue;
      
      // Aprēķinam preču summu
      foreach ($orderDetails->products as $product) {
          $item_sum += $product->price * $product->quantity;
          $item_count += $product->quantity;
      }

      // Piemērojam atlaidi, ja tāda ir
      if ($order->promo_code) {
          $promo = Promo::where('promo_code', $order->promo_code)->first();
          if ($promo) {
              if ($promo->discount_type === 'percentage') {
                  $discount = $item_sum * ($promo->discount_value / 100);
              } else {
                  $discount = $promo->discount_value;
              }
              $item_sum = round($item_sum - $discount);
          }
      }

      // Pievienojam piegādes vai montāžas izmaksas
      if ($order->delivery_price > 0) {
          $item_sum += $order->delivery_price;
      } else if ($order->mounting_price > 0) {
          $item_sum += $order->mounting_price;
      }

      $sheet->setCellValue('A' . $b, Carbon::parse($order->created_at)->format('Y-m-d'));
      if (isset($order->customer_name) || isset($order->customer_surname)) {
        $sheet->setCellValue('B' . $b, $order->customer_name . ', ' . $order->customer_surname);
      } else {
        $sheet->setCellValue('B' . $b, 'Nav info');
      }
      $sheet->setCellValue('C' . $b, $order->phone_number);
      $sheet->setCellValue('D' . $b, $order->email);
      $sheet->setCellValue('E' . $b, (isset($order->email_notifications)) ? 'Jā' : 'Nē');
      $sheet->setCellValue('F' . $b, $item_count);
      $sheet->setCellValue('G' . $b, $item_sum);
      $sheet->setCellValue('H' . $b, $pay_enum[$order->payment_method] ?? 'Nav norādīts');
      $sheet->setCellValue('I' . $b, $status_enum[$order->order_status]);
      if (User::find($order->edituser)) {
        $sheet->setCellValue('J' . $b, User::find($order->edituser)->fullName);
      } else {
        $sheet->setCellValue('J' . $b, 'Neviens nav veicis labojumus');
      }
      if (isset($order->delivery_city)) {
        if ($order->delivery_city == 1) {
          $sheet->setCellValue('L' . $b, 'Rīga, ' . $order->delivery_address);
        } else if ($order->delivery_city == 3) {
          $sheet->setCellValue('L' . $b, 'Cits, ' . $order->delivery_address);
        } else {
          $sheet->setCellValue('L' . $b, $order->delivery_address);
        }
      } else {
        $sheet->setCellValue('L' . $b, '');
      }
      if ($order->promo_code){
        $sheet->setCellValue('M' . $b, $order->promo_code);
      }

      $b++;
    }

    $sheet->setAutoFilter('A:H');
    $lastRow = $sheet->getHighestRow();
    $sheet->getStyle('A2:J' . $lastRow)->getAlignment()->setHorizontal('center');
    $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(true);
    foreach ($cellIterator as $cell) {
      if ($cell->getColumn() == 'I') continue;
      $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
    }
    $sheet->getColumnDimension('D')->setWidth(33);
    $sheet->getColumnDimension('E')->setWidth(10);
    $sheet->getColumnDimension('F')->setWidth(10);
    $sheet->getColumnDimension('G')->setWidth(31);
    $sheet->getColumnDimension('H')->setWidth(45);
    $sheet->getColumnDimension('I')->setWidth(27);
    $sheet->getColumnDimension('J')->setWidth(14);

    $writer = new Xlsx($spreadsheet);
    $filename = 'pasutijumi.xlsx';

    $writer->save($filename);

    // Set the content-type:
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filename));
    readfile($filename); // send file
    unlink($filename); // delete file
    exit;
  }

  public function order($id)
  {
    $status_enum = $this->status_enum;
    $pay_enum = $this->pay_enum;

    $order = Order::findOrFail($id);
    
    // Iegūstam datus no serializētā lauka order_details
    $orderDetails = json_decode($order->order_details);
    if (!$orderDetails || !isset($orderDetails->products)) {
      return \Redirect::to(route('admin.orders'))->with('danger', 'Nevar atvērt pasūtījumu');
    }

    // Если payment_method равен NULL, создаем временную переменную с текстом "Nav norādīts"
    $display_payment = null;
    if ($order->payment_method === null) {
      $display_payment = 'Nav norādīts';
    }

    $offices = Office::all();

    $this->preloadOrderProductsStock($orderDetails->products);
    
    // Pārveidojam products par items masīvu, lai tos varētu izmantot attēlojumā
    $tires = [];
    foreach ($orderDetails->products as $article => $product) {
      $productModel = $this->getProductModel($product->url, $product->id, $product->category ?? null);
      
      // NEW: If stored URL is empty but model found, use model's link attribute
      $productUrl = $product->url;
      if ((!$productUrl || empty($productUrl)) && $productModel && isset($productModel->link)) {
        $productUrl = $productModel->link;
      }
      
      $tire = (object)[
        'tire_id' => $product->id,
        'article' => $article,
        'url' => $productUrl,  // Uses current URL from model if available
        'title' => $product->name,
        'quantity' => $product->quantity,
        'price' => $product->price,
        'dotAvailable' => $productModel ? $productModel->dotAvailable : '',
        'stockAvailability' => $productModel ? $productModel->stockAvailability : ''
      ];
      $tires[] = $tire;
    }

    // Izveidojam objektu userData, lai to varētu izmantot attēlojumā
    $userData = (object)[
      'name' => $order->customer_name,
      'surname' => $order->customer_surname,
      'email' => $order->email,
      'phone_number' => $order->phone_number,
      'notes' => $order->comments,
      'company_registration_number' => $order->company_reg_nr,
      'company_pvn_number' => $order->company_pvn_nr,
      'company_name' => $order->company_name,
      'company_address' => $order->company_address,
      'shipping_city' => $order->delivery_city,
      'shipping_address' => $order->delivery_address,
      'fitting_address' => $order->mounting_office,
      'items' => $tires
    ];

    // Aprēķinam kopējo summu
    $item_sum = 0;
    foreach ($orderDetails->products as $article => $product) {
      $item_sum += $product->price * $product->quantity;
    }
    
    // Pārbaudam promokodu
    $promo = null;
    if ($order->promo_code) {
      $promo = \App\Models\Promo::where('promo_code', $order->promo_code)->first();
      if ($promo) {
        if ($promo->discount_type === 'percentage') {
          $discount = $item_sum * ($promo->discount_value / 100);
        } else {
          $discount = $promo->discount_value;
        }
        $item_sum = round($item_sum - $discount);
      }
    }

    // Pievienojam piegādes vai montāžas izmaksas (bet ne abām)
    if ($order->delivery_price > 0) {
      $item_sum += $order->delivery_price;
    } else if ($order->mounting_price > 0) {
      $item_sum += $order->mounting_price;
    }

    // Formatējam piegādes adresi, lai to varētu parādīt
    if ($order->delivery_city == 1) {
      $order->formatted_address = 'Rīga, ' . $order->delivery_address;
    } else if ($order->delivery_city == 3) {
      $order->formatted_address = 'Cits, ' . $order->delivery_address;
    } else {
      $order->formatted_address = $order->delivery_address;
    }

    return view('admin.shop.order', compact('order', 'userData', 'tires', 'offices', 'status_enum', 'pay_enum', 'item_sum', 'promo', 'display_payment'))
      ->with(['getStatusClass' => [$this, 'getStatusClass'], 'status_class_map' => $this->status_class_map]);
  }

  public function order_update(Request $request, $id)
  {
    $order = Order::findOrFail($id);

    // Atjaunojam galveno informāciju
    $fullName = trim((string) $request->customer_name);
    if ($fullName !== '' && strpos($fullName, ' ') !== false) {
      $parts = preg_split('/\s+/', $fullName, 2);
      $order->customer_name = $parts[0];
      $order->customer_surname = $parts[1];
    } else {
      $order->customer_name = $fullName;
      $order->customer_surname = trim((string) $request->customer_surname);
      if ($order->customer_surname === '' && $request->has('customer_surname') === false) {
        $order->customer_surname = null;
      }
    }
    // Normalizējam tālruni: izņemam valsts kodu, atstājam tikai vietējo numuru
    if (isset($request->phone_number)) {
      $rawNumber = (string) $request->phone_number;
      $countryCode = (string) ($order->phone_country_code ?? '');
      $digits = preg_replace('/\D+/', '', $rawNumber);
      $countryDigits = preg_replace('/\D+/', '', $countryCode);

      if (!empty($countryDigits) && substr($digits, 0, strlen($countryDigits)) === $countryDigits) {
        $digits = substr($digits, strlen($countryDigits));
      }

      if ($countryDigits === '371' && strlen($digits) > 8) {
        $digits = substr($digits, -8);
      }

      $order->phone_number = $digits;
    }
    $order->comments = $request->notes;
    $order->admin_info = $request->admin_info;
    
    // Atjaunojam piegādes informāciju
    $deliveryMethodInput = $request->input('delivery_method', null);
    $mountingOfficeInput = $request->input('mounting_office', null);

    $selectedOfficeId = null;
    $isDeliverySelected = $order->delivery_method == 2;

    if ($request->has('delivery_method')) {
      $deliveryMethodRaw = (string) $deliveryMethodInput;

      if ($deliveryMethodRaw === '3') {
        $isDeliverySelected = true;
      } else {
        $isDeliverySelected = false;
        $selectedOfficeId = (int) $deliveryMethodRaw;
      }
    } else {
      $selectedOfficeId = $order->mounting_office;
    }

    if ($isDeliverySelected) {
      $order->delivery_method = 2;
      $order->mounting_office = null;

      if ($request->has('delivery_city')) {
        $order->delivery_city = $request->delivery_city;
      }
      if ($request->has('delivery_address')) {
        $order->delivery_address = $request->delivery_address;
      }
      if ($request->has('door_code')) {
        $order->door_code = $request->door_code;
      }
      if ($request->has('delivery_price')) {
        $order->delivery_price = $request->delivery_price;
      }
      if ($request->has('mounting_price')) {
        $order->mounting_price = $request->mounting_price;
      }
    } else {
      if ($selectedOfficeId !== null) {
        $order->delivery_method = 1;
        $order->mounting_office = $selectedOfficeId;
      }

      $order->delivery_city = null;
      $order->delivery_address = null;
      $order->door_code = null;

      if ($request->has('delivery_price')) {
        $order->delivery_price = $request->delivery_price;
      }
      if ($request->has('mounting_price')) {
        $order->mounting_price = $request->mounting_price;
      }

      if ($request->has('mounting_office')) {
        $officeId = (int) $mountingOfficeInput;
        $order->mounting_office = $officeId;
        $order->delivery_method = 1;
      }
    }
    
    // Atjaunojam uzņēmuma informāciju
    $order->company_reg_nr = $request->company_reg_nr;
    $order->company_pvn_nr = $request->company_pvn_nr;
    $order->company_name = $request->company_name;
    $order->company_address = $request->company_address;
    
    // Atjaunojam automobiļa informāciju (visi lauki ir brīvprātīgi)
    $carPlate = trim((string) ($request->car_plate ?? '')) ?: null;
    $carBrand = trim((string) ($request->car_brand ?? '')) ?: null;
    $carModel = trim((string) ($request->car_model ?? '')) ?: null;
    $carReleaseYear = trim((string) ($request->car_release_year ?? '')) ?: null;
    $carEngineSize = trim((string) ($request->car_engine_size ?? '')) ?: null;

    if ($carPlate === null && $carBrand === null && $carModel === null && $carReleaseYear === null && $carEngineSize === null) {
        $order->car_details = null;
    } else {
        $order->car_details = json_encode([
            'car_plate' => $carPlate,
            'car_brand' => $carBrand,
            'car_model' => $carModel,
            'car_release_year' => $carReleaseYear,
            'car_engine_size' => $carEngineSize,
        ]);
    }

    $order->order_status = $request->order_status;
    $order->edituser = Auth::user()->id;

    if ($order->save()) {
      \App\Models\Audit::audit(
        AUDIT_SEVERITY_INFO,
        AUDIT_FACILITY_DOCUMENT,
        $order->id,
        null,
        'Pasūtījums labots',
        $order
      );
      
      return redirect()->back()->with('success', 'Pasūtījums informācija veiksmīgi labota!');
    } else {
      return redirect()->back()->with('danger', 'Notika kļūda labojot pasūtījuma informāciju!');
    }
  }

  public function delete($id) {

    $order = Order::findOrFail($id);

    if ($order->delete()) {
	    return redirect()->route('admin.orders')
          ->with('success','Pasūtījums veiksmīgi dzēsts');
    } else {
	    return redirect()->route('admin.shop.orders')->with('danger', 'Kļūda pasūtījuma dzēšanā');
    }

  }

  private function preloadOrderProductsStock($products): void
  {
    $motoIds = [];
    $autoIds = [];
    $bigIds = [];

    foreach ($products as $product) {
      $id = (int) ($product->id ?? 0);
      if ($id <= 0) {
        continue;
      }

      $categoryClass = $this->resolveModelClassFromCategory(
        isset($product->category) ? (string) $product->category : null
      );

      if ($categoryClass === \App\Models\Moto::class) {
        $motoIds[] = $id;
        continue;
      }
      if ($categoryClass === \App\Models\Autotire::class) {
        $autoIds[] = $id;
        continue;
      }
      if ($categoryClass === \App\Models\Bigtire::class) {
        $bigIds[] = $id;
        continue;
      }

      $url = (string) ($product->url ?? '');
      if ($url === '') {
        continue;
      }

      if (strpos($url, '/motociklu-riepas/') !== false || strpos($url, '/moto/') !== false) {
        $motoIds[] = $id;
      } elseif (strpos($url, '/lielas-riepas/') !== false || strpos($url, '/big/') !== false) {
        $bigIds[] = $id;
      } elseif (strpos($url, '/vasaras-riepa/') !== false || strpos($url, '/ziemas-riepa/') !== false) {
        $autoIds[] = $id;
      }
    }

    if ($motoIds !== []) {
      \App\Models\Moto::preloadStockData(array_values(array_unique($motoIds)));
    }
    if ($autoIds !== []) {
      \App\Models\Autotire::preloadStockData(array_values(array_unique($autoIds)));
    }
    if ($bigIds !== []) {
      \App\Models\Bigtire::preloadStockData(array_values(array_unique($bigIds)));
    }
  }

  private function resolveModelClassFromCategory(?string $category): ?string
  {
    if ($category === null || $category === '') {
      return null;
    }
    $normalized = trim((string) $category);
    if ($normalized === '') {
      return null;
    }
    $normalized = preg_replace('#^App\\\\Models\\\\#i', '', $normalized);
    $normalized = trim($normalized);
    if ($normalized === '') {
      return null;
    }
    switch (strtolower($normalized)) {
      case 'autotire':
        return \App\Models\Autotire::class;
      case 'bigtire':
        return \App\Models\Bigtire::class;
      case 'quadr':
        return \App\Models\Quadr::class;
      case 'quadrim':
        return \App\Models\Quadrim::class;
      case 'moto':
        return \App\Models\Moto::class;
      default:
        return null;
    }
  }

  private function getProductModel($url, $id, $category = null)
  {
    if ($url) {
      if (strpos($url, '/vasaras-riepa/') !== false || strpos($url, '/ziemas-riepa/') !== false) {
        $model = \App\Models\Autotire::find($id);
      } elseif (strpos($url, '/motociklu-riepas/') !== false || strpos($url, '/moto/') !== false) {
        $model = \App\Models\Moto::find($id);
      } elseif (strpos($url, '/kvadraciklu-riepas/') !== false || strpos($url, '/kvadru-riepas/') !== false || strpos($url, '/quadr/') !== false) {
        $model = \App\Models\Quadr::find($id);
      } elseif (strpos($url, '/kvadraciklu-diski/') !== false || strpos($url, '/kvadru-diski/') !== false) {
        $model = \App\Models\Quadrim::find($id);
      } elseif (strpos($url, '/lietais-disks/') !== false) {
        return (object)['dotAvailable' => '', 'stockAvailability' => '', 'url' => $url];
      } elseif (strpos($url, '/lielas-riepas/') !== false || strpos($url, '/big/') !== false) {
        $model = \App\Models\Bigtire::find($id);
      } else {
        $model = \App\Models\Autotire::find($id);
      }

      if ($model) {
        return $model;
      }
    }

    $categoryClass = $this->resolveModelClassFromCategory(
      $category !== null && $category !== '' ? (string) $category : null
    );
    if ($categoryClass) {
      $model = $categoryClass::find($id);
      if ($model) {
        return $model;
      }
    }

    // If no URL or model not found, try each product type sequentially
    $productTypes = [
      \App\Models\Autotire::class,
      \App\Models\Moto::class,
      \App\Models\Quadr::class,
      \App\Models\Quadrim::class,
      \App\Models\Bigtire::class,
    ];
    
    foreach ($productTypes as $productType) {
      $model = $productType::find($id);
      if ($model) {
        return $model;
      }
    }
    
    // If still not found, return empty values
    return (object)['dotAvailable' => '', 'stockAvailability' => '', 'url' => null];
  }

  public function searchOrders(Request $request)
  {
    try {
      // Валидация входных данных
      $request->validate([
        'search' => 'required|string|min:1|max:100'
      ]);
      
      $searchTerm = $request->input('search');
      
      \Log::info('Search request received', ['search' => $searchTerm]);
      
      if (empty($searchTerm)) {
        return response()->json([
          'success' => false,
          'message' => 'Поисковый запрос не может быть пустым'
        ], 400);
      }

      // Поиск по номеру заказа, имени, фамилии, email
      $orders = Order::where(function($query) use ($searchTerm) {
        $query->where('id', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('order_number', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('customer_name', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('customer_surname', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('email', 'LIKE', '%' . $searchTerm . '%')
              ->orWhereRaw("CONCAT(customer_name, ' ', customer_surname) LIKE ?", ['%' . $searchTerm . '%']);
      })
      // Фильтруем заказы без контактной информации (как в основном методе)
      ->where(function($query) {
          $query->where(function($q) {
              $q->whereNotNull('phone_number')
                ->where('phone_number', '!=', '');
          })
          ->orWhere(function($q) {
              $q->whereNotNull('email')
                ->where('email', '!=', '');
          });
      })
      ->orderBy('order_number', 'desc')
      ->limit(100) // Ограничиваем результаты для производительности
      ->get();

      // Рассчитываем суммы и добавляем дополнительные поля (как в основном методе)
      foreach($orders as $order) {
        $item_sum = 0;
        $item_count = 0;
        
        // Получаем данные из сериализованного поля order_details
        $orderDetails = json_decode($order->order_details);
        if ($orderDetails && isset($orderDetails->products)) {
          // Рассчитываем сумму товаров и количество
          foreach ($orderDetails->products as $product) {
            $item_sum += $product->price * $product->quantity;
            $item_count += $product->quantity;
          }
        }

        // Применяем скидку, если есть
        if ($order->promo_code) {
          $promo = Promo::where('promo_code', $order->promo_code)->first();
          if ($promo) {
            if ($promo->discount_type === 'percentage') {
              $discount = $item_sum * ($promo->discount_value / 100);
            } else {
              $discount = $promo->discount_value;
            }
            $item_sum = round($item_sum - $discount);
          }
        }

        // Добавляем стоимость доставки или монтажа
        if ($order->delivery_price > 0) {
          $item_sum += $order->delivery_price;
        } else if ($order->mounting_price > 0) {
          $item_sum += $order->mounting_price;
        }

        $order->total_sum = $item_sum;
        $order->item_count = $item_count;
        $order->status_name = $this->status_enum[$order->order_status] ?? 'Неизвестно';
        $order->payment_method_name = $this->pay_enum[$order->payment_method] ?? 'Nav norādīts';
        $order->editor_name = null; // Пока не загружаем информацию об редакторе
      }

      \Log::info('Search completed', ['count' => $orders->count()]);

      return response()->json([
        'success' => true,
        'orders' => $orders,
        'count' => $orders->count()
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Ошибка при поиске: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Получить CSS класс для статуса заказа
   */
  public function getStatusClass($status)
  {
    return $this->status_class_map[$status] ?? 'pending';
  }

}

