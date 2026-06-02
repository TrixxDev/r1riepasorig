<?php

  namespace App\Http\Controllers;

  use App\Models\FilterCars;
  use App\Models\FilterSizes;
  use App\Models\FilterModels;
  use App\Models\Quadrim;
  use App\Models\Quadrimbrand;
  use App\Models\Quadrimmake;
  use Dflydev\DotAccessData\Data;
  use Gloudemans\Shoppingcart\Facades\Cart;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Facades\View;

  class QuadrRimsController extends Controller
  {

    public $currentCar;
    public $currentModel;
    public $currentR1;
    public $currentR2;
    public $currentR;

    public $currentForm;

    public $currentSkr;
    public $currentPcd;
    public $currentEt;
    public $currentDia;
    public $currentCenter;

    public $model = 'Quadrim';
    public $cartQty = 1;

    public $models;

    public function __construct(Request $request)
    {

      $this->d1 = ($request->d1 == 'Visi') ? 'Visi' : $request->d1;

      $this->currentCar = ($request->car) ? $request->car : '';
      $this->currentModel = ($request->model) ? $request->model : '';
      $this->currentR1 = ($request->r1) ? $request->r1 : '';
      $this->currentR2 = ($request->r2) ? $request->r2 : '';
      $this->currentR = $this->currentR1;

      $this->currentForm = ($request->currentForm == 1) ? 1 : 2;

      $this->currentSkr = ($request->currentSkr) ? $request->currentSkr : '';
      $this->currentPcd = ($request->currentPcd) ? $request->currentPcd : '';
      $this->currentEt = ($request->currentEt) ? $request->currentEt : '';
      $this->currentDia = ($request->currentDia) ? $request->currentDia : '';
      $this->currentCenter = ($request->currentCenter) ? $request->currentCenter : '';

      if (($this->currentSkr !== false)||($this->currentPcd !== false)||($this->currentEt !== false)) {
        $this->currentCar = -1;
        $this->currentModel = -1;
        if ($this->currentR2 !== false) $this->currentR = $this->currentR2;
      }

      if ($this->currentCar === -1) $this->currentModel = false;

      View::share('brandList', $this->getBrandList());
      View::share('currentCar', $this->currentCar);
      View::share('currentEt', $this->currentEt);
      View::share('currentPcd', $this->currentPcd);
      View::share('currentSkr', $this->currentSkr);
      View::share('currentDia', $this->currentDia);
      View::share('currentCenter', $this->currentCenter);
      View::share('offsets', $this->getRimOptions()['offsets']);
      View::share('diameters', $this->getRimOptions()['diameters']);
      View::share('lugs', $this->getRimOptions()['lug_counts']);
      View::share('studs_spread', $this->getRimOptions()['stud_spreads']);
      View::share('makes', $this->getRimMakes());
      View::share('models', $this->getRimModels());
      View::share('cartQty', $this->cartQty);

    }

    public function rims()
    {

      // THESE HARDCODED VALUES SHOULD BE REPLACED WITH DATA FROM API

      $brands = Quadrimbrand::paginate();

      $rims = Quadrim::leftJoin('quadrim_makes', 'quadrims.make_id', '=', 'quadrim_makes.make_id')
        ->where('quadrims.visible_users', '<>', 0)
        ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
        ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
        ->orderBy('price2', 'DESC')
        ->paginate();

      return view('rims.quadrim', compact('rims','brands'));
    }

    public function rims_search(Request $request){

      //    DB::enableQueryLog();

      $this->currentEt = ($request->currentEt == 'Visi') ? '' : $request->currentEt;
      $this->currentPcd = ($request->currentPcd == 'Visi') ? '' : $request->currentPcd;
      $this->currentSkr = ($request->currentSkr == 'Visi') ? '' : $request->currentSkr;
      $this->currentDia = ($request->currentDia == 'Visi') ? '' : $request->currentDia;
      $this->currentCenter = ($request->currentCenter == 'Visi') ? '' : $request->currentCenter;
      $this->d1 = ($this->d1 == 'Visi') ? '' : $request->d1;

      $rims = Rim::select('rims.*')
        ->leftJoin('rim_makes', 'rims.make_id', '=', 'rim_makes.make_id')
        ->leftJoin('rim_brands', 'rim_makes.brand_id', '=', 'rim_brands.brand_id')
        ->when($this->currentEt, function($query) {
          $query->where('rims.et', $this->currentEt);
        })->when($this->currentPcd, function($query) {
          $query->where('rims.pcd', $this->currentPcd);
        })->when($this->currentSkr, function($query) {
          $query->where('rims.skr', $this->currentSkr);
        })->when($this->currentDia, function($query) {
          $query->where('rims.d3', $this->currentDia);
        })->where('rims.visible_users', '<>', 0)
        ->orderBy('d3', 'ASC')
        ->orderBy('d1', 'ASC')
        ->orderBy('price2', 'DESC')
        ->groupBy('rims.rim_id')->paginate()->appends($request->query());


//      dd(DB::getQueryLog());

//    return view('tires.moto.index',
//      ['tires' => $tires, 'filterCount' => $this->filterCount]
//    );

      return view('rims.autorims', compact('rims'));
    }

    public function rims_tread($brand, $tread, $rim)
    {
      $brand = Quadrimbrand::where('b_title', $brand)->first();

      $tread = Quadrimmake::where('t_title', $tread)->first();

      $currRim = Quadrim::join('quadrim_makes', 'quadrims.make_id', '=', 'quadrim_makes.make_id')
        ->where('quadrim_makes.t_title', $tread->t_title)
        ->where('quadrims.rim_id', $rim)
        ->first();

      $rims = Quadrim::leftJoin('quadrim_makes', 'quadrims.make_id', '=', 'quadrim_makes.make_id')
        ->leftJoin('quadrim_brands', 'quadrim_makes.brand_id', '=', 'quadrim_brands.brand_id')
        ->select('quadrims.*', 'quadrim_makes.*', 'quadrim_brands.brand_id as brand_id', 'quadrim_brands.b_title as brand_title')
        ->where('quadrims.make_id', $tread->make_id )
        ->paginate(20);

      return view('rims.quadr.tread', compact('rims', 'currRim', 'brand', 'tread'));
    }

    public function rims_ajax(Request $request)
    {
      $rim = Quadrim::selectRaw('quadrims.*, quadrim_makes.*')
        ->rightJoin('quadrim_makes', 'quadrims.make_id', '=', 'quadrim_makes.make_id')
        ->where('quadrims.rim_id', $request->tire_id)
        ->first();

      if ($request->quantity) {
        $cart = CartController::addProduct($this->model, $rim->rim_id, $request->quantity);
      } else {
        $cart = CartController::addProduct($this->model, $rim->rim_id, $this->cartQty);
      }

      $quantity = Cart::count();
      $total_sum = str_replace([',', '.00'], '', Cart::total());
      $bought = ($request->quantity) ? $request->quantity : $this->cartQty;

      echo json_encode(['cart' => $cart, 'total_sum' => $total_sum, 'quantity' => $quantity, 'bought' => $bought]);

    }

    public function getRimOptions()
    {
      $rim_offsets = [];
      $rim_diameters = [];
      $rim_lug_count = [];
      $rim_stud_spreads = [];
      $rim_center = [];

      foreach (Quadrim::all() as $rim) {
        array_push($rim_offsets, $rim->et);
        array_push($rim_diameters, $rim->d3);
        array_push($rim_lug_count, $rim->skr);
        array_push($rim_stud_spreads, $rim->pcd);
      }

      $rim_offsets = array_unique($rim_offsets);
      $rim_offsets = array_values($rim_offsets);
      $rim_offsets = array_filter($rim_offsets);

      $rim_diameters = array_unique($rim_diameters);
      $rim_diameters = array_values($rim_diameters);
      $rim_diameters = array_filter($rim_diameters);

      $rim_lug_count = array_unique($rim_lug_count);
      $rim_lug_count = array_values($rim_lug_count);
      $rim_lug_count = array_filter($rim_lug_count);

      $rim_stud_spreads = array_unique($rim_stud_spreads);
      $rim_stud_spreads = array_values($rim_stud_spreads);
      $rim_stud_spreads = array_filter($rim_stud_spreads);

      asort($rim_offsets, SORT_NATURAL | SORT_FLAG_CASE);
      asort($rim_diameters, SORT_NATURAL | SORT_FLAG_CASE);
      asort($rim_lug_count, SORT_NATURAL | SORT_FLAG_CASE);
      asort($rim_stud_spreads, SORT_NATURAL | SORT_FLAG_CASE);

      return [
        'offsets' => $rim_offsets,
        'diameters' => $rim_diameters,
        'lug_counts' => $rim_lug_count,
        'stud_spreads' => $rim_stud_spreads,
      ];
    }

    public function getRimMakes()
    {
      $rim_makes = [];

      foreach (Quadrim::all() as $rim) {
        array_push($rim_makes, $rim->offset);
      }

      $rim_makes = array_unique($rim_makes);
      $rim_makes = array_values($rim_makes);

      asort($rim_makes, SORT_NATURAL | SORT_FLAG_CASE);

      return $rim_makes;
    }

    public function getRimModels()
    {
      $rim_models = [];

      foreach (Quadrim::all() as $rim) {
        array_push($rim_models, $rim->offset);
      }

      $rim_models = array_unique($rim_models);
      $rim_models = array_values($rim_models);

      asort($rim_models, SORT_NATURAL | SORT_FLAG_CASE);

      return $rim_models;
    }

    public function getBrandList()
    {
      $rim_brands = [];

      foreach (Quadrimbrand::all() as $rim_brand) {
        array_push($rim_brands, $rim_brand->title);
      }

      $rim_brands = array_unique($rim_brands);
      $rim_brands = array_values($rim_brands);

      asort($rim_brands, SORT_NATURAL | SORT_FLAG_CASE);

      return $rim_brands;
    }

  }
