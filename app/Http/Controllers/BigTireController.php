<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CartController;
use App\Http\Controllers\ShopController;
use App\Helper\Tires;
use App\Models\Bigbrand;
use App\Models\Bigtire;
use App\Models\Bigtread;
use App\Models\Code;
use Cart;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use View;
use Illuminate\Support\Facades\Cookie;
use App\Helper\Image;
use Illuminate\Database\Eloquent\Builder;

class BigTireController extends Controller
{

    public $brands;
    public $season;
    public $currBrand;
    public $d1;
    public $d2;
    public $d3;
    public $bigTiresD1;
    public $bigTiresD2;
    public $bigTiresD3;
    public $model = 'Bigtire';
    public $tiresSize;
    public $tire_types;
    public $tire_implementions;
    public $tire_axis;
    public $tire_conditions;
    public $code;
    public $code_array = [];
    public $type;
    public $types;
    public $implemention;
    public $implementions;
    public $surface;
    public $availability;
    public $filterCount = 0;

    public $cartQty = 1;

    public function __construct(Request $request)
    {
      $this->cartQty = 1;
    }

    public function index(Request $request)
    {
      $this->parseIndexRequestParams($request);
      $this->shareIndexViewData();

      $tires = $this->paginateUniqueTires();

      return view('tires.industrial.index',
                  compact('tires')
      );
    }

    public function tires_search(Request $request)
    {
      $this->parseIndexRequestParams($request);

      $this->currBrand = ($request->brand == 'Visi') ? '' : $request->brand;

      $code = $request->code;
      $types = $this->type = ($request->type) ? $request->type : [];
      $implementions = $this->implementions = ($request->implemention) ? $request->implemention : [];

      $this->d1 = ($this->d1 == 'Visi') ? '' : $request->d1;
      $this->d2 = ($this->d2 == 'Visi') ? '' : $request->d2;
      $this->d3 = ($this->d3 == 'Visi') ? '' : $request->d3;

      $this->shareIndexViewData();

      $tires = $this->paginateUniqueTires($this->currBrand, $this->type, $this->implementions)
        ->appends($request->query());

      return view('tires.industrial.index',
        compact('tires', 'code', 'types', 'implementions')
      );
    }

    public function tires_tread($brand, $tread, $tire) {

      View::share('cartQty', $this->cartQty);

      $brandModel = Bigbrand::query()
        ->where(function ($query) use ($brand) {
          $query->where('slug', $brand)->orWhere('title', $brand);
        })
        ->firstOrFail();

      $treadTitle = str_replace('_', '/', $tread);

      $tires = Bigtire::with(['tread.brand', 'stocks'])
        ->whereHas('tread.brand', function ($query) use ($brandModel) {
          $query->where('brand_id', $brandModel->brand_id);
        })
        ->whereHas('tread', function ($query) use ($treadTitle) {
          $query->where('title', $treadTitle);
        })
        ->where('big_tires.visible_users', '<>', 0)
        ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
        ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
        ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
        ->orderBy('price3', 'ASC')
        ->get();

      $currTire = $tires->firstWhere('tire_id', (int) $tire);
      if (!$currTire) {
        $currTire = Bigtire::with(['tread.brand', 'stocks'])
          ->whereHas('tread.brand', function ($query) use ($brandModel) {
            $query->where('brand_id', $brandModel->brand_id);
          })
          ->whereHas('tread', function ($query) use ($treadTitle) {
            $query->where('title', $treadTitle);
          })
          ->where('big_tires.tire_id', $tire)
          ->firstOrFail();
      }

      $currBrand = $currTire->tread->brand ?? $brandModel;
      $currTire->includeStock = true;
      $pageHeading = trim($brandModel->title . ' ' . $treadTitle);

      Bigtire::preloadStockData(
        $tires->pluck('tire_id')->push($currTire->tire_id)->unique()->values()->all()
      );

      return view('tires.industrial.industrialtread',
        compact('tires', 'currTire', 'currBrand', 'brand', 'tread', 'pageHeading')
      );
    }

