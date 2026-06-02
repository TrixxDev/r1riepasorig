<?php

namespace App\Models;

use App\Helper\PartnerDelivery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;

class Quadr extends Model
{
    use HasFactory;

    protected $table = 'quadr_tires';

    protected $primaryKey = 'tire_id';

    /** @var array<int, int> */
    protected static array $stockTotals = [];

    /** @var array<int, \Illuminate\Support\Collection> */
    protected static array $stockRows = [];

    public $_includeStock = true;

    public function setIncludeStockAttribute($value)
    {
        return $this->_includeStock = $value;
    }

    public static function preloadStockData(array $tireIds): void
    {
        self::$stockTotals = [];
        self::$stockRows = [];

        if ($tireIds === []) {
            return;
        }

        self::$stockTotals = DB::table('quadr_stock')
            ->whereIn('tire_id', $tireIds)
            ->selectRaw('tire_id, SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END) as total')
            ->groupBy('tire_id')
            ->pluck('total', 'tire_id')
            ->map(fn ($total) => (int) $total)
            ->all();

        self::$stockRows = Quadrstock::whereIn('tire_id', $tireIds)
            ->get()
            ->groupBy('tire_id')
            ->all();
    }

    public static function clearStockCache(): void
    {
        self::$stockTotals = [];
        self::$stockRows = [];
    }

    public function getImageAttribute()
    {
      $fileName = '/storage/app/public/quadr/tread/' . $this->tread_id . '.png';
      if (file_exists(dirname(__DIR__, 2) . $fileName)) {
        return $fileName;
      } else {
        return false;
      }
    }

    public function getFullNameAttribute()
    {
      return $this->getTitleAttribute() . ' ' . $this->getFullSizeAttribute() . ' ' . $this->comment . ' ' . $this->getLiSiAttribute();
    }

    public function getFullSizeAttribute(): string
    {
        if (!$this->sep && !$this->d2) {
            $size = $this->d1 . '-' . $this->d3;
        } else {
            $size = $this->d1 . $this->sep . $this->d2 . $this->sep2 . $this->d3;
        }
        return $size;
    }

    public function getOfferPriceAttribute()
    {
        if ($this->price2 == null) {
            return $this->price1;
        } else {
            return $this->price2;
        }
    }

    public function getQuadrCommentAttribute()
    {
        $tire = Quadr::where('tire_id', $this->tire_id)->first();
        return $tire->comment;
    }

    public function getStockCount()
    {
        if (array_key_exists($this->tire_id, self::$stockTotals)) {
            return self::$stockTotals[$this->tire_id];
        }

        $stocks = array_key_exists($this->tire_id, self::$stockRows)
            ? self::$stockRows[$this->tire_id]
            : Quadrstock::where('tire_id', $this->tire_id)->get();

        $count = 0;

        foreach ($stocks as $stock) {
          if ($stock !== NULL && $stock->quantity >= 1) {
            $count += $stock->quantity;
          }
        }

        return $count;
    }

  public static function DuellLink($article)
  {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://www.duell.fi/jm/en/search?q=' . $article . '&ajaxSearch=1',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: application/x-www-form-urlencoded"
      ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    return json_decode($response)[0]->product_link;
  }

