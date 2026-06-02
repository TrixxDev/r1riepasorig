<?php

namespace App\Http\Controllers;

use App\Helper\Image;
use App\Helper\Tires;
use App\Models\Quadrbrand;
use App\Models\Quadrtread;
use Cart;
use Illuminate\Http\Request;
use App\Models\Quadr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use View;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Cookie;

class QuadTireController extends Controller
{

    public $brands;
    public $currBrand;
    public $d1;
    public $d2;
    public $d3;
    public $quadrTiresD1;
    public $quadrTiresD2;
    public $quadrTiresD3;
    public $model = 'Quadr';
    public $availability = [];
    public $filterCount = 0;

    public $cartQty = 2;

    public function __construct(Request $request)
    {
        $this->brands = $this->tires_getBrands();

        $this->quadrTiresD1 = Tires::getQuadrTiresD1();
        $this->quadrTiresD2 = Tires::getQuadrTiresD2();
        $this->quadrTiresD3 = Tires::getQuadrTiresD3();

        ($request->brand == 'Visi') ? $this->currBrand = 'Visi' : $this->currBrand = $request->brand;
        ($this->currBrand === NULL) ? $this->currBrand = 'Visi' : $this->currBrand = $request->brand;

        ($request->d1 == 'Visi') ? $this->d1 = 'Visi' : $this->d1 = $request->d1;
        ($request->d2 == 'Visi') ? $this->d2 = 'Visi' : $this->d2 = $request->d2;
        ($request->d3 == NULL) ? $this->d3 = 12 : $this->d3 = $request->d3;

        if ($request->d1 == NULL && $this->d1 == NULL) {
          $this->d1 = 25;
        }

        if ($request->d2 == NULL && $this->d2 == NULL) {
          $this->d2 = 8;
        }

        if ($request->d3 == NULL && $this->d3 == NULL) {
          $this->d3 = 12;
        }

        View::share('brands', $this->brands);
        View::share('quadrTiresD1', $this->quadrTiresD1);
        View::share('quadrTiresD2', $this->quadrTiresD2);
        View::share('quadrTiresD3', $this->quadrTiresD3);
        View::share('currBrand', $this->currBrand);
        View::share('d1', $this->d1);
        View::share('d2', $this->d2);
        View::share('d3', $this->d3);
        View::share('filterCount', $this->filterCount);
        View::share('availability', $this->availability);
        View::share('cartQty', $this->cartQty);

    }

    public function index()
    {

        $tires = Quadr::with('tread')->leftJoin('quadr_treads', 'quadr_tires.make_id', '=', 'quadr_treads.tread_id')
                          ->when($this->d1, function($query) {
                            $query->where('d1', $this->d1);
                          })->when($this->d2, function($query) {
                            $query->where('d2', $this->d2);
                          })->when($this->d3, function($query) {
                            $query->where('d3', $this->d3);
                          })->groupBy('quadr_tires.article')
                          ->where('quadr_tires.visible_users', '<>', 0)
                          ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
                          ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
                          ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
                          ->orderBy('price2', 'DESC')->paginate(80);

//        dd(DB::getQueryLog());


          return view('tires.quadr.index',
              compact('tires')
          );
    }