    public function tires_ajax(Request $request): \Illuminate\Http\JsonResponse
    {
      $tire = Bigtire::with(['tread.brand', 'stocks'])
        ->where('big_tires.tire_id', $request->tire_id)
        ->first();

      if (!$tire) {
        return response()->json(['error' => 'Tire not found'], 404);
      }

      $quantity = $request->quantity ? $request->quantity : $this->cartQty;

      $cart = session()->get('cart', ['products' => []]);

      if (isset($cart['products'][$tire->tire_id])) {
        $cart['products'][$tire->tire_id]['quantity'] += $quantity;
      } else {
        $cart['products'][$tire->tire_id] = [
          'id' => $tire->tire_id,
          'name' => $tire->getFullNameAttribute(),
          'make_id' => $tire->make_id,
          'd1' => $tire->d1,
          'd2' => $tire->d2,
          'd3' => $tire->d3,
          'type' => 'Rūpnieciskā riepa',
          'url' => $request->tire_url,
          'image' => Image::image('big', $tire->make_id),
          'price' => $tire->price3,
          'quantity' => $quantity,
          'availability' => $tire->dotAvailable,
          'category' => $this->model,
        ];
      }

      $totalSum = 0;
      foreach ($cart['products'] as $product) {
        $totalSum += $product['quantity'] * $product['price'];
      }

      $cart['total_sum'] = $totalSum;

      session()->put('cart', $cart);

      Cookie::queue('cart', json_encode($cart), 43200);

      ShopController::updateCartInDatabase($totalSum);

      $totalQuantity = array_sum(array_column($cart['products'], 'quantity'));

      try {
        return response()->json([
          'cart' => $cart,
          'total_sum' => $totalSum,
          'quantity' => $totalQuantity,
          'bought' => $quantity
        ]);
      } catch (\Exception $e) {
        dd('Error in JSON response: ' . $e->getMessage());
      }
    }

    public function tires_getBrands()
    {
      return Cache::remember('big_tire_brands_list', 3600, function () {
        $brands = Bigbrand::query()
          ->whereHas('treads.tires', function ($query) {
            $query->where('visible_users', '<>', 0);
          })
          ->orderBy('title')
          ->get()
          ->mapWithKeys(function ($brand) {
            return [$brand->brand_id => ucwords(strtolower($brand->title))];
          })
          ->all();

        asort($brands, SORT_NATURAL | SORT_FLAG_CASE);

        return $brands;
      });
    }

  public function getTireOptions()
  {
    return Cache::remember('big_tire_filter_options', 3600, function () {
      $baseQuery = Bigtire::query()->where('visible_users', '<>', 0);

      $tire_type = (clone $baseQuery)->whereNotNull('type')->where('type', '<>', '')->distinct()->orderBy('type')->pluck('type')->filter()->values()->all();
      $tire_implemention = (clone $baseQuery)->whereNotNull('implemention')->where('implemention', '<>', '')->distinct()->orderBy('implemention')->pluck('implemention')->filter()->values()->all();
      $tire_axis = (clone $baseQuery)->whereNotNull('axis')->where('axis', '<>', '')->distinct()->orderBy('axis')->pluck('axis')->filter()->values()->all();
      $tire_condition = (clone $baseQuery)->whereNotNull('conditions')->where('conditions', '<>', '')->distinct()->orderBy('conditions')->pluck('conditions')->filter()->values()->all();

      asort($tire_type, SORT_NATURAL | SORT_FLAG_CASE);
      asort($tire_implemention, SORT_NATURAL | SORT_FLAG_CASE);
      asort($tire_axis, SORT_NATURAL | SORT_FLAG_CASE);
      asort($tire_condition, SORT_NATURAL | SORT_FLAG_CASE);

      return [
        'tire_types' => array_values($tire_type),
        'tire_implementions' => array_values($tire_implemention),
        'tire_axis' => array_values($tire_axis),
        'tire_conditions' => array_values($tire_condition),
      ];
    });
  }

