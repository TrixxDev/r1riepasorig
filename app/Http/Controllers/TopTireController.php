<?php

namespace App\Http\Controllers;

use App\Models\Autotire;
use App\Models\Bigtire;
use App\Models\Code;
use App\Models\Moto;
use App\Models\Quadr;
use App\Models\Rim;
use App\Models\Stud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class TopTireController extends Controller
{

  public array $categories = [];
  public string $summerURL = 'vasaras-riepa';
  public string $winterURL = 'ziemas-riepa';

  public function __construct() {
    $this->categories = $this->buildCategoriesList();

    View::share('summerURL', $this->summerURL);
    View::share('winterURL', $this->winterURL);
    View::share('categories', $this->categories);
  }

  protected function buildCategoriesList(): array
  {
    $categories = [];

    if (config('site.season') === 1) {
      $categories[] = ['name' => 'Vasaras riepas', 'class' => 'autoTiresSummer'];
      $categories[] = ['name' => 'Ziemas riepas', 'class' => 'autoTiresWinter'];
    } else {
      $categories[] = ['name' => 'Ziemas riepas', 'class' => 'autoTiresWinter'];
      $categories[] = ['name' => 'Vasaras riepas', 'class' => 'autoTiresSummer'];
    }

    if ($this->hasSaleAlloyRims()) {
      $categories[] = ['name' => 'Lietie diski', 'class' => 'alloyRims'];
    }
    if ($this->hasSaleMotoTires()) {
      $categories[] = ['name' => 'Motociklu riepas', 'class' => 'motoTires'];
    }
    if ($this->hasSaleQuadrTires()) {
      $categories[] = ['name' => 'Kvadraciklu riepas', 'class' => 'quadrTires'];
    }
    if ($this->hasSaleBigTires()) {
      $categories[] = ['name' => 'Lielās riepas', 'class' => 'bigTires'];
    }
    if ($this->hasSaleStuds()) {
      $categories[] = ['name' => 'Skrūvējamās radzes', 'class' => 'studs'];
    }

    return $categories;
  }

  protected function hasSaleAlloyRims(): bool
  {
    return Rim::where('priceoffer', 1)
      ->where('visible_users', '<>', 0)
      ->exists();
  }

  protected function hasSaleMotoTires(): bool
  {
    return Moto::where('priceoffer', 1)
      ->where('visible_users', 1)
      ->exists();
  }

  protected function hasSaleQuadrTires(): bool
  {
    return Quadr::where('priceoffer', 1)
      ->where('visible_users', '<>', 0)
      ->exists();
  }

  protected function hasSaleBigTires(): bool
  {
    return Bigtire::where('priceoffer', 1)
      ->where('visible_users', '<>', 0)
      ->exists();
  }

  protected function hasSaleStuds(): bool
  {
    return Stud::where('priceoffer', 1)
      ->where('visible_users', '<>', 0)
      ->exists();
  }

  public function index() {
    $sections = $this->buildSaleSections();

    return view('sales', compact('sections'));
  }

  protected function buildSaleSections(): array
  {
    $sections = [];

    if (config('site.season') === 1) {
      $sections[] = $this->autoTiresSummer();
      $sections[] = $this->autoTiresWinter();
    } else {
      $sections[] = $this->autoTiresWinter();
      $sections[] = $this->autoTiresSummer();
    }

    $sections[] = $this->alloyRims();
    $sections[] = $this->motoTires();
    $sections[] = $this->quadrTires();
    $sections[] = $this->bigTires();
    $sections[] = $this->studs();

    return $sections;
  }

  public function autoTiresSummer()
  {
    $tires = $this->loadSaleAutoTires(1);
    $this->prepareSaleAutoTires($tires);

    $cartQty = 4;

    if ($tires->count() > 0) {
      return view('tires.auto.seasonsales.summer', compact('tires', 'cartQty'));
    }

    return null;
  }

  public function autoTiresWinter()
  {
    $tires = $this->loadSaleAutoTires(2);
    $this->prepareSaleAutoTires($tires);

    $cartQty = 4;

    if ($tires->count() > 0) {
      return view('tires.auto.seasonsales.winter', compact('tires', 'cartQty'));
    }

    return null;
  }

  protected function loadSaleAutoTires(int $season)
  {
    return Autotire::selectRaw('auto_tires.*, auto_treads.*, auto_brands.title as api_brand_title, auto_brands.slug as api_brand_slug')
      ->leftJoin('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
      ->leftJoin('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
      ->where('auto_tires.priceoffer', 1)
      ->where('auto_treads.season', $season)
      ->where('auto_tires.visible_users', '<>', 0)
      ->groupBy('auto_tires.article')
      ->orderBy('d3', 'ASC')
      ->orderBy('d1', 'ASC')
      ->orderBy('d2', 'ASC')
      ->orderBy('price2', 'DESC')
      ->groupBy('auto_tires.tire_id')
      ->get();
  }

  protected function prepareSaleAutoTires($tires): void
  {
    Autotire::preloadStockData($tires->pluck('tire_id')->all());

    foreach ($tires as $tire) {
      $tire->includeStock = true;

      $brandTitle = (string) ($tire->api_brand_title ?? '');
      $treadTitle = (string) ($tire->t_title ?? '');
      $fullTitle = trim($brandTitle . ' ' . $treadTitle);
      $fullSize = $tire->d1 . '/' . $tire->d2 . ' R' . $tire->d3;

      $tire->setAttribute('sale_title', $fullTitle);
      $tire->setAttribute('sale_full_name', trim($fullTitle . ' ' . $fullSize . ' ' . $tire->code . ' ' . $tire->li . $tire->si));
      $tire->setAttribute('brand_slug', $tire->api_brand_slug ?: Str::slug($brandTitle));
      $tire->setAttribute('tread_slug', strtolower(str_replace('/', '_', $treadTitle)));
      $tire->setAttribute('code_explain', $tire->getCodeExplainAttribute());

      $dotAvailable = $tire->getDotAvailableAttribute();
      $tire->setAttribute('sale_dot_available', $dotAvailable);
      $tire->setAttribute('sale_stock_availability', $tire->resolveStockAvailability($dotAvailable));
      $tire->setAttribute('sale_stock_count', $tire->getStockCount());
    }
  }

  public function alloyRims()
  {
    $rims = Rim::select('rims.*', 'rim_makes.title as tread_title', 'rim_brands.title as brand_title')
      ->leftJoin('rim_makes', 'rims.make_id', '=', 'rim_makes.make_id')
      ->leftJoin('rim_brands', 'rim_makes.brand_id', '=', 'rim_brands.brand_id')
      ->where('rims.priceoffer', 1)
      ->where('rims.visible_users', '<>', 0)
      ->groupBy('rims.article')
      ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
      ->orderBy('price3', 'DESC')
      ->get();

    $this->prepareSaleRims($rims);

    $cartQty = 4;

    if ($rims->count() > 0) {
      return view('rims.auto.sales', compact('rims', 'cartQty'));
    }

    return null;
  }

  protected function prepareSaleRims($rims): void
  {
    Rim::preloadStockData($rims->pluck('rim_id')->all());
    Rim::preloadMakeData($rims->pluck('make_id')->filter()->unique()->values()->all());

    foreach ($rims as $rim) {
      $rim->includeStock = true;

      $brandTitle = (string) ($rim->brand_title ?? '');
      $treadTitle = (string) ($rim->tread_title ?? '');

      if ($brandTitle !== '') {
        $rim->setAttribute('brandTitle', $brandTitle);
      }
      if ($treadTitle !== '') {
        $rim->setAttribute('treadTitle', $treadTitle);
      }

      $rim->setAttribute('fullTitle', trim($brandTitle . ' ' . $treadTitle));
      $rim->setAttribute(
        'fullName',
        trim($brandTitle . ' ' . $treadTitle . ' ' . $rim->skr . 'x' . $rim->pcd . ' R' . $rim->d3 . ' ' . $rim->d1 . 'J et' . $rim->et . ' ' . $rim->dc . ' ' . $rim->color)
      );

      $dotAvailable = $rim->getDotAvailableAttribute();
      $rim->setAttribute('dotAvailable', $dotAvailable);
      $rim->setAttribute('stockAvailability', $rim->getStockAvailabilityAttribute());
      $rim->setAttribute('stockCount', $rim->getStockCount());
    }
  }

  public function motoTires()
  {
    $tires = $this->loadSaleMotoTires();
    $this->prepareSaleMotoTires($tires);

    $cartQty = 1;

    if ($tires->count() > 0) {
      return view('tires.moto.sales', compact('tires', 'cartQty'));
    }

    return null;
  }

  protected function loadSaleMotoTires()
  {
    return Moto::selectRaw('moto_tires.*, moto_treads.title as t_title, moto_brands.title as api_brand_title, moto_brands.slug as api_brand_slug')
      ->leftJoin('moto_treads', 'moto_tires.make_id', '=', 'moto_treads.tread_id')
      ->leftJoin('moto_brands', 'moto_treads.brand_id', '=', 'moto_brands.brand_id')
      ->where('moto_tires.visible_users', 1)
      ->where('moto_tires.priceoffer', 1)
      ->groupBy('moto_tires.article')
      ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
      ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
      ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
      ->orderBy('d4', 'ASC')
      ->orderBy('price2', 'DESC')
      ->get();
  }

  protected function prepareSaleMotoTires($tires): void
  {
    Moto::preloadStockData($tires->pluck('tire_id')->all());

    foreach ($tires as $tire) {
      $tire->includeStock = true;

      $brandTitle = (string) ($tire->api_brand_title ?? '');
      $treadTitle = (string) ($tire->t_title ?? '');
      $fullTitle = trim($brandTitle . ' ' . $treadTitle);
      $fullSize = $tire->getFullSizeAttribute();
      $typeDesc = $this->resolveMotoTypeDesc((string) $tire->type);

      $tire->setAttribute('sale_title', $fullTitle);
      $tire->setAttribute('sale_full_name', trim($fullTitle . ' ' . $fullSize . ' ' . $tire->code . ' ' . $tire->li . $tire->si));
      $tire->setAttribute('brand_slug', $tire->api_brand_slug ?: Str::slug($brandTitle));
      $tire->setAttribute('tread_slug', strtolower(str_replace('/', '_', $treadTitle)));
      $tire->setAttribute('sale_moto_type', $typeDesc[0]);
      $tire->setAttribute('sale_type_desc', $typeDesc[1]);
      $tire->setAttribute('sale_lisi_desc', $tire->lisiDesc($tire->li, $tire->si));
      $tire->setAttribute('code_explain', $tire->getCodeExplainAttribute());

      $dotAvailable = $tire->getDotAvailableAttribute();
      $tire->setAttribute('sale_dot_available', $dotAvailable);
      $tire->setAttribute('sale_stock_availability', $tire->resolveStockAvailability($dotAvailable));
      $tire->setAttribute('sale_stock_count', $tire->getStockCount());
    }
  }

  protected function resolveMotoTypeDesc(string $type): array
  {
    $type = strtolower(trim($type));

    if ($type === '' || $type === '1') {
      return ['', 'Nav'];
    }

    $map = [
      'custom' => ['Ct', 'Custom'],
      'harley davidson' => ['Hd', 'Harley Davidson'],
      'moto cross' => ['Mx', 'Moto Cross'],
      'racing' => ['Rc', 'Racing'],
      'sport' => ['Sp', 'Sport'],
      'sport touring' => ['St', 'Sport Touring'],
      'trail' => ['Tr', 'Trail'],
      'scooter' => ['Sc', 'Scooter'],
    ];

    return $map[$type] ?? ['', ''];
  }

  public function quadrTires()
  {
    $tires = $this->loadSaleQuadrTires();
    $this->prepareSaleQuadrTires($tires);

    $cartQty = 2;

    if ($tires->count() > 0) {
      return view('tires.quadr.sales', compact('tires', 'cartQty'));
    }

    return null;
  }

  protected function loadSaleQuadrTires()
  {
    return Quadr::selectRaw('quadr_tires.*, quadr_treads.t_title, quadr_treads.brand_id, quadr_brands.b_title as api_brand_title, quadr_brands.slug as api_brand_slug')
      ->leftJoin('quadr_treads', 'quadr_tires.make_id', '=', 'quadr_treads.tread_id')
      ->leftJoin('quadr_brands', 'quadr_treads.brand_id', '=', 'quadr_brands.brand_id')
      ->where('quadr_tires.priceoffer', 1)
      ->where('quadr_tires.visible_users', '<>', 0)
      ->groupBy('quadr_tires.article')
      ->orderBy('d3', 'ASC')
      ->orderBy('d1', 'ASC')
      ->orderBy('d2', 'ASC')
      ->orderBy('price2', 'DESC')
      ->get();
  }

  protected function prepareSaleQuadrTires($tires): void
  {
    Quadr::preloadStockData($tires->pluck('tire_id')->all());

    foreach ($tires as $tire) {
      $tire->includeStock = true;

      $brandTitle = (string) ($tire->api_brand_title ?? '');
      $treadTitle = (string) ($tire->t_title ?? '');
      $fullTitle = trim($brandTitle . ' ' . $treadTitle);
      $fullSize = $tire->getFullSizeAttribute();

      $tire->setAttribute('sale_title', $fullTitle);
      $tire->setAttribute('sale_full_name', trim($fullTitle . ' ' . $fullSize . ' ' . $tire->comment . ' ' . $tire->li . $tire->si));
      $tire->setAttribute('brand_slug', $tire->api_brand_slug ?: Str::slug($brandTitle));
      $tire->setAttribute('tread_slug', strtolower(str_replace('/', '_', $treadTitle)));

      $dotAvailable = $tire->getDotAvailableAttribute();
      $tire->setAttribute('sale_dot_available', $dotAvailable);
      $tire->setAttribute('sale_stock_availability', $tire->getStockAvailabilityAttribute());
      $tire->setAttribute('sale_stock_count', $tire->getStockCount());
    }
  }

  public function bigTires()
  {
    $tires = $this->loadSaleBigTires();
    $this->prepareSaleBigTires($tires);

    $cartQty = 1;
    $code_array = $this->getCodeArray();

    if ($tires->count() > 0) {
      return view('tires.industrial.sales', compact('tires', 'cartQty', 'code_array'));
    }

    return null;
  }

  protected function loadSaleBigTires()
  {
    return Bigtire::selectRaw('big_tires.*, bigtire_treads.title as t_title, bigtire_brands.title as api_brand_title, bigtire_brands.slug as api_brand_slug')
      ->leftJoin('bigtire_treads', 'big_tires.make_id', '=', 'bigtire_treads.tread_id')
      ->leftJoin('bigtire_brands', 'bigtire_treads.brand_id', '=', 'bigtire_brands.brand_id')
      ->where('big_tires.priceoffer', 1)
      ->where('big_tires.visible_users', '<>', 0)
      ->groupBy('big_tires.article')
      ->orderBy('d3', 'ASC')
      ->orderBy('d1', 'ASC')
      ->orderBy('d2', 'ASC')
      ->orderBy('quantity', 'DESC')
      ->get();
  }

  protected function prepareSaleBigTires($tires): void
  {
    Bigtire::preloadStockData($tires->pluck('tire_id')->all());

    foreach ($tires as $tire) {
      $tire->includeStock = true;

      $brandTitle = (string) ($tire->api_brand_title ?? '');
      $treadTitle = (string) ($tire->t_title ?? '');
      $fullTitle = trim($brandTitle . ' ' . $treadTitle);
      $fullSize = $tire->getFullSizeAttribute();

      $tire->setAttribute('sale_title', $fullTitle);
      $tire->setAttribute('sale_full_name', trim($fullTitle . ' ' . $fullSize . ' ' . $tire->code . 'PR ' . $tire->li . $tire->si));
      $tire->setAttribute('brand_slug', $tire->api_brand_slug ?: Str::slug($brandTitle));
      $tire->setAttribute('tread_slug', strtolower(str_replace('/', '_', $treadTitle)));
      $tire->setAttribute('sale_lisi_desc', $tire->lisiDesc($tire->li, $tire->si));

      $dotAvailable = $tire->getDotAvailableAttribute();
      $tire->setAttribute('sale_dot_available', $dotAvailable);
      $tire->setAttribute('sale_stock_availability', $tire->getStockAvailabilityAttribute());
      $tire->setAttribute('sale_stock_count', $tire->getStockCount());
    }
  }

  protected function getCodeArray(): array
  {
    return Cache::remember('codes_table_map', 3600, function () {
      $codeArray = [];
      foreach (Code::all() as $code) {
        $codeArray[$code->name] = $code->explanation;
      }
      return $codeArray;
    });
  }

  public function studs()
  {
    $studs = $this->loadSaleStuds();
    $this->prepareSaleStuds($studs);

    $cartQty = 1;
    $length = [1, 2, 3, 4, 5, 6, 7, 8, 9];

    if ($studs->count() > 0) {
      return view('studs.sales', compact('studs', 'length', 'cartQty'));
    }

    return null;
  }

  protected function loadSaleStuds()
  {
    return Stud::selectRaw('studs.*, studs_treads.t_title, studs_brands.b_title as brand_title')
      ->leftJoin('studs_treads', 'studs.make_id', '=', 'studs_treads.tread_id')
      ->leftJoin('studs_brands', 'studs_treads.brand_id', '=', 'studs_brands.brand_id')
      ->where('studs.priceoffer', 1)
      ->where('studs.visible_users', '<>', 0)
      ->groupBy('studs.article')
      ->orderBy('price2', 'DESC')
      ->get();
  }

  protected function prepareSaleStuds($studs): void
  {
    foreach ($studs as $stud) {
      $brandTitle = (string) ($stud->brand_title ?? '');
      $treadTitle = (string) ($stud->t_title ?? '');
      $fullName = trim($brandTitle . ' ' . $treadTitle);

      $stud->setAttribute('sale_full_name', $fullName);
      $stud->setAttribute('brand_slug', $brandTitle);
      $stud->setAttribute('tread_slug', strtolower(str_replace('/', '_', $treadTitle)));

      $dotAvailable = ((int) $stud->quantity >= 1) ? 'green' : 'red';
      $stud->setAttribute('sale_dot_available', $dotAvailable);
      $stud->setAttribute('sale_stock_availability', $this->resolveStudStockAvailability($stud, $dotAvailable));
    }
  }

  protected function resolveStudStockAvailability(Stud $stud, string $dotAvailable): string
  {
    if ($stud->urs_quantity >= 1) {
      $availability = '<p>Ulbrokā: 1 un vairāk</p><br>';
    } else {
      $availability = '<p>Ulbrokā: ' . $stud->urs_quantity . '</p><br>';
    }

    if ($stud->krs_quantity >= 1) {
      $availability .= '<p>Kalnciema ielā: 1 un vairāk</p>';
    } else {
      $availability .= '<p>Kalnciema ielā: ' . $stud->krs_quantity . '</p>';
    }

    if (Auth::check() && Auth::user()->hasRole(['administrators', 'moderators'])) {
      $availability = '<p>Ulbrokā: ' . $stud->urs_quantity . '</p><br>';
      $availability .= '<p>Kalnciema ielā: ' . $stud->krs_quantity . '</p>';
    } elseif ($dotAvailable === 'red') {
      $availability = '<p style="text-align: center;">Nepieciešams<br>pārbaudīt pieejamību.</p>';
    } elseif ($dotAvailable === 'yellow' || $dotAvailable === 'half-yellow') {
      $availability = '<p style="text-align: center;">Riepas pieejamas partneru noliktavās<br>Piegāde 1 darbadienas laikā.</p>';
    }

    return $availability;
  }

  public function filter(Request $request) {

    $categoryId = (int) $request->ct;

    $className = $this->categories[$categoryId]['class'];

    $view = $this->$className();

    return view('salesCt', compact('view', 'categoryId'));
  }
}
