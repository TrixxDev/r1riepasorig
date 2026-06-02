<?php

namespace App\Http\Controllers;

use App\Helper\Image;
use App\Helper\Tires;
use App\Models\Autobrand;
use App\Models\Autotire;
use App\Models\Autotread;
use App\Models\Code;
use Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Route;
use View;


class AutoTireController extends Controller
{


    public $brands;
    public $season;
    public $currBrand;
    public $d1;
    public $d2;
    public $d3;
    public $d1b;
    public $d2b;
    public $d3b;
    public $dualSizeMode = false;
    public $autoTiresD1;
    public $autoTiresD2;
    public $autoTiresD3;
    public $model = 'Autotire';
    public $tiresSize;
    public $lastYear;
    public $type;
    public $code;
    public $fuel;
    public $wet;
    public $noise;
    public $availability;
    public $code_array = [];
    public $filterCount = 0;

    public $cartQty = 4;

  public function __construct(Request $request)
  {
    if ($request->is('api/tires/auto/*') || $this->shouldSkipHeavyViewShare($request)) {
      $this->resolveSeasonFromRequest($request);
      $this->shareCodeArray();
      View::share('cartQty', $this->cartQty);
      return;
    }

    // || strpos(str_replace(url('/'), '', \URL::previous()), 'vasaras-riepas') !== false

    $this->resolveSeasonFromRequest($request, true);

    $season = $this->season;
    $this->brands = Cache::remember('autotire_brands_s' . $season, 600, function () {
      return $this->tires_getBrands();
    });

    $this->autoTiresD1 = Cache::remember('autotire_d1_s' . $season, 600, function () use ($season) {
      return Tires::getAutoTiresSize('d1', $season);
    });
    $this->autoTiresD2 = Cache::remember('autotire_d2_s' . $season, 600, function () use ($season) {
      return Tires::getAutoTiresSize('d2', $season);
    });
    $this->autoTiresD3 = Cache::remember('autotire_d3_s' . $season, 600, function () use ($season) {
      return Tires::getAutoTiresSize('d3', $season);
    });

    $this->currBrand = ($request->brand == 'Visi') ? 'Visi' : $request->brand;
    $this->currBrand = ($this->currBrand === NULL) ? 'Visi' : $request->brand;

    $this->d1 = ($request->d1 == 'Visi') ? 'Visi' : $request->d1;
    $this->d2 = ($request->d2 == 'Visi') ? 'Visi' : $request->d2;
    $this->d3 = ($request->d3 == NULL) ? '16' : $request->d3;

    $this->types = ($request->types) ? $request->types : [];
    $this->code = ($request->code) ? $request->code : [];
    $this->fuel = ($request->fuel) ? $request->fuel : [];
    $this->wet = ($request->wet) ? $request->wet : [];

    if ($request->d1 == NULL && $this->d1 == NULL) {
      $this->d1 = 205;
    }

    if ($request->d2 == NULL && $this->d2 == NULL) {
      $this->d2 = 55;
    }

    $this->dualSizeMode = $this->isAutoTireCatalogRequest($request);
    if ($this->dualSizeMode) {
      $this->d1b = ($request->d1b === 'Visi' || $request->d1b === null) ? 'Visi' : $request->d1b;
      $this->d2b = ($request->d2b === 'Visi' || $request->d2b === null) ? 'Visi' : $request->d2b;
      $this->d3b = ($request->d3b === 'Visi' || $request->d3b === null) ? 'Visi' : $request->d3b;
    }

    $this->shareCodeArray();

//        if ($request->d3 == NULL && $this->d3 == NULL) {
//            $this->d3 = 16;
//        }

        View::share('brands', $this->brands);
        View::share('autoTiresD1', $this->autoTiresD1);
        View::share('autoTiresD2', $this->autoTiresD2);
        View::share('autoTiresD3', $this->autoTiresD3);
        View::share('currBrand', $this->currBrand);
        View::share('d1', $this->d1);
        View::share('d2', $this->d2);
        View::share('d3', $this->d3);
        View::share('dualSizeMode', $this->dualSizeMode);
        View::share('d1b', $this->d1b ?? null);
        View::share('d2b', $this->d2b ?? null);
        View::share('d3b', $this->d3b ?? null);
        View::share('types', $this->types);
        View::share('type', $this->type);
        View::share('code', $this->code);
        View::share('fuel', $this->fuel);
        View::share('wet', $this->wet);
        View::share('noise', $this->noise);
	      View::share('codes', Cache::remember('autotire_filter_codes_v1', 3600, function () {
          return $this->tires_getCodes();
        }));
	      View::share('filterCount', $this->filterCount);
	      View::share('availability', explode(' ', $this->availability));
        View::share('cartQty', $this->cartQty);
  }

