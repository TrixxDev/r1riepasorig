<?php

namespace App\Models;

use App\Helper\Image;
use App\Helper\PartnerDelivery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Autotire extends Model
{
    use HasFactory;

    protected $table = 'auto_tires';

    protected $primaryKey = 'tire_id';

    public $_includeStock = true;
    public $cbrand;

    /** @var array<int, int> */
    protected static array $stockTotals = [];

    /** @var array<int, \Illuminate\Support\Collection> */
    protected static array $stockRows = [];

    /** @var array<string, string>|null */
    protected static ?array $codeExplainMap = null;

    public static function clearFilterCache(): void
    {
        if (Cache::has('autotire_api_count_version')) {
            Cache::increment('autotire_api_count_version');
        } else {
            Cache::forever('autotire_api_count_version', 2);
        }
    }

    public static function preloadStockData(array $tireIds): void
    {
        self::$stockTotals = [];
        self::$stockRows = [];

        if ($tireIds === []) {
            return;
        }

        self::$stockTotals = DB::table('auto_stock')
            ->whereIn('tire_id', $tireIds)
            ->selectRaw('tire_id, SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END) as total')
            ->groupBy('tire_id')
            ->pluck('total', 'tire_id')
            ->map(fn ($total) => (int) $total)
            ->all();

        self::$stockRows = Autostock::whereIn('tire_id', $tireIds)
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
        return Image::showAd('auto', $this->make_id);
    }

    public function getFullSizeAttribute()
    {
        return $this->d1 . '/' . $this->d2 . ' R' . $this->d3;
    }

    public function getSizeTitleAttribute()
    {
      $this->_includeStock = true;

      $brand = $this->getFullSizeAttribute();
      return '<h4 class="tire-brand-name">' . $brand . '</h4>';
    }

    /** @var \Illuminate\Support\Collection|null */
    protected static $codeExplainCodesCache = null;

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

    public function getAutoCommentAttribute()
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

        return (int) DB::table('auto_stock')
            ->where('tire_id', $this->tire_id)
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity >= 1 THEN quantity ELSE 0 END), 0) as total')
            ->value('total');
    }

    /**
     * Effective own-store stock (Ulbroka + Kalnciema), same rules as getDotAvailableAttribute().
     */
    public static function ownStockQuantitySql(string $table = 'auto_tires'): string
    {
        return '(CASE '
            . "WHEN {$table}.urs_quantity > 0 AND {$table}.krs_quantity <= 0 THEN {$table}.urs_quantity "
            . "WHEN {$table}.urs_quantity <= 0 AND {$table}.krs_quantity > 0 THEN {$table}.krs_quantity "
            . "WHEN {$table}.urs_quantity <= 0 AND {$table}.krs_quantity <= 0 THEN 0 "
            . "ELSE {$table}.urs_quantity + {$table}.krs_quantity END)";
    }

    /** Dot on own stock: 1–3 → half-green, 4+ → green. */
    public static function dotColorForOwnStock(int $qty): string
    {
        if ($qty >= 1 && $qty <= 3) {
            return 'half-green';
        }
        if ($qty >= 4) {
            return 'green';
        }

        return 'red';
    }

    /** SUM of partner auto_stock rows (0 when no rows). */
    public static function partnerStockSumSql(string $tireIdColumn = 'auto_tires.tire_id'): string
    {
        return "(SELECT COALESCE(SUM(quantity), 0) FROM auto_stock WHERE auto_stock.tire_id = {$tireIdColumn})";
    }

    /** Dot on partner stock: 1–3 → half-yellow, 4+ → yellow. */
    public static function dotColorForPartnerStock(int $qty): string
    {
        if ($qty >= 1 && $qty <= 3) {
            return 'half-yellow';
        }
        if ($qty >= 4) {
            return 'yellow';
        }

        return 'red';
    }