  protected function parseIndexRequestParams(Request $request): void
  {
    $this->currBrand = ($request->brand == 'Visi') ? 'Visi' : $request->brand;
    $this->currBrand = ($this->currBrand === NULL) ? 'Visi' : $request->brand;

    $this->d1 = ($request->d1 == 'Visi') ? 'Visi' : $request->d1;
    $this->d2 = ($request->d2 == 'Visi') ? 'Visi' : $request->d2;
    $this->d3 = ($request->d3 == 'Visi') ? 'Visi' : $request->d3;

    if ($request->d1 == NULL && $this->d1 == NULL) {
      $this->d1 = 10;
    }

    if ($request->d2 == NULL && $this->d2 == NULL) {
      $this->d2 = '';
    }

    if ($request->d3 == NULL && $this->d3 == NULL) {
      $this->d3 = 16.5;
    }
  }

  protected function shareIndexViewData(): void
  {
    $this->brands = $this->tires_getBrands();
    $this->bigTiresD1 = Tires::getBigTiresSize('d1');
    $this->bigTiresD2 = Tires::getBigTiresSize('d2');
    $this->bigTiresD3 = Tires::getBigTiresSize('d3');

    $tireOptions = $this->getTireOptions();

    $this->shareCodeArray();

    View::share('brands', $this->brands);
    View::share('bigTiresD1', $this->bigTiresD1);
    View::share('bigTiresD2', $this->bigTiresD2);
    View::share('bigTiresD3', $this->bigTiresD3);
    View::share('currBrand', $this->currBrand);
    View::share('d1', $this->d1);
    View::share('d2', $this->d2);
    View::share('d3', $this->d3);
    View::share('types', []);
    View::share('implementions', []);
    View::share('code_array', $this->code_array);
    View::share('tire_types', $tireOptions['tire_types']);
    View::share('tire_implementions', $tireOptions['tire_implementions']);
    View::share('filterCount', $this->filterCount);
    View::share('cartQty', $this->cartQty);
  }

  protected function shareCodeArray(): void
  {
    if (!empty($this->code_array)) {
      View::share('code_array', $this->code_array);
      return;
    }

    $codes = Cache::remember('codes_table_all', 3600, function () {
      return Code::all();
    });

    foreach ($codes as $code) {
      $this->code_array[$code->name] = $code->explanation;
    }

    View::share('code_array', $this->code_array);
  }

  protected function paginateUniqueTires(?string $brandTitle = null, array $types = [], array $implementions = [])
  {
    $query = $this->buildTireQuery($brandTitle, $types, $implementions);

    $uniqueIds = (clone $query)
      ->reorder()
      ->selectRaw('MIN(big_tires.tire_id)')
      ->groupBy('big_tires.article');

    return $query
      ->whereIn('big_tires.tire_id', $uniqueIds)
      ->simplePaginate();
  }

  protected function buildTireQuery(?string $brandTitle = null, array $types = [], array $implementions = []): Builder
  {
    $d1Filter = $this->normalizeDimension($this->d1);
    $d2Filter = $this->normalizeDimension($this->d2);
    $d3Filter = $this->normalizeDimension($this->d3);

    return Bigtire::with(['tread.brand', 'stocks'])
      ->when($brandTitle, function ($query) use ($brandTitle) {
        $query->whereHas('tread.brand', function ($brandQuery) use ($brandTitle) {
          $brandQuery->where('title', $brandTitle);
        });
      })
      ->when($d1Filter !== null, function ($query) use ($d1Filter) {
        $this->applyDimensionFilter($query, 'd1', $d1Filter);
      })
      ->when($d2Filter !== null, function ($query) use ($d2Filter) {
        $this->applyDimensionFilter($query, 'd2', $d2Filter);
      })
      ->when($d3Filter !== null, function ($query) use ($d3Filter) {
        $this->applyDimensionFilter($query, 'd3', $d3Filter);
      })
      ->when($types, function ($query) use ($types) {
        $query->whereIn('big_tires.type', $types);
      })
      ->when($implementions, function ($query) use ($implementions) {
        $query->whereIn('big_tires.implemention', $implementions);
      })
      ->where('big_tires.visible_users', '<>', 0)
      ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
      ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
      ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
      ->orderBy('quantity', 'DESC');
  }

  protected function normalizeDimension($value): ?string
  {
    if ($value === null) {
      return null;
    }

    $value = trim((string) $value);

    if ($value === '' || strcasecmp($value, 'Visi') === 0) {
      return null;
    }

    return $value;
  }

  protected function applyDimensionFilter(Builder $query, string $column, string $value): void
  {
    $query->where($column, $value);
  }

}
