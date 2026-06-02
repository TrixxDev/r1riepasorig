<?php

namespace App\Http\Controllers;

use App\Models\Studbrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use View;
use App\Models\Stud;
use Cart;
use Route;
use Auth;


class StudsController extends Controller
{
  public $brands;
  public $season;
  public $currBrand;
  public $applications;
  public $stud_length;
  public $model = 'Stud';
  public $tiresSize;
  public $availability;
  public $code_array = [];
  public $filterCount = 0;

  public $cartQty = 1;

  public function __construct(Request $request)
  {

    ($request->application == 'Visi') ? $this->currBrand = 'Visi' : $this->currBrand = $request->application;
    ($this->currBrand === NULL) ? $this->currBrand = 'Visi' : $this->currBrand = $request->application;

    ($request->stud_length == 'Visi') ? $this->stud_length = 'Visi' : $this->stud_length = $request->stud_length;
    ($this->stud_length === NULL) ? $this->stud_length = 'Visi' : $this->stud_length = $request->stud_length;

    $this->applications = [
      1 => 'Apaviem',
      2 => 'Kvadracikliem',
      3 => 'Motocikliem',
      4 => 'Mini traktoriem',
      5 => 'Iekrāvējiem',
      6 => 'Būvniecības tehnikai',
      7 => 'Agro tehnikai',
      8 => '4x4 visurgājēji',
    ];

    View::share('currBrand', $this->currBrand);
    View::share('applications', $this->applications);
    View::share('current_url', 'radzes');
    View::share('stud_lengths', $this->getStudLengths());
    View::share('curr_length', $this->stud_length);
    View::share('cartQty', $this->cartQty);
}

  public function studs() {

    $studs = Stud::leftJoin('studs_treads', 'studs.make_id', '=', 'studs_treads.tread_id')
      ->where('studs.visible_users', '<>', 0)
      ->orderBy('price2', 'DESC')
      ->paginate();

    $length = [1,2,3,4,5,6,7,8,9];

    return view('studs.studs', compact('length', 'studs'));
  }

  public function studs_ajax(Request $request) {
    $stud = Stud::with('tread')->selectRaw('studs.*, studs_treads.*')
      ->rightJoin('studs_treads', 'studs.make_id', '=', 'studs_treads.tread_id')
      ->where('studs.stud_id', $request->tire_id)
      ->where('studs.visible_users', '<>', 0)
      ->first();

    if ($request->quantity) {
      $cart = CartController::addProduct($this->model, $stud->stud_id, $request->quantity);
    } else {
      $cart = CartController::addProduct($this->model, $stud->stud_id, $this->cartQty);
    }

    $quantity = Cart::count();
    //dd(Cart::subTotal());
    $total_sum = str_replace([',', '.00'], '', Cart::subTotal());
    $bought = ($request->quantity) ? $request->quantity : $this->cartQty;

    echo json_encode(['cart' => $cart, 'total_sum' => $total_sum, 'quantity' => $quantity, 'bought' => $bought]);
  }

  public function studs_find(Request $request) {

  }

  public function studs_filter(Request $request) {

  }

  public function studs_search(Request $request) {
    $this->filterCount = 0;

    ($request->application == 'Visi') ? $this->currBrand = '' : $this->currBrand = $request->application;
    ($request->stud_length == 'Visi') ? $this->stud_length = '' : $this->stud_length = $request->stud_length;

    if ($request->availability) {
      $this->filterCount += 1;
      $this->availability = $request->availability;
    } else {
      $this->availability = [];
    }

    $studs = Stud::select('studs.*', 'studs_treads.*')
                  ->leftJoin('studs_treads', 'studs.make_id', '=', 'studs_treads.tread_id')
                  ->leftJoin('studs_brands', 'studs_treads.brand_id', '=', 'studs_brands.brand_id')
                  ->when($this->currBrand, function($query) {
                    $query->where('studs.application', 'LIKE', '%' . Stud::convertToAppId($this->currBrand) . '%');
                  })->when($this->stud_length, function($query) {
                    $query->where('studs.stud_length', 'LIKE', $this->stud_length);
                  })->where('studs.visible_users', '<>', 0)
                  ->orderBy('price2', 'DESC')
                  ->groupBy('studs.stud_id')->paginate()->appends($request->query());

//    dd(DB::getQueryLog());

    return view('studs.studs',
      ['studs' => $studs, 'filterCount' => $this->filterCount, 'availability' => $this->availability]
    );
  }

  public function studs_tread($brand, $tread, $stud) {

    $brand = Studbrand::where('b_title', $brand)->first();

    $studs = Stud::selectRaw('studs.*, studs_treads.*, studs_brands.*,
                              studs_brands.b_title as brands_title')
                              ->join('studs_treads', 'studs.make_id', '=', 'studs_treads.tread_id')
                              ->join('studs_brands', 'studs_treads.brand_id', '=', 'studs_brands.brand_id')
                              ->where('studs_brands.b_title', $brand->b_title)
                              ->where('studs_treads.t_title', str_replace('_', '/', $tread))
                              ->get();

    $currStud = Stud::selectRaw('studs.*, studs_treads.t_comment as t_comment, studs_brands.b_comment as b_comment')
                      ->leftJoin('studs_treads', 'studs.make_id', '=', 'studs_treads.tread_id')
                      ->leftJoin('studs_brands', 'studs_treads.brand_id', '=', 'studs_brands.brand_id')
                      ->where('studs_treads.t_title', str_replace('_', '/', $tread))
                      ->where('studs.stud_id', $stud)
                      ->first();

    $currBrand = Studbrand::where('brand_id', $currStud->brand_id)->first();

    return view('studs.tread',
      compact('studs', 'currStud', 'currBrand')
    );

  }

  public function getStudLengths()
  {
    $brands = [];

    $stud_lengths = [];

    foreach (Stud::all() as $stud) {
      array_push($stud_lengths, $stud->stud_length);
    }

    $stud_lengths = array_unique($stud_lengths);
    $stud_lengths = array_values($stud_lengths);

    asort($stud_lengths, SORT_NATURAL | SORT_FLAG_CASE);

    return $stud_lengths;
  }
}