    public function tires_tread(Request $request, $brand, $tread, $tire)
    {

        $selectedTires = [];
        if ($request->input('selected')) {
          $selectedTires = explode(',', $request->input('selected'));
        }

	//dd($brand);

        //$brand = Quadrbrand::where('b_title', $brand)->first();
	$brand = Quadrbrand::where('slug', $brand)->first();

        $tread = str_replace('_', '/', $tread);
        $tread = str_replace('$1', '&', $tread);
	//dd($tread, $brand);
        $tread = Quadrtread::where('t_title', $tread)->where('brand_id', $brand->brand_id)->first();

        $tires = Quadr::selectRaw('quadr_tires.*, quadr_treads.*, quadr_brands.*')
            ->join('quadr_treads', 'quadr_tires.make_id', '=', 'quadr_treads.tread_id')
            ->join('quadr_brands', 'quadr_treads.brand_id', '=', 'quadr_brands.brand_id')
            ->where('quadr_tires.visible_users', '<>', 0)
            ->where('quadr_tires.make_id', $tread->tread_id)
            ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
            ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
            ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
            ->get();

        $currTire = Quadr::selectRaw('quadr_tires.*, quadr_treads.*, quadr_brands.*')
            ->join('quadr_treads', 'quadr_tires.make_id', '=', 'quadr_treads.tread_id')
            ->join('quadr_brands', 'quadr_treads.brand_id', '=', 'quadr_brands.brand_id')
            ->where('quadr_tires.tire_id', $tire)
            ->first();

        $currBrand = Quadrbrand::where('brand_id', $brand->brand_id)->first();

        $currTire->includeStock = true;

        return view('tires.quadr.quadrtread',
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
        $show_selected = $request->show_selected;

        $fastsearch = $request->fastsearch;

        if ($fastsearch) {
          $splited = $this->splitInput($fastsearch);
          $this->d1 = $d1 = $splited['d1'];
          $this->d2 = $d2 = $splited['d2'];
          $this->d3 = $d3 = $splited['d3'];
        }

        $tires = Quadr::selectRaw('quadr_tires.*, quadr_tires.quantity as tire_quantity, quadr_treads.*, (SELECT SUM(quantity) FROM quadr_stock WHERE quadr_stock.tire_id = quadr_tires.tire_id) as stock_quantity')
          ->join('quadr_treads', 'quadr_tires.make_id', '=', 'quadr_treads.tread_id')
          ->join('quadr_brands', 'quadr_treads.brand_id', '=', 'quadr_brands.brand_id')
          ->when($currBrand, function($query) use ($currBrand) {
            $query->where('quadr_brands.b_title', $currBrand);
          })->when($d1, function ($query) use ($d1) {
            $query->where('d1', $d1);
          })->when($d2, function ($query) use ($d2) {
            $query->where('d2', $d2);
          })->when($d3, function ($query) use ($d3) {
            $query->where('d3', $d3);
          })->when($availability, function ($query) use ($availability) {
            switch ($availability) {
              case 'green':
                {
                  $query->where('quadr_tires.quantity', '>', 0);
                  break;
                }
              case 'green+yellow':
                {
                  $query->where(function ($query) {
                    $query->where('quadr_tires.quantity', '>', 0)
                      ->orWhere(function ($query) {
                        $query->whereRaw('quadr_tires.tire_id IN (SELECT tire_id FROM quadr_stock WHERE quantity > 0)')
                              ->where('quadr_tires.quantity', '=', 0);
                      });
                  });
                  break;
                }
              case 'green+red':
                {
                  $query->where(function ($query) {
                    $query->where('quadr_tires.quantity', '>', 0); // Green dot filter
                    $query->orWhere(function ($query) {
                      $query->where('quadr_tires.quantity', '=', 0); // Red dot filter
                      $query->whereRaw('quadr_tires.tire_id NOT IN (SELECT tire_id FROM quadr_stock WHERE quantity > 0)');
                    });
                  });
                  break;
                }
              case 'yellow':
                {
                  $query->where('quadr_tires.quantity', '<=', 0)->having('stock_quantity', '>', 0);
                  break;
                }
              case 'yellow+red':
                {
                  $query->where('quadr_tires.quantity', '<=', 0)->having('stock_quantity', '>=', 0);
                  break;
                }
              case 'red':
                {
                  $query->where('quadr_tires.quantity', '<=', 0)->having('stock_quantity', '<=', 0);
                  break;
                }
            }
          })->when($show_selected, function ($query) use ($selectedTires) {
            $query->whereIn('tire_id', $selectedTires);
          })->where('quadr_tires.visible_users', '<>', 0)
          ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
          ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
          ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
          ->orderBy('price2', 'DESC')
          ->groupBy('quadr_tires.article');

        $totalItems = count($tires->get());
        $totalPages = ceil($totalItems / $perPage);

        $tires = $tires->skip($offset)
          ->take($perPage)
          ->get();

        $tiresGrouped = $tires->groupBy(function($tire) {
          return $tire->getFullSizeAttribute();
        });
        $index = 0;
        if ($request->table_type === 'list') {
          foreach ($tiresGrouped as $fullSize => $group) {
            $group = $group->sortBy('price2', SORT_REGULAR, true);
            $html .= '<table id="tires-table" class="table table-striped quadr-sorter tires-table table-hover tablesorter">';
            $html .= '<thead class="tires-thead sticky-table">
                        <tr>
                          <th scope="col"></th>
                          <th scope="col" class="table-tire-name-cell">Brends / modelis</th>
                          <th scope="col" class="hidden-sm-down text-center">Kods</th>
                          <th id="store-price-button" scope="col" class="text-center">Veikala cena</th>
                          <th id="store-sale-button" scope="col" class="text-center">Akcijas cena</th>
                          <th scope="col" class="hidden-sm-down text-center">Piezīmes</th>
                          <th scope="col"></th>
                          <th scope="col"><div class="tire-table-icon icon-question" title="Pieejamība" data-toggle="tooltip"></div></th>
                        </tr>
                        </thead>';
            $html .= '<tbody id="tires-table-body">';
            if ($index === 0) {
              $html .= '<span class="text-uppercase flipped-title tire-brand-name placeholder" style="color: black;">Kvadraciklu riepas</span>';
            }
            $index++;
            $html .= '<h4 class="tire-brand-name">' . $fullSize . '</h4>';
            foreach ($group as $tire) {
              $tire->includeStock = true;
              $tire->fullName = $tire->getFullNameAttribute();
              $tire->fullSize = $tire->getFullSizeAttribute();
              $current_url = 'kvadraciklu-riepa';
              //$tire->getUrl = $tire->link;
	      $tire->getUrl = route($current_url, [\Str::slug(Tires::getQuadrTireBrand($tire->brand_id)->b_title), strtolower(str_replace('/', '_', $tire->t_title)), $tire->tire_id]);
              $tire->fullTitle = $tire->getTitleAttribute();
              $tire->codeExplain = $tire->getCodeExplainAttribute();
              $tire->dotAvailable = $tire->getDotAvailableAttribute();
              $tire->stockAvailability = $tire->getStockAvailabilityAttribute();
              $tire->stockCount = $tire->getStockCount();

              $isSelected = in_array((string) $tire->tire_id, $selectedTires, true);
              $html .= '<tr class="tire-table-row' . ($isSelected ? ' selected' : '') . '" role="row">';
              $html .= '<th scope="row" class="tire-table-checkbox"><input type="checkbox" value="' . $tire->tire_id . '" name="product_ids[]" class="tire-table-checkbox" title=""'
                  . ' data-availability="' . htmlspecialchars($tire->dotAvailable) . '"'
                  . ($isSelected ? ' checked' : '')
                  . '></th>';
              $html .= '<td class="table-tire-name-cell"><a class="tire-table-link tippy image" data-tippy-content="<div><img data-src=\'https://r1riepas.lv/storage/quadr/tread/' . $tire->tread_id . '-o.jpg\'></div>" href="' . $tire->getUrl . '" data-content="' . $tire->fullName . '" data-article="' . $tire->article . '" data-quantity="4"><div class="table-link-title">' . $tire->fullTitle . '</div></a></td>';

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
            }
            $html .= '</tbody>';
            $html .= '</table>';
          }
        } else if ($request->table_type === 'grid') {
          foreach ($tiresGrouped as $fullSize => $group) {
            $group = $group->sortBy('price2', SORT_REGULAR, true);
            $html .= '</div><h4 class="tire-brand-name grid-t" style="margin-left: 5px;">' . $fullSize;
            if ($index == 0) {
              $html .= ' <span class="tire-type-title">Kvadraciklu riepas</span>';
            }
            $index++;
            $html .= '<span style="margin: 0 auto;"></span>';
            $html .= '<button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">Filtrs ()</button></h4><div class="row grid-ex pr-1 mobile-tire-container" style="padding-left: 5px;">';
            foreach ($group as $tire) {
              $tire->fullSize = $tire->getFullSizeAttribute();
              $current_url = 'kvadraciklu-riepa';
              $tire->getUrl = $tire->link;

              $brand = $tire->fullSize;
              $tire->includeStock = true;
              $isSelected = in_array((string) $tire->tire_id, $selectedTires, true);
              $html .= '<a href="' . $tire->getUrl . '" class="grid-view-link" data-article="' . $tire->article . '" data-url="' . $tire->getUrl . '">';
              $html .= '<div class="tire-image-card sort-order' . ($isSelected ? ' selected' : '') . '">';
              $html .= '<div class="text-center image-grid-overflow">';
              $html .= Image::showGrid('quadr', $tire->make_id);
              $html .= '</div>';

              $html .= '<div class="tire-list-caption">';

              $html .= '<div class="card-title-text"><span class="tippy lisi-tooltip" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . $tire->title . '</span></div>">' . $tire->title . '</span></div>';

              $html .= '<div class="tire-tread">';
              $html .= '<b>' . $tire->fullSize . ' </b>';
              $html .= '<span class="tire-image-code">' . $tire->code . '</span>';
              $html .= '</div>';
              $html .= '<div style="display: flex;">';
              $html .= '<input type="checkbox" name="product_ids[]" value="' . $tire->tire_id . '" class="tire-table-checkbox" style="margin-right: 5px;"'
                  . ($isSelected ? ' checked' : '')
                  . ' data-availability="' . htmlspecialchars($tire->dotAvailable) . '"'
                  . '>';
              $html .= '<div class="rim-price-old" style="align-self: center;">€' . $tire->price1 . '</div>';
              $html .= '<div class="rim-price-red" style="align-self: center;">€' . $tire->price2 . '</div>';
              $html .= '<span style="margin-left: auto;" data-toggle="tooltip" title="<span style=\'color: black\'>Pievienot grozam</span>">';
              if (Auth::check()) {
                if (\Illuminate\Support\Facades\Auth::user()->hasRole('administrators')) {
                  $html .= '<span style="margin-left: auto;" data-toggle="tooltip" title="<span style=\'color: black\'>Pievienot grozam</span>"><button class="grid-buy-btn cart-shopping-button" data-toggle="modal" data-info="' . $tire->tire_id . '" onclick="event.preventDefault()" data-target="#"><i class="material-icons">add_shopping_cart</i></button></span>';
                } else {
                  $html .= '<span style="margin-left: auto;" data-toggle="tooltip" title="<span style=\'color: black\'>Pievienot grozam</span>"><button class="grid-buy-btn cart-shopping-button" data-toggle="modal" data-info="' . $tire->tire_id .'" onclick="event.preventDefault()" data-target="#blockcart-modal"><i class="material-icons">add_shopping_cart</i></button></span>';
                }
              } else {
                $html .= '<span style="margin-left: auto;" data-toggle="tooltip" title="<span style=\'color: black\'>Pievienot grozam</span>"><button class="grid-buy-btn cart-shopping-button" data-toggle="modal" data-info="' . $tire->tire_id .'" onclick="event.preventDefault()" data-target="#blockcart-modal"><i class="material-icons">add_shopping_cart</i></button></span>';
              }
              $html .= '</button>';
              $html .= '</span>';

              $html .= '<span class="tippy lisi-tooltip grid-dot ' . $tire->dotAvailable . $tire->stockCount . '" data-tippy-content=\'<div style="padding: 5px;"><span style="color: black; font-size: 15px;">' . $tire->stockAvailability . '</span></div>\'></span>';
              $html .= '<span class="sort-order" style="display: none;">' . $tire->dotAvailable . '</span>';
              $html .= '</span>';
              $html .= '</div>';
              $html .= '</div>';
              $html .= '</div>';
              $html .= '</a>';
            }
            $html .= '</div>';
          }
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
      // Fetch the tire from the database
      $tire = Quadr::with('tread')->selectRaw('quadr_tires.*, quadr_treads.*')
        ->rightJoin('quadr_treads', 'quadr_tires.make_id', '=', 'quadr_treads.tread_id')
        ->where('quadr_tires.tire_id', $request->tire_id)
        ->first();

      if (!$tire) {
        return response()->json(['error' => 'Tire not found'], 404);
      }

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
          'type' => 'Kvadraciklu riepa',
          'url' => $request->tire_url,
          'image' => Image::image('quadr', $tire->make_id),
          'price' => $tire->price2,
          'quantity' => $quantity,
          'availability' => $tire->dotAvailable,
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

    if (preg_match('/^(\d{2})(\d)(\d{2})$/', $input, $matches)) {
      $d1 = $matches[1]; // 25
      $d2 = $matches[2]; // 8
      $d3 = $matches[3]; // 12
    } else if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $input, $matches)) {
      $d1 = $matches[1]; // 25
      $d2 = $matches[2]; // 10
      $d3 = $matches[3]; // 12
    } else if (preg_match('/^(\d{2})((\d).(\d))(\d{2})$/', $input, $matches)) {
      $d1 = $matches[1]; // 24
      $d2 = $matches[2]; // 9.5
      $d3 = $matches[5]; // 10
    } else if (preg_match('/^(\d{2})((\d{2}).(\d))(\d{2})$/', $input, $matches)) {
      $d1 = $matches[1]; // 23
      $d2 = $matches[2]; // 10.5
      $d3 = $matches[5]; // 12
    } else if (preg_match('/^(\d{2})((\d).(\d))(\d)$/', $input, $matches)) {
      $d1 = $matches[1]; // 16
      $d2 = $matches[2]; // 6.5
      $d3 = $matches[5]; // 8
    } else if (preg_match('/^(\d{2})((\d{2}).(\d))(\d)$/', $input, $matches)) {
      $d1 = $matches[1]; // 25
      $d2 = $matches[2]; // 12.5
      $d3 = $matches[5]; // 1
    } else {
      $d1 = 1;
      $d2 = 1;
      $d3 = 1;
    }

    return compact('d1', 'd2', 'd3');
  }

  public function tires_find(Request $request) {


    return view('tires.quadr.index');
  }

  public function get_sizes()
  {
    try {
      $tireSizes = Quadr::select(DB::raw('CONCAT(D1, D2, D3) as tire_size'))
        ->where('quadr_tires.visible_users', '<>', 0)
        ->groupBy('quadr_tires.article')
        ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
        ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
        ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
        ->distinct()
        ->get();

      return response()->json($tireSizes, 200);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function tires_getBrands()
  {
    $brands = [];

    foreach (Quadrbrand::all() as $brand) {
      $treadIds = Quadrtread::where('brand_id', $brand->brand_id)->pluck('tread_id')->toArray();

      $tire = Quadr::whereIn('make_id', $treadIds)
        ->where('visible_users', '<>', 0)
        ->first();

      if ($tire) {
        $brands[] = $brand->brand_id;
      }
    }

    $uniqueBrands = array_unique($brands);

    $brandList = Quadrbrand::whereIn('brand_id', $uniqueBrands)
      ->pluck('b_title', 'brand_id')
      ->map(fn ($title) => ucwords(strtolower($title)))
      ->sort(SORT_NATURAL | SORT_FLAG_CASE)
      ->toArray();

    return $brandList;
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


}

