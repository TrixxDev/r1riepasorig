<?php

namespace App\Models;

use App\Helper\PartnerDelivery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Bigtire extends Model
{

    protected $table = 'big_tires';

    protected $primaryKey = 'tire_id';

    protected $fillable = ['visible_users', 'visible_list'];

    public $timestamps = false;
    public static $url;

    use HasFactory;

    private const CARRY_CAPS = [
        50 => '50 - 190 kg', 51 => '51 - 195 kg', 52 => '52 - 200 kg', 53 => '53 - 206 kg',
        54 => '54 - 212 kg', 55 => '55 - 218 kg', 56 => '56 - 224 kg', 57 => '57 - 230 kg',
        58 => '58 - 236 kg', 59 => '59 - 243 kg', 60 => '60 - 250 kg', 61 => '61 - 257 kg',
        62 => '62 - 265 kg', 63 => '63 - 272 kg', 64 => '64 - 280 kg', 65 => '65 - 290 kg',
        66 => '66 - 300 kg', 67 => '67 - 307 kg', 68 => '68 - 315 kg', 69 => '69 - 325 kg',
        70 => '70 - 335 kg', 71 => '71 - 345 kg', 72 => '72 - 355 kg', 73 => '73 - 365 kg',
        74 => '74 - 375 kg', 75 => '75 - 387 kg', 76 => '76 - 400 kg', 77 => '77 - 412 kg',
        78 => '78 - 425 kg', 79 => '79 - 437 kg', 80 => '80 - 450 kg', 81 => '81 - 462 kg',
        82 => '82 - 475 kg', 83 => '83 - 487 kg', 84 => '84 - 500 kg', 85 => '85 - 515 kg',
        86 => '86 - 530 kg', 87 => '87 - 545 kg', 88 => '88 - 560 kg', 89 => '89 - 580 kg',
        90 => '90 - 600 kg', 91 => '91 - 615 kg', 92 => '92 - 630 kg', 93 => '93 - 650 kg',
        94 => '94 - 670 kg', 95 => '95 - 690 kg', 96 => '96 - 710 kg', 97 => '97 - 730 kg',
        98 => '98 - 750 kg', 99 => '99 - 775 kg', 100 => '100 - 800 kg', 101 => '101 - 825 kg',
        102 => '102 - 850 kg', 103 => '103 - 875 kg', 104 => '104 - 900 kg', 105 => '105 - 925 kg',
        106 => '106 - 950 kg', 107 => '107 - 975 kg', 108 => '108 - 1000 kg', 109 => '109 - 1030 kg',
        110 => '110 - 1060 kg', 111 => '111 - 1090 kg', 112 => '112 - 1120 kg', 113 => '113 - 1150 kg',
        114 => '114 - 1180 kg', 115 => '115 - 1215 kg', 116 => '116 - 1250 kg', 117 => '117 - 1285 kg',
        118 => '118 - 1320 kg', 119 => '119 - 1360 kg', 120 => '120 - 1400 kg', 121 => '121 - 1450 kg',
        122 => '122 - 1500 kg', 123 => '123 - 1550 kg', 124 => '124 - 1600 kg', 125 => '125 - 1650 kg',
        126 => '126 - 1700 kg', 127 => '127 - 1750 kg', 128 => '128 - 1800 kg', 129 => '129 - 1850 kg',
        130 => '130 - 1900 kg', 131 => '131 - 1950 kg', 132 => '132 - 2000 kg', 133 => '133 - 2065 kg',
        134 => '134 - 2125 kg', 135 => '135 - 2185 kg', 136 => '136 - 2245 kg', 137 => '137 - 2305 kg',
        138 => '138 - 2365 kg', 139 => '139 - 2435 kg',
    ];

    private const SPEED_CAPS = [
        'A1' => 'A1 - 5 Km/h', 'A2' => 'A2 - 10 Km/h', 'A3' => 'A3 - 15 Km/h', 'A4' => 'A4 - 20 Km/h',
        'A5' => 'A5 - 25 Km/h', 'A6' => 'A6 - 30 Km/h', 'A7' => 'A7 - 35 Km/h', 'A8' => 'A8 - 40 Km/h',
        'B' => 'B - 50 Km/h', 'C' => 'C - 60 Km/h', 'D' => 'D - 65 Km/h', 'E' => 'E - 70 Km/h',
        'F' => 'F - 80 Km/h', 'G' => 'G - 90 Km/h', 'J' => 'J - 100 Km/h', 'K' => 'K - 110 Km/h',
        'L' => 'L - 120 Km/h', 'M' => 'M - 130 Km/h', 'N' => 'N - 140 Km/h', 'P' => 'P - 150 Km/h',
        'Q' => 'Q - 160 Km/h', 'R' => 'R - 170 Km/h', 'S' => 'S - 180 Km/h', 'T' => 'T - 190 Km/h',
        'U' => 'U - 200 Km/h', 'H' => 'H - 210 Km/h', 'V' => 'V - 240 Km/h', 'VR' => 'VR - Virs 210 Km/h',
        'W' => 'W - 270 Km/h', 'Z' => 'Z - Virs 240 Km/h', 'Y' => 'Y - 300 Km/h', 'ZR' => 'ZR - Virs 240 Km/h',
    ];

  /**
   * @var mixed
   */
  public $_includeStock = true;

  protected $stockCountMemo = null;

  protected $dotAvailableMemo = null;

  protected $stockAvailabilityMemo = null;

  protected static $stockTotals = [];

  protected static $stockRows = [];

  protected static $starcoCatalogByArticle = null;

  public static function preloadStockData(array $tireIds): void
  {
    self::$stockTotals = [];
    self::$stockRows = [];

    if ($tireIds === []) {
      return;
    }

    self::$stockTotals = DB::table('bigtire_stock')
      ->whereIn('tire_id', $tireIds)
      ->selectRaw('tire_id, SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END) as total')
      ->groupBy('tire_id')
      ->pluck('total', 'tire_id')
      ->map(fn ($total) => (int) $total)
      ->all();

    self::$stockRows = Bigstock::whereIn('tire_id', $tireIds)
      ->get()
      ->groupBy('tire_id')
      ->all();
  }

  public static function clearStockCache(): void
  {
    self::$stockTotals = [];
    self::$stockRows = [];
    self::$starcoCatalogByArticle = null;
    Cache::forget('bigtire_starco_catalog_by_article');
  }

  public static function clearCatalogCache(): void
  {
    Cache::forget('big_tire_brands_list');
    Cache::forget('big_tire_filter_options');
    foreach (['d1', 'd2', 'd3'] as $column) {
      Cache::forget('big_tires_sizes_' . $column);
    }
    self::clearStockCache();
  }

  public function setIncludeStockAttribute($value)
  {
    return $this->_includeStock = $value;
  }

  public function tread()
  {
    return $this->belongsTo(Bigtread::class, 'make_id', 'tread_id');
  }

  public function stocks()
  {
    return $this->hasMany(Bigstock::class, 'tire_id', 'tire_id');
  }

  protected function resolveTreadWithBrand(): ?Bigtread
  {
    if ($this->relationLoaded('tread')) {
      $tread = $this->tread;
      if ($tread && !$tread->relationLoaded('brand') && $tread->brand_id) {
        $tread->load('brand');
      }
      return $tread;
    }

    return Bigtread::with('brand')->where('tread_id', $this->make_id)->first();
  }

  public function getImageAttribute()
  {
    $fileName = '/storage/app/public/industrial/tread/' . $this->tread_id . '.jpg';
    $dirname = dirname(__DIR__, 2) . $fileName;
    if (file_exists($dirname)) {
      if (filesize($dirname) !== 0) {
        return $fileName;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public function getFullSizeAttribute()
  {
    if (empty($this->sep2) && empty($this->d2)) {
      $size = $this->d1 . $this->sep . $this->d3;
    } else {
      $size = $this->d1 . $this->sep . $this->d2 . $this->sep2 . $this->d3;
    }
    return strtoupper($size);
  }

  public function getOfferPriceAttribute()
  {
    if ($this->price3 == null) {
      return $this->price1;
    } else {
      return $this->price3;
    }
  }

  public function getAutoCommentAttribute()
  {
    return $this->comment;
  }

  public function getStockCount()
  {
    if ($this->stockCountMemo !== null) {
      return $this->stockCountMemo;
    }

    if (array_key_exists($this->tire_id, self::$stockTotals)) {
      $count = self::$stockTotals[$this->tire_id];
    } elseif ($this->relationLoaded('stocks')) {
      $count = $this->stocks->where('quantity', '>=', 1)->sum('quantity');
    } else {
      $count = (int) Bigstock::where('tire_id', $this->tire_id)->where('quantity', '>=', 1)->sum('quantity');
    }

    $this->stockCountMemo = $count;

    return $count;
  }

  public function getStockCountAttribute()
  {
    return $this->getStockCount();
  }

  public static function StockLink($tire)
  {
    if (array_key_exists($tire->tire_id, self::$stockRows)) {
      $stocks = self::$stockRows[$tire->tire_id];
    } elseif ($tire->relationLoaded('stocks')) {
      $stocks = $tire->stocks;
    } else {
      $stocks = Bigstock::where('tire_id', $tire->tire_id)->get();
    }

    $urls = [];

    foreach ($stocks as $stock) {
      switch ($stock->itype) {
        case 'i3': {
          $urls['Latakko'] = ['link' => 'https://shop.latakko.eu/product/' . $stock->article, 'remaining' => $stock->quantity];
          break;
        }
        case 'starco': {
          $urls['Starco'] = [
            'link' => self::resolveStarcoProductUrl((string) $stock->article),
            'remaining' => $stock->quantity,
          ];
          break;
        }
      }
    }

    return $urls;
  }

  protected static function loadStarcoCatalogByArticle(): array
  {
    if (self::$starcoCatalogByArticle !== null) {
      return self::$starcoCatalogByArticle;
    }

    self::$starcoCatalogByArticle = Cache::remember('bigtire_starco_catalog_by_article', 3600, function () {
      $path = public_path('starco.sync.xml');
      if (!is_file($path)) {
        return [];
      }

      $decoded = json_decode(@file_get_contents($path), true);
      if (!is_array($decoded)) {
        return [];
      }

      $map = [];
      foreach ($decoded as $item) {
        if (!isset($item['product_no'])) {
          continue;
        }
        $map[(string) $item['product_no']] = $item;
      }

      return $map;
    });

    return self::$starcoCatalogByArticle;
  }

  protected static function resolveStarcoProductUrl(string $article): string
  {
    $searchUrl = 'https://shop.bohnenkamp-baltic.com/catalogsearch/result/?q=' . rawurlencode($article);
    $catalog = self::loadStarcoCatalogByArticle();

    if (!isset($catalog[$article]['name'])) {
      return $searchUrl;
    }

    $slug = Str::slug(str_replace(['/', '.'], '-', (string) $catalog[$article]['name']));
    if ($slug === '') {
      return $searchUrl;
    }

    return 'https://shop.bohnenkamp-baltic.com/' . $slug . '.html';
  }

  public function getAvailableAttribute()
  {
    switch ($this->quantity) {
      case 1: {
        return 'Pēdējā';
      }
      case 2: {
        return 'Pēdējās 2';
      }
      case 3: {
        return 'Pēdējās 3';
      }
      case -1:
      case 0: {
        if ($this->_includeStock) {
          $count = $this->getStockCount();
          switch ($count){
            case 1: {
              return 'Pēdējā';
            }
            case 2: {
              return 'Pēdējās 2';
            }
            case 3: {
              return 'Pēdējās 3';
            }
            case -1:
            case 0:{
              return 'Zvaniet!';
            }
            default:{
              return 'Pieejams';
            }
          }
        } else {
          return 'Zvaniet!';
        }
      }
      default: {
        return 'Pieejams';
      }
    }
  }

  protected function resolveDotColorFromStockCount(int $count): string
  {
    switch ($count) {
      case -1:
      case 0:
        return 'red';
      case 1:
      case 2:
      case 3:
        return 'half-yellow';
      default:
        return 'yellow';
    }
  }

  public function getDotAvailableAttribute()
  {
    if ($this->dotAvailableMemo !== null) {
      return $this->dotAvailableMemo;
    }

    if ($this->urs_quantity > 0 && $this->krs_quantity <= 0) {
      $this->quantity = $this->urs_quantity;
    } else if ($this->urs_quantity <= 0 && $this->krs_quantity > 0) {
      $this->quantity = $this->krs_quantity;
    }

    if ($this->quantity < 0 && $this->getStockCount() > 0) {
      if ($this->_includeStock) {
        $this->dotAvailableMemo = $this->resolveDotColorFromStockCount($this->getStockCount());
      } else {
        $this->dotAvailableMemo = 'red';
      }

      return $this->dotAvailableMemo;
    }

    switch ($this->quantity) {
      case 1:
      case 2:
      case 3:
        $this->dotAvailableMemo = 'half-green';
        break;
      case -1:
      case 0:
        if ($this->_includeStock) {
          $this->dotAvailableMemo = $this->resolveDotColorFromStockCount($this->getStockCount());
        } else {
          $this->dotAvailableMemo = 'red';
        }
        break;
      default:
        $this->dotAvailableMemo = 'green';
    }

    return $this->dotAvailableMemo;
  }

  public function getFullNameAttribute()
  {
    return $this->getTitleAttribute() . ' ' . $this->getFullSizeAttribute() . ' ' . $this->code . 'PR ' . $this->getLiSiAttribute();
  }

  public function getTitleAttribute()
  {
    $tread = $this->resolveTreadWithBrand();
    if ($tread && $tread->brand && !empty($tread->brand->title) && !empty($tread->title)) {
      return $tread->brand->title . ' ' . $tread->title;
    }

    return (string) ($this->attributes['title'] ?? $this->article ?? '');
  }

  public function getLiSiAttribute()
  {
    return $this->li . $this->si;
  }

  public function getBrandAttribute()
  {
    $tread = $this->resolveTreadWithBrand();
    if (!$tread || !$tread->brand || !isset($tread->brand->title)) {
      return false;
    }

    return $tread->brand->title;
  }

  public function getLinkAttribute()
  {
    $tread = $this->resolveTreadWithBrand();
    if (!$tread || !$tread->brand || !isset($tread->brand->slug) || !isset($tread->title)) {
      return false;
    }

    return route('lielas-riepa', [$tread->brand->slug, str_replace('/', '_', $tread->title), $this->tire_id]);
  }

  public function getStockAvailabilityAttribute()
  {
    if ($this->stockAvailabilityMemo !== null) {
      return $this->stockAvailabilityMemo;
    }

    $stock_names = [
      'i3' => 'I3',
      'starco' => 'StarCo',
    ];

    $quantity = $this->quantity ?? 0;
    $ursQuantity = $this->urs_quantity ?? 0;
    $krsQuantity = $this->krs_quantity ?? 0;

    $availability = '<p>Ulbrokā: ' . $ursQuantity . '</p><br>';
    $availability .= '<p>Kalnciema ielā: ' . $krsQuantity . '</p>';

    if (Auth::check() && Auth::user()->hasRole(['administrators', 'moderators'])) {
      $stocks = array_key_exists($this->tire_id, self::$stockRows)
        ? self::$stockRows[$this->tire_id]
        : ($this->relationLoaded('stocks')
          ? $this->stocks
          : Bigstock::where('tire_id', $this->tire_id)->get());

      foreach ($stock_names as $key => $stock_name) {
        $stock = $stocks->firstWhere('itype', $key);
        if ($stock && $stock->quantity > 0) {
          $availability .= '<br><p>' . $stock_name . ': ' . $stock->quantity . '</p>';
        } else {
          $availability .= '<br><p>' . $stock_name . ': 0</p>';
        }
      }
      if ($this->acomment !== null) {
        $availability .= '<br><hr class="admin-comments"><p><b>Piezīmes:</b> </p><br><p>' . $this->acomment . '</p>';
      }
    } else {
      $dot = $this->getDotAvailableAttribute();
      if ($dot === 'red') {
        $availability = '<p style="text-align: center;">Nepieciešams<br>pārbaudīt pieejamību.</p>';
      } else if ($dot === 'yellow' || $dot === 'half-yellow') {
        $stocks = array_key_exists($this->tire_id, self::$stockRows)
          ? self::$stockRows[$this->tire_id]
          : ($this->relationLoaded('stocks')
            ? $this->stocks
            : Bigstock::where('tire_id', $this->tire_id)->get());
        $availability = PartnerDelivery::partnerAvailabilityHtml($stocks, 'p');
      }
    }

    $this->stockAvailabilityMemo = $availability;

    return $availability;
  }

  public function addSecondaryArticle($article, $type, $quantity = 0)
  {

    $list = Bigstock::where('tire_id', $this->tire_id)->where('article', $article)->get();

    if (count($list) == 0) {
      $item = new Bigstock();
      $item->article = $article;
      $item->tire_id = $this->tire_id;
      $item->quantity = $quantity;
      $item->itype = $type;
      $item->save();
    } else {
      foreach ($list as $item) {
        $item->update(['quantity' => $quantity]);
      }
    }

  }

  public function lisiDesc($weight, $speed)
  {
    $carryCapacity = 'Kravnesības indekss: ';
    $speedCapacity = 'Ātruma indekss: ';

    return @$carryCapacity . @(self::CARRY_CAPS[$weight] ?? '') . '<br>' . @$speedCapacity . @(self::SPEED_CAPS[$speed] ?? '');
  }

}
