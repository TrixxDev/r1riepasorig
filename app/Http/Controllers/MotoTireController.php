<?php

namespace App\Http\Controllers;

use App\Helper\Image;
use App\Helper\Tires;
use Illuminate\Http\Request;
use App\Models\Moto;
use App\Models\Motobrand;
use App\Models\Mototread;
use App\Models\Code;
use Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use View;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Cookie;

class MotoTireController extends Controller
{

    public $brands;
    public $currBrand;
    public $d1;
    public $d2;
    public $d3;
    public $motoTiresD1;
    public $motoTiresD2;
    public $motoTiresD3;
    public $model = 'Moto';
    public $type;
    public $availability = [];
    public $code_array = [];
    public $code = [];
    public $motoFilterCodes = ['F', 'R', 'TL', 'WW'];
    public $filterCount = 0;

    public $cartQty = 1;

    public function __construct(Request $request)
    {
        if ($request->is('api/moto/*')
            || $request->is('motociklu-riepas/search/api/*')
            || $this->shouldSkipHeavyViewShare($request)) {
            $this->shareCodeArray();
            View::share('cartQty', $this->cartQty);
            return;
        }

        $this->brands = $this->tires_getBrands();

        $this->motoTiresD1 = Tires::getMotoTiresD1();
        $this->motoTiresD2 = Tires::getMotoTiresD2();
        $this->motoTiresD3 = Tires::getMotoTiresD3();

        ($request->brand == 'Visi') ? $this->currBrand = 'Visi' : $this->currBrand = $request->brand;
        ($this->currBrand === NULL) ? $this->currBrand = 'Visi' : $this->currBrand = $request->brand;

        ($request->d1 == 'Visi') ? $this->d1 = 'Visi' : $this->d1 = $request->d1;
        ($request->d2 == 'Visi') ? $this->d2 = 'Visi' : $this->d2 = $request->d2;
        ($request->d3 == NULL) ? $this->d3 = 17 : $this->d3 = $request->d3;

        ($request->type) ? $this->type = $request->type : $this->type = [];
        $this->code = self::parseCodeFilterParam($request->code ?? null);

        if ($request->d1 == NULL && $this->d1 == NULL) {
          $this->d1 = 120;
        }

        if ($request->d2 == NULL && $this->d2 == NULL) {
          $this->d2 = 70;
        }

        if ($request->d3 == NULL && $this->d3 == NULL) {
          $this->d3 = 17;
        }

        $this->shareCodeArray();

        View::share('brands', $this->brands);
        View::share('motoTiresD1', $this->motoTiresD1);
        View::share('motoTiresD2', $this->motoTiresD2);
        View::share('motoTiresD3', $this->motoTiresD3);
        View::share('currBrand', $this->currBrand);
        View::share('d1', $this->d1);
        View::share('d2', $this->d2);
        View::share('d3', $this->d3);
        View::share('type', $this->type);
        View::share('types', (new Moto)->types());
        View::share('code', $this->code);
        View::share('motoFilterCodes', $this->motoFilterCodes);
        View::share('motoFilterCodeAliases', self::motoFilterCodeAliases());
        View::share('filterCount', $this->filterCount);
        View::share('availability', $this->availability);
        View::share('cartQty', $this->cartQty);
    }

    public function index()
    {

        return view('tires.moto.index');
    }

