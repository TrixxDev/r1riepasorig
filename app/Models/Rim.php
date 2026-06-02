<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Rim extends Model
{
  use HasFactory;

  protected $primaryKey = 'rim_id';

  public $_includeStock = true;

  /** @var array<int, int> */
  protected static array $stockTotals = [];

  /** @var array<int, \Illuminate\Support\Collection> */
  protected static array $stockRows = [];

  /** @var array<int, string> */
  protected static array $makeBrandTitles = [];

  /** @var array<int, string> */
  protected static array $makeTreadTitles = [];

  public static function partnerStockSumSql(string $rimIdColumn = 'rims.rim_id'): string
  {
    return "(SELECT COALESCE(SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END), 0) FROM rim_stock WHERE rim_stock.rim_id = {$rimIdColumn})";
  }

  public static function hasAvailableStockSql(string $table = 'rims'): string
  {
    $partnerStock = self::partnerStockSumSql("{$table}.rim_id");

    return "({$table}.quantity > 0 OR ({$table}.quantity = 0 AND {$partnerStock} > 0))";
  }

  public static function clearFilterCache(): void
  {
    Cache::forget('rim_filter_options_v2');
    Cache::forget('rim_brand_list_v1');
    if (Cache::has('rim_api_count_version')) {
      Cache::increment('rim_api_count_version');
    } else {
      Cache::forever('rim_api_count_version', 2);
    }
  }

  public static function preloadStockData(array $rimIds): void
  {
    self::$stockTotals = [];
    self::$stockRows = [];

    if ($rimIds === []) {
      return;
    }

    self::$stockTotals = Rimstock::whereIn('rim_id', $rimIds)
      ->selectRaw('rim_id, SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END) as total')
      ->groupBy('rim_id')
      ->pluck('total', 'rim_id')
      ->map(fn ($total) => (int) $total)
      ->all();

    self::$stockRows = Rimstock::whereIn('rim_id', $rimIds)
      ->get()
      ->groupBy('rim_id')
      ->all();
  }

  public static function preloadMakeData(array $makeIds): void
  {
    self::$makeBrandTitles = [];
    self::$makeTreadTitles = [];

    if ($makeIds === []) {
      return;
    }

    $rows = Rimmake::select(
      'rim_makes.make_id',
      'rim_makes.title as tread_title',
      'rim_brands.title as brand_title'
    )
      ->leftJoin('rim_brands', 'rim_makes.brand_id', '=', 'rim_brands.brand_id')
      ->whereIn('rim_makes.make_id', $makeIds)
      ->get();

    foreach ($rows as $row) {
      self::$makeBrandTitles[$row->make_id] = (string) ($row->brand_title ?? '');
      self::$makeTreadTitles[$row->make_id] = (string) ($row->tread_title ?? '');
    }
  }

  public static function clearStockCache(): void
  {
    self::$stockTotals = [];
    self::$stockRows = [];
    self::$makeBrandTitles = [];
    self::$makeTreadTitles = [];
  }

  protected function joinedBrandTitle(): ?string
  {
    if (!empty($this->attributes['brand_title'])) {
      return (string) $this->attributes['brand_title'];
    }

    return self::$makeBrandTitles[$this->make_id] ?? null;
  }

  protected function joinedTreadTitle(): ?string
  {
    if (!empty($this->attributes['tread_title'])) {
      return (string) $this->attributes['tread_title'];
    }

    return self::$makeTreadTitles[$this->make_id] ?? null;
  }

  public function setIncludeStockAttribute($value)
  {
    return $this->_includeStock = $value;
  }

  public function getOfferPriceAttribute()
  {
    return $this->price3;
  }

  public function getLinkAttribute()
  {
    $brandTitle = $this->joinedBrandTitle();
    $treadTitle = $this->joinedTreadTitle();

    if ($brandTitle === null || $treadTitle === null || $brandTitle === '' || $treadTitle === '') {
      return false;
    }

    return route('lietais-disks', [$brandTitle, str_replace('/', '_', $treadTitle), $this->rim_id]);
  }

  public function getAvailableAttribute()
  {
    switch ($this->quantity) {
      case 1: {
        return 'Pēdējais';
      }
      case 2: {
        return 'Pēdējie 2';
      }
      case 3: {
        return 'Pēdējie 3';
      }
      case -1:
      case 0: {
        if ($this->_includeStock) {
          $count = $this->getStockCount();
          switch ($count){
            case 1: {
              return 'Pēdējais';
            }
            case 2: {
              return 'Pēdējie 2';
            }
            case 3: {
              return 'Pēdējie 3';
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

  public function getDotAvailableAttribute()
  {

    if ($this->urs_quantity > 0 && $this->krs_quantity <= 0) {
      $this->quantity = $this->urs_quantity;
    } else if ($this->urs_quantity <= 0 && $this->krs_quantity > 0) {
      $this->quantity = $this->krs_quantity;
    } else if ($this->urs_quantity <= 0 && $this->krs_quantity <= 0) {
      $this->quantity = 0;
    }

    if ($this->quantity < 0 && $this->getStockCount() > 0) {
      if ($this->_includeStock) {
        $count = $this->getStockCount();
        switch ($count){
          case -1:
          case 0: {
            return 'red';
          }
          case 1:
          case 2:
          case 3: {
            return 'half-yellow';
          }
          default:{
            return 'yellow';
          }
        }
      } else {
        return 'red';
      }
    }
    switch ($this->quantity) {
      case 1:
      case 2:
      case 3: {
        return 'half-green';
      }
      case -1:
      case 0: {
        if ($this->_includeStock) {
          $count = $this->getStockCount();
          switch ($count){
            case -1:
            case 0: {
              return 'red';
            }
            case 1:
            case 2:
            case 3: {
              return 'half-yellow';
            }
            default:{
              return 'yellow';
            }
          }
        } else {
          return 'red';
        }
      }
      default: {
        return 'green';
      }
    }

  }

  public function getStockCount()
  {
    if (array_key_exists($this->rim_id, self::$stockTotals)) {
      return self::$stockTotals[$this->rim_id];
    }

    return (int) Rimstock::where('rim_id', $this->rim_id)
      ->selectRaw('COALESCE(SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END), 0) as total')
      ->value('total');
  }

  public static function StockLink($rim)
  {
    $stocks = array_key_exists($rim->rim_id, self::$stockRows)
      ? self::$stockRows[$rim->rim_id]
      : Rimstock::where('rim_id', $rim->rim_id)->get();

    $urls = [];

    foreach ($stocks as $stock) {
      switch ($stock->itype) {
        case 'i3': {
          $urls['Latakko'] = ['link' => 'https://shop.latakko.eu/product/' . $stock->article, 'remaining' => $stock->quantity];
          break;
        }
      }
    }

    return $urls;
  }

  public function getStockAvailabilityAttribute()
  {
    $stocks = array_key_exists($this->rim_id, self::$stockRows)
      ? self::$stockRows[$this->rim_id]
      : Rimstock::where('rim_id', $this->rim_id)->get();

    $stock_names = [
      'i3' => 'I3',
    ];

    if ($this->urs_quantity >= 4) {
      $availability = '<span>Ulbrokā: 4 un vairāk</span><br>';
    } else {
      $availability = '<span>Ulbrokā: ' . $this->urs_quantity . '</span><br>';
    }
    if ($this->krs_quantity >= 4) {
      $availability .= '<span>Kalnciema ielā: 4 un vairāk</span>';
    } else {
      $availability .= '<span>Kalnciema ielā: ' . $this->krs_quantity . '</span>';
    }

    if (Auth::check() && Auth::user()->hasRole(['administrators', 'moderators'])) {
      $availability = '<span>Ulbrokā: ' . $this->urs_quantity . '</span><br>';
      $availability .= '<span>Kalnciema ielā: ' . $this->krs_quantity . '</span>';
      foreach ($stock_names as $key => $stock_name) {
        $stock = $stocks->firstWhere('itype', $key);
        if ($stock && $stock->quantity > 0) {
          $availability .= '<br><span>' . $stock_name . ': ' . $stock->quantity . '</span>';
        } else {
          $availability .= '<br><span>' . $stock_name . ': 0</span>';
        }
      }
      if ($this->acomment !== null) {
        $availability .= '<br><hr class="admin-comments"><span><b>Piezīmes:</b> </span><br><span>' . $this->acomment . '</span>';
      }
    } else {
      $dot = $this->getDotAvailableAttribute();
      if ($dot === 'red') {
        $availability = '<span style="text-align: center;">Nepieciešams<br>pārbaudīt pieejamību.</span>';
      } else if ($dot === 'yellow' || $dot === 'half-yellow') {
        $availability = '<span style="text-align: center;">Diski pieejami partneru noliktavās<br>Piegāde 1 darbadienas laikā.</span>';
      }
    }
    $availability .= '';

    return $availability;
  }

  public function getBrandTitleAttribute()
  {
    return $this->joinedBrandTitle() ?? '';
  }

  public function getTreadTitleAttribute()
  {
    return $this->joinedTreadTitle() ?? '';
  }

  public function getFullNameAttribute()
  {
    return trim($this->getBrandTitleAttribute() . ' ' . $this->getTreadTitleAttribute() . ' ' . $this->skr . 'x' . $this->pcd . ' R' . $this->d3 . ' ' . $this->d1 . 'J et' . $this->et . ' ' . $this->dc . ' ' . $this->color);
  }

  public function getFullTitleAttribute()
  {
    return trim($this->getBrandTitleAttribute() . ' ' . $this->getTreadTitleAttribute());
  }

  public function getBrandCommentAttribute()
  {
    return null;
  }

  public function getTreadCommentAttribute()
  {
    return null;
  }

  public function tread()
  {
    return $this->hasOne('App\Models\Rimmake', 'make_id', 'make_id');
  }

}