  public static function StockLink($tire)
  {

    $stocks = Quadrstock::where('tire_id', $tire->tire_id)->get();

    $urls = [];

    foreach ($stocks as $stock) {
      switch ($stock->itype) {
        case 'i3': {
          $urls['Latakko'] = ['link' => 'https://shop.latakko.eu/product/' . $stock->article, 'remaining' => $stock->quantity];
          break;
        }
        case 'duell': {
          $urls['Duell'] = ['link' => Self::DuellLink($stock->article), 'remaining' => $stock->quantity];
          break;
        }
      }
    }

    return $urls;

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
                            return 'Pasūtāms';
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
            case 1: {
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
            case 1: {
                return 'half-green';
            }
            case -1:
            case 0: {
                if ($this->_includeStock) {
                    $count = $this->getStockCount();
                    switch ($count){
                        case -1:
                        case 0:{
                            return 'red';
                        }
                        case 1: {
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

    public function getTitleAttribute()
    {
        $sql = DB::table('quadr_treads')->selectRaw('quadr_treads.*, quadr_treads.t_title as tread_title')
            ->selectRaw('quadr_brands.*, quadr_brands.b_title as brand_title')
            ->leftJoin('quadr_brands', 'quadr_treads.brand_id', '=', 'quadr_brands.brand_id')
            ->where('quadr_treads.tread_id', $this->make_id)
            ->first();
        if (!isset($sql->brand_title) || !isset($sql->tread_title)) {
            return false;
        } else {
            return $sql->brand_title . ' ' . $sql->tread_title;
        }
    }

    public function getLinkAttribute()
    {
        $tire = Quadrtread::selectRaw('quadr_treads.*, quadr_treads.t_title as tread_title')
            ->selectRaw('quadr_brands.*, quadr_brands.slug as brand_title')
            ->leftJoin('quadr_brands', 'quadr_treads.brand_id', '=', 'quadr_brands.brand_id')
            ->where('quadr_treads.tread_id', $this->make_id)
            ->first();
        if (!isset($tire->brand_title) || !isset($tire->tread_title)) {
            return false;
        } else {
            $url = route('kvadraciklu-riepa', [$tire->brand_title, str_replace('/', '_', $tire->tread_title), $this->tire_id]);
            $url = str_replace('&', '$1', $url);
            return $url;
        }
    }

    public function getLiSiAttribute()
    {
        return $this->li . $this->si;
    }

    public function getBrandAttribute()
    {
        $sql = DB::table('quadr_treads')->selectRaw('quadr_treads.*, quadr_treads.t_title as tread_title')
            ->selectRaw('quadr_brands.*, quadr_brands.b_title as brand_title')
            ->leftJoin('quadr_brands', 'quadr_treads.brand_id', '=', 'quadr_brands.brand_id')
            ->where('quadr_treads.tread_id', $this->make_id)
            ->first();
        if (!isset($sql->brand_title)) {
            return false;
        } else {
            return $sql->brand_title;
        }
    }

  public function getCodeExplainAttribute()
  {
    $code_array = [];

    $return = '';

    $codes = Code::all();

    foreach ($codes as $code) {
      $code_array[$code->name] = $code->explanation;
    }

    $codes = explode(' ', $this->code);
    foreach ($codes as $code) {
      if (isset($code_array[$code])) {
        $return .= $code_array[$code] . '<br>';
      }
    }

    if (strpos($this->code, 'DOT') !== false) {
      $return .= $code_array['DOT'];
    }

    return $return;
  }

    public function getStocksAttribute()
    {
      $tire = Quadr::where('tire_id', $this->tire_id)->first();

      $stock_qty = [];

      $stock_names = [
        'i3' => 'I3',
        'duell' => 'Duell',
        'starco' => 'StarCo',
      ];

      array_push($stock_qty, $tire->quantity);

      foreach ($stock_names as $key => $stock_name) {
        $stock = Quadrstock::where('itype', $key)->where('tire_id', $tire->tire_id)->first();
        if ($stock && $stock->quantity != '-1') {
          array_push($stock_qty, $stock->quantity);
        }
      }

      return array_sum($stock_qty);
    }

    public function getStockAvailabilityAttribute()
    {
      $stock_names = [
        'i3' => 'I3',
        'duell' => 'Duell',
        'starco' => 'StarCo',
      ];

      if ($this->urs_quantity >= 2) {
        $availability = '<span>Ulbrokā: 2 un vairāk</span><br>';
      } else {
        $availability = '<span>Ulbrokā: ' . $this->urs_quantity . '</span><br>';
      }
      if ($this->krs_quantity >= 2) {
        $availability .= '<span>Kalnciema ielā: 2 un vairāk</span>';
      } else {
        $availability .= '<span>Kalnciema ielā: ' . $this->krs_quantity . '</span>';
      }

      if (Auth::check()) {
        $availability = '<span>Ulbrokā: ' . $this->urs_quantity . '</span><br>';
        $availability .= '<span>Kalnciema ielā: ' . $this->krs_quantity . '</span>';
        $stocks = array_key_exists($this->tire_id, self::$stockRows)
          ? self::$stockRows[$this->tire_id]
          : Quadrstock::where('tire_id', $this->tire_id)->get();
        $stocksByType = $stocks->keyBy('itype');
        foreach ($stock_names as $key => $stock_name) {
          $stock = $stocksByType->get($key);
          if ($stock && $stock->quantity > 0) {
            $availability .= '<br><span>' . $stock_name . ': ' . $stock->quantity . '</span>';
          } else {
            $availability .= '<br><span>' . $stock_name . ': 0</span>';
          }
        }
        if ($this->acomment !== null || !empty($this->acomment)) {
          $availability .= '<br><hr class="admin-comments"><span><b>Piezīmes:</b> </span><br><span>' . $this->acomment . '</span>';
        }
      } else {
        $dot = $this->getDotAvailableAttribute();
        if ($dot === 'red') {
          $availability = '<span style="text-align: center;">Nepieciešams<br>pārbaudīt pieejamību.</span>';
        } else if ($dot === 'yellow' || $dot === 'half-yellow') {
          $stocks = array_key_exists($this->tire_id, self::$stockRows)
            ? self::$stockRows[$this->tire_id]
            : Quadrstock::where('tire_id', $this->tire_id)->get();
          $availability = PartnerDelivery::partnerAvailabilityHtml($stocks);
        }
      }

      return $availability;
    }

    public function addSecondaryArticle($article, $type, $quantity = 0)
    {

      $list = Quadrstock::where('tire_id', $this->tire_id)->where('article', $article)->get();

      if (count($list) == 0) {
        $item = new Quadrstock();
        $item->tire_id = $this->tire_id;
        $item->article = $article;
        $item->quantity = $quantity;
        $item->itype = $type;
        $item->save();
      } else {
        foreach ($list as $item) {
          $item->update(['quantity' => $quantity]);
        }
      }

    }

    public function tread()
    {
        return $this->hasOne('App\Models\Quadrtread', 'tread_id', 'make_id');
    }
}