    public function tires_tread(Request $request, $brand, $tread, $tire)
    {

        $selectedTires = [];
        if ($request->input('selected')) {
          $selectedTires = explode(',', $request->input('selected'));
        }

        $brand = Motobrand::where('title', $brand)->first();

        $tread = str_replace('_', '/', $tread);
        $tread = Mototread::where('title', $tread)->first();

        $tires = Moto::selectRaw('moto_tires.*, moto_treads.*, moto_brands.*,
                                  moto_brands.title as brands_title, moto_treads.title as treads_title')
                                  ->join('moto_treads', 'moto_tires.make_id', '=', 'moto_treads.tread_id')
                                  ->join('moto_brands', 'moto_treads.brand_id', '=', 'moto_brands.brand_id')
                                  ->where('moto_tires.visible_users', '<>', 0)
                                  ->where('moto_brands.title', $brand->title)
                                  ->where('moto_treads.title',  $tread->title)
                                  ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
                                  ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
                                  ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
                                  ->orderBy('d4', 'ASC')
                                  ->get();

        $currTire = $tires->firstWhere('tire_id', (int) $tire);

        if (!$currTire) {
            abort(404);
        }

        $currBrand = Motobrand::where('brand_id', $currTire->brand_id)->first();

	//dd($tire);
	//$stock = DB::table('moto_stock')->where('tire_id', $currTire->tire_id)->first();
        $currTire->includeStock = true;

        Moto::preloadStockData($tires->pluck('tire_id')->all());

        return view('tires.moto.mototread',
            compact('tires', 'currTire', 'currBrand', 'selectedTires')
        );
    }

  public function api_tires(Request $request) {
    try {

      $html = '';

      $page = ($request->page) ? (int) $request->page : 1; // Get the current page from the request, default to 1
      $perPage = 80; // Number of items per page

      $offset = ($page - 1) * $perPage;

      $d1 = ($request->d1 == 'Visi') ? '' : $request->d1;
      $d2 = ($request->d2 == 'Visi') ? '' : $request->d2;
      $d3 = ($request->d3 == 'Visi') ? '' : $request->d3;

      $this->availability = $availability = '';
      if (isset($request->availability)) {
        $availability = explode(' ', $request->availability);
        $this->availability = $availability = implode('+', $availability);
      }

      $currBrand = ($request->brand == 'Ražotājs') ? '' : $request->brand;

      $selectedTires = array_values(array_filter(explode(',', (string) ($request->selected ?? ''))));
      $topTires = $request->top;
      $show_selected = $request->show_selected;

      $fastsearch = $request->fastsearch;

      if ($fastsearch) {
        $splited = $this->splitInput($fastsearch);
        $this->d1 = $d1 = $splited['d1'];
        $this->d2 = $d2 = $splited['d2'];
        $this->d3 = $d3 = $splited['d3'];
      }

      $selectedTypes = array_values(array_filter(
        Moto::parseTypeFilterParam($request->type ?? null),
        static fn ($type) => str_replace(' ', '', strtolower($type)) !== 'kamera'
      ));

      $filterCamera = in_array((string) $request->camera, ['1', 'true'], true);

      $selectedCodes = self::parseCodeFilterParam($request->code ?? null);
      $codeFilterGroups = self::buildCodeFilterGroups($selectedCodes);

      $typeConditions = [
        'custom' => ['moto_tires.type', '=', 'custom'],
        'harleydavidson' => ['moto_tires.type', '=', 'harley davidson'],
        'motocross' => ['moto_tires.type', '=', 'moto cross'],
        'racing' => ['moto_tires.type', '=', 'racing'],
        'scooter' => ['moto_tires.type', '=', 'scooter'],
        'sport' => ['moto_tires.type', '=', 'sport'],
        'sporttouring' => ['moto_tires.type', '=', 'sport touring'],
        'trail' => ['moto_tires.type', '=', 'trail'],
      ];

      $filterContext = [
        'currBrand' => $currBrand,
        'd1' => $d1,
        'd2' => $d2,
        'd3' => $d3,
        'availability' => $availability,
        'codeFilterGroups' => $codeFilterGroups,
        'selectedTypes' => $selectedTypes,
        'typeConditions' => $typeConditions,
        'filterCamera' => $filterCamera,
        'selectedTires' => $selectedTires,
        'show_selected' => $show_selected,
        'topTires' => $topTires,
      ];

      $countQuery = $this->buildApiMotoBaseQuery();
      $this->applyApiMotoCatalogFilters($countQuery, $filterContext);

      $countCacheKey = $this->motoApiCountCacheKey($filterContext);
      $totalItems = Cache::remember($countCacheKey, 120, function () use ($countQuery) {
        return (int) $countQuery->distinct()->count('moto_tires.article');
      });
      $totalPages = (int) ceil($totalItems / $perPage);

      $listQuery = $this->buildApiMotoBaseQuery()
        ->selectRaw('moto_tires.*, moto_tires.quantity as tire_quantity, moto_treads.title as t_title, moto_treads.*, '
          . $this->partnerStockColumnSql() . ' as stock_quantity, moto_brands.title as api_brand_title, moto_brands.slug as api_brand_slug');
      $this->applyApiMotoCatalogFilters($listQuery, $filterContext);
      $listQuery->orderByRaw('cast(d3 as decimal(7,2)) ASC')
        ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
        ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
        ->orderBy('d4', 'ASC')
        ->orderBy('price2', 'DESC')
        ->groupBy('moto_tires.article');

      $tires = $listQuery->skip($offset)
        ->take($perPage)
        ->get();

      $this->prepareMotosForApiList($tires);

      $fullSize = '';
      $loopIndex = 0;

      if ($request->table_type === 'list') {
        if ($tires->count() > 0) {

          foreach ($tires as $index => $tire) {

            if ($index === 0) {
              $html .= '<span class="text-uppercase flipped-title tire-brand-name" style="color: black">Motociklu riepas</span>';
            }
            $index++;
            if ($fullSize !== $tire->fullSize) {
              $loopIndex = 0;
              $html .= '<table id="tires-table" class="table table-striped moto-sorter tires-table table-hover tablesorter">';
              $html .= '<thead class="tires-thead sticky-table">
                        <tr>
                          <th scope="col"></th>
                          <th scope="col" class="table-tire-name-cell">Brends / modelis</th>
                          <th scope="col" class="hidden-sm-down text-center">Tips</th>
                          <th scope="col" class="hidden-sm-down text-center">LI/SI</th>
                          <th scope="col" class="hidden-sm-down text-center">Kods</th>

                          <th id="store-price-button" scope="col" class="text-center">
                            Veikala cena
                          </th>

                          <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>
                          <th scope="col" class="table-tire-desc-cell hidden-sm-down text-center">Piezīmes</th>
                          <th scope="col"></th>
                          <th scope="col">
                            <div class="tire-table-icon icon-question" title="Pieejamība" data-toggle="tooltip"></div>
                          </th>

                        </tr>
                        </thead>';
              $html .= '<tbody id="tires-table-body">';
              $html .= '<h4 class="tire-brand-name">' . $tire->fullSize . '</h4>';
            }
            $isSelected = in_array((string) $tire->tire_id, $selectedTires, true);
            $html .= '<tr class="tire-table-row' . ($isSelected ? ' selected' : '') . '" role="row">';
            $typeValue = str_replace(' ', '', strtolower($tire->type));
            $html .= '<th scope="row" class="tire-table-checkbox"><input type="checkbox" value="' . $tire->tire_id . '" name="product_ids[]" class="tire-table-checkbox" title=""' . ($isSelected ? ' checked' : '')
                . ' data-availability="' . htmlspecialchars($tire->dotAvailable) . '"'
                . ' data-type="' . htmlspecialchars($typeValue) . '"'
                . ' data-is-camera="' . ((int) $tire->is_camera) . '"'
                . ' data-code="' . htmlspecialchars($tire->code) . '"'
                . '></th>';
            $html .= '<td class="table-tire-name-cell"><a class="tire-table-link tippy image" data-tippy-content="<div><img data-src=\'https://r1riepas.lv/storage/moto/tread/' . $tire->tread_id . '-o.jpg\'></div>" href="' . $tire->getUrl . '" data-content="' . $tire->fullName . '" data-article="' . $tire->article . '" data-quantity="1"><div class="table-link-title">' . $tire->fullTitle . '</div></a></td>';

            $html .= '<td scope="col" class="hidden-sm-down text-center">';
            $html .= '<span class="tippy lisi-tooltip type-explain" data-type="' . htmlspecialchars($typeValue) . '" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . $tire->typeDesc[1] . '</span></div>">' . $tire->motoType . '</span>';
            $html .= '</td>';

            $html .= '<td class="hidden-sm-down text-center"><span class="tippy lisi-tooltip" data-tippy-content="<div style=\'padding: 5px; text-align: left;\'><span style=\'color: black; font-size: 15px;\'>' . $tire->lisiDesc . '</span></div>">' . $tire->li . $tire->si . '</span></td>';

            $html .= '<td class="hidden-sm-down text-center"><span class="tippy lisi-tooltip" data-tippy-content="<div style=\'padding: 5px; text-align: left;\'><span style=\'color: black; font-size: 15px;\'>' . $tire->codeExplain . '</span></div>">' . $tire->code . '</span></td>';
            $html .= '<td id="store-price" class="text-center store-price">€ ' . $tire->price1 . '</td>';
            $html .= '<td id="sale-price" class="text-center tire-price-red sale-price">€ ' . $tire->price2 . '</td>';
            if ($tire->comment == 'Izpārdošana!' || $tire->priceoffer == 1) {
              $html .= '<td class="hidden-sm-down text-center sellout">' . $tire->comment . '</td>';
            } else {
              $html .= '<td class="hidden-sm-down text-center">' . $tire->comment . '</td>';
            }
            $html .= '<td class="shopping-cart-col"><div class="clearfix atc_div text-right">';
            if (Auth::check()) {
              if (Auth::user()->hasRole('administrators')) {
                $html .= '<button class="cart-shopping-button" data-toggle="modal" data-target="#" data-info="' . $tire->tire_id . '" data-url="' . $tire->getUrl . '"><i class="material-icons">add_shopping_cart</i></button>';
              } else {
                $html .= '<button class="cart-shopping-button" data-toggle="modal" data-target="#blockcart-modal" data-info="' . $tire->tire_id . '" data-url="' . $tire->getUrl . '"><i class="material-icons">add_shopping_cart</i></button>';
              }
            } else {
              $html .= '<button class="cart-shopping-button" data-toggle="modal" data-target="#blockcart-modal" data-info="' . $tire->tire_id . '" data-url="' . $tire->getUrl . '"><i class="material-icons">add_shopping_cart</i></button>';
            }
            $html .= '</div></td>';
            $html .= '<td class="dot-availability text-center"><span class="tippy lisi-tooltip dot ' . $tire->dotAvailable . '" data-tippy-content=\'<div style="padding: 5px; text-align: left;"><span style="color: black; font-size: 15px; line-height: 28px;">' . $tire->stockAvailability . '</span></div>\'></span></td>';
            $html .= '</tr>';
            $fullSize = $tire->fullSize;
            if ($fullSize !== $tire->fullSize) {
              $html .= '</tbody>';
              $html .= '</table>';
            }
          }
        }
      } else if ($request->table_type === 'grid') {
        $tiresGrouped = $tires->groupBy(function($tire) {
          return $tire->getFullSizeAttribute();
        });
        $html .= '<div class="tire-image-container">';
        $index = 0;
        foreach ($tiresGrouped as $fullSize => $group) {
          $group = $group->sortBy('price2', SORT_REGULAR, true);
          $html .= '</div><h4 class="tire-brand-name grid-t" style="margin-left: 5px;">' . $fullSize;
          if ($index == 0) {
            $html .= ' <span class="tire-type-title">Motociklu riepas</span>';
          }
          $html .= '<span style="margin: 0 auto;"></span>';
          $html .= '<button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">Filtrs ()</button></h4><div class="row grid-ex pr-1 mobile-tire-container" style="padding-left: 5px;">';
          foreach ($group as $tire) {
            $isSelected = in_array((string) $tire->tire_id, $selectedTires, true);
            $html .= '<a href="' . $tire->getUrl . '" class="grid-view-link"'
                . ' data-article="' . htmlspecialchars($tire->article) . '"'
                . ' data-content="' . htmlspecialchars($tire->fullName) . '"'
                . ' data-quantity="' . $cartQty . '"'
                . ' data-url="' . htmlspecialchars($tire->getUrl) . '">';
            $html .= '<div class="tire-image-card sort-order' . ($isSelected ? ' selected' : '') . '">';
            $html .= '<div class="text-center image-grid-overflow">';
            $html .= Image::showGrid('moto', $tire->make_id);
            $html .= '</div>';
            $html .= '<div class="tire-list-caption">';
            $html .= '<div class="card-title-text"><span class="tippy lisi-tooltip" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . $tire->title . '</span></div>">' . $tire->title . '</span></div>';
            $html .= '<div class="tire-tread">';
            $html .= '<b>' . $tire->fullSize . ' </b>';
            $html .= '<span data-toggle="tooltip" title="<span style=\'color: black\'>' . $tire->lisiDesc . '</span>">' . $tire->li . $tire->si . ' </span>';
            $html .= '<span class="tire-image-code">' . $tire->code . '</span>';
            $html .= '</div>';
            $html .= '<div style="display: flex;">';
            $typeValue = str_replace(' ', '', strtolower($tire->type));
            $html .= '<input type="checkbox" name="product_ids[]" value="' . $tire->tire_id . '" class="tire-table-checkbox" style="margin-right: 5px;"'
                . ($isSelected ? ' checked' : '')
                . ' data-availability="' . htmlspecialchars($tire->dotAvailable) . '"'
                . ' data-type="' . htmlspecialchars($typeValue) . '"'
                . ' data-is-camera="' . ((int) $tire->is_camera) . '"'
                . ' data-code="' . htmlspecialchars($tire->code) . '"'
                . '>';
            $html .= '<div class="rim-price-old" style="align-self: center;">€' . $tire->price1 . '</div>';
            $html .= '<div class="rim-price-red" style="align-self: center;">€' . $tire->price2 . '</div>';
            $html .= '<span style="margin-left: auto;" data-toggle="tooltip" title="<span style=\'color: black\'>Pievienot grozam</span>">';
            if (Auth::check()) {
              if (\Illuminate\Support\Facades\Auth::user()->hasRole('administrators')) {
                $html .= '<button class="grid-buy-btn cart-shopping-button" data-toggle="modal"'
                    . ' data-info="' . $tire->tire_id . '"'
                    . ' data-url="' . htmlspecialchars($tire->getUrl) . '"'
                    . ' data-article="' . htmlspecialchars($tire->article) . '"'
                    . ' data-content="' . htmlspecialchars($tire->fullName) . '"'
                    . ' data-quantity="' . $cartQty . '"'
                    . ' onclick="event.preventDefault()" data-target="#">';
              } else {
                $html .= '<button class="grid-buy-btn cart-shopping-button" data-toggle="modal"'
                    . ' data-info="' . $tire->tire_id . '"'
                    . ' data-url="' . htmlspecialchars($tire->getUrl) . '"'
                    . ' data-article="' . htmlspecialchars($tire->article) . '"'
                    . ' data-content="' . htmlspecialchars($tire->fullName) . '"'
                    . ' data-quantity="' . $cartQty . '"'
                    . ' onclick="event.preventDefault()" data-target="#blockcart-modal">';
              }
            } else {
              $html .= '<button class="grid-buy-btn cart-shopping-button" data-toggle="modal"'
                  . ' data-info="' . $tire->tire_id . '"'
                  . ' data-url="' . htmlspecialchars($tire->getUrl) . '"'
                  . ' data-article="' . htmlspecialchars($tire->article) . '"'
                  . ' data-content="' . htmlspecialchars($tire->fullName) . '"'
                  . ' data-quantity="' . $cartQty . '"'
                  . ' onclick="event.preventDefault()" data-target="#blockcart-modal">';
            }
            $html .= '<i class="material-icons">add_shopping_cart</i>';
            $html .= '</button>';
            $html .= '</span>';
            $html .= '<span class="tippy lisi-tooltip grid-dot ' . $tire->dotAvailable . '" data-color="' . htmlspecialchars($tire->dotAvailable) . '" data-tippy-content=\'<div style="padding: 5px;"><span style="color: black; font-size: 15px;">' . $tire->stockAvailability . '</span></div>\'></span>';
            $html .= '<span class="sort-order" style="display: none;">' . $tire->dotAvailable . '</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</a>';
          }
          $index++;
        }
        $html .= '</div>';
      }

      if ($tires->count() <= 0) {
        $html .= '<div class="container"><div class="col-md-12 mt-1 alert alert-danger">Ar šādiem parametriem nav atrasta neviena pozīcija.</div></div>';
      }

      $html .= $this->generatePagination($page, $totalPages, $offset, $perPage, $totalItems);

      return response()->json($html, 200);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], 500);
    }
  }

  public function tires_ajax(Request $request): \Illuminate\Http\JsonResponse
  {
    $tire = Moto::query()
      ->selectRaw('moto_tires.*, moto_treads.title as t_title, moto_brands.title as api_brand_title')
      ->join('moto_treads', 'moto_tires.make_id', '=', 'moto_treads.tread_id')
      ->join('moto_brands', 'moto_treads.brand_id', '=', 'moto_brands.brand_id')
      ->where('moto_tires.tire_id', $request->tire_id)
      ->first();

    if (!$tire) {
      return response()->json(['error' => 'Tire not found'], 404);
    }

    Moto::preloadStockData([(int) $tire->tire_id]);
    $dotAvailable = $tire->getDotAvailableAttribute();

    // Determine the quantity to add
    $quantity = $request->quantity ? $request->quantity : $this->cartQty;

    // Retrieve the current cart from the session or cookies
    $cart = session()->get('cart', ['products' => []]);

    // Check if the tire is already in the cart
    if (isset($cart['products'][$tire->tire_id])) {
      // If it exists, update the quantity
      $cart['products'][$tire->tire_id]['quantity'] += $quantity;
    } else {
      // If it doesn't exist, add it to the cart
      $cart['products'][$tire->tire_id] = [
        'id' => $tire->tire_id,
        'name' => $tire->getFullNameAttribute(),
        'make_id' => $tire->make_id,
        'd1' => $tire->d1,
        'd2' => $tire->d2,
        'd3' => $tire->d3,
        'type' => 'Motociklu riepa',
        'li' => $tire->li,
        'si' => $tire->si,
        'url' => $request->tire_url,
        'image' => Image::image('moto', $tire->make_id),
        'price' => $tire->price2,
        'quantity' => $quantity,
        'availability' => $dotAvailable,
        'category' => $this->model,
      ];
    }

    // Calculate the total price
    $totalSum = 0;
    foreach ($cart['products'] as $product) {
      $totalSum += $product['quantity'] * $product['price'];
    }

    $cart['total_sum'] = $totalSum;

    // Save the updated cart back to the session
    session()->put('cart', $cart);

    // Optionally save to cookies
    Cookie::queue('cart', json_encode($cart), 43200); // 30 days

    // Save the cart to the database
    ShopController::updateCartInDatabase($totalSum);
    
    event(new \App\Events\CartUpdated());

    // Calculate the total quantity of items in the cart
    $totalQuantity = array_sum(array_column($cart['products'], 'quantity'));

    // Return the response
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

  public function splitInput($input) {
    $input = str_replace(',', '.', $input);

    $europeanPattern = '/^(\d{2,3})(\d{2,3})(\d{2})$/';

    $fractionalPattern = '/^([\d.]+)(\d{2})$/';
    $randomPattern = '/^(\d{1}\.\d{2})(\d{2})$/';
    $randomPattern2 = '/^(\d{1}\.\d{2})(\d{1})$/';

    $americanPattern = '/^([A-Z]+\d{2})(\d{2})$/';

    if (preg_match($europeanPattern, $input, $matches)) {
      $matches = preg_split("/(?<=[05])(?=[1-9])/", $input);
      $d1 = $matches[0];
      if (isset($matches[3])) {
        $d2 = $matches[1] . $matches[2];
        $d3 = $matches[3];
      } else {
        $d2 = $matches[1];
        $d3 = $matches[2];
      }
    } else if (preg_match($randomPattern, $input, $matches) ||
               preg_match($randomPattern2, $input, $matches)) {
      $d1 = $matches[1];
      $d2 = '';
      $d3 = $matches[2];
    } elseif (preg_match($fractionalPattern, $input, $matches)) {
      $d1 = $matches[1];
      $d2 = '';
      $d3 = $matches[2];
    } elseif (preg_match($americanPattern, $input, $matches)) {
      $d1 = $matches[1];
      $d2 = '';
      $d3 = $matches[2];
    } else {
      $d1 = 1;
      $d2 = 1;
      $d3 = 1;
    }

    return compact('d1', 'd2', 'd3');
  }

  public function tires_find(Request $request) {
      if ($request->type) {
        $this->filterCount += 1;
        $this->type = $request->type;
      } else {
        $this->type = '';
      }
      if (in_array((string) $request->camera, ['1', 'true'], true)) {
        $this->filterCount += 1;
      }
      if ($this->code) {
        $this->filterCount += 1;
      }
      return view('tires.moto.index');
  }

  /** @return string[] */
  public static function parseCodeFilterParam(?string $code): array
  {
    if ($code === null || $code === '') {
      return [];
    }

    $allowed = ['F', 'R', 'TL', 'WW'];
    $selected = preg_split('/[\s+]+/', trim($code), -1, PREG_SPLIT_NO_EMPTY);

    return array_values(array_intersect($selected, $allowed));
  }

  /** @return array<string, string[]> */
  public static function motoFilterCodeAliases(): array
  {
    return [
      'F' => ['F', 'F/R'],
      'R' => ['R', 'F/R'],
      'WW' => ['WW', 'SW', 'MW'],
      'TL' => ['TL'],
    ];
  }

  /** @return string|string[] */
  public static function categorizeMotoFilterCode(string $code)
  {
    return self::motoFilterCodeAliases()[$code] ?? $code;
  }

  /** @return array<int, string[]> */
  public static function buildCodeFilterGroups(array $selectedCodes): array
  {
    $groups = [];

    foreach ($selectedCodes as $code) {
      $aliases = self::categorizeMotoFilterCode($code);
      $groups[] = is_array($aliases) ? $aliases : [$aliases];
    }

    return $groups;
  }

  public function get_sizes()
  {
    try {
      $tireSizes = Cache::remember('moto_tire_sizes_v1', 300, function () {
        return Moto::select(DB::raw('CONCAT(d1, d2, d3) as tire_size'))
          ->where('moto_tires.visible_users', '<>', 0)
          ->distinct()
          ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
          ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
          ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
          ->get();
      });

      return response()->json($tireSizes, 200);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function tires_getBrands()
  {
    return Cache::remember('moto_catalog_brands_v1', 300, function () {
      return Motobrand::join('moto_treads', 'moto_brands.brand_id', '=', 'moto_treads.brand_id')
        ->join('moto_tires', 'moto_treads.tread_id', '=', 'moto_tires.make_id')
        ->where('moto_tires.visible_users', '<>', 0)
        ->distinct()
        ->pluck('moto_brands.title', 'moto_brands.brand_id')
        ->sort(SORT_NATURAL | SORT_FLAG_CASE)
        ->map(fn ($title) => ucwords(strtolower($title)))
        ->all();
    });
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

      // Previous button
      $html .= '<li class="paginate_button page-item previous ';
      $html .= ($page == 1) ? 'disabled' : '';
      $html .= '">';
      $html .= '<a href="#" class="page-link">Atpakaļ</a>';
      $html .= '</li>';

      // Page numbers
      $visiblePages = 8; // Number of visible pages between first and last
      $firstPages = 2; // Number of pages to show at the beginning
      $lastPages = 2; // Number of pages to show at the end

      // Display first pages
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

      // Display pages between first and last
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

      // Display last pages
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

      // Next button
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

  protected function shouldSkipHeavyViewShare(Request $request): bool
  {
    return $request->routeIs('motociklu-riepa')
      || $request->routeIs('motociklu-riepas-ajax');
  }

  protected function shareCodeArray(): void
  {
    if (!empty($this->code_array)) {
      View::share('code_array', $this->code_array);
      return;
    }

    foreach (Code::all() as $code) {
      $this->code_array[$code->name] = $code->explanation;
    }

    View::share('code_array', $this->code_array);
  }

  protected function applyPartnerStockJoin($query)
  {
    return $query->leftJoinSub(
      DB::table('moto_stock')
        ->selectRaw('tire_id, SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END) as partner_stock')
        ->groupBy('tire_id'),
      'moto_stock_totals',
      'moto_tires.tire_id',
      '=',
      'moto_stock_totals.tire_id'
    );
  }

  protected function partnerStockColumnSql(): string
  {
    return 'COALESCE(moto_stock_totals.partner_stock, 0)';
  }

  protected function buildApiMotoBaseQuery()
  {
    return $this->applyPartnerStockJoin(
      Moto::query()->from('moto_tires')
        ->join('moto_treads', 'moto_tires.make_id', '=', 'moto_treads.tread_id')
        ->join('moto_brands', 'moto_treads.brand_id', '=', 'moto_brands.brand_id')
        ->where('moto_tires.visible_users', '<>', 0)
    );
  }

  protected function applyApiMotoCatalogFilters($query, array $filters): void
  {
    $partnerStock = $this->partnerStockColumnSql();

    $query->when($filters['currBrand'] ?? '', function ($query) use ($filters) {
      $query->where('moto_brands.slug', Str::slug($filters['currBrand']));
    })->when($filters['d1'] ?? '', function ($query) use ($filters) {
      $query->where('moto_tires.d1', $filters['d1']);
    })->when($filters['d2'] ?? '', function ($query) use ($filters) {
      $query->where('moto_tires.d2', $filters['d2']);
    })->when($filters['d3'] ?? '', function ($query) use ($filters) {
      $query->where('moto_tires.d3', $filters['d3']);
    })->when($filters['filterCamera'] ?? false, function ($query) {
      $query->where('moto_tires.is_camera', 1);
    })->when($filters['codeFilterGroups'] ?? [], function ($query) use ($filters) {
      foreach ($filters['codeFilterGroups'] as $aliasGroup) {
        $query->where(function ($query) use ($aliasGroup) {
          foreach ($aliasGroup as $alias) {
            $query->orWhere('moto_tires.code', 'like', '%' . $alias . '%');
          }
        });
      }
    })->when($filters['availability'] ?? '', function ($query) use ($filters, $partnerStock) {
      switch ($filters['availability']) {
        case 'green':
          $query->where('moto_tires.quantity', '>', 0);
          break;
        case 'green+yellow':
          $query->where(function ($query) use ($partnerStock) {
            $query->where('moto_tires.quantity', '>', 0)
              ->orWhere(function ($query) use ($partnerStock) {
                $query->where('moto_tires.quantity', '<=', 0)
                  ->whereRaw("{$partnerStock} > 0");
              });
          });
          break;
        case 'green+red':
          $query->where(function ($query) use ($partnerStock) {
            $query->where('moto_tires.quantity', '>', 0)
              ->orWhere(function ($query) use ($partnerStock) {
                $query->where('moto_tires.quantity', '<=', 0)
                  ->whereRaw("{$partnerStock} <= 0");
              });
          });
          break;
        case 'yellow':
          $query->where('moto_tires.quantity', '<=', 0)->whereRaw("{$partnerStock} > 0");
          break;
        case 'yellow+red':
          $query->where('moto_tires.quantity', '<=', 0);
          break;
        case 'red':
          $query->where('moto_tires.quantity', '<=', 0)->whereRaw("{$partnerStock} <= 0");
          break;
      }
    });

    $selectedTypes = $filters['selectedTypes'] ?? [];
    $typeConditions = $filters['typeConditions'] ?? [];
    if ($selectedTypes !== []) {
      $query->where(function ($query) use ($selectedTypes, $typeConditions) {
        $firstCondition = true;
        foreach ($selectedTypes as $type) {
          $typeKey = str_replace(' ', '', strtolower($type));
          if (!isset($typeConditions[$typeKey])) {
            continue;
          }
          $condition = $typeConditions[$typeKey];
          if ($firstCondition) {
            $query->where(function ($query) use ($condition) {
              call_user_func_array([$query, 'where'], $condition);
            });
            $firstCondition = false;
          } else {
            $query->orWhere(function ($query) use ($condition) {
              call_user_func_array([$query, 'where'], $condition);
            });
          }
        }
      });
    }

    $query->when($filters['show_selected'] ?? null, function ($query) use ($filters) {
      $query->whereIn('moto_tires.tire_id', $filters['selectedTires'] ?? []);
    })->when($filters['topTires'] ?? null, function ($query) {
      $query->where('moto_tires.top', 1);
    });
  }

  protected function motoApiCountCacheKey(array $filters): string
  {
    return 'moto_api_total_v1_' . Cache::get('moto_api_count_version', 1) . '_' . md5(json_encode($filters));
  }

  protected function buildMotoUrl(string $brandTitle, string $treadTitle, int $tireId): string
  {
    return '/motociklu-riepas/'
      . strtolower($brandTitle) . '/'
      . str_replace('/', '_', $treadTitle) . '/'
      . $tireId;
  }

  protected function prepareMotosForApiList($tires): void
  {
    Moto::preloadStockData($tires->pluck('tire_id')->all());

    foreach ($tires as $tire) {
      $this->hydrateMotoForApiResponse($tire);
    }
  }

  private function hydrateMotoForApiResponse(Moto $tire): void
  {
    $tire->includeStock = true;
    $tire->fullTitle = $tire->getTitleAttribute();
    $tire->fullSize = $tire->getFullSizeAttribute();
    $tire->fullName = $tire->fullTitle . ' ' . $tire->fullSize . ' ' . $tire->code . ' ' . $tire->li . $tire->si;
    $tire->getUrl = $this->buildMotoUrl($tire->api_brand_title, $tire->t_title, (int) $tire->tire_id);
    $tire->setAttribute('hydrated_cart_link', $tire->getUrl);
    $tire->lisiDesc = $tire->lisiDesc($tire->li, $tire->si);
    $tire->codeExplain = $tire->getCodeExplainAttribute();
    $dotAvailable = $tire->getDotAvailableAttribute();
    $tire->dotAvailable = $dotAvailable;
    $tire->stockAvailability = $tire->resolveStockAvailability($dotAvailable);
    $tire->stockCount = $tire->getStockCount();
  }

}

