<?php

namespace App\Models;

use App\Helper\PartnerDelivery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Moto extends Model
{
    use HasFactory;

    protected $table = 'moto_tires';

    protected $primaryKey = 'tire_id';

    public $_includeStock = true;

    /** @var array<int, int> */
    protected static array $stockTotals = [];

    /** @var array<int, \Illuminate\Support\Collection> */
    protected static array $stockRows = [];

    /** @var array<string, string>|null */
    protected static ?array $codeExplainMap = null;

    /** @var \Illuminate\Support\Collection|null */
    protected static $codeExplainCodesCache = null;

    public static function clearFilterCache(): void
    {
        Cache::forget('moto_catalog_brands_v1');
        Cache::forget('moto_tire_types_v1');
        Cache::forget('moto_tire_sizes_v1');

        if (Cache::has('moto_api_count_version')) {
            Cache::increment('moto_api_count_version');
        } else {
            Cache::forever('moto_api_count_version', 2);
        }
    }

    public static function preloadStockData(array $tireIds): void
    {
        self::$stockTotals = [];
        self::$stockRows = [];

        if ($tireIds === []) {
            return;
        }

        self::$stockTotals = DB::table('moto_stock')
            ->whereIn('tire_id', $tireIds)
            ->selectRaw('tire_id, SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END) as total')
            ->groupBy('tire_id')
            ->pluck('total', 'tire_id')
            ->map(fn ($total) => (int) $total)
            ->all();

        self::$stockRows = Motostock::whereIn('tire_id', $tireIds)
            ->get()
            ->groupBy('tire_id')
            ->all();
    }

    public static function clearStockCache(): void
    {
        self::$stockTotals = [];
        self::$stockRows = [];
    }

    public function setIncludeStockAttribute($value)
    {
        return $this->_includeStock = $value;
    }

    public function getImageAttribute()
    {
      $fileName = '/storage/app/public/moto/tread/' . $this->tread_id . '.png';
      if (file_exists(dirname(__DIR__, 2) . $fileName)) {
        return $fileName;
      } else {
        return false;
      }
    }

    public function getFullNameAttribute()
    {
      return $this->getTitleAttribute() . ' ' . $this->getFullSizeAttribute() . ' ' . $this->code . ' ' . $this->getLiSiAttribute();
    }

    public function getFullSizeAttribute()
    {
        if ($this->d2 != '') {
          return $this->d1 . '/' . $this->d2 . ' ' . $this->d4 . ' ' . $this->d3;
        } else {
          return $this->d1 . ' ' . $this->d4 . ' ' . $this->d3;
        }
    }

    public function getCodeExplainAttribute()
    {
      if (self::$codeExplainMap === null) {
        if (self::$codeExplainCodesCache === null) {
          self::$codeExplainCodesCache = Code::all();
        }
        self::$codeExplainMap = [];
        foreach (self::$codeExplainCodesCache as $code) {
          self::$codeExplainMap[$code->name] = $code->explanation;
        }
      }

      $return = '';
      foreach (explode(' ', (string) $this->code) as $code) {
        if (isset(self::$codeExplainMap[$code])) {
          $return .= self::$codeExplainMap[$code] . '<br>';
        }
      }

      if (strpos((string) $this->code, 'DOT') !== false && isset(self::$codeExplainMap['DOT'])) {
        $return .= self::$codeExplainMap['DOT'];
      }

      return $return;
    }

    public function getOfferPriceAttribute()
    {
        if ($this->price2 == null) {
            return $this->price1;
        } else {
            return $this->price2;
        }
    }

    public function getMotoCommentAttribute()
    {
        return $this->comment;
    }

    public function getStockCount()
    {
        if (array_key_exists('stock_quantity', $this->attributes)
            && $this->attributes['stock_quantity'] !== null
            && $this->attributes['stock_quantity'] !== '') {
            return (int) $this->attributes['stock_quantity'];
        }

        if (array_key_exists($this->tire_id, self::$stockTotals)) {
            return self::$stockTotals[$this->tire_id];
        }

        return (int) DB::table('moto_stock')
            ->where('tire_id', $this->tire_id)
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END), 0) as total')
            ->value('total');
    }

    public static function DuellLink($article)
    {

//      $curl = curl_init();
//
//      $ch = curl_init('https://lv.e-cat.intercars.eu/');
//      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//      curl_setopt($ch, CURLOPT_HEADER, 1);
//      $result = curl_exec($ch);
//
//
//      preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $cookies);
//
//
//      $session = (strpos($cookies[1][2], 'JSESSIONID') !== false) ? $cookies[1][1] . ' ' .$cookies[1][2] : $cookies[1][0] . ' ' . $cookies[1][1];
//
//      curl_close($ch);
//
//      curl_setopt_array($curl, array(
//        CURLOPT_URL => 'https://lv.e-cat.intercars.eu/lv/api/products/search/suggest?query=2055516',
//        CURLOPT_RETURNTRANSFER => true,
//        CURLOPT_HEADER => 1,
//        CURLOPT_ENCODING => "",
//        CURLOPT_MAXREDIRS => 10,
//        CURLOPT_TIMEOUT => 30,
//        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//        CURLOPT_CUSTOMREQUEST => "GET",
//        CURLOPT_HTTPHEADER => array(
//          "cache-control: no-cache",
//          "content-type: application/json;charset=UTF-8",
//          "Cookie: JSESSIONID=Y13-69097244-5439-4c8f-963a-85f59ad6e4b9.app13"
//        ),
//      ));
//  //      JSESSIONID=Y10-7e7cd814-32f3-4a5c-a5fe-ee66f72d2f2d.app10
//      $response = curl_exec($curl);
//      $err = curl_error($curl);
//
//      curl_close($curl);
//      //dd($response);
//
//      $return = json_decode($response);
//      if (isset($return[0])) {
//	return $return[0]->product_link;
//      } else {
//	return '#';
//      }
//      //return json_decode($response)[0]->product_link;

      return Cache::remember('moto_duell_link_' . md5((string) $article), 3600, function () use ($article) {
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.duell.fi/jm/en/search?q=' . $article . '&ajaxSearch=1',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 3,
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

      if (!empty($err) || $response === false || $response === null || $response === '') {
        return '#';
      }

      $decoded = json_decode($response);
      if (json_last_error() !== JSON_ERROR_NONE) {
        return '#';
      }

      if (is_array($decoded) && isset($decoded[0]) && isset($decoded[0]->product_link)) {
        return $decoded[0]->product_link;
      }

      return '#';
      });
    }

    public static function StockLink($tire)
    {

      $stocks = array_key_exists($tire->tire_id, self::$stockRows)
        ? self::$stockRows[$tire->tire_id]
        : Motostock::where('tire_id', $tire->tire_id)->get();

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
            default:{
              return 'yellow';
            }
          }
        } else {
          return 'red';
        }
      }
        switch ($this->quantity) {
            case -1:
            case 0: {
                if ($this->_includeStock) {
                    $count = $this->getStockCount();
                    switch ($count){
                        case -1:
                        case 0: {
                            return 'red';
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
        if (!empty($this->api_brand_title) && !empty($this->t_title)) {
            return $this->api_brand_title . ' ' . $this->t_title;
        }

        $sql = Mototread::selectRaw('moto_treads.*, moto_treads.title as tread_title')
            ->selectRaw('moto_brands.*, moto_brands.title as brand_title')
            ->leftJoin('moto_brands', 'moto_treads.brand_id', '=', 'moto_brands.brand_id')
            ->where('moto_treads.tread_id', $this->make_id)
            ->first();
        if (!isset($sql->brand_title) || !isset($sql->tread_title)) {
            return false;
        } else {
            return $sql->brand_title . ' ' . $sql->tread_title;
        }
    }

    public function getLiSiAttribute()
    {
        return $this->li . $this->si;
    }

    public function getBrandAttribute()
    {
        $sql = Mototread::selectRaw('moto_treads.*, moto_treads.title as tread_title')
            ->selectRaw('moto_brands.*, moto_brands.title as brand_title')
            ->leftJoin('moto_brands', 'moto_treads.brand_id', '=', 'moto_brands.brand_id')
            ->where('moto_treads.tread_id', $this->make_id)
            ->first();
        if (!isset($sql->brand_title) || !isset($sql->tread_title)) {
            return false;
        } else {
            return $sql->brand_title . ' ' . $sql->tread_title;
        }
    }

    public function getLinkAttribute()
    {
        if ($this->getAttribute('hydrated_cart_link')) {
            return $this->getAttribute('hydrated_cart_link');
        }

        if (!empty($this->api_brand_title) && !empty($this->t_title)) {
            return route('motociklu-riepa', [
                strtolower($this->api_brand_title),
                str_replace('/', '_', $this->t_title),
                $this->tire_id,
            ]);
        }

        $tire = Mototread::selectRaw('moto_treads.*, moto_treads.title as tread_title')
            ->selectRaw('moto_brands.*, moto_brands.title as brand_title')
            ->leftJoin('moto_brands', 'moto_treads.brand_id', '=', 'moto_brands.brand_id')
            ->where('moto_treads.tread_id', $this->make_id)
            ->first();
        if (!isset($tire->brand_title) || !isset($tire->tread_title)) {
            return false;
        }

        return route('motociklu-riepa', [
            strtolower($tire->brand_title),
            str_replace('/', '_', $tire->tread_title),
            $this->tire_id,
        ]);
    }

    public function getStocksAttribute()
    {
      $tire = Moto::where('tire_id', $this->tire_id)->first();

      $stock_qty = [];

      $stock_names = [
        'i3' => 'I3',
        'duell' => 'Duell',
      ];

      array_push($stock_qty, $tire->quantity);

      foreach ($stock_names as $key => $stock_name) {
        $stock = Motostock::where('itype', $key)->where('tire_id', $tire->tire_id)->orderBy('stock_id', 'DESC')->first();
        if ($stock && $stock->quantity != '-1') {
          array_push($stock_qty, $stock->quantity);
        }
      }

      return array_sum($stock_qty);
    }

    public function getStockAvailabilityAttribute()
    {
        return $this->resolveStockAvailability(null);
    }

    public function resolveStockAvailability(?string $dotAvailable = null): string
    {
        $stock_names = [
            'i3' => 'I3',
            'duell' => 'Duell',
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
          $stocks = array_key_exists($this->tire_id, self::$stockRows)
            ? self::$stockRows[$this->tire_id]
            : Motostock::where('tire_id', $this->tire_id)->get();
          $stocksByType = $stocks->keyBy('itype');
          foreach ($stock_names as $key => $stock_name) {
            $stock = $stocksByType->get($key);
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
          $dot = $dotAvailable ?? $this->getDotAvailableAttribute();
          if ($dot === 'red') {
            $availability = '<span style="text-align: center;">Nepieciešams<br>pārbaudīt pieejamību.</span>';
          } else if ($dot === 'yellow' || $dot === 'half-yellow') {
            $stocks = array_key_exists($this->tire_id, self::$stockRows)
              ? self::$stockRows[$this->tire_id]
              : Motostock::where('tire_id', $this->tire_id)->get();
            $availability = PartnerDelivery::partnerAvailabilityHtml($stocks);
          }
        }

        return $availability;
    }

    public function types(): array
    {
      return Cache::remember('moto_tire_types_v1', 3600, function () {
      $tipi = [];

      $types = Self::select('type')->orderBy('type')->get();
      foreach ($types as $type) {
        switch ($type->type) {
          case 'CUSTOM':
          case 'Custom':
          case 'custom':
            $tipi[$type->type] = 'Custom';
            break;
          case 'SCOOTER':
          case 'Scooter':
          case 'scooter':
            $tipi[$type->type] = 'Scooter';
            break;
          case 'HARLEY DAVIDSON':
          case 'Harley Davidson':
          case 'harley davidson':
            $tipi[$type->type] = 'Harley Davidson';
            break;
          case 'MOTO CROSS':
          case 'Moto Cross':
          case 'moto cross':
            $tipi[$type->type] = 'Moto Cross';
            break;
          case 'RACING':
          case 'Racing':
          case 'racing':
            $tipi[$type->type] = 'Racing';
            break;
          case 'SPORT':
          case 'Sport':
          case 'sport':
            $tipi[$type->type] = 'Sport';
            break;
          case 'SPORT TOURING':
          case 'Sport Touring':
          case 'sport touring':
            $tipi[$type->type] = 'Sport Touring';
            break;
          case 'TRAIL':
          case 'Trail':
          case 'trail':
            $tipi[$type->type] = 'Trail';
            break;
        }
      }

      sort($tipi);

      return array_unique($tipi);
      });
    }

    /** @return string[] */
    public static function parseTypeFilterParam(?string $type): array
    {
        if ($type === null || $type === '') {
            return [];
        }

        return preg_split('/[\s+]+/', trim($type), -1, PREG_SPLIT_NO_EMPTY);
    }

    public function getMotoTypeAttribute()
    {
      $type = strtolower($this->type);

      if ($type == 1) return '';

      if ($type != '') {
        $arr = [
          'custom' => 'Ct',
          'harley davidson' => 'Hd',
          'moto cross' => 'Mx',
          'racing' => 'Rc',
          'sport' => 'Sp',
          'sport touring' => 'St',
          'trail' => 'Tr',
          'scooter' => 'Sc',
        ];

        return $arr[$type] ?? '';
      } else {

      }

    }

    public function getTypeDescAttribute()
    {
      $type = strtolower($this->type);

      if ($type == '') return ['', 'Nav'];

      if ($type != '') {
        $arr = [
          'custom' => ['Ct', 'Custom'],
          'harley davidson' => ['Hd', 'Harley Davidson'],
          'moto cross' => ['Mx', 'Moto Cross'],
          'racing' => ['Rc', 'Racing'],
          'sport' => ['Sp', 'Sport'],
          'sport touring' => ['St', 'Sport Touring'],
          'trail' => ['Tr', 'Trail'],
	        'scooter' => ['Sc', 'Scooter'],
        ];

        if ($type == 1) return '';

        return $arr[$type] ?? ['', ''];
      } else {
        return ['', ''];
      }
    }

    public function tread()
    {
        return $this->hasOne('App\Models\Mototread', 'tread_id', 'make_id');
    }

  public function lisiDesc($weight, $speed)
  {
    static $carryCaps = null;
    static $speedCaps = null;

    $carryCapacity = 'Kravnesības indekss: ';
    $speedCapacity = 'Ātruma indekss: ';

    if ($carryCaps === null) {
    $carryCaps = [
      0 => '45 kg',
      1 => '46.2 kg',
      2 => '47.5 kg',
      3 => '48.7 kg',
      4 => '50 kg',
      5 => '51.5 kg',
      6 => '53 kg',
      7 => '54.5 kg',
      8 => '56 kg',
      9 => '58 kg',
      10 => '60 kg',
      11 => '61.5 kg',
      12 => '63 kg',
      13 => '65 kg',
      14 => '67 kg',
      15 => '69 kg',
      16 => '71 kg',
      17 => '73 kg',
      18 => '75 kg',
      19 => '77.5 kg',
      20 => '80 kg',
      21 => '82.5 kg',
      22 => '85 kg',
      23 => '87.5 kg',
      24 => '90 kg',
      25 => '92.5 kg',
      26 => '95 kg',
      27 => '97.5 kg',
      28 => '100 kg',
      29 => '103 kg',
      30 => '106 kg',
      31 => '109 kg',
      32 => '112 kg',
      33 => '115 kg',
      34 => '118 kg',
      35 => '121 kg',
      36 => '125 kg',
      37 => '128 kg',
      38 => '132 kg',
      39 => '136 kg',
      40 => '140 kg',
      41 => '145 kg',
      42 => '150 kg',
      43 => '155 kg',
      44 => '160 kg',
      45 => '165 kg',
      46 => '170 kg',
      47 => '175 kg',
      48 => '180 kg',
      49 => '185 kg',
      50 => '190 kg',
      51 => '195 kg',
      52 => '200 kg',
      53 => '206 kg',
      54 => '212 kg',
      55 => '218 kg',
      56 => '224 kg',
      57 => '230 kg',
      58 => '236 kg',
      59 => '243 kg',
      60 => '250 kg',
      61 => '257 kg',
      62 => '265 kg',
      63 => '272 kg',
      64 => '280 kg',
      65 => '290 kg',
      66 => '300 kg',
      67 => '307 kg',
      68 => '315 kg',
      69 => '325 kg',
      70 => '335 kg',
      71 => '345 kg',
      72 => '355 kg',
      73 => '365 kg',
      74 => '375 kg',
      75 => '387 kg',
      76 => '400 kg',
      77 => '412 kg',
      78 => '425 kg',
      79 => '437 kg',
      80 => '450 kg',
      81 => '462 kg',
      82 => '475 kg',
      83 => '487 kg',
      84 => '500 kg',
      85 => '515 kg',
      86 => '530 kg',
      87 => '545 kg',
      88 => '560 kg',
      89 => '580 kg',
      90 => '600 kg',
      91 => '615 kg',
      92 => '630 kg',
      93 => '650 kg',
      94 => '670 kg',
      95 => '690 kg',
      96 => '710 kg',
      97 => '730 kg',
      98 => '750 kg',
      99 => '775 kg',
      100 => '800 kg',
      101 => '825 kg',
      102 => '850 kg',
      103 => '875 kg',
      104 => '900 kg',
      105 => '925 kg',
      106 => '950 kg',
      107 => '975 kg',
      108 => '1000 kg',
      109 => '1030 kg',
      110 => '1060 kg',
      111 => '1090 kg',
      112 => '1120 kg',
      113 => '1150 kg',
      114 => '1180 kg',
      115 => '1215 kg',
      116 => '1250 kg',
      117 => '1285 kg',
      118 => '1320 kg',
      119 => '1360 kg',
      120 => '1400 kg',
      121 => '1450 kg',
      122 => '1500 kg',
      123 => '1550 kg',
      124 => '1600 kg',
      125 => '1650 kg',
      126 => '1700 kg',
      127 => '1750 kg',
      128 => '1800 kg',
      129 => '1850 kg',
      130 => '1900 kg',
      131 => '1950 kg',
      132 => '2000 kg',
      133 => '2060 kg',
      134 => '2120 kg',
      135 => '2180 kg',
      136 => '2240 kg',
      137 => '2300 kg',
      138 => '2360 kg',
      139 => '2430 kg',
      140 => '2500 kg',
      141 => '2570 kg',
      142 => '2650 kg',
      143 => '2720 kg',
      144 => '2800 kg',
      145 => '2900 kg',
      146 => '3000 kg',
      147 => '3075 kg',
      148 => '3150 kg',
      149 => '3250 kg',
      150 => '3350 kg',
      151 => '3450 kg',
      152 => '3550 kg',
      153 => '3650 kg',
      154 => '3750 kg',
      155 => '3875 kg',
      156 => '4000 kg',
      157 => '4125 kg',
      158 => '4250 kg',
      159 => '4375 kg',
      160 => '4500 kg',
      161 => '4625 kg',
      162 => '4750 kg',
      163 => '4875 kg',
      164 => '5000 kg',
      165 => '5150 kg',
      166 => '5300 kg',
      167 => '5450 kg',
      168 => '5600 kg',
      169 => '5800 kg',
      170 => '6000 kg',
      171 => '6150 kg',
      172 => '6300 kg',
      173 => '6500 kg',
      174 => '6700 kg',
      175 => '6900 kg',
      176 => '7100 kg',
      177 => '7300 kg',
      178 => '7500 kg',
      179 => '7750 kg',
      180 => '8000 kg',
      181 => '8250 kg',
      182 => '8500 kg',
      183 => '8750 kg',
      184 => '9000 kg',
      185 => '9250 kg',
      186 => '9500 kg',
      187 => '9750 kg',
      188 => '10000 kg',
      189 => '10300 kg',
      190 => '10600 kg',
      191 => '10900 kg',
      192 => '11200 kg',
      193 => '11500 kg',
      194 => '11800 kg',
      195 => '12150 kg',
      196 => '12500 kg',
      197 => '12850 kg',
      198 => '13200 kg',
      199 => '13600 kg',
      200 => '14000 kg',
      201 => '14500 kg',
      202 => '15000 kg',
      203 => '15500 kg',
      204 => '16000 kg',
      205 => '16500 kg',
      206 => '17000 kg',
      207 => '17500 kg',
      208 => '18000 kg',
      209 => '18500 kg',
      210 => '19000 kg',
      211 => '19500 kg',
      212 => '20000 kg',
      213 => '20600 kg',
      214 => '21200 kg',
      215 => '21800 kg',
      216 => '22400 kg',
      217 => '23000 kg',
      218 => '23600 kg',
      219 => '24300 kg',
      220 => '25000 kg',
      221 => '25700 kg',
      222 => '26500 kg',
      223 => '27200 kg',
      224 => '28000 kg',
      225 => '29000 kg',
      226 => '30000 kg',
      227 => '30750 kg',
      228 => '31500 kg',
      229 => '32500 kg',
      230 => '33500 kg',
      231 => '34500 kg',
      232 => '35500 kg',
      233 => '36500 kg',
      234 => '37500 kg',
      235 => '38750 kg',
      236 => '40000 kg',
      237 => '41250 kg',
      238 => '42500 kg',
      239 => '43750 kg',
      240 => '45000 kg',
      241 => '46250 kg',
      242 => '47500 kg',
      243 => '48750 kg',
      244 => '50000 kg',
      245 => '51500 kg',
      246 => '53000 kg',
      247 => '54500 kg',
      248 => '56000 kg',
      249 => '58000 kg',
      250 => '60000 kg',
      251 => '61500 kg',
      252 => '63000 kg',
      253 => '65000 kg',
      254 => '67000 kg',
      255 => '69000 kg',
      256 => '71000 kg',
      257 => '73000 kg',
      258 => '75000 kg',
      259 => '77500 kg',
      260 => '80000 kg',
      261 => '82500 kg',
      262 => '85000 kg',
      263 => '87500 kg',
      264 => '90000 kg',
      265 => '92500 kg',
      266 => '95000 kg',
      267 => '97500 kg',
      268 => '100000 kg',
      269 => '103000 kg',
      270 => '106000 kg',
      271 => '109000 kg',
      272 => '112000 kg',
      273 => '115000 kg',
      274 => '118000 kg',
      275 => '121500 kg',
      276 => '125000 kg',
      277 => '128500 kg',
      278 => '132000 kg',
      279 => '136000 kg',
    ];

    $speedCaps = [
      'A1' => 'A1 - 5 Km/h',
      'A2' => 'A2 - 10 Km/h',
      'A3' => 'A3 - 15 Km/h',
      'A4' => 'A4 - 20 Km/h',
      'A5' => 'A5 - 25 Km/h',
      'A6' => 'A6 - 30 Km/h',
      'A7' => 'A7 - 35 Km/h',
      'A8' => 'A8 - 40 Km/h',
      'B' => 'B - 50 Km/h',
      'C' => 'C - 60 Km/h',
      'D' => 'D - 65 Km/h',
      'E' => 'E - 70 Km/h',
      'F' => 'F - 80 Km/h',
      'G' => 'G - 90 Km/h',
      'J' => 'J - 100 Km/h',
      'K' => 'K - 110 Km/h',
      'L' => 'L - 120 Km/h',
      'M' => 'M - 130 Km/h',
      'N' => 'N - 140 Km/h',
      'P' => 'P - 150 Km/h',
      'Q' => 'Q - 160 Km/h',
      'R' => 'R - 170 Km/h',
      'S' => 'S - 180 Km/h',
      'T' => 'T - 190 Km/h',
      'U' => 'U - 200 Km/h',
      'H' => 'H - 210 Km/h',
      'V' => 'V - 240 Km/h',
      'VR' => 'VR - Virs 210 Km/h',
      'W' => 'W - 270 Km/h',
      'Z' => 'Z - Virs 240 Km/h',
      'Y' => 'Y - 300 Km/h',
      'ZR' => 'ZR - Virs 240 Km/h',
    ];
    }

    return @$carryCapacity . @$carryCaps[$weight] . '<br>' . @$speedCapacity . @$speedCaps[$speed];

  }

  public function addSecondaryArticle($article, $type, $quantity = 0)
  {

    $list = Motostock::where('tire_id', $this->tire_id)->where('article', $article)->get();

    if (count($list) == 0) {
      $item = new Motostock();
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
}
