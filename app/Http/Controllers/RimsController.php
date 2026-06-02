<?php

namespace App\Http\Controllers;

use App\Helper\Image;
use App\Models\Rim;
use App\Models\Rimbrand;
use App\Models\Rimmake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class RimsController extends Controller
{
  public $currentCar;
  public $currentModel;
  public $currentR1;
  public $currentR2;
  public $currentR;
  public $currentForm;
  public $currentWid;
  public $currentWid2;
  public $currentSkr;
  public $currentPcd;
  public $currentEt;
  public $currentEt2;
  public $currentDia;
  public $currentCenter;
  public $model = 'Rim';
  public $cartQty = 4;
  public $models;

  private const RIMS_PER_PAGE = 80;

  public function __construct(Request $request)
  {
    $this->applyRequestParams($request);

    if ($this->shouldSkipViewShare($request)) {
      View::share('cartQty', $this->cartQty);
      return;
    }

    $this->shareCatalogViewData();
  }

  protected function shouldSkipViewShare(Request $request): bool
  {
    return $request->is('api/rims/auto')
      || $request->routeIs('lietie-diski-ajax')
      || $request->routeIs('lietais-disks');
  }

  protected function applyRequestParams(Request $request): void
  {
    $this->d1 = ($request->d1 == 'Visi') ? 'Visi' : $request->d1;

    $this->currentCar = ($request->car) ? $request->car : '';
    $this->currentModel = ($request->model) ? $request->model : '';
    $this->currentR1 = ($request->r1) ? $request->r1 : '';
    $this->currentR2 = ($request->r2) ? $request->r2 : '';
    $this->currentR = $this->currentR1;

    $this->currentForm = ($request->currentForm == 1) ? 1 : 2;

    $this->currentWid = ($request->currentWid) ? $request->currentWid : 6;
    $this->currentWid2 = ($request->currentWid2) ? $request->currentWid2 : 8;
    $this->currentSkr = ($request->currentSkr) ? $request->currentSkr : 5;
    $this->currentPcd = ($request->currentPcd) ? $request->currentPcd : 112;
    $this->currentEt = ($request->currentEt) ? $request->currentEt : '';
    $this->currentEt2 = ($request->currentEt2) ? $request->currentEt2 : '';
    $this->currentDia = ($request->currentDia) ? $request->currentDia : 16;
    $this->currentCenter = ($request->currentCenter) ? $request->currentCenter : '';

    if (($this->currentSkr !== false) || ($this->currentPcd !== false) || ($this->currentEt !== false)) {
      $this->currentCar = -1;
      $this->currentModel = -1;
      if ($this->currentR2 !== false) {
        $this->currentR = $this->currentR2;
      }
    }

    if ($this->currentCar === -1) {
      $this->currentModel = false;
    }
  }

  protected function shareCatalogViewData(): void
  {
    View::share('brandList', $this->getBrandList());
    View::share('currentCar', $this->currentCar);
    View::share('currentWid', $this->currentWid);
    View::share('currentWid2', $this->currentWid2);
    View::share('currentEt', $this->currentEt);
    View::share('currentEt2', $this->currentEt2);
    View::share('currentPcd', $this->currentPcd);
    View::share('currentSkr', $this->currentSkr);
    View::share('currentDia', $this->currentDia);
    View::share('currentCenter', $this->currentCenter);

    $options = $this->getRimOptions();
    View::share([
      'widths' => $options['widths'],
      'offsets' => $options['offsets'],
      'centers' => $options['rim_center'],
      'diameters' => $options['diameters'],
      'lugs' => $options['lug_counts'],
      'studs_spread' => $options['stud_spreads'],
      'makes' => [],
      'models' => [],
    ]);
    View::share('cartQty', $this->cartQty);
  }

  public function rims()
  {
    $brands = Rimbrand::paginate();
    $rims = $this->buildFilteredRimsQuery()->paginate(self::RIMS_PER_PAGE);
    $this->prepareRimsForList($rims->getCollection());

    return view('rims.autorims', compact('rims', 'brands'));
  }

  protected function applyRimCatalogFilters($query)
  {
    return $query
      ->when($this->currentWid, function ($query) {
        $query->where('rims.d1', '>=', $this->currentWid);
      })->when($this->currentWid2, function ($query) {
        $query->where('rims.d1', '<=', $this->currentWid2);
      })->when($this->currentEt, function ($query) {
        $query->where('rims.et', '>=', $this->currentEt);
      })->when($this->currentEt2, function ($query) {
        $query->where('rims.et', '<=', $this->currentEt2);
      })->when($this->currentPcd, function ($query) {
        $query->where('rims.pcd', $this->currentPcd);
      })->when($this->currentSkr, function ($query) {
        $query->where('rims.skr', $this->currentSkr);
      })->when($this->currentDia, function ($query) {
        $query->where('rims.d3', $this->currentDia);
      })->when($this->currentCenter, function ($query) {
        $query->where('rims.dc', $this->currentCenter);
      })
      ->where(function ($query) {
        $query->where('rims.quantity', '>', 0)
          ->orWhere(function ($query) {
            $query->where('rims.quantity', '=', 0)
              ->whereIn('rims.rim_id', function ($sub) {
                $sub->select('rim_id')
                  ->from('rim_stock')
                  ->where('quantity', '>=', 1);
              });
          });
      })
      ->where('rims.visible_users', '<>', 0);
  }

  protected function buildFilteredRimsCountQuery()
  {
    return $this->applyRimCatalogFilters(Rim::query()->from('rims'));
  }

  protected function buildFilteredRimsQuery()
  {
    return $this->applyRimCatalogFilters(
      Rim::select('rims.*', 'rim_makes.title as tread_title', 'rim_brands.title as brand_title')
        ->from('rims')
        ->leftJoin('rim_makes', 'rims.make_id', '=', 'rim_makes.make_id')
        ->leftJoin('rim_brands', 'rim_makes.brand_id', '=', 'rim_brands.brand_id')
    )
      ->orderBy('rims.quantity', 'DESC')
      ->orderBy('rims.price2', 'DESC');
  }

  protected function rimApiCountCacheKey(bool $showSelected, array $selectedRims): string
  {
    return 'rim_api_total_v1_' . Cache::get('rim_api_count_version', 1) . '_' . md5(json_encode([
      'wid' => $this->currentWid,
      'wid2' => $this->currentWid2,
      'et' => $this->currentEt,
      'et2' => $this->currentEt2,
      'pcd' => $this->currentPcd,
      'skr' => $this->currentSkr,
      'dia' => $this->currentDia,
      'center' => $this->currentCenter,
      'show_selected' => $showSelected,
      'selected' => $selectedRims,
    ]));
  }

  protected function applyApiFilters(Request $request): void
  {
    $this->currentWid = ($request->currentWid == 'Visi') ? '' : $request->currentWid;
    $this->currentWid2 = ($request->currentWid2 == 'Visi') ? '' : $request->currentWid2;
    $this->currentEt = ($request->currentEt == 'Visi') ? '' : $request->currentEt;
    $this->currentEt2 = ($request->currentEt2 == 'Visi') ? '' : $request->currentEt2;
    $this->currentPcd = ($request->currentPcd == 'Visi') ? '' : $request->currentPcd;
    $this->currentSkr = ($request->currentSkr == 'Visi') ? '' : $request->currentSkr;
    $this->currentDia = ($request->currentDia == 'Visi') ? '' : $request->currentDia;
    $this->currentCenter = ($request->currentCenter == 'Visi') ? '' : $request->currentCenter;
  }

  protected function prepareRimsForList($rims): void
  {
    $items = $rims instanceof \Illuminate\Support\Collection ? $rims : collect($rims);

    Rim::preloadStockData($items->pluck('rim_id')->all());
    Rim::preloadMakeData($items->pluck('make_id')->filter()->unique()->values()->all());

    foreach ($items as $rim) {
      $rim->includeStock = true;

      $brandTitle = $rim->brand_title ?? $rim->brandTitle ?? '';
      $treadTitle = $rim->tread_title ?? $rim->treadTitle ?? '';

      if ($brandTitle !== '') {
        $rim->setAttribute('brandTitle', $brandTitle);
      }
      if ($treadTitle !== '') {
        $rim->setAttribute('treadTitle', $treadTitle);
      }

      $rim->setAttribute('fullTitle', trim($brandTitle . ' ' . $treadTitle));
      $rim->setAttribute('fullName', trim($brandTitle . ' ' . $treadTitle . ' ' . $rim->skr . 'x' . $rim->pcd . ' R' . $rim->d3 . ' ' . $rim->d1 . 'J et' . $rim->et . ' ' . $rim->dc . ' ' . $rim->color));
      $rim->setAttribute('getUrl', '/lietie-diski/' . Str::slug($brandTitle) . '/' . strtolower(str_replace('/', '_', $treadTitle)) . '/' . $rim->rim_id);
      $rim->setAttribute('dotAvailable', $rim->getDotAvailableAttribute());
      $rim->setAttribute('stockAvailability', $rim->getStockAvailabilityAttribute());
      $rim->setAttribute('stockCount', $rim->getStockCount());
    }
  }

  public function api_tires(Request $request)
  {
    try {
      $page = ($request->page) ? (int) $request->page : 1;
      $perPage = self::RIMS_PER_PAGE;
      $offset = ($page - 1) * $perPage;

      $selectedRims = array_values(array_filter(explode(',', (string) ($request->selected ?? ''))));
      $show_selected = $request->show_selected;

      $this->applyApiFilters($request);

      $countQuery = $this->buildFilteredRimsCountQuery()
        ->when($show_selected, function ($query) use ($selectedRims) {
          $query->whereIn('rims.rim_id', $selectedRims);
        });

      $listQuery = $this->buildFilteredRimsQuery()
        ->when($show_selected, function ($query) use ($selectedRims) {
          $query->whereIn('rims.rim_id', $selectedRims);
        });

      $countCacheKey = $this->rimApiCountCacheKey((bool) $show_selected, $selectedRims);
      $totalItems = Cache::remember($countCacheKey, 120, function () use ($countQuery) {
        return (int) $countQuery->count('rims.rim_id');
      });
      $totalPages = (int) ceil($totalItems / $perPage);

      $rims = $listQuery->skip($offset)->take($perPage)->get();
      $this->prepareRimsForList($rims);

      if ($rims->isEmpty()) {
        $html = view('rims.auto.api-empty')->render();
      } elseif ($request->table_type === 'grid') {
        $html = view('rims.auto.api-grid', [
          'rims' => $rims,
          'cartQty' => $this->cartQty,
        ])->render();
      } else {
        $html = view('rims.auto.api-list', [
          'rims' => $rims,
          'cartQty' => $this->cartQty,
        ])->render();
      }

      $html .= $this->generatePagination($page, $totalPages, $offset, $perPage, $totalItems);

      return response()->json($html, 200);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function rims_search(Request $request)
  {
    $this->applyApiFilters($request);

    $rims = $this->buildFilteredRimsQuery()
      ->orderBy('rims.d3', 'ASC')
      ->orderBy('rims.d1', 'ASC')
      ->orderBy('rims.price3', 'DESC')
      ->paginate(self::RIMS_PER_PAGE)
      ->appends($request->query());

    $this->prepareRimsForList($rims->getCollection());

    return view('rims.autorims', compact('rims'));
  }

  public function rims_tread($brand, $tread, $rim)
  {
    $brandModel = Rimbrand::where('slug', $brand)->first()
      ?? Rimbrand::where('title', $brand)->first();

    if (!$brandModel) {
      abort(404);
    }

    $treadTitle = str_replace('_', '/', $tread);
    $rimId = (int) $rim;

    $rims = Rim::select(
      'rims.*',
      'rim_makes.brand_id',
      'rim_makes.title as tread_title',
      'rim_brands.title as brand_title',
      'rim_brands.title as brands_title'
    )
      ->join('rim_makes', 'rims.make_id', '=', 'rim_makes.make_id')
      ->join('rim_brands', 'rim_makes.brand_id', '=', 'rim_brands.brand_id')
      ->where('rim_brands.brand_id', $brandModel->brand_id)
      ->where('rim_makes.title', $treadTitle)
      ->where('rims.visible_users', '<>', 0)
      ->orderBy('rims.quantity', 'DESC')
      ->orderBy('rims.price3', 'DESC')
      ->orderBy('rims.d3', 'ASC')
      ->orderBy('rims.d1', 'ASC')
      ->get();

    $currRim = $rims->firstWhere('rim_id', $rimId);

    if (!$currRim) {
      $currRim = Rim::select(
        'rims.*',
        'rim_makes.brand_id',
        'rim_makes.title as tread_title',
        'rim_brands.title as brand_title',
        'rim_brands.title as brands_title'
      )
        ->leftJoin('rim_makes', 'rims.make_id', '=', 'rim_makes.make_id')
        ->leftJoin('rim_brands', 'rim_makes.brand_id', '=', 'rim_brands.brand_id')
        ->where('rims.rim_id', $rimId)
        ->first();

      if (!$currRim) {
        abort(404);
      }

      if (!$rims->contains('rim_id', $currRim->rim_id)) {
        $rims->push($currRim);
      }
    }

    $this->prepareRimsForList($rims);

    return view('rims.auto.tread', [
      'rims' => $rims,
      'currRim' => $currRim,
      'currBrand' => $brandModel,
    ]);
  }

  public function quadr_rims_tread($brand, $tread, $rim)
  {
    $brand = Rimbrand::where('slug', $brand)->first();
    $tread = Rimmake::where('slug', $tread)->first();

    $currRim = Rim::join('rim_makes', 'rims.make_id', '=', 'rim_makes.make_id')
      ->where('rim_makes.title', $tread->title)
      ->where('rims.rim_id', $rim)
      ->first();

    $rims = Rim::leftJoin('rim_makes', 'rims.make_id', '=', 'rim_makes.make_id')
      ->leftJoin('rim_brands', 'rim_makes.brand_id', '=', 'rim_brands.brand_id')
      ->select('rims.*', 'rim_makes.title as tread_title', 'rim_brands.brand_id as brand_id', 'rim_brands.title as brand_title')
      ->where('rims.make_id', $tread->make_id)
      ->paginate(20);

    $this->prepareRimsForList($rims->getCollection());

    return view('rims.quadr.tread', compact('rims', 'currRim', 'brand', 'tread'));
  }

  public function rims_ajax(Request $request): \Illuminate\Http\JsonResponse
  {
    $rimId = $request->rim_id ?? $request->tire_id ?? $request->id ?? null;

    if (!$rimId || $rimId === '') {
      return response()->json(['error' => 'No rim ID provided', 'received_data' => $request->all()], 400);
    }

    $rim = Rim::select('rims.*', 'rim_makes.title as tread_title', 'rim_brands.title as brand_title')
      ->leftJoin('rim_makes', 'rims.make_id', '=', 'rim_makes.make_id')
      ->leftJoin('rim_brands', 'rim_makes.brand_id', '=', 'rim_brands.brand_id')
      ->where('rims.rim_id', $rimId)
      ->first();

    if (!$rim) {
      return response()->json(['error' => 'Rim not found with ID: ' . $rimId], 404);
    }

    $this->prepareRimsForList(collect([$rim]));

    $quantity = $request->quantity ? $request->quantity : $this->cartQty;
    $cart = session()->get('cart', ['products' => []]);

    $brandTitle = $rim->brandTitle ?? '';
    $treadTitle = $rim->treadTitle ?? '';

    $cart['products'][$rim->rim_id] = array_merge($cart['products'][$rim->rim_id] ?? [], [
      'id' => $rim->rim_id,
      'name' => trim($brandTitle . ' ' . $treadTitle),
      'make_id' => $rim->make_id,
      'd1' => $rim->d1 ?? null,
      'd2' => $rim->d2 ?? null,
      'd3' => $rim->d3 ?? null,
      'skr' => $rim->skr ?? null,
      'pcd' => $rim->pcd ?? null,
      'et' => $rim->et ?? null,
      'dc' => $rim->dc ?? null,
      'color' => $rim->color ?? null,
      'type' => 'Disks',
      'url' => $request->rim_url ?? '',
      'image' => Image::image('auto-rim', $rim->make_id),
      'price' => $rim->price2 ?? $rim->price1 ?? 0,
      'availability' => $rim->dotAvailable ?? 'green',
      'category' => $this->model,
    ]);

    $existingQuantity = $cart['products'][$rim->rim_id]['quantity'] ?? 0;
    $cart['products'][$rim->rim_id]['quantity'] = $existingQuantity + $quantity;

    $priceMap = $this->getRimPriceMap(array_column($cart['products'], 'id'));

    $totalSum = 0;
    foreach ($cart['products'] as $product) {
      $totalSum += $product['quantity'] * ($priceMap[$product['id']] ?? 0);
    }

    $cart['total_sum'] = $totalSum;
    session()->put('cart', $cart);
    ShopController::updateCartInDatabase($totalSum);

    $totalQuantity = array_sum(array_column($cart['products'], 'quantity'));

    try {
      return response()->json([
        'cart' => $cart,
        'total_sum' => $totalSum,
        'quantity' => $totalQuantity,
        'bought' => $quantity,
      ]);
    } catch (\Exception $e) {
      return response()->json(['error' => 'Error in JSON response: ' . $e->getMessage()], 500);
    }
  }

  public function quadr_rims()
  {
    $options = $this->getRimOptions();
    $diameters = $options['diameters'];
    $lug_count = $options['lug_counts'];

    $brands = Rimbrand::paginate();

    $rims = Rim::leftJoin('rim_makes', 'rims.make_id', '=', 'rim_makes.make_id')
      ->leftJoin('rim_brands', 'rim_makes.brand_id', '=', 'rim_brands.brand_id')
      ->select('rims.*', 'rim_makes.title as tread_title', 'rim_brands.brand_id as brand_id', 'rim_brands.title as brand_title')
      ->orderBy('rim_brands.brand_id', 'ASC')
      ->where('rims.price1', '<>', 0)
      ->where('rims.price3', '<>', 0)
      ->where('rims.price2', '<>', 0)
      ->paginate();

    $this->prepareRimsForList($rims->getCollection());

    return view('rims.quadrim', compact('rims', 'brands', 'diameters', 'lug_count') + [
      'makes' => [],
      'models' => [],
    ]);
  }

  public function rims_getBrands()
  {
    return response()->json($this->getBrandList());
  }

  public function getRimOptions()
  {
    return Cache::remember('rim_filter_options_v2', 3600, function () {
      $options = [
        'widths' => [],
        'offsets' => [],
        'diameters' => [],
        'lug_counts' => [],
        'stud_spreads' => [],
        'rim_center' => [],
      ];

      $rimProperties = [
        'd1' => 'widths',
        'et' => 'offsets',
        'd3' => 'diameters',
        'skr' => 'lug_counts',
        'pcd' => 'stud_spreads',
        'dc' => 'rim_center',
      ];

      foreach ($rimProperties as $column => $optionKey) {
        $values = Rim::query()
          ->where('visible_users', '<>', 0)
          ->whereNotNull($column)
          ->where($column, '<>', '')
          ->distinct()
          ->orderBy($column)
          ->pluck($column)
          ->filter(function ($value) use ($optionKey) {
            return $optionKey === 'offsets' || !empty($value);
          })
          ->values()
          ->all();

        $options[$optionKey] = $values;
      }

      return $options;
    });
  }

  public function getBrandList()
  {
    return Cache::remember('rim_brand_list_v1', 3600, function () {
      return Rimbrand::query()
        ->whereNotNull('title')
        ->where('title', '<>', '')
        ->orderBy('title')
        ->pluck('title')
        ->all();
    });
  }

  protected function getRimPriceMap(array $rimIds): array
  {
    if ($rimIds === []) {
      return [];
    }

    return Rim::query()
      ->whereIn('rim_id', $rimIds)
      ->get(['rim_id', 'price1', 'price2'])
      ->mapWithKeys(function ($rim) {
        return [$rim->rim_id => (int) ($rim->price2 ?? $rim->price1 ?? 0)];
      })
      ->all();
  }

  public function generatePagination($page, $totalPages, $offset, $perPage, $totalItems)
  {
    $html = '';

    if ($totalPages > 1) {
      $html .= '<div class="col-sm-12 col-md-12 pagination-col">';
      $html .= '<div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">';
      $html .= '<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite">';
      if (($offset + $perPage) > $totalItems) {
        $html .= $offset + 1 . ' līdz ' . $totalItems . ' ieraksti no ' . $totalItems . ' ierakstiem';
      } else {
        $html .= $offset + 1 . ' līdz ' . ($offset + $perPage) . ' ieraksti no ' . $totalItems . ' ierakstiem';
      }
      $html .= '</div>';
      $html .= '<ul class="pagination">';

      $html .= '<li class="paginate_button page-item previous ';
      $html .= ($page == 1) ? 'disabled' : '';
      $html .= '">';
      $html .= '<a href="#" class="page-link">Atpakaļ</a>';
      $html .= '</li>';

      $visiblePages = 8;
      $firstPages = 2;
      $lastPages = 2;

      $startFirstPages = 1;
      $endFirstPages = min($firstPages, $totalPages);

      for ($i = $startFirstPages; $i <= $endFirstPages; $i++) {
        $html .= '<li class="paginate_button page-item ';
        $html .= ($i == $page) ? 'active' : '';
        $html .= '">';
        if ($i == $page) {
          $html .= '<span style="pointer-events: none;" class="page-link">' . $i . '</span>';
        } else {
          $html .= '<a href="#" data-page="' . $i . '" class="page-link">' . $i . '</a>';
        }
        $html .= '</li>';
      }

      $startPage = max($firstPages + 1, min($page - floor($visiblePages / 2), $totalPages - $visiblePages + 1));
      $endPage = min($totalPages, $startPage + $visiblePages - 1);

      for ($i = $startPage; $i <= $endPage; $i++) {
        $html .= '<li class="paginate_button page-item ';
        $html .= ($i == $page) ? 'active' : '';
        $html .= '">';
        if ($i == $page) {
          $html .= '<span style="pointer-events: none;" class="page-link">' . $i . '</span>';
        } else {
          $html .= '<a href="#" data-page="' . $i . '" class="page-link">' . $i . '</a>';
        }
        $html .= '</li>';
      }

      $startLastPages = max($totalPages - $lastPages + 1, $endPage + 1);
      for ($i = $startLastPages; $i <= $totalPages; $i++) {
        $html .= '<li class="paginate_button page-item ';
        $html .= ($i == $page) ? 'active' : '';
        $html .= '">';
        if ($i == $page) {
          $html .= '<span style="pointer-events: none;" class="page-link">' . $i . '</span>';
        } else {
          $html .= '<a href="#" data-page="' . $i . '" class="page-link">' . $i . '</a>';
        }
        $html .= '</li>';
      }

      $html .= '<li class="paginate_button page-item next ';
      $html .= ($page == $totalPages) ? 'disabled' : '';
      $html .= '">';
      $html .= '<a href="#" class="page-link">Uz priekšu</a>';
      $html .= '</li>';

      $html .= '</ul>';
      $html .= '</div>';
      $html .= '</div>';
    }

    return $html;
  }
}