//    public static function RZLink($article)
//    {
//      $curl = curl_init();
//      curl_setopt_array($curl, array(
//        CURLOPT_URL => 'https://riepuzona.lv/ajax/searchGoods',
//        CURLOPT_RETURNTRANSFER => true,
//        CURLOPT_ENCODING => "",
//        CURLOPT_MAXREDIRS => 10,
//        CURLOPT_TIMEOUT => 30,
//        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//        CURLOPT_CUSTOMREQUEST => "POST",
//        CURLOPT_POSTFIELDS => "term=$article&data%5Bactive%5D=-1",
//        CURLOPT_HTTPHEADER => array(
//          "cache-control: no-cache",
//          "content-type: application/x-www-form-urlencoded"
//        ),
//      ));
//
//      $response = curl_exec($curl);
//      $err = curl_error($curl);
//
//      curl_close($curl);
//
//      return $response;
//    }

    public static function StockLink($tire)
    {
      $stocks = array_key_exists($tire->tire_id, self::$stockRows)
        ? self::$stockRows[$tire->tire_id]
        : Autostock::where('tire_id', $tire->tire_id)->get();

      $urls = [];

      foreach ($stocks as $stock) {
        switch ($stock->itype) {
          case 'i3': {
            $urls['Latakko'] = ['link' => 'https://shop.latakko.eu/product/' . $stock->article, 'remaining' => $stock->quantity];
            break;
          }
          case 'gy': {
            $urls['Goodyear'] = ['link' => 'https://myway.goodyear.com/p/' . $stock->article, 'remaining' => $stock->quantity];
            break;
          }
          case 'rz': {
            $urls['RiepuZona'] = ['link' => 'https://riepuzona.lv/lv/meklet/t-' . $stock->article, 'remaining' => $stock->quantity];
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
          $this->tire_quantity = $this->urs_quantity;
        } else if ($this->urs_quantity <= 0 && $this->krs_quantity > 0) {
          $this->tire_quantity = $this->krs_quantity;
        } else if ($this->urs_quantity <= 0 && $this->krs_quantity <= 0) {
          $this->tire_quantity = 0;
        } else {
          $this->tire_quantity = $this->urs_quantity + $this->krs_quantity;
        }

//        $this->quantity = (int) $this->quantity;

//        dump($this->quantity);

        if ($this->tire_quantity > 0) {
            return self::dotColorForOwnStock((int) $this->tire_quantity);
        }

        if ($this->_includeStock) {
            return self::dotColorForPartnerStock($this->getStockCount());
        }

        return 'red';
    }

    public function getTitleAttribute()
    {
        $sql = DB::table('auto_treads')->selectRaw('auto_treads.*, auto_treads.t_title as tread_title')
                                              ->selectRaw('auto_brands.*, auto_brands.title as brand_title')
                                              ->leftJoin('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
                                              ->where('auto_treads.tread_id', $this->make_id)
                                              ->first();
        if (!isset($sql->brand_title) || !isset($sql->tread_title)) {
            return false;
        } else {
            return $sql->brand_title . ' ' . $sql->tread_title;
        }
    }

    public function getFullNameAttribute()
    {
	    return $this->getTitleAttribute() . ' ' . $this->getFullSizeAttribute() . ' ' . $this->code . ' ' . $this->getLiSiAttribute();
    }

    public function getLiSiAttribute()
    {
        return $this->li . $this->si;
    }

    public function getBrandAttribute()
    {
        $sql = DB::table('auto_treads')->selectRaw('auto_treads.*, auto_treads.t_title as tread_title')
            ->selectRaw('auto_brands.*, auto_brands.title as brand_title')
            ->leftJoin('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
            ->where('auto_treads.tread_id', $this->make_id)
            ->first();
        if (!isset($sql->brand_title)) {
            return false;
        } else {
            return $sql->brand_title;
        }
    }

    public function getLinkAttribute()
    {
        $tire = Autotread::selectRaw('auto_treads.*, auto_treads.t_title as tread_title')
            ->selectRaw('auto_brands.*, auto_brands.slug as brand_title')
            ->leftJoin('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
            ->where('auto_treads.tread_id', $this->make_id)
            ->first();
        if (!isset($tire->brand_title) || !isset($tire->tread_title)) {
            return false;
        } else {
	        if ($tire->season == 1) {
                return route('vasaras-riepa', [$tire->brand_title, str_replace('/', '_', $tire->tread_title), $this->tire_id]);
	        } else {
                return route('ziemas-riepa', [$tire->brand_title, str_replace('/', '_', $tire->tread_title), $this->tire_id]);
	        }
        }
    }

    public function getStockAvailabilityAttribute()
    {
        return $this->resolveStockAvailability(null);
    }

    public function resolveStockAvailability(?string $dotAvailable = null): string
    {
        $stock_names = [
            'i3' => 'I3',
            'gy' => 'GoodYear',
            'rz' => 'RiepuZona',
            'rg' => 'Riepu Garāža',
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

        if (Auth::check()) {
            $availability = '<span>Ulbrokā: ' . $this->urs_quantity . '</span><br>';
            $availability .= '<span>Kalnciema ielā: ' . $this->krs_quantity . '</span>';
            $stocks = array_key_exists($this->tire_id, self::$stockRows)
                ? self::$stockRows[$this->tire_id]
                : Autostock::where('tire_id', $this->tire_id)->get();
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
                    : Autostock::where('tire_id', $this->tire_id)->get();
                $availability = PartnerDelivery::partnerAvailabilityHtml($stocks);
            }
        }

        return $availability;
    }

    public function lisiDesc($weight, $speed)
    {
      static $carryCaps = null;
      static $speedCaps = null;

      $carryCapacity = 'Kravnesības indekss: ';

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

      $speedCapacity = 'Ātruma indekss: ';

      return $carryCapacity . ($carryCaps[$weight] ?? '') . '<br>' . $speedCapacity . ($speedCaps[$speed] ?? '');
    }

    public function addSecondaryArticle($article, $type, $quantity = 0)
    {

      $list = Autostock::where('tire_id', $this->tire_id)->where('article', $article)->get();

      if (count($list) == 0) {
        $item = new Autostock;
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
        return $this->hasOne('App\Models\Autotread', 'tread_id', 'make_id');
    }
}

