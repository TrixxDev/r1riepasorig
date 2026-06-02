<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Quickorder;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MainController extends Controller
{

    public $models;
    public $model;
    public $param;
    public $searchBy;

    public function models()
    {
      $this->models = [
        'slots' => [
          'name' => 'App\\Models\\Slot',
          'title' => 'Slots',
          Schema::getColumnListing((new Slot)->getTable()),
          'searchBy' => [
            'audit_event' => 'Notikums',
            'vehiclePlate' => 'Mašīnas numurs',
            'ownerPhone' => 'Telefona numurs',
          ],
        ],
        'orders' => [
          'name' => 'App\\Models\\Order',
          'title' => 'Pasūtījumi (Grozs)',
          Schema::getColumnListing((new \App\Models\Order)->getTable()),
          'searchBy' => [
            'audit_event' => 'Notikums',
            'order_number' => 'Pasūtījuma numurs',
            'email' => 'E-pasts',
            'phone_number' => 'Telefons',
          ],
        ],
        'quickorder' => [
          'name' => 'App\\Models\\Quickorder',
          'title' => 'Ātrie pasūtījumi',
          Schema::getColumnListing((new Quickorder)->getTable()),
          'searchBy' => [
            'order_id' => 'Pasūtījuma numurs',
          ],
        ],
//        'users' => [
//          'name' => 'App\\Models\\User',
//          'title' => 'Lietotāji',
//          Schema::getColumnListing((new User)->getTable()),
//          'searchBy' => [
//            'audit_time' => 'Laiks',
//            'audit_time' => 'Datums',
//            // Datums, Laiks, Mašīnas numurs, Telefona numurs, Klienta vārds-uzvārds
//          ]
//        ],
      ];

      return $this->models;
    }

    public function home()
    {
        // Статистика заказов
        $todayOrders = \App\Models\Order::whereDate('created_at', today())->count();
        $totalOrders = \App\Models\Order::count();
        $completedOrders = \App\Models\Order::where('order_status', 5)->count();
        $pendingOrders = \App\Models\Order::whereIn('order_status', [1, 2, 3, 4])->count();
        
        // Статистика товаров
        $totalTires = \App\Models\Autotire::count();
        $lowStockTires = \App\Models\Autotire::where('quantity', '<=', 5)->where('quantity', '>', 0)->count();
        $outOfStockTires = \App\Models\Autotire::where('quantity', '<=', 0)->count();
        
        // Статистика пользователей
        $totalUsers = \App\Models\User::count();
        $activeUsersToday = \App\Models\Audit::whereDate('audit_time', today())
            ->where('audit_uid', '>', 0)
            ->distinct('audit_uid')
            ->count();
        
        // Статистика резерваций
        $todaySlots = \App\Models\Slot::whereDate('date', today())->count();
        $takenSlotsToday = \App\Models\Slot::whereDate('date', today())
            ->where('takenby', '!=', null)
            ->count();
        
        // Доход за месяц
        $monthlyRevenue = \App\Models\Order::where('order_status', 5)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_price');
        
        // График продаж за последние 7 дней
        $salesChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $ordersCount = \App\Models\Order::whereDate('created_at', $date)->count();
            $revenue = \App\Models\Order::whereDate('created_at', $date)
                ->where('order_status', 5)
                ->sum('total_price');
            
            $salesChart[] = [
                'date' => $date->format('d.m'),
                'orders' => $ordersCount,
                'revenue' => $revenue ?? 0
            ];
        }
        
        // Последние заказы
        $recentOrders = \App\Models\Order::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Последние действия (из аудита)
        $recentActivity = \App\Models\Audit::with('user')
            ->orderBy('audit_time', 'desc')
            ->limit(10)
            ->get();

        $rgSyncLast = DB::table('sync_times')->where('name', 'rg-auto')->value('updated_at');

        return view('admin.home', compact(
            'todayOrders', 'totalOrders', 'completedOrders', 'pendingOrders',
            'totalTires', 'lowStockTires', 'outOfStockTires',
            'totalUsers', 'activeUsersToday',
            'todaySlots', 'takenSlotsToday',
            'monthlyRevenue', 'salesChart', 'recentOrders', 'recentActivity',
            'rgSyncLast'
        ));
    }

    public function dashboardData()
    {
        try {
            $data = [
                'todayOrders' => \App\Models\Order::whereDate('created_at', today())->count(),
                'monthlyRevenue' => \App\Models\Order::where('order_status', 5)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_price'),
                'lowStockTires' => \App\Models\Autotire::where('quantity', '<=', 5)->where('quantity', '>', 0)->count(),
                'activeUsersToday' => \App\Models\Audit::whereDate('audit_time', today())
                    ->where('audit_uid', '>', 0)
                    ->distinct('audit_uid')
                    ->count(),
                'totalOrders' => \App\Models\Order::count(),
                'completedOrders' => \App\Models\Order::where('order_status', 5)->count(),
                'totalTires' => \App\Models\Autotire::count(),
                'outOfStockTires' => \App\Models\Autotire::where('quantity', '<=', 0)->count(),
                'todaySlots' => \App\Models\Slot::whereDate('date', today())->count(),
                'takenSlotsToday' => \App\Models\Slot::whereDate('date', today())
                    ->where('takenby', '!=', null)
                    ->count(),
                'timestamp' => now()->toISOString()
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }

  public function audits(Request $request)
  {
      $models = $this->models();

      $filters = [
        'modelname' => $request->input('model', ''),
        'param' => $request->input('params', ''),
        'date_from' => $request->input('date_from', ''),
        'date_to' => $request->input('date_to', ''),
        'user_id' => $request->input('user_id', ''),
        'facility' => $request->input('facility', ''),
        'severity' => $request->input('severity', ''),
      ];

      $query = Audit::with('user');

      if (!empty($filters['modelname']) && !empty($filters['param'])) {
        $quote = "'";
        $params = explode(';', $filters['modelname']);
        $modelKey = $params[0];
        $searchBy = $params[1];

        if (isset($this->models()[$modelKey])) {
          $modelName = $this->models()[$modelKey]['name'];

          if ($searchBy == 'audit_event') {
            $query->where('audit_classname', $modelName)
              ->where($searchBy, 'like', '%' . $filters['param'] . '%');
          } else if ($modelKey == 'quickorder' && $searchBy == 'order_id') {
            $query->where('audit_classname', $modelName)
              ->whereRaw('audit_instance LIKE ' . $quote . '%"' . $searchBy . '";i:' . $filters['param'] . '%' . $quote);
          } else {
            $query->where('audit_classname', $modelName)
              ->where(function($q) use ($searchBy, $filters) {
                $q->where('audit_instance', 'LIKE', '%"' . $searchBy . '":"' . $filters['param'] . '"%')
                  ->orWhere('audit_instance', 'LIKE', '%"' . $searchBy . '":' . $filters['param'] . '%')
                  ->orWhere('audit_instance', 'LIKE', '%"' . $searchBy . '":"%' . $filters['param'] . '%"%');
              });
          }
        }
      }

      if (!empty($filters['date_from'])) {
        $query->where('audit_time', '>=', $filters['date_from'] . ' 00:00:00');
      }

      if (!empty($filters['date_to'])) {
        $query->where('audit_time', '<=', $filters['date_to'] . ' 23:59:59');
      }

      if (!empty($filters['user_id'])) {
        $query->where('audit_uid', $filters['user_id']);
      }

      if (!empty($filters['facility'])) {
        $query->where('audit_facility', $filters['facility']);
      }

      if (!empty($filters['severity'])) {
        $query->where('audit_severity', $filters['severity']);
      }

      $audits = $query->orderBy('audit_time', 'DESC')
        ->orderBy('id', 'DESC')
        ->paginate(200)
        ->appends($filters);

      $users = User::orderBy('name')->orderBy('surname')->get();
      
      $facilities = [
        AUDIT_FACILITY_LOGIN => 'Autorizācijas apakšsistēma',
        AUDIT_FACILITY_DB => 'Datubāzes apakšsistēma',
        AUDIT_FACILITY_MESSAGE => 'Ziņu apakšsistēma',
        AUDIT_FACILITY_SYSCORE => 'Sistēmas kodols',
        AUDIT_FACILITY_USER => 'Lietotāju apakšsistēma',
        AUDIT_FACILITY_DOCUMENT => 'Datu objekts',
      ];

      $severities = [
        AUDIT_SEVERITY_CRITICAL => 'Critical',
        AUDIT_SEVERITY_WARNING => 'Warning',
        AUDIT_SEVERITY_INFO => 'Info',
        AUDIT_SEVERITY_DEBUG => 'Debug',
      ];

      return view('admin.audits.audits', compact('audits', 'models', 'filters', 'users', 'facilities', 'severities'));
  }

  public function audit($id)
  {
    $audit = Audit::with('user')->where('id', $id)->first();

    $classname = $audit->audit_classname;
    $instance_id = $audit->audit_item;
    $instance_data = $audit->audit_instance;
    $instance_class = $audit->audit_classname;
    
    $decoded = @json_decode($instance_data);
    if (json_last_error() === JSON_ERROR_NONE) {
      $instance = $decoded;
    } else {
      $instance = @unserialize($instance_data);
      if ($instance === false) {
        $instance = null;
      }
    }

    $row1 = Audit::where('audit_item', $instance_id)
      ->where('id', '<', $id)
      ->where('audit_classname', $instance_class)
      ->orderBy('audit_time', 'DESC')
      ->orderBy('id', 'DESC')
      ->first();

    if ($row1) {
      $old_instance_data = $row1->audit_instance;
      
      $decoded = @json_decode($old_instance_data);
      if (json_last_error() === JSON_ERROR_NONE) {
        $old_instance = $decoded;
      } else {
        $old_instance = @unserialize($old_instance_data);
        if ($old_instance === false) {
          if (class_exists($instance_class)) {
            $old_instance = new $instance_class;
          } else {
            $old_instance = '';
          }
        }
      }
    } else {
      if (class_exists($instance_class)) {
        $old_instance = new $instance_class;
      } else {
        $old_instance = '';
      }
    }

    return view('admin.audits.audit', compact('audit', 'classname', 'id', 'old_instance', 'instance', 'instance_id'));
  }

}