  public function tires() {

    $tires = Autotire::leftJoin('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
      ->when($this->dualSizeMode && $this->secondaryDualSizeSelected(), function ($query) {
        $query->where(function ($query) {
          $query->where(function ($query) {
            $query->where('d1', $this->d1)->where('d2', $this->d2)->where('d3', $this->d3);
          })->orWhere(function ($query) {
            $query->where('d1', $this->d1b)->where('d2', $this->d2b)->where('d3', $this->d3b);
          });
        });
      }, function ($query) {
        $query->when($this->d1, function ($query) {
          $query->where('d1', $this->d1);
        })->when($this->d2, function ($query) {
          $query->where('d2', $this->d2);
        })->when($this->d3, function ($query) {
          $query->where('d3', $this->d3);
        });
      })->where('auto_treads.season', $this->season)
      ->where('auto_tires.visible_users', '<>', 0)
      ->orderBy('d3', 'ASC')
      ->orderBy('d1', 'ASC')
      ->orderBy('d2', 'ASC')
      ->orderBy('price2', 'DESC')
      ->groupBy('article')
      ->simplePaginate();

    $code_array = $this->code_array;

    return view('tires.auto.tires', compact('tires', 'code_array'));
  }

  public function generateCombinations(array $array) {
    foreach (array_pop($array) as $value) {
      if (count($array)) {
        foreach ($this->generateCombinations($array) as $combination) {
          yield array_merge([$value], $combination);
        };
      } else {
        yield [$value];
      }
    }
  }

  public function checkArrays($arrays) {

    foreach ($arrays as $array) {
      if (is_array($array)) {
        return true;
      }
    }

    return false;
  }

  public function api_tires(Request $request, $season) {

    try {
      if (! isset($this->code)) {
        $this->code = ($request->code) ? $request->code : null;
      }

      $html = '';

      $page = ($request->page) ? (int) $request->page : 1; // Get the current page from the request, default to 1
      $perPage = 80; // Number of items per page

      $offset = ($page - 1) * $perPage;

      $d1 = ($request->d1 == 'Visi') ? '' : $request->d1;
      $d2 = ($request->d2 == 'Visi') ? '' : $request->d2;
      $d3 = ($request->d3 == 'Visi') ? '' : $request->d3;
      $d1b = ($request->d1b == 'Visi') ? '' : $request->d1b;
      $d2b = ($request->d2b == 'Visi') ? '' : $request->d2b;
      $d3b = ($request->d3b == 'Visi') ? '' : $request->d3b;
      $dualSize = $request->boolean('dual') || ($d1b !== '' && $d1b !== null) || ($d2b !== '' && $d2b !== null) || ($d3b !== '' && $d3b !== null);

      $this->availability = $availability = '';
      if (isset($request->availability)) {
        $availability = explode(' ', $request->availability);
        $this->availability = $availability = implode('+', $availability);
      }

      $currBrand = ($request->brand == 'Raإ¾otؤپjs') ? '' : $request->brand;

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

      $codeCombinations = [];
      $findLike = false;

      if (!is_null($request->code)) {
        $this->code = explode(' ', $request->code);
        $categorizedCodes = [];
        foreach ($this->code as $code) {
          if ($code === 'SOUND') {
            $categorizedCodes['SOUND'] = ['SOUND', 'ACOUSTIC', 'NCS', 'SCT'];
          } elseif ($code === 'XL') {
            $categorizedCodes['XL'] = ['XL', 'HL'];
          } else {
            $categorizedCodes[$code] = $code; // Other codes without subcategories
          }
        }

        $this->code = $categorizedCodes;

        $permutations = [];
        $hasArrays = false; // Flag to check if we need permutations

        $findLike = count($categorizedCodes) === 1;

        foreach ($categorizedCodes as $key => $value) {
          if (is_array($value)) { // If a code has an array of subcategories
            $hasArrays = true;
            $temp = [];

            // Initial permutation if other codes present
            if ($permutations) {
              foreach ($permutations as $perm) {
                foreach ($value as $subcategory) {
                  $temp[] = $perm . '%' . $subcategory;
                }
              }
            } else {
              // First set of permutations
              $temp = $value;
            }

            $permutations = $temp;

          } else {
            // Single codes without subcategories
            if ($permutations) {
              foreach($permutations as &$perm) {
                $perm .= '%' . $value;
              }
            } else {
              $permutations[] = $value;
            }
          }
        }


        if ($hasArrays) {
          $codeCombinations = $permutations;
        } else {
          $codeCombinations = [implode('%', $categorizedCodes)];
        }
        //        $selectedCodes = [];
//        foreach ($this->code as $codes) {
//          $selectedCodes[$codes] = $codes;
//        }
//        if (isset($selectedCodes['SOUND'])) {
//          $selectedCodes['SOUND'][] = 'SOUND';
//          $selectedCodes['SOUND'][] = 'ACOUSTIC';
//          $selectedCodes['SOUND'][] = 'NCS';
//          $selectedCodes['SOUND'][] = 'SCT';
//        }
//        if (isset($selectedCodes['XL'])) $selectedCodes['XL'][] = 'HL';
      }

      $this->type = $selectedTypes = explode(' ', $request->type);

      $typeConditions = [
        1 => ['auto_tires.type', '=', 1],
        2 => ['auto_tires.type', '=', 2],
        3 => ['auto_tires.type', '=', 3],
        4 => ['auto_tires.type', '=', 4],
      ];

      $this->fuel = $selectedFuel = explode(' ', $request->fuel);
      $this->wet = $selectedWet = explode(' ', $request->wet);
      $this->noise = $selectedNoise = explode(' ', $request->noise);

      $fuelConditions = [
        'A' => ['auto_tires.eco', '=', 'A'],
        'B' => ['auto_tires.eco', '=', 'B'],
        'C' => ['auto_tires.eco', '=', 'C'],
        'D' => ['auto_tires.eco', '=', 'D'],
        'E' => ['auto_tires.eco', '=', 'E'],
        'F' => ['auto_tires.eco', '=', 'F'],
        'G' => ['auto_tires.eco', '=', 'G'],
      ];

      $wetConditions = [
        'A' => ['auto_tires.wet', '=', 'A'],
        'B' => ['auto_tires.wet', '=', 'B'],
        'C' => ['auto_tires.wet', '=', 'C'],
        'D' => ['auto_tires.wet', '=', 'D'],
        'E' => ['auto_tires.wet', '=', 'E'],
        'F' => ['auto_tires.wet', '=', 'F'],
        'G' => ['auto_tires.wet', '=', 'G'],
      ];

      $noiseConditions = [
        'A' => ['auto_tires.noise', 'LIKE', '%A%'],
        'B' => ['auto_tires.noise', 'LIKE', '%B%'],
        'C' => ['auto_tires.noise', 'LIKE', '%C%'],
      ];

      $filterContext = [
        'currBrand' => $currBrand,
        'd1' => $d1,
        'd2' => $d2,
        'd3' => $d3,
        'd1b' => $d1b,
        'd2b' => $d2b,
        'd3b' => $d3b,
        'dualSize' => $dualSize,
        'availability' => $availability,
        'codeCombinations' => $codeCombinations,
        'selectedTypes' => $selectedTypes,
        'typeConditions' => $typeConditions,
        'selectedFuel' => $selectedFuel,
        'fuelConditions' => $fuelConditions,
        'selectedWet' => $selectedWet,
        'wetConditions' => $wetConditions,
        'selectedNoise' => $selectedNoise,
        'noiseConditions' => $noiseConditions,
        'selectedTires' => $selectedTires,
        'show_selected' => $show_selected,
        'topTires' => $topTires,
        'code' => $this->code,
        'type' => $this->type,
        'fuel' => $this->fuel,
        'wet' => $this->wet,
        'noise' => $this->noise,
      ];

      if ($this->dualSizePairFiltersComplete($filterContext)) {
        $filterContext['pairCount'] = true;
        $html = $this->buildDualSizeApiResponseHtml(
          $request,
          (int) $season,
          $filterContext,
          $selectedTires,
          $page,
          $offset
        );

        return response()->json($html, 200);
      }

      $countQuery = $this->buildApiTiresBaseQuery((int) $season);
      $this->applyApiTireCatalogFilters($countQuery, $filterContext);

      $countCacheKey = $this->tireApiCountCacheKey((int) $season, $filterContext);
      $totalItems = Cache::remember($countCacheKey, 120, function () use ($countQuery) {
        return (int) $countQuery->count('auto_tires.tire_id');
      });
      $totalPages = (int) ceil($totalItems / $perPage);

      $listQuery = $this->buildApiTiresBaseQuery((int) $season)
        ->selectRaw('auto_tires.*, auto_tires.quantity as tire_quantity, auto_treads.*, ' . $this->partnerStockColumnSql() . ' as stock_quantity, auto_brands.title as api_brand_title, auto_brands.slug as api_brand_slug');
      $this->applyApiTireCatalogFilters($listQuery, $filterContext);
      $listQuery->orderBy('d3', 'ASC')
        ->orderBy('d1', 'ASC')
        ->orderBy('d2', 'ASC')
        ->orderBy('price2', 'DESC');

      $tires = $listQuery->skip($offset)->take($perPage)->get();
      $this->prepareAutotiresForApiList($tires, (int) $season);

      if ($season == 1) {
        $type = '';
        $season_title = 'Vasaras riepas';
      } else {
        $type = '<th scope="col" class="hidden-sm-down text-center table-tire-param-cell">Tips</th>';
        $season_title = 'Ziemas riepas';
      }

      $fullSize = '';
      $loopIndex = 0;

      if ($request->table_type === 'list') {
        if ($tires->count() > 0) {

          foreach ($tires as $index => $tire) {

            if ($index === 0) {
              $html .= '<button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">
                                      Filtrs
                                    </button>';
              $html .= '<span class="text-uppercase flipped-title tire-brand-name" style="color: black">' . $season_title .  '</span>';
            }
            $index++;
            if ($fullSize !== $tire->fullSize) {
              $loopIndex = 0;
              $html .= '<table id="tires-table" class="table table-striped summer-sorter tires-table table-hover tablesorter">';
              $html .= $this->renderAutoTireTableColgroup($type !== '');
              $html .= '<thead class="tires-thead sticky-table">
                        <tr>
                          <th scope="col"></th>
                          <th scope="col" class="table-tire-name-cell">Brends / modelis</th>
                          <th scope="col" class="hidden-sm-down text-center table-tire-param-cell">LI/SI</th>
                          ' . $type . '
                          <th scope="col" class="hidden-sm-down text-center table-tire-code-cell">Kods</th>

                          <th scope="col" class="hidden-sm-down table-tire-param-cell">
                            <div class="tire-table-icon icon-tire-fuel" title="Degvielas ekonomija"></div>
                          </th>

                          <th scope="col" class="hidden-sm-down table-tire-param-cell">
                            <div class="tire-table-icon icon-tire-rain" title="Slapjإ، segums"></div>
                          </th>

                          <th scope="col" class="hidden-sm-down table-tire-param-cell">
                            <div class="tire-table-icon icon-tire-sound" title="Troksnis"></div>
                          </th>

                          <th id="store-price-button" scope="col" class="text-center catalog-price-th">
                            <span class="hidden-sm-down">Veikala cena</span><span class="hidden-md-up">Veik.</span>
                          </th>

                          <th id="store-sale-button" scope="col" class="text-center catalog-price-th"><span class="hidden-sm-down">Akcijas cena</span><span class="hidden-md-up">Akc.</span></th>
                          <th scope="col" class="hidden-sm-down text-center">Piezؤ«mes</th>
                          <th scope="col"></th>
                          <th scope="col">
                            <div class="tire-table-icon icon-question" title="Pieejamؤ«ba" data-toggle="tooltip"></div>
                          </th>

                        </tr>
                        </thead>';
              $html .= '<tbody id="tires-table-body">';
              $html .= '<h4 class="tire-brand-name">' . $tire->fullSize . '</h4>';
            }
            $isSelected = in_array((string) $tire->tire_id, $selectedTires, true);
            $html .= '<tr class="tire-table-row' . ($isSelected ? ' selected' : '') . '" role="row">';
            $html .= '<th scope="row" class="tire-table-checkbox"><input type="checkbox" value="' . $tire->tire_id . '" name="product_ids[]" class="tire-table-checkbox" title=""' . ($isSelected ? ' checked' : '') . '></th>';
            $html .= '<td class="table-tire-name-cell"><a class="tire-table-link tippy image" data-tippy-content="<div><img data-src=\'https://r1riepas.lv/storage/auto/tread/' . $tire->tread_id . '-o.jpg\'></div>" href="' . $tire->getUrl . '" data-content="' . $tire->fullName . '" data-article="' . $tire->article . '" data-quantity="4"><div class="table-link-title">' . $tire->fullTitle . '</div></a></td>';
            $html .= '<td class="hidden-sm-down text-center table-tire-param-cell"><span class="tippy lisi-tooltip" data-tippy-content="<div style=\'padding: 5px; text-align: left;\'><span style=\'color: black; font-size: 15px;\'>' . $tire->lisiDesc . '</span></div>">' . $tire->li . $tire->si . '</span></td>';
            if ($tire->season == 2) {
              $html .= '<td scope="col" class="hidden-sm-down text-center table-tire-param-cell">';
              if ($tire->type == 1) $html .= '<span class="tippy lisi-tooltip type-explain" data-type="1" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . \App\Helper\Tires::codeExplain('ms tips') . '</span></div>"><img src="/images/ms.png" alt="ms"></span>';
              if ($tire->type == 2) $html .= '<span class="tippy lisi-tooltip type-explain" data-type="2" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . \App\Helper\Tires::codeExplain('radإ¾ojamu tips') . '</span></div>"><img src="/images/radzeb.png" alt="ms"></span>';
              if ($tire->type == 3) $html .= '<span class="tippy lisi-tooltip type-explain" data-type="3" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . \App\Helper\Tires::codeExplain('ar radzؤ“m tips') . '</span></div>"><img src="/images/radzea.png" alt="ms"></span>';
              if ($tire->type == 4) $html .= '<span class="tippy lisi-tooltip type-explain" data-type="4" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . \App\Helper\Tires::codeExplain('ziemas tips') . '</span></div>"><img src="/images/parsla.png" alt="ms"></span>';
              $html .= '</td>';
            }
            $html .= '<td class="hidden-sm-down text-center table-tire-code-cell"><span class="tippy lisi-tooltip code-explain" data-tippy-content="<div style=\'padding: 5px; text-align: left;\'><span style=\'color: black; font-size: 15px;\'>' . $tire->codeExplain . '</span></div>">' . $tire->code . '</span></td>';
            $html .= '<td class="hidden-sm-down text-center table-tire-param-cell"><span class="fuel-explain">' . $tire->eco . '</span></td>';
            $html .= '<td class="hidden-sm-down text-center table-tire-param-cell"><span class="wet-explain">' . $tire->wet . '</span></td>';
            $html .= '<td class="hidden-sm-down text-center table-tire-param-cell"><span class="noise-explain">' . $tire->noise . '</span></td>';
            $html .= '<td id="store-price" class="text-center store-price">€ ' . $tire->price1 . '</td>';
            $html .= '<td id="sale-price" class="text-center tire-price-red sale-price">€ ' . $tire->price2 . '</td>';
            if ($tire->comment == 'Izpؤپrdoإ،ana!' || $tire->priceoffer == 1) {
              $html .= '<td class="hidden-sm-down text-center sellout">' . $tire->comment . '</td>';
            } else {
              $html .= '<td class="hidden-sm-down text-center">' . $tire->comment . '</td>';
            }
            $html .= '<td class="shopping-cart-col"><div class="clearfix atc_div text-right">';
            if (Auth::check()) {
              if (Auth::user()->hasRole('administrators')) {
                $html .= '<button class="cart-shopping-button" data-toggle="modal" data-target="#" data-info="' . $tire->tire_id . '" data-url="' . $tire->hydrated_cart_link . '"><i class="material-icons">add_shopping_cart</i></button>';
              } else {
                $html .= '<button class="cart-shopping-button" data-toggle="modal" data-target="#blockcart-modal" data-info="' . $tire->tire_id . '" data-url="' . $tire->hydrated_cart_link . '"><i class="material-icons">add_shopping_cart</i></button>';
              }
            } else {
              $html .= '<button class="cart-shopping-button" data-toggle="modal" data-target="#blockcart-modal" data-info="' . $tire->tire_id . '" data-url="' . $tire->hydrated_cart_link . '"><i class="material-icons">add_shopping_cart</i></button>';
            }
            $html .= '</div></td>';
            $html .= '<td class="dot-availability text-center"><span class="tippy lisi-tooltip dot ' . $tire->dotAvailable . '" data-color="' . $tire->dotAvailable . '" data-tippy-content=\'<div style="padding: 5px; text-align: left;"><span style="color: black; font-size: 15px; line-height: 28px;">' . $tire->stockAvailability . '</span></div>\'></span></td>';
            $html .= '</tr>';
            $fullSize = $tire->fullSize;
            if ($fullSize !== $tire->fullSize) {
              $html .= '</tbody>';
              $html .= '</table>';
            }
          }
        }
      } else if ($request->table_type === 'grid') {
        $html .= '<div class="tire-image-container">';
        $cbrand = '';
        $index = 0;
        foreach ($tires as $tire) {

          $brand = $tire->fullSize;
          if ($cbrand != $brand) {
            $html .= '</div><h4 class="tire-brand-name grid-t" style="margin-left: 5px;">' . $brand;
            if ($index == 0) {
                $html .= ' <span class="tire-type-title">' . $season_title . '</span>';
            }
            $html .= '<span style="margin: 0 auto;"></span>';
            $html .= '<button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">
                                      Filtrs
                                    </button></h4>
                          <div class="row grid-ex pr-1 mobile-tire-container" style="padding-left: 5px;">';
            $cbrand = $brand;
          }
          $isSelected = in_array((string) $tire->tire_id, $selectedTires, true);
          $html .= '<a href="' . $tire->getUrl . '" class="grid-view-link" data-article="' . $tire->article . '">';
          $html .= '<div class="tire-image-card sort-order' . ($isSelected ? ' selected' : '') . '">';
          $html .= '<div class="text-center image-grid-overflow">';
          $html .= Image::showGrid('auto', $tire->make_id);
          $html .= '</div>';

          $html .= '<div class="tire-list-caption">';

          $html .= '<div class="card-title-text"><span class="tippy lisi-tooltip" data-content="' . $tire->fullName . '" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . $tire->fullTitle . '</span></div>">' . $tire->fullTitle . '</span></div>';

          $html .= '<div class="tire-tread">';
          $html .= '<b>' . $tire->fullSize . ' </b>';
          $html .= '<span data-toggle="tooltip" title="<span style=\'color: black\'>' . $tire->lisiDesc . '</span>">' . $tire->li . $tire->si . ' </span>';
          $html .= '<span class="tire-image-code code-explain">' . $tire->code . '</span>';
          $html .= '</div>';
          $html .= $this->renderAutoTireGridCardFooter($tire, $selectedTires, (int) $season);
          $html .= '</div>';
          $html .= '</div>';
          $html .= '</a>';
        }
        $index++;
        $html .= '</div>';
      }

      if ($tires->count() <= 0) {
        $html .= '<div class="container"><div class="col-md-12 mt-1 alert alert-danger">Ar إ،ؤپdiem parametriem nav atrasta neviena pozؤ«cija.</div></div>';
      }

      $html .= $this->generatePagination($page, $totalPages, $offset, $perPage, $totalItems);

      return response()->json($html, 200);
    } catch (\Throwable $e) {
      if (config('app.debug')) {
        throw $e;
      }

      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function tires_ajax(Request $request): \Illuminate\Http\JsonResponse
  {
    $tire = $this->findAutotireForCart((int) $request->tire_id);

    if (!$tire) {
      return response()->json(['error' => 'Tire not found'], 404);
    }

    $quantity = $request->quantity ? (int) $request->quantity : $this->cartQty;
    $cart = session()->get('cart', ['products' => []]);
    $this->addAutotireToCart($cart, $tire, $quantity, $request->tire_url);

    return $this->persistCartAndRespond($cart, $quantity);
  }

  public function tires_ajax_dual_kit(Request $request): \Illuminate\Http\JsonResponse
  {
    $tireA = $this->findAutotireForCart((int) $request->tire_id_a);
    $tireB = $this->findAutotireForCart((int) $request->tire_id_b);

    if (!$tireA || !$tireB) {
      return response()->json(['error' => 'Tire not found'], 404);
    }

    $kitQuantity = 2;
    $cart = session()->get('cart', ['products' => []]);
    $this->hydrateAutotireForApiResponse($tireA, 1);
    $this->hydrateAutotireForApiResponse($tireB, 1);
    $this->addAutotireToCart($cart, $tireA, $kitQuantity, $request->tire_url_a);
    $this->addAutotireToCart($cart, $tireB, $kitQuantity, $request->tire_url_b);

    $kitTotalValue = $this->getDualSizePairKitTotalValue($tireA, $tireB);
    $kitTitle = trim($tireA->fullTitle);

    $payload = $this->persistCartAndRespond($cart, $kitQuantity * 2, false);
    $payload['dual_kit'] = true;
    $payload['kit'] = [
      'title' => $kitTitle,
      'total_price' => (int) round($kitTotalValue),
      'items' => [
        $this->buildDualKitCartItemPayload($tireA, $kitQuantity, 'Priekإ،ؤ“jؤپ ass'),
        $this->buildDualKitCartItemPayload($tireB, $kitQuantity, 'Aizmugurؤ“jؤپ ass'),
      ],
    ];

    return response()->json($payload);
  }

  protected function findAutotireForCart(int $tireId): ?Autotire
  {
    return Autotire::selectRaw('auto_tires.*, auto_treads.*, auto_brands.title as api_brand_title, auto_brands.slug as api_brand_slug')
      ->join('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
      ->join('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
      ->where('auto_treads.season', $this->season)
      ->where('auto_tires.tire_id', $tireId)
      ->where('auto_tires.visible_users', '<>', 0)
      ->first();
  }

  protected function resolveAutotireTypeLabel(int $type): string
  {
    switch ($type) {
      case 1:
        return 'M+S';
      case 2:
        return 'Radإ¾ojama';
      case 3:
        return 'Ar radzؤ“m';
      case 4:
        return 'Ziemas';
      default:
        return '';
    }
  }

  protected function addAutotireToCart(array &$cart, Autotire $tire, int $quantity, ?string $url): void
  {
    if (isset($cart['products'][$tire->tire_id])) {
      $cart['products'][$tire->tire_id]['quantity'] += $quantity;

      return;
    }

    $cart['products'][$tire->tire_id] = [
      'id' => $tire->tire_id,
      'name' => $tire->fullName,
      'make_id' => $tire->make_id,
      'd1' => $tire->d1,
      'd2' => $tire->d2,
      'd3' => $tire->d3,
      'type' => $this->resolveAutotireTypeLabel((int) $tire->type),
      'li' => $tire->li,
      'si' => $tire->si,
      'url' => $url,
      'image' => Image::image('auto', $tire->make_id),
      'price' => $tire->price2,
      'quantity' => $quantity,
      'availability' => $tire->dotAvailable,
      'category' => $this->model,
    ];
  }

  protected function persistCartAndRespond(array $cart, int $boughtQuantity, bool $asJsonResponse = true)
  {
    $priceMap = $this->getTirePriceMap(array_column($cart['products'], 'id'));
    $totalSum = 0;
    foreach ($cart['products'] as $product) {
      $totalSum += $product['quantity'] * ($priceMap[$product['id']] ?? 0);
    }

    $cart['total_sum'] = $totalSum;
    session()->put('cart', $cart);
    Cookie::queue('cart', json_encode($cart), 43200);
    ShopController::updateCartInDatabase($totalSum);
    event(new \App\Events\CartUpdated());

    $payload = [
      'cart' => $cart,
      'total_sum' => $totalSum,
      'quantity' => array_sum(array_column($cart['products'], 'quantity')),
      'bought' => $boughtQuantity,
    ];

    return $asJsonResponse ? response()->json($payload) : $payload;
  }

  protected function buildDualKitCartItemPayload(Autotire $tire, int $quantity, string $axisLabel): array
  {
    $unitPrice = (int) $tire->price2;
    $typeLabel = $this->resolveAutotireTypeLabel((int) $tire->type);

    return [
      'id' => $tire->tire_id,
      'axis_label' => $axisLabel,
      'name' => $tire->fullName,
      'title' => $tire->fullTitle,
      'size' => $tire->fullSize,
      'quantity' => $quantity,
      'price' => $unitPrice,
      'line_total' => $unitPrice * $quantity,
      'image' => Image::image('auto', $tire->make_id),
      'd1' => $tire->d1,
      'd2' => $tire->d2,
      'd3' => $tire->d3,
      'type' => $typeLabel,
      'li' => $tire->li,
      'si' => $tire->si,
      'code' => trim((string) $tire->code),
      'eco' => trim((string) $tire->eco),
      'wet' => trim((string) $tire->wet),
      'noise' => trim((string) $tire->noise),
      'comment' => trim((string) $tire->comment),
    ];
  }


  public function splitInput($input) {
    $input = str_replace(',', '.', $input);
    $d3_suffix = '';

    if (substr($input, -1) === 'C') {
      $input = substr($input, 0, -1);
      $d3_suffix = 'C';
    }

    if (preg_match('/^(\d{2})(\d{2}\.\d{1,2})(\d{2})$/', $input, $matches)) {
      $d1 = $matches[1];
      $d2 = $matches[2];
      $d3 = $matches[3] . $d3_suffix;
    } else {
      if (preg_match('/^(\d{3})(\d{2})(\d{2})$/', $input, $matches)) {
        $d1 = $matches[1];
        $d2 = $matches[2];
        $d3 = $matches[3] . $d3_suffix;
      } else {
        $d1 = 1;
        $d2 = 1;
        $d3 = 1;
      }
    }

    return compact('d1', 'd2', 'd3');
  }

  public function tires_find(Request $request) {

    return view('tires.auto.tires');
//    'availability' => $this->availability
  }

  public function tires_filter(Request $request) {

    $this->d1 = $request->d1;
    $this->d2 = $request->d2;
    $this->d3 = $request->d3;

    $ownStock = Autotire::ownStockQuantitySql('auto_tires');
    $partnerStock = $this->partnerStockColumnSql();
    $availabilities = $request->input('availabilities', []);

    $query = $this->applyPartnerStockJoin(
      Autotire::query()->from('auto_tires')
        ->leftJoin('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
        ->where('auto_treads.season', $this->season)
        ->where('auto_tires.visible_users', '<>', 0)
    );

    if ($this->d1 !== null) {
      $query->where('auto_tires.d1', $this->d1);
    }
    if ($this->d2 !== null) {
      $query->where('auto_tires.d2', $this->d2);
    }
    if ($this->d3 !== null) {
      $query->where('auto_tires.d3', $this->d3);
    }

    if (($availabilities['green'] ?? 0) == 1) {
      $query->whereRaw("{$ownStock} > 0");
    }
    if (($availabilities['yellow'] ?? 0) == 1) {
      $query->whereRaw("{$ownStock} <= 0")->whereRaw("{$partnerStock} > 0");
    }
    if (($availabilities['red'] ?? 0) == 1) {
      $query->whereRaw("{$ownStock} <= 0")->whereRaw("{$partnerStock} <= 0");
    }

    $tires = $query->orderBy('auto_tires.d3', 'ASC')
      ->orderBy('auto_tires.d1', 'ASC')
      ->orderBy('auto_tires.d2', 'ASC')
      ->orderBy('auto_tires.price2', 'DESC')
      ->get();

    //dd(DB::getQueryLog());

    $tires_array = [];

    foreach ($tires as $tire) {

      $size = $tire->d1 . '/' . $tire->d2 . 'R' . $tire->d3;

      $tires_array[$size] = $tire;

    }

    return json_encode($tires_array);
  }

  public function tires_search(Request $request) {

    $this->currBrand = ($request->brand == 'Visi') ? '' : $request->brand;

    $code = $request->code;
    $sql = Autotread::selectRaw('auto_treads.*')
                      ->selectRaw('auto_brands.*, auto_brands.title as brand_title')
                      ->leftJoin('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
                      ->where('auto_treads.season', $this->season)
                      ->where('auto_brands.title', $this->currBrand)
                      ->get();
    $makes = [];
    foreach ($sql as $make) {
      $makes[] = $make->tread_id;
    }

    $this->d1 = ($this->d1 == 'Visi') ? '' : $request->d1;
    $this->d2 = ($this->d2 == 'Visi') ? '' : $request->d2;

    $tires = Autotire::join('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')->when($makes, function($query) use ($makes) {
      $query->whereIn('make_id', $makes);
    })->when($this->d1, function($query) {
      $query->where('d1', $this->d1);
    })->when($this->d2, function($query) {
      $query->where('d2', $this->d2);
    })->when($code, function($query) use ($code){
      $query->where('code', 'LIKE', '%' . $code . '%');
    })->where('d3', $this->d3)
      ->where('auto_treads.season', $this->season)
      ->orderBy('d3', 'ASC')
      ->orderBy('d1', 'ASC')
      ->orderBy('d2', 'ASC')
      ->orderBy('price2', 'DESC')
      ->paginate()->appends($request->query());

    return view('tires.auto.tires',
      compact('tires', 'code')
    );
  }

  public function tires_tread(Request $request, $brand, $tread, $tire) {

    $selectedTires = [];
    if ($request->input('selected')) {
      $selectedTires = explode(',', $request->input('selected'));
    }

    $brand = Autobrand::where('slug', $brand)->first();

    $tires = Autotire::selectRaw('auto_tires.*, auto_treads.*, auto_brands.*,
                                                auto_brands.title as brands_title')
      ->join('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
      ->join('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
      ->where('auto_brands.title', $brand->title)
      ->where('auto_treads.t_title', str_replace('_', '/', $tread))
      ->orderBy('d3', 'ASC')
      ->orderBy('d1', 'ASC')
      ->orderBy('d2', 'ASC')
      ->get();

    $currTire = Autotire::selectRaw('auto_tires.*, auto_treads.*')
      ->leftJoin('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
      ->where('auto_treads.t_title', str_replace('_', '/', $tread))
      ->where('auto_tires.tire_id', $tire)
      ->first();

    $currBrand = Autobrand::where('brand_id', $currTire->brand_id)->first();

    $currTire->includeStock = true;

    $tireIds = $tires->pluck('tire_id')->all();
    if ($currTire && !in_array($currTire->tire_id, $tireIds, true)) {
      $tireIds[] = $currTire->tire_id;
    }
    Autotire::preloadStockData($tireIds);

    return view('tires.auto.autotread',
      compact('tires', 'currTire', 'currBrand', 'selectedTires')
    );
  }

  public function get_sizes($season)
  {
    try {
      $tireSizes = Autotire::join('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
        ->select(DB::raw('CONCAT(D1, D2, D3) as tire_size'))
        ->where('auto_tires.visible_users', '<>', 0)
        ->where('auto_treads.season', $season)
        ->orderBy('d3', 'ASC')
        ->orderBy('d1', 'ASC')
        ->orderBy('d2', 'ASC')
        ->groupBy('auto_tires.article')
        ->distinct()
        ->get();

      return response()->json($tireSizes, 200);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function tires_getCodes()
  {

    $codes = Autotire::join('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
      ->selectRaw('MIN(code) AS code')
      ->where('auto_tires.visible_users', '<>', 0)
      ->where('auto_treads.season', 1)
      ->where('code', 'NOT LIKE', '%DOT%')
      ->where('code', 'NOT LIKE', '')
      ->groupBy('code')
      ->get();

    $explodedCodes = [];
    $uniqueCodes = [];

    foreach ($codes as $codeString) {
      $explodedCodes[] = explode(' ', $codeString->code);
    }

    foreach ($explodedCodes as $code) {
      foreach ($code as $code1) {
        $uniqueCodes[] = $code1;
      }
    }

    return array_filter(array_unique($uniqueCodes));
  }

  public function tires_getBrands()
  {
    $brands = Autobrand::join('auto_treads', 'auto_brands.brand_id', '=', 'auto_treads.brand_id')
      ->join('auto_tires', 'auto_treads.tread_id', '=', 'auto_tires.make_id')
      ->where('auto_tires.visible_users', '<>', 0)
      ->where('auto_treads.season', $this->season)
      ->distinct()
      ->pluck('auto_brands.title', 'auto_brands.brand_id')
      ->sort(SORT_NATURAL | SORT_FLAG_CASE);

    return $brands->all();
  }

  protected function shouldSkipHeavyViewShare(Request $request): bool
  {
    return $request->routeIs('vasaras-riepa')
      || $request->routeIs('ziemas-riepa')
      || $request->routeIs('vasaras-riepas-ajax')
      || $request->routeIs('ziemas-riepas-ajax')
      || $request->routeIs('vasaras-riepas-ajax-dual-kit')
      || $request->routeIs('ziemas-riepas-ajax-dual-kit');
  }

  protected function isAutoTireCatalogRequest(Request $request): bool
  {
    return $request->routeIs(
      'vasaras-riepas',
      'vasaras-riepas-meklet',
      'ziemas-riepas',
      'ziemas-riepas-meklet'
    );
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

  protected function resolveSeasonFromRequest(Request $request, bool $shareViews = false): void
  {
    if (strpos($request->path(), 'vasaras-riepas') !== false) {
      $this->season = 1;
      if ($shareViews) {
        View::share('season', 'Vasaras riepas');
        View::share('current_url', 'vasaras-riepa');
        View::share('season_title', 'vasaras-riepas');
      }
    } elseif (strpos($request->path(), 'ziemas-riepas') !== false || ($request->route() && $request->route()->getName() === 'home')) {
      $this->season = 2;
      if ($shareViews) {
        View::share('season', 'Ziemas riepas');
        View::share('current_url', 'ziemas-riepa');
        View::share('season_title', 'ziemas-riepas');
      }
    }

    if ($shareViews) {
      View::share('season_id', $this->season);
    }
  }

  protected function applyPartnerStockJoin($query)
  {
    return $query->leftJoinSub(
      DB::table('auto_stock')
        ->selectRaw('tire_id, SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END) as partner_stock')
        ->groupBy('tire_id'),
      'auto_stock_totals',
      'auto_tires.tire_id',
      '=',
      'auto_stock_totals.tire_id'
    );
  }

  protected function partnerStockColumnSql(): string
  {
    return 'COALESCE(auto_stock_totals.partner_stock, 0)';
  }

  protected function buildApiTiresBaseQuery(int $season)
  {
    return $this->applyPartnerStockJoin(
      Autotire::query()->from('auto_tires')
        ->join('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
        ->join('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
        ->where('auto_treads.season', $season)
        ->where('auto_tires.visible_users', '<>', 0)
    );
  }

  protected function applyApiTireCatalogFilters($query, array $filters): void
  {
    $ownStock = Autotire::ownStockQuantitySql();
    $partnerStock = $this->partnerStockColumnSql();

    $query->when($filters['currBrand'] ?? '', function ($query) use ($filters) {
      $query->where('auto_brands.slug', Str::slug($filters['currBrand']));
    });

    if (!empty($filters['dualSize'])) {
      $this->applyDualTireSizeFilter($query, $filters);
    } else {
      $query->when($filters['d1'] ?? '', function ($query) use ($filters) {
        $query->where('auto_tires.d1', $filters['d1']);
      })->when($filters['d2'] ?? '', function ($query) use ($filters) {
        $query->where('auto_tires.d2', $filters['d2']);
      })->when($filters['d3'] ?? '', function ($query) use ($filters) {
        $query->where('auto_tires.d3', $filters['d3']);
      });
    }

    $query->when($filters['availability'] ?? '', function ($query) use ($filters, $ownStock, $partnerStock) {
      switch ($filters['availability']) {
        case 'green':
          $query->whereRaw("{$ownStock} > 0");
          break;
        case 'green+yellow':
          $query->where(function ($query) use ($ownStock, $partnerStock) {
            $query->whereRaw("{$ownStock} > 0")
              ->orWhere(function ($query) use ($ownStock, $partnerStock) {
                $query->whereRaw("{$ownStock} <= 0")
                  ->whereRaw("{$partnerStock} > 0");
              });
          });
          break;
        case 'green+red':
          $query->where(function ($query) use ($ownStock, $partnerStock) {
            $query->whereRaw("{$ownStock} > 0")
              ->orWhere(function ($query) use ($ownStock, $partnerStock) {
                $query->whereRaw("{$ownStock} <= 0")
                  ->whereRaw("{$partnerStock} <= 0");
              });
          });
          break;
        case 'yellow':
          $query->whereRaw("{$ownStock} <= 0")->whereRaw("{$partnerStock} > 0");
          break;
        case 'yellow+red':
          $query->whereRaw("{$ownStock} <= 0");
          break;
        case 'red':
          $query->whereRaw("{$ownStock} <= 0")->whereRaw("{$partnerStock} <= 0");
          break;
      }
    })->when($filters['code'] ?? null, function ($query) use ($filters) {
      $codeCombinations = $filters['codeCombinations'] ?? [];
      $query->where(function ($query) use ($codeCombinations) {
        if (count($codeCombinations) > 1) {
          foreach ($codeCombinations as $combination) {
            $query->orWhere('auto_tires.code', 'like', '%' . $combination . '%');
          }
        } elseif ($codeCombinations !== []) {
          $query->where('auto_tires.code', 'like', '%' . implode(' ', $codeCombinations) . '%');
        }
      });
    })->when($filters['type'] ?? null, function ($query) use ($filters) {
      $selectedTypes = $filters['selectedTypes'] ?? [];
      $typeConditions = $filters['typeConditions'] ?? [];
      $query->where(function ($query) use ($selectedTypes, $typeConditions) {
        $firstCondition = true;
        foreach ($selectedTypes as $type) {
          if (!isset($typeConditions[$type])) {
            continue;
          }
          $condition = $typeConditions[$type];
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
    })->when($filters['fuel'] ?? null, function ($query) use ($filters) {
      $selectedFuel = $filters['selectedFuel'] ?? [];
      $fuelConditions = $filters['fuelConditions'] ?? [];
      $query->where(function ($query) use ($selectedFuel, $fuelConditions) {
        $firstCondition = true;
        foreach ($selectedFuel as $type) {
          if (!isset($fuelConditions[$type])) {
            continue;
          }
          $condition = $fuelConditions[$type];
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
    })->when($filters['wet'] ?? null, function ($query) use ($filters) {
      $selectedWet = $filters['selectedWet'] ?? [];
      $wetConditions = $filters['wetConditions'] ?? [];
      $query->where(function ($query) use ($selectedWet, $wetConditions) {
        $firstCondition = true;
        foreach ($selectedWet as $type) {
          if (!isset($wetConditions[$type])) {
            continue;
          }
          $condition = $wetConditions[$type];
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
    })->when($filters['noise'] ?? null, function ($query) use ($filters) {
      $selectedNoise = $filters['selectedNoise'] ?? [];
      $noiseConditions = $filters['noiseConditions'] ?? [];
      $query->where(function ($query) use ($selectedNoise, $noiseConditions) {
        $firstCondition = true;
        foreach ($selectedNoise as $type) {
          if (!isset($noiseConditions[$type])) {
            continue;
          }
          $condition = $noiseConditions[$type];
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
    })->when($filters['show_selected'] ?? null, function ($query) use ($filters) {
      $query->whereIn('auto_tires.tire_id', $filters['selectedTires'] ?? []);
    })->when($filters['topTires'] ?? null, function ($query) {
      $query->where('auto_tires.top', 1);
    });
  }

  protected function applyDualTireSizeFilter($query, array $filters): void
  {
    $sizeA = array_filter([
      'd1' => $filters['d1'] ?? '',
      'd2' => $filters['d2'] ?? '',
      'd3' => $filters['d3'] ?? '',
    ], function ($value) {
      return $value !== '' && $value !== null;
    });

    $sizeB = array_filter([
      'd1' => $filters['d1b'] ?? '',
      'd2' => $filters['d2b'] ?? '',
      'd3' => $filters['d3b'] ?? '',
    ], function ($value) {
      return $value !== '' && $value !== null;
    });

    if ($sizeA === [] && $sizeB === []) {
      return;
    }

    $query->where(function ($query) use ($sizeA, $sizeB) {
      if ($sizeA !== []) {
        $query->where(function ($query) use ($sizeA) {
          foreach ($sizeA as $column => $value) {
            $query->where('auto_tires.' . $column, $value);
          }
        });
      }

      if ($sizeB !== []) {
        if ($sizeA !== []) {
          $query->orWhere(function ($query) use ($sizeB) {
            foreach ($sizeB as $column => $value) {
              $query->where('auto_tires.' . $column, $value);
            }
          });
        } else {
          $query->where(function ($query) use ($sizeB) {
            foreach ($sizeB as $column => $value) {
              $query->where('auto_tires.' . $column, $value);
            }
          });
        }
      }
    });
  }

  protected function secondaryDualSizeSelected($d1b = null, $d2b = null, $d3b = null): bool
  {
    $d1b = $d1b ?? $this->d1b;
    $d2b = $d2b ?? $this->d2b;
    $d3b = $d3b ?? $this->d3b;

    foreach ([$d1b, $d2b, $d3b] as $value) {
      if ($value === '' || $value === null || $value === 'Visi') {
        return false;
      }
    }

    return true;
  }

  protected function dualSizePairFiltersComplete(array $filters): bool
  {
    if (empty($filters['dualSize'])) {
      return false;
    }

    foreach (['d1', 'd2', 'd3'] as $key) {
      if (($filters[$key] ?? '') === '' || $filters[$key] === null) {
        return false;
      }
    }

    return $this->secondaryDualSizeSelected(
      $filters['d1b'] ?? null,
      $filters['d2b'] ?? null,
      $filters['d3b'] ?? null
    );
  }

  protected function dualSizeTreadPairQuery(int $season, array $filters)
  {
    $query = $this->buildApiTiresBaseQuery($season);
    $this->applyApiTireCatalogFilters($query, $filters);

    return $query
      ->select('auto_tires.make_id')
      ->groupBy('auto_tires.make_id', 'auto_brands.title', 'auto_treads.t_title')
      ->havingRaw(
        'SUM(CASE WHEN auto_tires.d1 = ? AND auto_tires.d2 = ? AND auto_tires.d3 = ? THEN 1 ELSE 0 END) > 0',
        [$filters['d1'], $filters['d2'], $filters['d3']]
      )
      ->havingRaw(
        'SUM(CASE WHEN auto_tires.d1 = ? AND auto_tires.d2 = ? AND auto_tires.d3 = ? THEN 1 ELSE 0 END) > 0',
        [$filters['d1b'], $filters['d2b'], $filters['d3b']]
      )
      ->orderByRaw(
        $this->dualSizePairKitTotalOrderExpression() . ' DESC',
        $this->dualSizePairKitTotalOrderBindings($filters)
      );
  }

  protected function dualSizePairKitTotalOrderExpression(): string
  {
    return '(2 * MAX(CASE WHEN auto_tires.d1 = ? AND auto_tires.d2 = ? AND auto_tires.d3 = ? THEN auto_tires.price2 END)'
      . ' + 2 * MAX(CASE WHEN auto_tires.d1 = ? AND auto_tires.d2 = ? AND auto_tires.d3 = ? THEN auto_tires.price2 END))';
  }

  protected function dualSizePairKitTotalOrderBindings(array $filters): array
  {
    return [
      $filters['d1'],
      $filters['d2'],
      $filters['d3'],
      $filters['d1b'],
      $filters['d2b'],
      $filters['d3b'],
    ];
  }

  protected function countDualSizeTreadPairs(int $season, array $filters): int
  {
    return count($this->buildAllDualSizeTreadPairs($season, $filters));
  }

  protected function fetchDualSizeTreadPairs(int $season, array $filters, int $offset, int $perPage): array
  {
    return array_slice($this->buildAllDualSizeTreadPairs($season, $filters), $offset, $perPage);
  }

  protected function buildAllDualSizeTreadPairs(int $season, array $filters): array
  {
    $makeIds = $this->dualSizeTreadPairQuery($season, $filters)
      ->pluck('make_id')
      ->all();

    if ($makeIds === []) {
      return [];
    }

    $listQuery = $this->buildApiTiresBaseQuery($season)
      ->selectRaw('auto_tires.*, auto_tires.quantity as tire_quantity, auto_treads.*, ' . $this->partnerStockColumnSql() . ' as stock_quantity, auto_brands.title as api_brand_title, auto_brands.slug as api_brand_slug')
      ->whereIn('auto_tires.make_id', $makeIds);
    $this->applyApiTireCatalogFilters($listQuery, $filters);
    $listQuery->orderBy('auto_tires.price2', 'DESC');

    $tires = $listQuery->get();
    $this->prepareAutotiresForApiList($tires, $season);
    $grouped = $tires->groupBy('make_id');
    $pairs = [];

    foreach ($makeIds as $makeId) {
      $group = $grouped->get($makeId, collect());
      $sizeAVariants = $this->pickAllTiresForExactSize($group, $filters['d1'], $filters['d2'], $filters['d3']);
      $sizeBVariants = $this->pickAllTiresForExactSize($group, $filters['d1b'], $filters['d2b'], $filters['d3b']);

      if ($sizeAVariants->isEmpty() || $sizeBVariants->isEmpty()) {
        continue;
      }

      foreach ($sizeAVariants as $sizeA) {
        foreach ($sizeBVariants as $sizeB) {
          $pairs[] = [
            'title' => $sizeA->api_brand_title . ' ' . $sizeA->t_title,
            'size_a' => $sizeA,
            'size_b' => $sizeB,
            'kit_total' => $this->getDualSizePairKitTotalValue($sizeA, $sizeB),
          ];
        }
      }
    }

    usort($pairs, function (array $a, array $b): int {
      return ($b['kit_total'] ?? 0) <=> ($a['kit_total'] ?? 0);
    });

    return $pairs;
  }

  protected function pickAllTiresForExactSize($tires, $d1, $d2, $d3)
  {
    return $tires->filter(function ($tire) use ($d1, $d2, $d3) {
      return (string) $tire->d1 === (string) $d1
        && (string) $tire->d2 === (string) $d2
        && (string) $tire->d3 === (string) $d3;
    })->sortByDesc(function ($tire) {
      return $this->resolveTireUnitPrice($tire);
    })->values();
  }

  protected function buildDualSizeApiResponseHtml(
    Request $request,
    int $season,
    array $filterContext,
    array $selectedTires,
    int $page,
    int $offset
  ): string {
    $perPage = 40;
    $countCacheKey = $this->tireApiCountCacheKey($season, $filterContext);
    $pairsCacheKey = $countCacheKey . ':pairs';
    $allPairs = Cache::remember($pairsCacheKey, 120, function () use ($season, $filterContext) {
      return $this->buildAllDualSizeTreadPairs($season, $filterContext);
    });
    $totalItems = count($allPairs);
    $totalPages = (int) ceil($totalItems / $perPage);
    $pairs = array_slice($allPairs, $offset, $perPage);

    $type = $season === 2
      ? '<th scope="col" class="hidden-sm-down text-center table-tire-param-cell">Tips</th>'
      : '';
    $html = '';

    if ($request->table_type === 'list') {
      if ($pairs !== []) {
        $html .= '<button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">Filtrs</button>';

        $html .= '<div class="dual-size-tables-stack">';
        foreach ($pairs as $index => $pair) {
          $html .= '<div class="dual-size-pair-block">';
          $html .= $this->renderAutoTireListTableOpening($type, 'Brends / modelis', $index === 0);
          $html .= $this->renderAutoTireListRow(
            $pair['size_a'],
            $selectedTires,
            $type,
            $pair['size_a']->fullSize,
            $pair['size_a']->fullTitle,
            2
          );
          $html .= $this->renderAutoTireListRow(
            $pair['size_b'],
            $selectedTires,
            $type,
            $pair['size_b']->fullSize,
            $pair['size_b']->fullTitle,
            2
          );
          $html .= '</tbody>';
          $html .= $this->renderDualSizePairKitTotalFoot($pair['size_a'], $pair['size_b'], $type !== '');
          $html .= '</table>';
          $html .= '</div>';
        }
        $html .= '</div>';
      }
    } elseif ($request->table_type === 'grid') {
      $html .= '<div class="tire-image-container">';
      if ($pairs !== []) {
        $html .= '</div><h4 class="tire-brand-name grid-t" style="margin-left: 5px;">';
        $html .= '<span style="margin: 0 auto;"></span>';
        $html .= '<button type="button" class="btn-sm btn-outline-danger hidden-md-up sm-filter-btn" data-toggle="modal" data-target="#mobileFilterModal">Filtrs</button></h4>';
        $html .= '<div class="dual-size-grid-stack mobile-tire-container" style="padding-left: 5px;">';
        foreach ($pairs as $pair) {
          $html .= $this->renderDualSizePairGridBlock($pair, $selectedTires, $season);
        }
        $html .= '</div>';
      }
    }

    if ($pairs === []) {
      $html .= $this->renderCatalogEmptyResultsHtml(true, $filterContext);
    }

    $html .= $this->generatePagination($page, $totalPages, $offset, $perPage, $totalItems);

    return $html;
  }

  protected function formatTireSizeForDisplay($d1, $d2, $d3): string
  {
    return $d1 . '/' . $d2 . 'R' . $d3;
  }

  protected function renderCatalogEmptyResultsHtml(bool $dualPairMode, array $sizes = []): string
  {
    if (!$dualPairMode) {
      return '<div class="container"><div class="col-md-12 mt-1 alert alert-danger">Ar إ،ؤپdiem parametriem nav atrasta neviena pozؤ«cija.</div></div>';
    }

    $sizeA = e($this->formatTireSizeForDisplay($sizes['d1'] ?? '', $sizes['d2'] ?? '', $sizes['d3'] ?? ''));
    $sizeB = e($this->formatTireSizeForDisplay($sizes['d1b'] ?? '', $sizes['d2b'] ?? '', $sizes['d3b'] ?? ''));

    return '<div class="container dual-size-empty-results">'
      . '<div class="col-md-12 mt-1 alert alert-warning dual-size-empty-alert">'
      . '<strong>Nav atrasts neviens komplekts ar abiem izmؤ“riem.</strong>'
      . '<p class="mb-0 mt-2">Meklؤ“jam vienؤپdu protektoru, kuram pieejams gan <strong>' . $sizeA . '</strong>, gan <strong>' . $sizeB . '</strong> (pa 2 gab. katram izmؤ“ram).</p>'
      . '<ul class="dual-size-empty-tips mb-0 mt-2">'
      . '<li>Pؤپrbaudiet aizmugurؤ“jؤپ ass diametru â€” bieإ¾i jؤپizvؤ“las <em>17</em>, nevis <em>17C</em>.</li>'
      . '<li>Noإ†emiet papildu filtrus (raإ¾otؤپjs, pieejamؤ«ba, kods).</li>'
      . '<li>Noإ†emiet atzؤ«mi آ«Otrs izmؤ“rsآ», lai meklؤ“tu tikai pؤ“c viena izmؤ“ra.</li>'
      . '</ul>'
      . '</div></div>';
  }

  protected function renderDualSizeKitCartButton(Autotire $sizeA, Autotire $sizeB): string
  {
    if (Auth::check() && Auth::user()->hasRole('administrators')) {
      return '';
    }

    $urlA = $sizeA->hydrated_cart_link ?? $sizeA->getUrl ?? '';
    $urlB = $sizeB->hydrated_cart_link ?? $sizeB->getUrl ?? '';
    $kitTitle = e(trim(($sizeA->api_brand_title ?? '') . ' ' . ($sizeA->t_title ?? '')));

    return '<button type="button" class="dual-size-kit-cart-button" '
      . 'data-info-a="' . (int) $sizeA->tire_id . '" '
      . 'data-info-b="' . (int) $sizeB->tire_id . '" '
      . 'data-url-a="' . e($urlA) . '" '
      . 'data-url-b="' . e($urlB) . '" '
      . 'data-kit-title="' . $kitTitle . '" '
      . 'title="Pievienot komplektu grozam (2+2)" aria-label="Pievienot komplektu grozam">'
      . '<i class="material-icons">add_shopping_cart</i>'
      . '</button>';
  }

  protected function renderDualSizePairKitTotalFoot(Autotire $sizeA, Autotire $sizeB, bool $hasTypeColumn): string
  {
    $total = $this->calculateDualSizePairKitTotal($sizeA, $sizeB);
    $totalColumns = $hasTypeColumn ? 13 : 12;

    return '<tfoot class="dual-size-pair-tfoot">'
      . '<tr class="dual-size-pair-total-row">'
      . '<td colspan="' . $totalColumns . '" class="dual-size-pair-total-cell">'
      . '<div class="dual-size-pair-total-box">'
      . '<span class="dual-size-pair-total-label">Kopؤپ: <span class="dual-size-pair-total-amount">' . e($total) . ' €</span></span>'
      . $this->renderDualSizeKitCartButton($sizeA, $sizeB)
      . '</div>'
      . '</td>'
      . '</tr>'
      . '</tfoot>';
  }

  protected function getDualSizePairKitTotalValue(Autotire $sizeA, Autotire $sizeB): float
  {
    return (2 * $this->resolveTireUnitPrice($sizeA)) + (2 * $this->resolveTireUnitPrice($sizeB));
  }

  protected function calculateDualSizePairKitTotal(Autotire $sizeA, Autotire $sizeB): string
  {
    $total = $this->getDualSizePairKitTotalValue($sizeA, $sizeB);

    if (floor($total) === $total) {
      return (string) (int) $total;
    }

    return number_format($total, 2, '.', '');
  }

  protected function resolveTireUnitPrice(Autotire $tire): float
  {
    $price = $tire->price2 ?? $tire->price1 ?? 0;
    if (is_string($price)) {
      $price = str_replace(',', '.', preg_replace('/[^\d.,]/', '', $price));
    }

    return (float) $price;
  }

  protected function renderAutoTireListTableOpening(
    string $typeColumn,
    string $nameColumnLabel = 'Brends / modelis',
    bool $withDomIds = false
  ): string {
    $hasTypeColumn = $typeColumn !== '';
    $tableId = $withDomIds ? ' id="tires-table"' : '';
    $tbodyId = $withDomIds ? ' id="tires-table-body"' : '';
    $storePriceButtonId = $withDomIds ? ' id="store-price-button"' : '';
    $storeSaleButtonId = $withDomIds ? ' id="store-sale-button"' : '';
    $html = '<table' . $tableId . ' class="table table-striped tires-table dual-size-tires-table">';
    $html .= $this->renderAutoTireTableColgroup($hasTypeColumn);
    $html .= '<thead class="tires-thead sticky-table"><tr>';
    $html .= '<th scope="col"></th>';
    $html .= '<th scope="col" class="table-tire-name-cell">' . e($nameColumnLabel) . '</th>';
    $html .= '<th scope="col" class="hidden-sm-down text-center table-tire-param-cell">LI/SI</th>';
    $html .= $typeColumn;
    $html .= '<th scope="col" class="hidden-sm-down text-center table-tire-code-cell">Kods</th>';
    $html .= '<th scope="col" class="hidden-sm-down table-tire-param-cell"><div class="tire-table-icon icon-tire-fuel" title="Degvielas ekonomija"></div></th>';
    $html .= '<th scope="col" class="hidden-sm-down table-tire-param-cell"><div class="tire-table-icon icon-tire-rain" title="Slapjإ، segums"></div></th>';
    $html .= '<th scope="col" class="hidden-sm-down table-tire-param-cell"><div class="tire-table-icon icon-tire-sound" title="Troksnis"></div></th>';
    $html .= '<th' . $storePriceButtonId . ' scope="col" class="text-center catalog-price-th"><span class="hidden-sm-down">Veikala cena</span><span class="hidden-md-up">Veik.</span></th>';
    $html .= '<th' . $storeSaleButtonId . ' scope="col" class="text-center catalog-price-th"><span class="hidden-sm-down">Akcijas cena</span><span class="hidden-md-up">Akc.</span></th>';
    $html .= '<th scope="col" class="hidden-sm-down text-center">Piezؤ«mes</th>';
    $html .= '<th scope="col"></th>';
    $html .= '<th scope="col"><div class="tire-table-icon icon-question" title="Pieejamؤ«ba" data-toggle="tooltip"></div></th>';
    $html .= '</tr></thead><tbody' . $tbodyId . '>';

    return $html;
  }

  protected function renderAutoTireTableColgroup(bool $hasTypeColumn): string
  {
    $widths = $hasTypeColumn
      ? ['3%', '24%', '4%', '4%', '16%', '4%', '4%', '4%', '8%', '8%', '13%', '4%', '4%']
      : ['3%', '25%', '4%', '17%', '4%', '4%', '4%', '9%', '9%', '11%', '4%', '4%'];

    $html = '<colgroup>';
    foreach ($widths as $width) {
      $html .= '<col style="width: ' . $width . '">';
    }
    $html .= '</colgroup>';

    return $html;
  }

  protected function renderAutoTireListRow(
    Autotire $tire,
    array $selectedTires,
    string $typeColumn,
    ?string $linkTitle = null,
    ?string $linkSubtitle = null,
    int $cartAddQty = 4
  ): string {
    $linkTitle = $linkTitle ?? $tire->fullTitle;
    $isSelected = in_array((string) $tire->tire_id, $selectedTires, true);
    $nameCellHtml = $this->renderAutoTireListNameCell($linkTitle, $linkSubtitle);
    $html = '<tr class="tire-table-row' . ($isSelected ? ' selected' : '') . '" role="row">';
    $html .= '<th scope="row" class="tire-table-checkbox"><input type="checkbox" value="' . $tire->tire_id . '" name="product_ids[]" class="tire-table-checkbox" title=""' . ($isSelected ? ' checked' : '') . '></th>';
    $html .= '<td class="table-tire-name-cell"><a class="tire-table-link tippy image" data-tippy-content="<div><img data-src=\'https://r1riepas.lv/storage/auto/tread/' . $tire->tread_id . '-o.jpg\'></div>" href="' . $tire->getUrl . '" data-content="' . e($tire->fullName) . '" data-article="' . e($tire->article) . '" data-quantity="' . $cartAddQty . '">' . $nameCellHtml . '</a></td>';
    $html .= '<td class="hidden-sm-down text-center table-tire-param-cell"><span class="tippy lisi-tooltip" data-tippy-content="<div style=\'padding: 5px; text-align: left;\'><span style=\'color: black; font-size: 15px;\'>' . e($tire->lisiDesc) . '</span></div>">' . e($tire->li . $tire->si) . '</span></td>';

    if ($tire->season == 2) {
      $html .= '<td scope="col" class="hidden-sm-down text-center table-tire-param-cell">';
      if ($tire->type == 1) {
        $html .= '<span class="tippy lisi-tooltip type-explain" data-type="1" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . \App\Helper\Tires::codeExplain('ms tips') . '</span></div>"><img src="/images/ms.png" alt="ms"></span>';
      }
      if ($tire->type == 2) {
        $html .= '<span class="tippy lisi-tooltip type-explain" data-type="2" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . \App\Helper\Tires::codeExplain('radإ¾ojamu tips') . '</span></div>"><img src="/images/radzeb.png" alt="ms"></span>';
      }
      if ($tire->type == 3) {
        $html .= '<span class="tippy lisi-tooltip type-explain" data-type="3" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . \App\Helper\Tires::codeExplain('ar radzؤ“m tips') . '</span></div>"><img src="/images/radzea.png" alt="ms"></span>';
      }
      if ($tire->type == 4) {
        $html .= '<span class="tippy lisi-tooltip type-explain" data-type="4" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . \App\Helper\Tires::codeExplain('ziemas tips') . '</span></div>"><img src="/images/parsla.png" alt="ms"></span>';
      }
      $html .= '</td>';
    }

    $html .= '<td class="hidden-sm-down text-center table-tire-code-cell"><span class="tippy lisi-tooltip code-explain" data-tippy-content="<div style=\'padding: 5px; text-align: left;\'><span style=\'color: black; font-size: 15px;\'>' . e($tire->codeExplain) . '</span></div>">' . e($tire->code) . '</span></td>';
    $html .= '<td class="hidden-sm-down text-center table-tire-param-cell"><span class="fuel-explain">' . e($tire->eco) . '</span></td>';
    $html .= '<td class="hidden-sm-down text-center table-tire-param-cell"><span class="wet-explain">' . e($tire->wet) . '</span></td>';
    $html .= '<td class="hidden-sm-down text-center table-tire-param-cell"><span class="noise-explain">' . e($tire->noise) . '</span></td>';
    $html .= '<td id="store-price" class="text-center store-price">€ ' . e($tire->price1) . '</td>';
    $html .= '<td id="sale-price" class="text-center tire-price-red sale-price">€ ' . e($tire->price2) . '</td>';

    if ($tire->comment == 'Izpؤپrdoإ،ana!' || $tire->priceoffer == 1) {
      $html .= '<td class="hidden-sm-down text-center sellout">' . e($tire->comment) . '</td>';
    } else {
      $html .= '<td class="hidden-sm-down text-center">' . e($tire->comment) . '</td>';
    }

    $html .= '<td class="shopping-cart-col"><div class="clearfix atc_div text-right">';
    if (Auth::check() && Auth::user()->hasRole('administrators')) {
      $html .= '<button class="cart-shopping-button" data-toggle="modal" data-target="#" data-info="' . $tire->tire_id . '" data-url="' . $tire->hydrated_cart_link . '"><i class="material-icons">add_shopping_cart</i></button>';
    } else {
      $html .= '<button class="cart-shopping-button" data-toggle="modal" data-target="#blockcart-modal" data-info="' . $tire->tire_id . '" data-url="' . $tire->hydrated_cart_link . '"><i class="material-icons">add_shopping_cart</i></button>';
    }
    $html .= '</div></td>';
    $html .= '<td class="dot-availability text-center"><span class="tippy lisi-tooltip dot ' . $tire->dotAvailable . '" data-color="' . $tire->dotAvailable . '" data-tippy-content=\'<div style="padding: 5px; text-align: left;"><span style="color: black; font-size: 15px; line-height: 28px;">' . e($tire->stockAvailability) . '</span></div>\'></span></td>';
    $html .= '</tr>';

    return $html;
  }

  protected function renderAutoTireListNameCell(string $linkTitle, ?string $linkSubtitle = null): string
  {
    if ($linkSubtitle === null || $linkSubtitle === '') {
      return '<div class="table-link-title">' . e($linkTitle) . '</div>';
    }

    return '<div class="table-link-title dual-size-table-link-title">'
      . '<div class="table-link-size">' . e($linkTitle) . '</div>'
      . '<div class="table-link-model">' . e($linkSubtitle) . '</div>'
      . '</div>';
  }

  protected function renderDualSizePairGridBlock(array $pair, array $selectedTires, int $season): string
  {
    $sizeA = $pair['size_a'];
    $sizeB = $pair['size_b'];
    $kitTotal = $this->calculateDualSizePairKitTotal($sizeA, $sizeB);

    $html = '<div class="dual-size-pair-grid-block">';
    $html .= '<div class="dual-size-pair-grid-cards">';
    $html .= $this->renderAutoTireGridCard($sizeA, $selectedTires, $season, 2);
    $html .= $this->renderAutoTireGridCard($sizeB, $selectedTires, $season, 2);
    $html .= '</div>';
    $html .= '<div class="dual-size-pair-grid-total">';
    $html .= '<span class="dual-size-pair-total-label">Kopؤپ: <span class="dual-size-pair-total-amount">' . e($kitTotal) . ' €</span></span>';
    $html .= $this->renderDualSizeKitCartButton($sizeA, $sizeB);
    $html .= '</div>';
    $html .= '</div>';

    return $html;
  }

  protected function renderAutoTireGridCardMetaIcons(Autotire $tire, int $season): string
  {
    return $this->renderAutoTireGridCardHiddenMeta($tire, $season);
  }

  protected function renderAutoTireGridCardHiddenMeta(Autotire $tire, int $season): string
  {
    $html = '';

    if ($tire->season == 2) {
      $html .= '<div class="hidden-sm-down text-center" style="display: none;">';
      if ($tire->type == 1) {
        $html .= '<span class="tippy lisi-tooltip type-explain" data-type="1"><img src="/images/ms.png" alt="ms"></span>';
      }
      if ($tire->type == 2) {
        $html .= '<span class="tippy lisi-tooltip type-explain" data-type="2"><img src="/images/radzeb.png" alt="ms"></span>';
      }
      if ($tire->type == 3) {
        $html .= '<span class="tippy lisi-tooltip type-explain" data-type="3"><img src="/images/radzea.png" alt="ms"></span>';
      }
      if ($tire->type == 4) {
        $html .= '<span class="tippy lisi-tooltip type-explain" data-type="4"><img src="/images/parsla.png" alt="ms"></span>';
      }
      $html .= '</div>';
    }

    $html .= '<div class="hidden-sm-down text-center" style="display: none;"><span class="fuel-explain">' . e($tire->eco) . '</span></div>';
    $html .= '<div class="hidden-sm-down text-center" style="display: none;"><span class="wet-explain">' . e($tire->wet) . '</span></div>';
    $html .= '<div class="hidden-sm-down text-center" style="display: none;"><span class="noise-explain">' . e($tire->noise) . '</span></div>';

    return $html;
  }

  protected function renderAutoTireGridCardCartButton(Autotire $tire): string
  {
    $html = '<span class="grid-card-cart-wrap" data-toggle="tooltip" title="<span style=\'color: black\'>Pievienot grozam</span>">';
    if (Auth::check() && Auth::user()->hasRole('administrators')) {
      $html .= '<button class="cart-shopping-button" data-toggle="modal" data-target="#" data-info="' . $tire->tire_id . '" data-url="' . $tire->hydrated_cart_link . '" onclick="event.preventDefault()"><i class="material-icons">add_shopping_cart</i></button>';
    } else {
      $html .= '<button class="cart-shopping-button" data-toggle="modal" data-target="#blockcart-modal" data-info="' . $tire->tire_id . '" data-url="' . $tire->hydrated_cart_link . '" onclick="event.preventDefault()"><i class="material-icons">add_shopping_cart</i></button>';
    }
    $html .= '</span>';

    return $html;
  }

  protected function renderAutoTireGridCardFooter(Autotire $tire, array $selectedTires, int $season): string
  {
    $isSelected = in_array((string) $tire->tire_id, $selectedTires, true);
    $html = '<div class="grid-card-footer">';
    $html .= '<input type="checkbox" name="product_ids[]" value="' . $tire->tire_id . '" class="tire-table-checkbox grid-card-checkbox"'
      . ($isSelected ? ' checked' : '')
      . ' data-code="' . e($tire->code) . '"'
      . ' data-type="' . e($tire->type) . '"'
      . ' data-fuel="' . e($tire->eco) . '"'
      . ' data-wet="' . e($tire->wet) . '"'
      . ' data-noise="' . e($tire->noise) . '"'
      . ' data-availability="' . e($tire->dotAvailable) . '">';
    $html .= '<div class="grid-card-prices">';
    $html .= '<div class="rim-price-old">€' . e($tire->price1) . '</div>';
    $html .= '<div class="rim-price-red">€' . e($tire->price2) . '</div>';
    $html .= '</div>';
    $html .= $this->renderAutoTireGridCardHiddenMeta($tire, $season);
    $html .= '<div class="grid-card-actions">';
    $html .= $this->renderAutoTireGridCardCartButton($tire);
    $html .= '<span class="tippy lisi-tooltip grid-dot ' . $tire->dotAvailable . ' ' . $tire->stockCount . '" data-color="' . $tire->dotAvailable . '" data-tippy-content=\'<div style="padding: 5px;"><span style="color: black; font-size: 15px;">' . e($tire->stockAvailability) . '</span></div>\'></span>';
    $html .= '<span class="sort-order" style="display: none;">' . e($tire->dotAvailable) . '</span>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
  }

  protected function renderAutoTireGridCard(Autotire $tire, array $selectedTires, int $season, int $cartAddQty = 4): string
  {
    $isSelected = in_array((string) $tire->tire_id, $selectedTires, true);
    $html = '<a href="' . $tire->getUrl . '" class="grid-view-link" data-article="' . e($tire->article) . '" data-quantity="' . $cartAddQty . '">';
    $html .= '<div class="tire-image-card sort-order' . ($isSelected ? ' selected' : '') . '">';
    $html .= '<div class="text-center image-grid-overflow">';
    $html .= Image::showGrid('auto', $tire->make_id);
    $html .= '</div><div class="tire-list-caption">';
    $html .= '<div class="card-title-text"><span class="tippy lisi-tooltip" data-content="' . e($tire->fullName) . '" data-tippy-content="<div style=\'padding: 5px;\'><span style=\'color: black; font-size: 15px;\'>' . e($tire->fullTitle) . '</span></div>">' . e($tire->fullTitle) . '</span></div>';
    $html .= '<div class="tire-tread"><b>' . e($tire->fullSize) . ' </b>';
    $html .= '<span data-toggle="tooltip" title="<span style=\'color: black\'>' . e($tire->lisiDesc) . '</span>">' . e($tire->li . $tire->si) . ' </span>';
    $html .= '<span class="tire-image-code code-explain">' . e($tire->code) . '</span></div>';
    $html .= $this->renderAutoTireGridCardFooter($tire, $selectedTires, $season);
    $html .= '</div></div></a>';

    return $html;
  }

  protected function tireApiCountCacheKey(int $season, array $filters): string
  {
    return 'autotire_api_total_v1_' . Cache::get('autotire_api_count_version', 1) . '_s' . $season . '_' . md5(json_encode($filters));
  }

  protected function buildAutotireUrl(int $season, string $brandSlug, string $treadTitle, int $tireId): string
  {
    $prefix = $season === 1 ? '/vasaras-riepas' : '/ziemas-riepas';

    return $prefix . '/' . $brandSlug . '/' . strtolower(str_replace('/', '_', $treadTitle)) . '/' . $tireId;
  }

  protected function prepareAutotiresForApiList($tires, int $season): void
  {
    Autotire::preloadStockData($tires->pluck('tire_id')->all());

    foreach ($tires as $tire) {
      $this->hydrateAutotireForApiResponse($tire, $season);
    }
  }

  protected function getTirePriceMap(array $tireIds): array
  {
    if ($tireIds === []) {
      return [];
    }

    return Autotire::query()
      ->whereIn('tire_id', $tireIds)
      ->get(['tire_id', 'price1', 'price2'])
      ->mapWithKeys(function ($tire) {
        return [$tire->tire_id => (int) ($tire->price2 ?? $tire->price1 ?? 0)];
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
        $html .= $offset + 1 . ' lؤ«dz ' . $totalItems . ' ieraksti no ' . $totalItems . ' ierakstiem';
      } else {
        $html .= $offset + 1 . ' lؤ«dz ' . ($offset + $perPage) . ' ieraksti no ' . $totalItems . ' ierakstiem';
      }
      $html .= '</div>';
      $html .= '<ul class="pagination">';

      // Previous button
      $html .= '<li class="paginate_button page-item previous ';
      $html .= ($page == 1) ? 'disabled' : '';
      $html .= '">';
      $html .= '<a href="#" class="page-link">Atpakaؤ¼</a>';
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
      $html .= '<a href="#" class="page-link">Uz priekإ،u</a>';
      $html .= '</li>';

      $html .= '</ul>';
      $html .= '</div>';
      $html .= '</div>';
    }

    return $html;
  }

  /**
   * Fills computed fields for catalog HTML without N+1 (brand already selected as api_brand_title / api_brand_slug).
   */
  private function hydrateAutotireForApiResponse(Autotire $tire, int $season): void
  {
    $tire->includeStock = true;
    $tire->fullTitle = $tire->api_brand_title . ' ' . $tire->t_title;
    $tire->fullSize = $tire->getFullSizeAttribute();
    $tire->fullName = $tire->fullTitle . ' ' . $tire->fullSize . ' ' . $tire->code . ' ' . $tire->li . $tire->si;
    $tire->getUrl = $this->buildAutotireUrl($season, $tire->api_brand_slug, $tire->t_title, $tire->tire_id);
    $tire->setAttribute('hydrated_cart_link', $tire->getUrl);
    $tire->lisiDesc = $tire->lisiDesc($tire->li, $tire->si);
    $tire->codeExplain = $tire->getCodeExplainAttribute();
    $dotAvailable = $tire->getDotAvailableAttribute();
    $tire->dotAvailable = $dotAvailable;
    $tire->stockAvailability = $tire->resolveStockAvailability($dotAvailable);
    $tire->stockCount = $tire->getStockCount();
  }

}

