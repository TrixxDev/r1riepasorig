<?php

  namespace App\Http\Controllers;

  use App\Models\Autotire;
  use App\Models\Bigbrand;
  use App\Models\Bigstock;
  use App\Models\Bigtire;
  use App\Models\Bigtread;
  use App\Models\Moto;
  use App\Models\Motostock;
  use App\Models\Quadr;
  use App\Models\Quadrstock;
  use App\Models\Rim;
  use App\Models\Rimbrand;
  use App\Models\Rimmake;
  use App\Models\Rimstock;
  use Carbon\Carbon;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Http\Request;
  use App\Models\Autostock;
  use App\Services\AccrualDatabaseService;
  use Illuminate\Support\Facades\Session;
  use mysql_xdevapi\Exception;
  use PDO;
  use Illuminate\Support\Str;

  class SyncController extends Controller
  {

    public $accrual;
    public $tire_tables;
    public $stock_tables;
    public $articles = '';

    public $urs = 0;
    public $krs = 0;
    private $treadId;
    private $brandId;
    private $accrualStoresCache = null;
    private $accrualInventoryCache = null;
    private $accrualArticleIdMap = null;
    private $accrualKatalogsMap = null;

    public function __construct()
    {
      set_time_limit(0);
      $this->tire_tables = [
        'auto_tires' => [
          'Autotire',
          'auto_stock'
        ],
        'moto_tires' => [
          'Moto',
          'moto_stock'
        ],
        'quadr_tires' => [
          'Quadr',
          'quadr_stock'
        ],
        'big_tires' => [
          'Bigtire',
          'bigtire_stock',
        ],
        'rims' => [
          'Rim',
          ''
        ],
        'quadrims' => [
          'Quadrim',
          'quadrim_stock'
        ],
        'studs' => [
          'Stud',
          ''
        ],
      ];
    }

    // Accrual Sync - (Public) 212.3.218.22 - (Local) 192.168.0.36

    public function accrual(Request $request)
    {

      set_time_limit(0);

      try {
        $this->accrual = app(AccrualDatabaseService::class)->connection();
      } catch (\PDOException $e) {
        return $this->accrualErrorResponse('Accrual connection failed: ' . $e->getMessage(), [
          'server' => config('accrual.host') . ',' . config('accrual.port'),
          'database' => config('accrual.database'),
          'driver' => config('accrual.driver'),
          'available_drivers' => PDO::getAvailableDrivers(),
        ]);
      }

      (isset($request->articles)) ? $this->articles = $request->articles : $this->articles = '';

      try {
        if (!$this->articles) {
          $this->updateArticles();
          DB::table('sync_times')->where('name', 'accrual')->update(['updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
          return response()->json(['status' => 'ok', 'message' => 'Done']);
        }

        if (is_array($this->articles)) {
          $this->loadAccrualSyncCaches($this->articles);
          $return = [];
          foreach ($this->articles as $article) {
            $this->updateArticle($article, true);
            $qty = $this->getStoreQuantities($article);
            array_push($return, json_encode([
              'article' => $article,
              'urs_quantity' => $qty['urs'],
              'krs_quantity' => $qty['krs'],
            ]));
          }
          $this->resetAccrualSyncCaches();
          return $return;
        }

        $this->loadAccrualSyncCaches([$this->articles]);
        $this->updateArticle($this->articles, true);
        $qty = $this->getStoreQuantities($this->articles);
        $this->resetAccrualSyncCaches();
        return response()->json(['urs_quantity' => $qty['urs'], 'krs_quantity' => $qty['krs']]);
      } catch (\Throwable $e) {
        $this->resetAccrualSyncCaches();
        return $this->accrualErrorResponse('Accrual sync failed: ' . $e->getMessage(), [
          'articles' => $this->articles,
        ]);
      }
    }

    private function accrualErrorResponse(string $message, array $context = [], int $status = 503)
    {
      \Log::error('Accrual sync error', array_merge(['message' => $message], $context));

      return response()->json(['error' => $message], $status);
    }

    public function getStocks($article)
    {
      foreach ($this->tire_tables as $tire_table => $tire_options) {
        $model = "App\\Models\\" . $tire_options[0];

        if (class_exists($model)) {
          $item = $model::where('article', $article)->first();
          if (!$item) continue;
          return $item->getStockAvailability;
        } else {
          return false;
        }
      }
    }

    public function getStockLinks($article)
    {
      foreach ($this->tire_tables as $tire_table => $tire_options) {
        $model = "App\\Models\\" . $tire_options[0];

        if (class_exists($model)) {
          $item = $model::where('article', $article)->first();
          if (!$item) continue;
          if (method_exists($model, 'StockLink')) {
            return $model::StockLink($item);
          }
        } else {
          return false;
        }
      }
    }

    public function updateArticle($article, $cachesLoaded = false)
    {
      if (!$cachesLoaded) {
        $this->loadAccrualSyncCaches([$article]);
      }

      foreach ($this->tire_tables as $tire_table => $tire_options) {
        $model = "App\\Models\\" . $tire_options[0];
        $product = $model::where('article', $article)->first();
        if (!$product) {
          continue;
        }

        $this->syncAccrualProductModel($product, $article);
      }

      if (!$cachesLoaded) {
        $this->resetAccrualSyncCaches();
      }
    }

    public function updateArticles()
    {
      $this->loadAccrualSyncCaches();

      foreach ($this->tire_tables as $tire_table => $tire_options) {
        $modelClass = "App\\Models\\{$tire_options[0]}";
        $primary_key = app($modelClass)->getKeyName();

        DB::table($tire_table)->orderBy($primary_key)->chunk(1000, function ($products) use ($modelClass, $primary_key) {
          foreach ($products as $row) {
            if (empty($row->article)) {
              continue;
            }

            $product = (new $modelClass)->newFromBuilder((array) $row);
            $product->exists = true;
            $this->syncAccrualProductModel($product, $row->article);
          }
        });

        DB::table($tire_table)->whereNull('article')->orWhere('article', '=', "''")->update(['quantity' => 0, 'urs_quantity' => 0, 'krs_quantity' => 0]);
      }

      $this->resetAccrualSyncCaches();
    }

    public function updateStock($stock)
    {
      foreach ($stock as $id => $value) {

        $noliktavas = explode(';', $value);
        if (strpos(@$noliktavas[0], 'Noliktava') !== false) {
          $urs = @$noliktavas[0];
          $krs = @$noliktavas[1];
        } else if (strpos(@$noliktavas[0], 'Veikals') !== false) {
          $urs = @$noliktavas[1];
          $krs = @$noliktavas[0];
        }

        @$urs = explode(': ', $urs);
        @$urs_quantity = ((int) $urs[1] <= 0) ? 0 : (int) $urs[1];

        @$krs = explode(': ', $krs);
        @$krs_quantity = ((int) $krs[1] <= 0) ? 0 : (int) $krs[1];

        $total = $urs_quantity + $krs_quantity;

        foreach ($this->tire_tables as $tire_table => $tire_options) {

          $primary_key = app("App\\Models\\$tire_options[0]")->getKeyName();
          $product = DB::table($tire_table)->where($primary_key, $id)->first();

          if ($product) {
            DB::table($tire_table)->where($primary_key, $id)->update([
              'quantity' => $total,
              'urs_quantity' => @$urs_quantity,
              'krs_quantity' => @$krs_quantity,
              'updated_at' => date('Y-m-d H:i:s')
            ]);
          }
        }
      }
    }

    private function resetAccrualSyncCaches(): void
    {
      $this->accrualStoresCache = null;
      $this->accrualInventoryCache = null;
      $this->accrualArticleIdMap = null;
      $this->accrualKatalogsMap = null;
    }

    private function loadAccrualSyncCaches(?array $artikulsFilter = null): void
    {
      if ($this->accrualStoresCache === null) {
        $this->accrualStoresCache = $this->fetchAccrualStores();
      }

      if ($this->accrualInventoryCache === null) {
        $this->accrualInventoryCache = $this->fetchAccrualInventoryBulk($artikulsFilter);
      }

      if ($this->accrualArticleIdMap === null) {
        $this->accrualArticleIdMap = $this->fetchAccrualArticleIdMap($artikulsFilter);
      }

      if ($this->accrualKatalogsMap === null) {
        $articleIds = array_values($this->accrualArticleIdMap);
        $this->accrualKatalogsMap = $this->fetchAccrualKatalogsMap($articleIds);
      }
    }

    private function sqlInList(array $values): string
    {
      $values = array_values(array_unique(array_filter($values, function ($value) {
        return $value !== null && $value !== '';
      })));

      if ($values === []) {
        return "''";
      }

      $escaped = array_map(function ($value) {
        return "'" . str_replace("'", "''", (string) $value) . "'";
      }, $values);

      return implode(',', $escaped);
    }

    private function accrualFetchAll(string $sql): array
    {
      $result = $this->accrual->query($sql);
      if ($result === false) {
        $error = $this->accrual->errorInfo();
        $message = 'Accrual query failed: ' . ($error[2] ?? 'unknown error');
        throw new \RuntimeException($message . ' | SQL: ' . $sql);
      }

      $rows = $result->fetchAll(\PDO::FETCH_ASSOC);

      return $rows === false ? [] : $rows;
    }

    private function syncAccrualProductModel($product, string $artikuls): void
    {
      if ($artikuls === '') {
        return;
      }

      $this->applyQuantitiesToProduct($product, $artikuls);

      $articleId = $this->accrualArticleIdMap[$artikuls] ?? null;
      if ($articleId) {
        $this->applyKatalogPricesToProduct($product, $articleId);
      }

      $product->updated_at = date('Y-m-d H:i:s');
      $product->save();
    }

    private function applyQuantitiesToProduct($product, string $artikuls): void
    {
      $productInfo = $this->getAccrualInventory($artikuls);

      if (isset($productInfo[$artikuls])) {
        $product->quantity = intval($productInfo[$artikuls]);
        $stores = $productInfo['_stores'][$artikuls] ?? [];

        $urs = isset($stores[1]) ? $this->parseStoreQuantity($stores[1]) : 0;
        $krs = isset($stores[2]) ? $this->parseStoreQuantity($stores[2]) : 0;

        $product->urs_quantity = $urs > 0 ? $urs : 0;
        $product->krs_quantity = $krs > 0 ? $krs : 0;
        return;
      }

      $product->quantity = 0;
      $product->urs_quantity = 0;
      $product->krs_quantity = 0;
    }

    private function getStoreQuantities(string $artikuls): array
    {
      $productInfo = $this->getAccrualInventory($artikuls);
      $urs = 0;
      $krs = 0;

      if (isset($productInfo['_stores'][$artikuls])) {
        $stores = $productInfo['_stores'][$artikuls];
        if (isset($stores[1])) {
          $urs = $this->parseStoreQuantity($stores[1]);
        }
        if (isset($stores[2])) {
          $krs = $this->parseStoreQuantity($stores[2]);
        }
      }

      return ['urs' => $urs, 'krs' => $krs];
    }

    private function parseStoreQuantity($storeValue): int
    {
      $value = str_replace('Noliktava: ', '', (string) $storeValue);
      $value = str_replace('Veikals: ', '', $value);

      return intval($value);
    }

    private function applyKatalogPricesToProduct($product, string $articleId): void
    {
      if (!isset($this->accrualKatalogsMap[$articleId])) {
        return;
      }

      $priceData = $this->accrualKatalogsMap[$articleId];
      $product->price1 = $priceData['price1'];
      $product->price2 = $priceData['price2'];
      $product->priceoffer = $priceData['priceoffer'];

      if ($priceData['clear_sale_comment'] && $product->comment == env('SALE_TEXT')) {
        $product->comment = '';
      }

      if ($priceData['set_sale_comment'] && empty($product->comment)) {
        $product->comment = env('SALE_TEXT');
      }
    }

    private function buildPromoPriceData(array $rows): array
    {
      $veikala_cena = (int) round(round($rows['Cena1'], 5) * 1.21);

      if ($rows['Deleted'] == 1) {
        return [
          'price1' => $veikala_cena,
          'price2' => (int) round(round($rows['Cena3'], 5) * 1.21),
          'priceoffer' => 0,
          'clear_sale_comment' => true,
          'set_sale_comment' => false,
        ];
      }

      return [
        'price1' => $veikala_cena,
        'price2' => (int) round(round($rows['Cena'], 5) * 1.21),
        'priceoffer' => 1,
        'clear_sale_comment' => false,
        'set_sale_comment' => true,
      ];
    }

    private function buildFallbackPriceData(array $rows): array
    {
      return [
        'price1' => (int) round(round($rows['Cena1'], 5) * 1.21),
        'price2' => (int) round(round($rows['Cena3'], 5) * 1.21),
        'priceoffer' => 0,
        'clear_sale_comment' => true,
        'set_sale_comment' => false,
      ];
    }

    private function fetchAccrualArticleIdMap(?array $artikulsFilter = null): array
    {
      $sql = "SELECT ArticleId, Artikuls FROM katdetal WHERE Deleted = 0";

      if ($artikulsFilter) {
        $sql .= " AND Artikuls IN (" . $this->sqlInList($artikulsFilter) . ")";
      }

      $map = [];

      foreach ($this->accrualFetchAll($sql) as $row) {
        $map[$row['Artikuls']] = $row['ArticleId'];
      }

      return $map;
    }

    private function fetchAccrualKatalogsMap(?array $articleIds = null): array
    {
      $map = [];
      $useFilter = is_array($articleIds) && count($articleIds) > 0 && count($articleIds) <= 2000;

      $sql = "SELECT k.ArticleId, k.Cena1, k.Cena3, u.Cena, u.Deleted AS Deleted
        FROM katalogs k
        INNER JOIN unatlgrupas u ON (k.ArticleId = u.ArticleId)
        WHERE k.Deleted = 0 AND u.Deleted = 0";

      if ($useFilter) {
        $sql .= " AND k.ArticleId IN (" . $this->sqlInList($articleIds) . ")";
      }

      foreach ($this->accrualFetchAll($sql) as $rows) {
        $map[$rows['ArticleId']] = $this->buildPromoPriceData($rows);
      }

      $sql = "SELECT ArticleId, Cena1, Cena3 FROM katalogs WHERE Deleted = 0";

      if ($useFilter) {
        $sql .= " AND ArticleId IN (" . $this->sqlInList($articleIds) . ")";
      }

      foreach ($this->accrualFetchAll($sql) as $rows) {
        if (!isset($map[$rows['ArticleId']])) {
          $map[$rows['ArticleId']] = $this->buildFallbackPriceData($rows);
        }
      }

      return $map;
    }

    private function fetchAccrualInventoryBulk(?array $artikulsFilter = null): array
    {
      $stores = $this->accrualStoresCache ?? $this->fetchAccrualStores();
      $this->accrualStoresCache = $stores;

      $sql = "SELECT k.Artikuls, a.Atlikums, a.Rezervets, (a.Atlikums - a.Rezervets) AS atl_min_rez, a.StorId
        FROM atlikumi a INNER JOIN katdetal k ON (k.ArticleId = a.ArticleId) WHERE a.FrFirmId = 1 AND k.Deleted = 0";

      if ($artikulsFilter) {
        $sql .= " AND k.Artikuls IN (" . $this->sqlInList($artikulsFilter) . ")";
      }

      $inventory = ['_stores' => []];

      foreach ($this->accrualFetchAll($sql) as $row) {
        if ($row['StorId'] == 0) {
          $inventory[$row['Artikuls']] = $row['atl_min_rez'];
          continue;
        }

        $storId = $row['StorId'];

        if (!isset($inventory['_stores'][$row['Artikuls']])) {
          $inventory['_stores'][$row['Artikuls']] = [];
        }

        if (isset($stores[$storId])) {
          $inventory['_stores'][$row['Artikuls']][(int) $storId] = $stores[$storId] . ': ' . $row['atl_min_rez'];
        }
      }

      return $inventory;
    }

    private function fetchAccrualStores(): array
    {
      $sql = "SELECT StorId, Nosaukums FROM unobjekti WHERE Deleted = 0 AND Veids = 1;";
      $stores = [];

      foreach ($this->accrualFetchAll($sql) as $row) {
        if (strpos($row['Nosaukums'], 'Noliktava') !== false || strpos($row['Nosaukums'], 'Veikals') !== false) {
          $stores[$row['StorId']] = $row['Nosaukums'];
        }
      }

      return $stores;
    }

    public function getInventory($tire_tables, $article = null)
    {
      $map = $this->getAccrualIdToEntityIdMap($tire_tables, $article);

      //        dd($map);
      $inventory = $this->getAccrualInventory($article);

      //        dd($inventory);

      if ($article && !isset($inventory[$article])){
        $inventory = ['_stores' => [$article => ['0']], $article => 0];
      }

      $stores = $inventory['_stores'];
      unset($inventory['_stores']);
      $articles = array_keys($stores);
      $stores = array_combine($articles, array_map('implode', array_fill(0,count($stores),';'), $stores));

      //        dump($map, $inventory, $stores); die;

      $stock = $this->mapInventory($map, $inventory);
      $storestock = $this->mapInventory($map, $stores);

      //        dump($stock, $storestock);die;
      return [$articles, $stock, $storestock];
    }

    public function getAccrualIdToEntityIdMap($tire_tables, $article = null)
    {
      $result = [];

      foreach ($tire_tables as $tire_table => $tire_options) {

        if ($article) {
          $sql = DB::table($tire_table)->where('article', $article)->get();
        } else {
          $sql = DB::table($tire_table)->get();
        }

        foreach ($sql as $row) {
          $key = app("App\\Models\\$tire_options[0]")->getKeyName();
          array_push($result, ['tire_id' => $row->$key, 'article' => $row->article]);
        }

        //        Jāuztaisa masīvs - [
        //        [
        //              'tire_id' => $tire_id,
        //              'accrual_id' => $accrual_id
        //        ]

      }
      $mapped = array_column($result, 'article', 'tire_id');

      return $mapped;
    }

    public function mapInventory($map, $inventory) {
      $stock = [];

      foreach($map as $entity_id => $accrual_id) {
        if(array_key_exists($accrual_id, $inventory)) {
          $stock[$entity_id] = $inventory[$accrual_id];
        }
      }

      return $stock;
    }

    public function getAccrualInventory($article = null) {

      if ($this->accrualInventoryCache !== null) {
        if ($article === null) {
          return $this->accrualInventoryCache;
        }

        $result = ['_stores' => []];
        if (isset($this->accrualInventoryCache[$article])) {
          $result[$article] = $this->accrualInventoryCache[$article];
        }
        if (isset($this->accrualInventoryCache['_stores'][$article])) {
          $result['_stores'][$article] = $this->accrualInventoryCache['_stores'][$article];
        }

        return $result;
      }

      if ($this->accrualStoresCache === null) {
        $this->accrualStoresCache = $this->fetchAccrualStores();
      }

      return $this->fetchAccrualInventoryBulk($article ? [$article] : null);
    }

    public function getAccrualStores() {

      if ($this->accrualStoresCache !== null) {
        return $this->accrualStoresCache;
      }

      $this->accrualStoresCache = $this->fetchAccrualStores();

      return $this->accrualStoresCache;
    }

    // Lattako token generation

    public function getI3Token() {
      $token_url = "api.latakko.eu/Token";

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $token_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "grant_type=password&username=" . env('I3_USERNAME') . "&password=" . env('I3_PASSWORD'),
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache",
          "content-type: application/x-www-form-urlencoded"
        ),
      ));
      $response = curl_exec($curl);

      curl_close($curl);

      $token = json_decode($response);

      return $token->access_token;
    }

    // Lattako sync

        public function i3auto()
        {
            try {
                $sync = DB::table('sync_times')->where('name', 'i3-auto')->get();
                $sync_time = \Carbon\Carbon::parse($sync[0]->updated_at)->addHour();
                $time_now = \Carbon\Carbon::now();
                
                if ($time_now->diff($sync_time)->invert == 1) {
                    // Iegūstam datus tikai tad, ja pagājusi stunda kopš pēdējās sinhronizācijas
                    $token_bearer = $this->getI3Token();
                    $filename = dirname(__DIR__, 3) . '/xml/i3-articles.txt';
                    
                    // Palielinām taimautu līdz 120 sekundēm
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.latakko.eu/api/Articles?IncludeCarTyres=true&IncludeMotorcycleTyres=false&IncludeTruckTyres=false&IncludeEarthmoverTyres=false&IncludeAlloyRims=false&OnlyLocalStockItems=true',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 120, // Palielināts taimauts līdz 120 sekundēm
                        CURLOPT_CONNECTTIMEOUT => 30, // Pievienots savienojuma taimauts
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                            "cache-control: no-cache",
                            "authorization: Bearer " . $token_bearer,
                        ),
                    ));
                    
                    $response = curl_exec($curl);
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $err = curl_error($curl);
                    curl_close($curl);
                    
                    if ($err) {
                        throw new \Exception($err);
                    }
                    
                    if ($httpcode != 200) {
                        throw new \Exception("API atgrieza kļūdas kodu: " . $httpcode);
                    }
                    
                    if (empty($response)) {
                        throw new \Exception("Saņemta tukša atbilde no API");
                    }
                    
                    // Pārbaudām, vai atbilde ir derīgs JSON
                    $contentCheck = json_decode($response);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception("Saņemts nederīgs JSON formāts: " . json_last_error_msg());
                    }
                    
                    // Saglabājam saņemtos datus failā
                    file_put_contents($filename, $response);
                    chmod($filename, 0775);
                }
                
                // Nullējam daudzumu visām i3 precēm
                Autostock::where('itype', 'i3')->update(['quantity' => 0]);
                
                // Pārbaudām, vai fails eksistē pirms lasīšanas
                $filename = dirname(__DIR__, 3) . '/xml/i3-articles.txt';
                if (!file_exists($filename)) {
                    throw new \Exception("Datu fails nav atrasts: " . $filename);
                }
                
                $content = file_get_contents($filename);
                $content = json_decode($content);
                
                if (!is_array($content) && !is_object($content)) {
                    throw new \Exception("Kļūda dekodējot JSON no faila");
                }
                
                $counted = 0;
                $updated = 0;
                
                foreach ($content as $item) {
                    $counted++;
                    
                    $stock = Autostock::where('itype', 'i3')
                            ->where('article', $item->ArticleId)
                            ->orderBy('created_at', 'DESC')
                            ->first();
                            
                    if (!$stock) {
                        continue;
                    }
                    
                    $quantity = intval($item->QuantityAvailable);
                    $metadata = 'price: ' . round(($item->Price * 1.21), 2) . 
                            '; pkpcena: ' . round(($item->NetPrice * 1.21), 2) . 
                            '; Baseprice: ' . round(($item->RetailPrice * 1.21), 2) . ';';
                    
                    $stock->quantity = $quantity;
                    $stock->metadata = $metadata;
                    
                    if ($stock->save()) {
                        $updated++;
                    }
                }
                
                DB::table('sync_times')->where('name', 'i3-auto')->update(['updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
                return "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)";
                
            } catch (\Exception $e) {
                // Reģistrējam kļūdu žurnālā
                \Log::error('Kļūda metodē i3auto: ' . $e->getMessage());
                
                // Produkcijā, iespējams, nevajadzētu rādīt detalizētu kļūdas ziņojumu, bet atgriezt vispārēju ziņojumu
                return "Sinhronizācijas kļūda: " . $e->getMessage();
            }
        }

        public function i3autoalloyrims()
        {
            try {
                set_time_limit(0);

                $sync = DB::table('sync_times')->where('name', 'i3-alloy-rims')->get();
                $sync_time = \Carbon\Carbon::parse($sync[0]->updated_at)->addHour();
                $time_now = \Carbon\Carbon::now();
                
                if ($time_now->diff($sync_time)->invert == 1) {
                    // Iegūstam datus tikai tad, ja pagājusi stunda kopš pēdējās sinhronizācijas
                    $token_bearer = $this->getI3Token();
                    $filename = dirname(__DIR__, 3) . '/public/storage/xml/i3-alloy-rims-articles.txt';
                    
                    // Palielinām taimautu līdz 120 sekundēm
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.latakko.eu/api/Articles?IncludeCarTyres=false&IncludeMotorcycleTyres=false&IncludeTruckTyres=false&IncludeEarthmoverTyres=false&IncludeAlloyRims=true&OnlyLocalStockItems=true',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 120, // Palielināts taimauts līdz 120 sekundēm
                        CURLOPT_CONNECTTIMEOUT => 30, // Pievienots savienojuma taimauts
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                            "cache-control: no-cache",
                            "authorization: Bearer " . $token_bearer,
                        ),
                    ));
                    
                    $response = curl_exec($curl);
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $err = curl_error($curl);
                    curl_close($curl);
                    
                    if ($err) {
                        throw new \Exception($err);
                    }
                    
                    if ($httpcode != 200) {
                        throw new \Exception("API atgrieza kļūdas kodu: " . $httpcode);
                    }
                    
                    if (empty($response)) {
                        throw new \Exception("Saņemta tukša atbilde no API");
                    }
                    
                    // Pārbaudām, vai atbilde ir derīgs JSON
                    $contentCheck = json_decode($response);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception("Saņemts nederīgs JSON formāts: " . json_last_error_msg());
                    }
                    
                    // Saglabājam saņemtos datus failā
                    file_put_contents($filename, $response);
                    chmod($filename, 0775);
                }
                
                // Nullējam daudzumu visām i3 disku precēm
                Rimstock::where('itype', 'i3')->update(['quantity' => 0]);
                
                // Pārbaudām, vai fails eksistē pirms lasīšanas
                $filename = dirname(__DIR__, 3) . '/public/storage/xml/i3-alloy-rims-articles.txt';
                if (!file_exists($filename)) {
                    throw new \Exception("Datu fails nav atrasts: " . $filename);
                }
                
                $content = file_get_contents($filename);
                $content = json_decode($content);
                
                if (!is_array($content) && !is_object($content)) {
                    throw new \Exception("Kļūda dekodējot JSON no faila");
                }
                
                $counted = 0;
                $updated = 0;
                
                $returnText = '';
                
                foreach ($content as $item) {
                    $counted++;
                    
                    if (!$item->NumberOfBolts || !$item->BoltCircle || !$item->Diameter || !$item->RetailPrice) continue;
                    
                    $rim = Rim::where('article', $item->ArticleId)->first();
                    $newRim = false;
                    
                    if ($rim == null) {
                        $newRim = true;
                        $rim = new Rim;
                    }
                    
                    // Остальной код оставляем без изменений для обработки данных
                    
                    $rim->timestamps = false;
                    
                    $imageId = $item->ImageId;
                    
                    $brand = Rimbrand::where('title', $item->BrandName)->first();
                    $tread = Rimmake::where('title', $item->PatternModelText)->first();
                    
                    if ($brand === null) {
                        $brand = new Rimbrand;
                        $brand->timestamps = false;
                        $brand->title = $item->BrandName;
                        $brand->slug = Str::slug($brand->title);
                        $brand->save();
                    }
                    
                    if ($tread === null) {
                        $tread = new Rimmake;
                        $tread->timestamps = false;
                        $tread->brand_id = $brand->brand_id;
                        $tread->title = $item->PatternModelText;
                        $tread->slug = Str::slug($tread->title);
                        $tread->save();
                    } else {
                        if ($tread->brand_id != $brand->brand_id) {
                            $tread = new Rimmake;
                            $tread->timestamps = false;
                            $tread->brand_id = $brand->brand_id;
                            $tread->title = $item->PatternModelText;
                            $tread->slug = Str::slug($tread->title);
                            $tread->save();
                        }
                    }
                    
                    $treadId = $tread->make_id;
                    
                    $quantity = intval($item->QuantityAvailable);
                    if ($imageId != null) {
                        $outPath = dirname(__DIR__, 3) . '/public/storage/rims/tread/' . $treadId . '-o.jpg';
                        
                        if (!file_exists($outPath)) {
                            $this->grab_image('https://api.latakko.eu/api/ArticleImages/' . $imageId, $outPath);
                        }
                    }
                    
                    $rim->make_id = $treadId;
                    $rim->d1 = $item->Width;
                    $rim->d3 = $item->Diameter;
                    $rim->dc = $item->CenterBore;
                    $rim->used = 0;
                    $rim->price1 = ceil((round(($item->NetPrice * 1.21), 2) + 15) / 0.7);
                    $rim->price3 = $item->Price;
                    $rim->price2 = floor(round($item->RetailPrice * 1.21, 2)) - 2;
                    $rim->offer = 0;
                    $rim->priceOffer = 0;
                    if ($newRim == true) {
                        $rim->comment = '';
                    }
                    if ($quantity >= 4) {
                        $rim->visible_users = 1;
                        $rim->visible_list = 1;
                    } else {
                        $rim->visible_users = 0;
                        $rim->visible_list = 0;
                    }
                    $rim->available = 0;
                    $rim->skr = $item->NumberOfBolts;
                    
                    if ($item->BoltCircle == '139,7') {
                        $pcd = '139.7';
                    } else if ($item->BoltCircle == '114,3' || $item->BoltCircle == '114') {
                        $pcd = '114.3';
                    } else {
                        $pcd = $item->BoltCircle;
                    }
                    
                    $rim->pcd = $pcd;
                    $rim->et = $item->Offset;
                    $rim->color = $item->Color;
                    $rim->article = $item->ArticleId;
                    $rim->quantity = 0;
                    $rim->urs_quantity = 0;
                    $rim->krs_quantity = 0;
                    $rim->ordered = 0;
                    $rim->reserved = 0;
                    $rim->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    
                    $rim->save();
                    
                    $metadata = 'price: ' . round(($item->Price * 1.21), 2) . '; pkpcena: ' . round(($item->NetPrice * 1.21), 2) . '; Baseprice: ' . round(($item->RetailPrice * 1.21), 2) . ';';
                    
                    $stock = Rimstock::where('rim_id', $rim->rim_id)->first();
                    
                    if ($stock == null) $stock = new Rimstock;
                    $stock->rim_id = $rim->rim_id;
                    $stock->article = $rim->article;
                    $stock->quantity = $quantity;
                    $rimVisible = Rim::where('article', $stock->article)->first();
                    if (!is_null($rimVisible)) {
                        if ($quantity >= 4) {
                            $rimVisible->visible_users = 1;
                            $rimVisible->visible_list = 1;
                        } else {
                            $rimVisible->visible_users = 0;
                            $rimVisible->visible_list = 0;
                        }
                    }
                    $stock->itype = 'i3';
                    $stock->metadata = $metadata;
                    $rimVisible->save();
                    if ($stock->save()) {
                        $updated++;
                    }
                    $counted++;
                }
                
                DB::table('sync_times')->where('name', 'i3-alloy-rims')->update(['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
                return "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)";
                
            } catch (\Exception $e) {
                // Reģistrējam kļūdu žurnālā
                \Log::error('Kļūda metodē i3autoalloyrims: ' . $e->getMessage());
                
                // Produkcijā, iespējams, nevajadzētu rādīt detalizētu kļūdas ziņojumu, bet atgriezt vispārēju ziņojumu
                return "Sinhronizācijas kļūda: " . $e->getMessage();
            }
        }

    public function i3show()
    {
      echo 'Auto riepas:<br>';
      $stocks = Autostock::where('itype', 'i3')->get();
      foreach ($stocks as $stock) {
        $tire = Autotire::where('tire_id', $stock->tire_id)->first();
        if (!$tire) continue;
        $text = $tire->title . ' ' . $tire->li . $tire->si . ' ' .( $tire->fullSize) . ' [' . $tire->article . ']:[' . $stock->article . ']: ' . $stock->quantity . ' / ' . $stock->metadata . '<br>';
        //dd($text);
        echo $text;
      }
      echo '<br>';

      echo 'Moto riepas:<br>';
      $stocks = Motostock::where('itype', 'i3')->get();
      foreach ($stocks as $stock) {
        $tire = Moto::where('tire_id', $stock->tire_id)->first();
        if (!$tire) continue;
        $text = $tire->title . ' ' . $tire->li . $tire->si . ' ' .( $tire->fullSize) . ' [' . $tire->article . ']:[' . $stock->article . ']: ' . $stock->quantity . ' / ' . $stock->metadata . '<br>';
        //dd($text);
        echo $text;
      }
      echo '<br>';

      echo 'Kvadru riepas:<br>';
      $stocks = Quadrstock::where('itype', 'i3')->get();
      foreach ($stocks as $stock) {
        $tire = Quadr::where('tire_id', $stock->tire_id)->first();
        if (!$tire) continue;
        $text = $tire->title . ' ' . $tire->li . $tire->si . ' ' .( $tire->fullSize) . ' [' . $tire->article . ']:[' . $stock->article . ']: ' . $stock->quantity . ' / ' . $stock->metadata . '<br>';
        //dd($text);
        echo $text;
      }
      echo '<br>';
    }

        public function i3moto()
        {
            try {
                $sync = DB::table('sync_times')->where('name', 'i3-moto')->get();
                $sync_time = \Carbon\Carbon::parse($sync[0]->updated_at)->addHour();
                $time_now = \Carbon\Carbon::now();
                
                if ($time_now->diff($sync_time)->invert == 1) {
                    // Iegūstam datus tikai tad, ja pagājusi stunda kopš pēdējās sinhronizācijas
                    $token_bearer = $this->getI3Token();
                    $filename = dirname(__DIR__, 3) . '/xml/i3-moto-articles.txt';
                    
                    // Palielinām taimautu līdz 120 sekundēm
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.latakko.eu/api/Articles?OnlyStockItems&IncludeCarTyres=false&IncludeMotorcycleTyres=true&IncludeTruckTyres=false&IncludeEarthmoverTyres=false',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 120, // Palielināts taimauts līdz 120 sekundēm
                        CURLOPT_CONNECTTIMEOUT => 30, // Pievienots savienojuma taimauts
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                            "cache-control: no-cache",
                            "authorization: Bearer " . $token_bearer,
                        ),
                    ));
                    
                    $response = curl_exec($curl);
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $err = curl_error($curl);
                    curl_close($curl);
                    
                    if ($err) {
                        throw new \Exception($err);
                    }
                    
                    if ($httpcode != 200) {
                        throw new \Exception("API atgrieza kļūdas kodu: " . $httpcode);
                    }
                    
                    if (empty($response)) {
                        throw new \Exception("Saņemta tukša atbilde no API");
                    }
                    
                    // Pārbaudām, vai atbilde ir derīgs JSON
                    $contentCheck = json_decode($response);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception("Saņemts nederīgs JSON formāts: " . json_last_error_msg());
                    }
                    
                    // Saglabājam saņemtos datus failā
                    file_put_contents($filename, $response);
                    chmod($filename, 0775);
                }
                
                // Nullējam daudzumu visām moto i3 precēm
                Motostock::where('itype', 'i3')->update(['quantity' => 0]);
                
                // Pārbaudām, vai fails eksistē pirms lasīšanas
                $filename = dirname(__DIR__, 3) . '/xml/i3-moto-articles.txt';
                if (!file_exists($filename)) {
                    throw new \Exception("Datu fails nav atrasts: " . $filename);
                }
                
                $content = file_get_contents($filename);
                $content = json_decode($content);
                
                if (!is_array($content) && !is_object($content)) {
                    throw new \Exception("Kļūda dekodējot JSON no faila");
                }
                
                $counted = 0;
                $updated = 0;
                
                foreach ($content as $item) {
                    $counted++;
                    
                    $stock = Motostock::where('itype', 'i3')
                            ->where('article', $item->ArticleId)
                            ->orderBy('created_at', 'DESC')
                            ->first();
                            
                    if (!$stock) {
                        continue;
                    }
                    
                    $quantity = intval($item->QuantityAvailable);
                    $metadata = 'price: ' . round(($item->Price * 1.21), 2) . 
                            '; pkpcena: ' . round(($item->NetPrice * 1.21), 2) . 
                            '; Baseprice: ' . round(($item->RetailPrice * 1.21), 2) . ';';
                    
                    $stock->quantity = $quantity;
                    $stock->metadata = $metadata;
                    
                    if ($stock->save()) {
                        $updated++;
                    }
                }
                
                DB::table('sync_times')->where('name', 'i3-moto')->update(['updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
                return "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)";
                
            } catch (\Exception $e) {
                // Reģistrējam kļūdu žurnālā
                \Log::error('Kļūda metodē i3moto: ' . $e->getMessage());
                
                // Produkcijā, iespējams, nevajadzētu rādīt detalizētu kļūdas ziņojumu, bet atgriezt vispārēju ziņojumu
                return "Sinhronizācijas kļūda: " . $e->getMessage();
            }
        }

        public function i3quadr()
        {
            try {
                $sync = DB::table('sync_times')->where('name', 'i3-quadr')->get();
                $sync_time = \Carbon\Carbon::parse($sync[0]->updated_at)->addHour();
                $time_now = \Carbon\Carbon::now();
                
                if ($time_now->diff($sync_time)->invert == 1) {
                    // Iegūstam datus tikai tad, ja pagājusi stunda kopš pēdējās sinhronizācijas
                    $token_bearer = $this->getI3Token();
                    $filename = dirname(__DIR__, 3) . '/xml/i3-articles.txt';
                    
                    // Palielinām taimautu līdz 120 sekundēm
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.latakko.eu/api/Articles?OnlyStockItems',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 120, // Palielināts taimauts līdz 120 sekundēm
                        CURLOPT_CONNECTTIMEOUT => 30, // Pievienots savienojuma taimauts
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                            "cache-control: no-cache",
                            "authorization: Bearer " . $token_bearer,
                        ),
                    ));
                    
                    $response = curl_exec($curl);
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $err = curl_error($curl);
                    curl_close($curl);
                    
                    if ($err) {
                        throw new \Exception($err);
                    }
                    
                    if ($httpcode != 200) {
                        throw new \Exception("API atgrieza kļūdas kodu: " . $httpcode);
                    }
                    
                    if (empty($response)) {
                        throw new \Exception("Saņemta tukša atbilde no API");
                    }
                    
                    // Pārbaudām, vai atbilde ir derīgs JSON
                    $contentCheck = json_decode($response);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception("Saņemts nederīgs JSON formāts: " . json_last_error_msg());
                    }
                    
                    // Saglabājam saņemtos datus failā
                    file_put_contents($filename, $response);
                    chmod($filename, 0775);
                }
                
                // Nullējam daudzumu visām quadr i3 precēm
                Quadrstock::where('itype', 'i3')->update(['quantity' => 0]);
                
                // Pārbaudām, vai fails eksistē pirms lasīšanas
                $filename = dirname(__DIR__, 3) . '/xml/i3-articles.txt';
                if (!file_exists($filename)) {
                    throw new \Exception("Datu fails nav atrasts: " . $filename);
                }
                
                $content = file_get_contents($filename);
                $content = json_decode($content);
                
                if (!is_array($content) && !is_object($content)) {
                    throw new \Exception("Kļūda dekodējot JSON no faila");
                }
                
                $counted = 0;
                $updated = 0;
                
                foreach ($content as $item) {
                    if ($item->MainGroupName !== 'ATV tires') continue;
                    $counted++;
                    
                    $stock = Quadrstock::where('itype', 'i3')
                            ->where('article', $item->ArticleId)
                            ->orderBy('created_at', 'DESC')
                            ->first();
                            
                    if (!$stock) {
                        continue;
                    }
                    
                    $quantity = intval($item->QuantityAvailable);
                    $metadata = 'price: ' . round(($item->Price * 1.21), 2) . 
                            '; pkpcena: ' . round(($item->NetPrice * 1.21), 2) . 
                            '; Baseprice: ' . round(($item->RetailPrice * 1.21), 2) . ';';
                    
                    $stock->quantity = $quantity;
                    $stock->metadata = $metadata;
                    
                    if ($stock->save()) {
                        $updated++;
                    }
                }
                
                DB::table('sync_times')->where('name', 'i3-quadr')->update(['updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
                return "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)";
                
            } catch (\Exception $e) {
                // Reģistrējam kļūdu žurnālā
                \Log::error('Kļūda metodē i3quadr: ' . $e->getMessage());
                
                // Produkcijā, iespējams, nevajadzētu rādīt detalizētu kļūdas ziņojumu, bet atgriezt vispārēju ziņojumu
                return "Sinhronizācijas kļūda: " . $e->getMessage();
            }
        }

    public function duellmoto()
    {

      $url = 'ftp://duellus:WebUpdate!@updateftp.duell.fi/ic.TXT';

      $opts = ['ftp' => []];

      $context = stream_context_create($opts);

      $xmlString = file_get_contents($url, false, $context);

      $lines = explode("\r", $xmlString);
      $articles = [];
      foreach ($lines as $idx => $line) {
        if ($idx > 0) {
          $lineData = explode("\t", trim($line));
          if ((@$lineData[0] != '') && (is_numeric(@$lineData[3]))) {
            $articles[trim($lineData[0])] = trim($lineData[3]);
          }
        }
      }

      unset($context);

      echo "Moto riepas<br>";
      Motostock::where('itype', 'duell')->update(['quantity' => 0]);

      $updated = 0;
      $counted = 0;

      foreach ($articles as $item => $amount) {
        $article = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
        $quantity = intval($amount);

        $list = Motostock::where('article', $article)->where('itype', 'duell')->get();

        foreach ($list as $itam){
          $itam->quantity = $quantity;
          $itam->save();
          $updated++;
        }
        $counted++;
      }
      DB::table('sync_times')->where('name', 'duell-moto')->update(['updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
      echo "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)\n";

    }

    public function duellquadr()
    {

      $url = 'ftp://duellus:WebUpdate!@updateftp.duell.fi/ic.TXT';

      $opts = ['ftp' => []];

      $context = stream_context_create($opts);

      $xmlString = file_get_contents($url, false, $context);

      $lines = explode("\r", $xmlString);
      $articles = [];
      foreach ($lines as $idx => $line) {
        if ($idx > 0) {
          $lineData = explode("\t", trim($line));
          if ((@$lineData[0] != '') && (is_numeric(@$lineData[3]))) {
            $articles[trim($lineData[0])] = trim($lineData[3]);
          }
        }
      }

      unset($context);

      echo "Kvadraciklu riepas<br>";
      Quadrstock::where('itype', 'duell')->update(['quantity' => 0]);

      $updated = 0;
      $counted = 0;

      foreach ($articles as $item => $amount) {
        $article = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
        $quantity = intval($amount);

        $list = Quadrstock::where('article', $article)->where('itype', 'duell')->get();

        foreach ($list as $itam){
          $itam->quantity = $quantity;
          $itam->save();
          $updated++;
        }
        $counted++;
      }
      DB::table('sync_times')->where('name', 'duell-quadr')->update(['updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
      echo "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)\n";

    }

//    public function i3big()
//    {
//      set_time_limit(0);
//
//      $sync = DB::table('sync_times')->where('name', 'i3-big')->get();
//      $sync_time = \Carbon\Carbon::parse($sync[0]->updated_at)->addHour();
//      $time_now = \Carbon\Carbon::now();
//      if ($time_now->diff($sync_time)->invert == 1) {
//        if (!isset($_COOKIE['i3-token'])) {
//          $token_url = "gd-api-test.barnstenit.se/Token";
////        $token_url = "api.latakko.eu/Token";
//
//          $curl = curl_init();
//          curl_setopt_array($curl, array(
//            CURLOPT_URL => $token_url,
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 30,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "POST",
//            CURLOPT_POSTFIELDS => "grant_type=password&username=" . env('I3_USERNAME') . "&password=" . env('I3_PASSWORD'),
//            CURLOPT_HTTPHEADER => array(
//              "cache-control: no-cache",
//              "content-type: application/x-www-form-urlencoded"
//            ),
//          ));
//          $response = curl_exec($curl);
//          $err = curl_error($curl);
//
//          curl_close($curl);
//
//          if (!$err)
//          {
//            $token = json_decode($response);
//          } else {
//            dd($err);
//          }
//
//          setcookie('i3-token', $token->access_token, time() + $token->expires_in, '/');
//          $token_bearer = $token->access_token;
//        } else {
//          $token_bearer = $_COOKIE['i3-token'];
//        }
//
//        $curl = curl_init();
//        curl_setopt_array($curl, array(
//          CURLOPT_URL => 'https://gd-api-test.barnstenit.se/api/Articles?IncludeCarTyres=false&IncludeMotorcycleTyres=false&IncludeTruckTyres=true&IncludeEarthmoverTyres=false&OnlyLocalStockItems=true',
//          CURLOPT_RETURNTRANSFER => true,
//          CURLOPT_ENCODING => "",
//          CURLOPT_MAXREDIRS => 10,
//          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//          CURLOPT_CUSTOMREQUEST => "GET",
//          CURLOPT_HTTPHEADER => array(
//            "cache-control: no-cache",
//            "authorization: Bearer " . $token_bearer,
//          ),
//        ));
//        $response = curl_exec($curl);
//
//        $filename = dirname(__DIR__, 3) . '/public/storage/xml/i3-industrial.txt';
//
//        file_put_contents($filename, $response);
//        chmod($filename, 0775);
//
//        $err = curl_error($curl);
//
//        if ($err) throw new \Exception($err);
//
//        curl_close($curl);
//      }
//
//      $counted = 0;
//      $updated = 0;
//
//      Bigstock::where('itype', 'i3')->update(['quantity' => 0]);
//
//      $content = file_get_contents(dirname(__DIR__, 3) . '/public/storage/xml/i3-industrial.txt');
//      $content = json_decode($content);
//
//      $out = '';
//
//      foreach ($content as $item){
//
//        dd($item);
//
//        $item = json_encode($item);
//        $item = (object) json_decode($item, TRUE);
//
//        $type = $item->aplication;
//
//        if (empty($item->description)) {
//          continue;
//        }
//
//        if ($type === 'Bus/Truck' || $type === 'Truck' || $type === 'Bus') {
//
//          $article = $item->stockcode;
//
//          $price = round($item->price);
//
//          if ($price != 0) {
//            if ($price < 100) {
//              $price1 = ($price + 8) / 70 * 100;
//              $price2 = $price + 10;
//            }
//            if ($price >= 100 && $price < 200) {
//              $price1 = ($price + 12) / 70 * 100;
//              $price2 = $price + 15;
//            }
//            if ($price >= 200) {
//              $price1 = ($price + 15) / 70 * 100;
//              $price2 = $price + 20;
//            }
//          }
//
//          $price1 = round($price1);
//          $price2 = round($price2);
//
//          $d1 = $item->width;
//          $d2 = $item->profile;
//          $d3 = $item->diameter;
//
//          if (is_array($item->description)) {
//            continue;
//          }
//          $sizes = preg_split('/ /', $item->description)[0];
//          $sizes = SyncController::multiexplode([$d1, $d2, $d3], $sizes);
//          $sizes = array_values(array_filter($sizes));
//
//          if (count($sizes) < 0) {
//            continue;
//          }
//
//          for ($x = 0; $x < count($sizes); $x++) {
//            $sepNr = $x + 1;
//            ${"sep$sepNr"} = $sizes[$x];
//          }
//
//          $brand = $item->brand;
//          $brand = str_replace(' (KRAVAS)', '', $brand);
//          $brand = str_replace(' (COACH)', '', $brand);
//          $brand = ucfirst(strtolower($brand));
//          $tread = $item->protector;
//
//          if (strpos($brand, 'RIEPAS dažādas') !== false ||
//            strpos($brand, 'Atjaunotas') !== false ||
//            strpos($brand, 'Riepas daŽĀdas') !== false) {
//            $brand = '';
//            $tread = '';
//          }
//
////          if ($item->stockcode !== '385652251417943058TT0R0') continue;
//
//          $lisi = $item->li_si;
//          if (is_array($lisi)) {
//            continue;
//          }
//          if (preg_match('/ [\d]+PR/', $lisi)) {
//            $lisi = preg_replace('/ [0-9]+PR/', '', $lisi);
//          }
//          if (preg_match('/[\d]+PR /', $lisi)) {
//            $lisi = preg_replace('/[\d]+PR /', '', $lisi);
//          }
//
//          if (str_word_count($lisi) > 1) {
//            $lisi = preg_replace("/\([^)]+\)/","",$lisi);
//            $lisi = SyncController::multiexplode([' ', '/'], $lisi);
//          } else {
//            if (preg_match("/([\d]+[a-zA-Z]+)/i", $lisi)) {
//              if (strpos($lisi, '/') !== false) {
//                $lisi = explode('/', $lisi);
//                $si = preg_replace('/[\d]+/', '', $lisi[1]);
//              }
//            }
//          }
//          if (is_array($lisi)) {
//            $li = $lisi[0];
//            if (preg_match('/[a-zA-Z]/i', $li[0])) {
//              $lisi = preg_split('/(?<=[a-zA-Z])/i', $li);
//              $li = $lisi[1];
//              if (!isset($si)) {
//                $si = $lisi[0];
//              }
//            } else {
//              if (isset($lisi[1])) {
//                if (preg_match('/[a-zA-Z]/i', $lisi[1])) {
//                  $si = preg_replace('/[\d]+/i', '', $lisi[1]);
//                }
//                $li = $lisi[0];
//              } else {
//                $lisi = preg_split('/(?=[a-zA-Z])/i', $lisi[0]);
//                $li = $lisi[0];
//                $si = $lisi[1];
//              }
//            }
//          }
//
//          $position = SyncController::getByArticle($article);
//          if ($position === false) {
//            $position = new Bigtire();
//          }
//
//          $returnText = '';
//
//          if (!empty($brand) && !empty($tread)) {
//            $treadId = SyncController::getTreadId($tread, $brand);
//            // Jauns breands - Bigtire_brands
//            $returnText .= 'Jauns brends - ' . $brand . '<br>';
//            if ($treadId === false) {
//              $brandId = SyncController::getBrandId($brand);
//              if ($brandId === false) {
//                $brandId = Bigbrand::insertGetId([
//                  'title' => $brand,
//                  'slug' => Str::slug($brand),
//                ]);
//              }
//              // Jauns protektors - Bigtire_treads
//              $returnText .= 'Jauns protektors - ' . $tread . '<br>';
//              $treadId = Bigtread::insertGetId([
//                'brand_id' => $brandId,
//                'title' => $tread,
//                'slug' => Str::slug($tread),
//              ]);
//            }
//          }
//
//          $position->make_id = $treadId;
//
//          $image = $item->image;
//
//          $outPath = dirname(__DIR__, 3) . '/storage/app/public/industrial/tread/';
//
//          @$image = file_get_contents('http://i3.lattako.lv/images/tyres/' . $image . '-o.jpg');
//          $new_image = $outPath . $treadId . '.jpg';
//
//          if (trim($image) !== false) {
//            file_put_contents($new_image, $image);
//          } else {
//            echo 'Neeksistē - Artikuls (' . $article . ')';
//          }
//
//          $position->d1 = $d1;
//          $position->sep = $sep1;
//          if (is_array($d2)) {
//            $position->d2 = null;
//            $position->sep2 = null;
//            $position->d3 = $d3;
//          } else {
//            $position->d2 = $d2;
//            $position->sep2 = $sep2;
//            $position->d3 = $d3;
//          }
//
//          if ($type === 'Truck') {
//            $type = str_replace('Truck', 'Kravas', $type);
//          } else if ($type === 'Buss') {
//            $type = str_replace('Buss', 'Autobuss', $type);
//          } else if ($type === 'Bus/Truck') {
//            $type = str_replace('Bus/Truck', 'Autobuss/Kravas', $type);
//          }
//
//          $position->type = 'TRUCK';
//          $position->li = $li;
//          $position->si = $si;
//          $position->price1 = $price1;
//          $position->price2 = $price2;
//          $position->implemention = $type;
//          $position->kind = null;
//          (empty($item->buss_possition)) ? $position->axis_bus = null : $position->axis_bus = $item->buss_possition;
//          (empty($item->truck_possition)) ? $position->axis_truck = null : $position->axis_truck = $item->truck_possition;
//          (empty($item->road_for_Buss)) ? $position->conditions_bus = null : $position->conditions_bus = $item->road_for_Buss;
//          (empty($item->road_for_trucks)) ? $position->conditions_truck = null : $position->conditions_truck = $item->road_for_trucks;
//          $position->offer = null;
//          $position->priceoffer = null;
//          $position->comment = null;
//          if ($item->qty_available > 0) {
//            if (!empty($d1) && !empty($d2) && !empty($d3) || !empty($d1) && empty($d2) && !empty($d3)) {
//              $position->visible_users = 1;
//              $position->visible_list = 1;
//            } else {
//              $position->visible_users = 0;
//              $position->visible_list = 0;
//            }
//          } else {
//            $position->visible_users = 0;
//            $position->visible_list = 0;
//          }
//
//          $position->available = 1;
//          $position->article = $article;
//
//          $position->save();
//
//          if ($article !== '') {
//            $position->addSecondaryArticle($article, 'i3');
//          }
//
//          $lists = Bigstock::where('article', $article)->where('itype', 'i3')->get();
//
//          foreach ($lists as $list) {
//            $list->update(['quantity' => $item->qty_available, 'updated_at' => date('Y-m-d H:i:s')]);
//            $updated++;
//          }
//
//          $counted++;
//
//        }
//
//      }
//      DB::table('sync_times')->where('name', 'i3-big')->update(['updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
//      echo "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)\n";
//    }

    public function getBigSizes($tire)
    {
      $fullSize = [];

      $title = explode(' ', $tire->ArticleText)[0];
      $title = str_replace(',', '.', $title);

      if ($tire->Radial == true) {
        if (strpos($title, 'R') === true) return false;
        $delimiters = ['/', 'R', '-'];
        $pattern = '/(' . implode('|', array_map(function($delimiter) {
            return preg_quote($delimiter, '/');
          }, $delimiters)) . ')/';
        $parts = preg_split($pattern, $title, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (count($parts) == 5) {
          $d1 = sprintf('%g', $parts[0]);
          $sep1 = (is_string($parts[1])) ? strtolower($parts[1]) : $parts[1];
          $d2 = sprintf('%g', $parts[2]);
          $sep2 = $parts[3];
          $d3 = (fmod($parts[4], 1) === 0.0) ? (int) $parts[4] : $parts[4];
        } else if (count($parts) == 3) {
          if (preg_match("/[a-zA-Z]/i", $parts[0])){
            $parts[0] = str_replace('L', '', $parts[0]);
            $d1 = sprintf('%g', $parts[0]);
            $d1 = $d1 . 'L';
          } else {
            $d1= sprintf('%g', $parts[0]);
          }
          $sep1 = (is_string($parts[1])) ? strtolower($parts[1]) : $parts[1];
          $d2 = null;
          $sep2 = null;
          $d3 = sprintf('%g', $parts[2]);
        }
      } else {
        $delimiters = ['/', 'x', 'X', '-'];
        $pattern = '/(' . implode('|', array_map(function($delimiter) {
            return preg_quote($delimiter, '/');
          }, $delimiters)) . ')/';
        $parts = preg_split($pattern, $title, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (count($parts) == 5) {
          $d1 = sprintf('%g', $parts[0]);
          $sep1 = (is_string($parts[1])) ? strtolower($parts[1]) : $parts[1];
          $d2 = sprintf('%g', $parts[2]);
          $sep2 = $parts[3];
          $d3 = sprintf('%g', $parts[4]);
        } else if (count($parts) == 3) {
          if (preg_match("/[a-zA-Z]/i", $parts[0])){
            $parts[0] = str_replace('L', '', $parts[0]);
            $d1 = sprintf('%g', $parts[0]);
            $d1 = $d1 . 'L';
          } else {
            $d1 = sprintf('%g', $parts[0]);
          }
          $sep1 = (is_string($parts[1])) ? strtolower($parts[1]) : $parts[1];
          $d2 = null;
          $sep2 = null;
          $d3 = sprintf('%g', $parts[2]);
        }
      }

      $fullSize['d1'] = (!isset($d1)) ? null : $d1;
      $fullSize['sep1'] = (!isset($sep1)) ? null : $sep1;
      $fullSize['d2'] = (!isset($d2)) ? null : $d2;
      $fullSize['sep2'] = (!isset($sep2)) ? null : $sep2;
      $fullSize['d3'] = (!isset($d3)) ? null : $d3;

      return $fullSize;
    }

    public function getAgroPr($tire)
    {
      $returnText = '';

      $params = explode(' ', $tire);
      foreach ($params as $param) {
        if (stripos($param, 'PR') !== false) {
          $returnText = preg_replace('~\D~', '', $param);
        } else continue;
      }

      return $returnText;
    }

        public function i3agro()
        {
            try {
                $sync = DB::table('sync_times')->where('name', 'i3-agro')->first();
                $sync_time = \Carbon\Carbon::parse($sync->updated_at)->addHour();
                $time_now = \Carbon\Carbon::now();
                
                if ($time_now->diff($sync_time)->invert == 1) {
                    // Iegūstam datus tikai tad, ja pagājusi stunda kopš pēdējās sinhronizācijas
                    $token_bearer = $this->getI3Token();
                    $filename = dirname(__DIR__, 3) . '/public/storage/xml/i3-agro.txt';
                    
                    // Palielinām taimautu līdz 120 sekundēm
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => env('I3_AGRO_URL'),
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 120, // Palielināts taimauts līdz 120 sekundēm
                        CURLOPT_CONNECTTIMEOUT => 30, // Pievienots savienojuma taimauts
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                            "cache-control: no-cache",
                            "authorization: Bearer " . $token_bearer,
                        ),
                    ));
                    
                    $response = curl_exec($curl);
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $err = curl_error($curl);
                    curl_close($curl);
                    
                    if ($err) {
                        throw new \Exception($err);
                    }
                    
                    if ($httpcode != 200) {
                        throw new \Exception("API atgrieza kļūdas kodu: " . $httpcode);
                    }
                    
                    if (empty($response)) {
                        throw new \Exception("Saņemta tukša atbilde no API");
                    }
                    
                    // Pārbaudām, vai atbilde ir derīgs JSON
                    $contentCheck = json_decode($response);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception("Saņemts nederīgs JSON formāts: " . json_last_error_msg());
                    }
                    
                    // Saglabājam saņemtos datus failā
                    file_put_contents($filename, $response);
                    chmod($filename, 0775);
                }
                
                // Nullējam daudzumu visām agro i3 precēm
                Bigstock::where('itype', 'i3')->where('type', 'agro')->update(['quantity' => 0, 'visible_users' => 0, 'visible_list' => 0]);
                
                // Pārbaudām, vai fails eksistē pirms lasīšanas
                $filename = dirname(__DIR__, 3) . '/public/storage/xml/i3-agro.txt';
                if (!file_exists($filename)) {
                    throw new \Exception("Datu fails nav atrasts: " . $filename);
                }
                
                $content = file_get_contents($filename);
                $content = json_decode($content);
                
                if (!is_array($content) && !is_object($content)) {
                    throw new \Exception("Kļūda dekodējot JSON no faila");
                }
                
                $counted = 0;
                $updated = 0;
                
                $returnText = '';
                
                foreach ($content as $item) {
                    $counted++;
                    
                    $tire = Bigtire::where('article', $item->ArticleId)->first();
                    $newTire = false;
                    
                    if ($tire == null) {
                        $newTire = true;
                        $tire = new Bigtire();
                    }
                    
                    $tire->timestamps = false;
                    
                    $imageId = $item->ImageId;
                    
                    $brand = Bigbrand::where('title', $item->BrandName)->first();
                    $tread = Bigtread::where('title', $item->PatternModelText)->first();
                    
                    if ($brand === null) {
                        $brand = new Bigbrand;
                        $brand->timestamps = false;
                        $brand->title = $item->BrandName;
                        $brand->slug = Str::slug($brand->title);
                        $brand->save();
                    }

                    if ($tread === null) {
                        $tread = new Bigtread;
                        $tread->timestamps = false;
                        $tread->brand_id = $brand->brand_id;
                        $tread->title = $item->PatternModelText;
                        $tread->slug = Str::slug($tread->title);
                        $tread->save();
                    } else {
                        if ($tread->brand_id != $brand->brand_id) {
                            $tread = new Bigtread;
                            $tread->timestamps = false;
                            $tread->brand_id = $brand->brand_id;
                            $tread->title = $item->PatternModelText;
                            $tread->slug = Str::slug($tread->title);
                            $tread->save();
                        }
                    }

                    $treadId = $tread->tread_id;

                    $quantity = intval($item->QuantityAvailable);
                    if ($imageId != null) {
                        $outPath = dirname(__DIR__, 3) . '/public/storage/industrial/tread/' . $treadId . '-o.jpg';

                        if (!file_exists($outPath)) {
                            $this->grab_image(env('I3_IMAGE_URL') . $imageId, $outPath);
                        }
                    }


                    $sizes = $this->getBigSizes($item);

                    $pr = $this->getAgroPr($item->ArticleText);

                    $tire->make_id = $treadId;
                    $tire->d1 = $sizes['d1'];
                    $tire->sep = $sizes['sep1'];
                    $tire->d2 = $sizes['d2'];
                    $tire->sep2 = $sizes['sep2'];
                    $tire->d3 = $sizes['d3'];
                    $tire->type = 'AGRO';
    //        $tire->type = ($item->MainGroupName) ? 'AGRO' : 'IND';
                    $tire->li = ($item->LoadIndex !== null) ? $item->LoadIndex : null;
                    $tire->si = ($item->SpeedIndex !== null) ? $item->SpeedIndex : null;
                    $tire->code = $pr; // PR
                    $tire->price1 = ceil((round(($item->NetPrice * 1.21), 2) + 15) / 0.7);
                    $tire->price2 = $item->Price;
                    $tire->price3 = floor(round($item->RetailPrice * 1.21, 2));
                    $tire->implemention = null;
                    $tire->kind = null;
                    $positionText = $item->PositionText;
                    $parts = preg_split('/(?=[A-Z])/', $positionText);
                    $positionText = implode(' ', $parts);
                    $tire->axis = $positionText;
                    $tire->conditions = null;
                    $tire->visible_users = 1;
                    $tire->visible_list = 1;
                    $tire->available = 0;
                    $tire->article = $item->ArticleId;
                    $tire->quantity = 0;
                    $tire->urs_quantity = 0;
                    $tire->krs_quantity = 0;
                    $tire->updated_at = Carbon::now()->format('Y-m-d H:i:s');

                    $tire->save();

                    $metadata = 'price: ' . round(($item->Price * 1.21), 2) . '; pkpcena: ' . round(($item->NetPrice * 1.21), 2) . '; Baseprice: ' . round(($item->RetailPrice * 1.21), 2) . ';';

                    $stock = Bigstock::where('tire_id', $tire->tire_id)->first();

                    if ($stock == null) $stock = new Bigstock();
                    $stock->tire_id = $tire->tire_id;
                    $stock->article = $tire->article;
                    $stock->quantity = $quantity;
    //        $tireVisible = Bigtire::where('article', $stock->article)->first();
    //        if (!is_null($tireVisible)) {
    //          if ($quantity > 0) {
    //            if ($quantity > 4) {
    //              $tireVisible->visible_users = 1;
    //              $tireVisible->visible_list = 1;
    //            } else {
    //              $tireVisible->visible_users = 0;
    //              $tireVisible->visible_list = 0;
    //            }
    //          } else {
    //            $tireVisible->visible_users = 0;
    //            $tireVisible->visible_list = 0;
    //          }
    //        }
                    $stock->itype = 'i3';
                    $stock->type = 'agro';
                    $stock->metadata = $metadata;
    //        $tireVisible->save();
                    if ($stock->save()) {
                        $updated++;
                    }
                    $counted++;

                }

                DB::table('sync_times')->where('name', 'i3-agro')->update(['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
                return "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)";
                
            } catch (\Exception $e) {
                // Reģistrējam kļūdu žurnālā
                \Log::error('Kļūda metodē i3agro: ' . $e->getMessage());
                
                // Produkcijā, iespējams, nevajadzētu rādīt detalizētu kļūdas ziņojumu, bet atgriezt vispārēju ziņojumu
                return "Sinhronizācijas kļūda: " . $e->getMessage();
            }
        }

    public function i3big()
    {
      set_time_limit(0);

      $sync = DB::table('sync_times')->where('name', 'i3-big')->first();
      $sync_time = \Carbon\Carbon::parse($sync->updated_at)->addHour();
      $time_now = \Carbon\Carbon::now();
      if ($time_now->diff($sync_time)->invert == 1) {

        $token_bearer = $this->getI3Token();

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => env('I3_TRUCK_URL'),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "authorization: Bearer " . $token_bearer,
          ),
        ));
        $response = curl_exec($curl);

        $filename = dirname(__DIR__, 3) . '/public/storage/xml/i3-industrial.txt';

        file_put_contents($filename, $response);
        chmod($filename, 0775);

        $err = curl_error($curl);

        if ($err) throw new \Exception($err);

        curl_close($curl);
      }

      $counted = 0;
      $updated = 0;

      Bigstock::where('itype', 'i3')->where('type', 'truck')->update(['quantity' => 0]);

      $content = file_get_contents(dirname(__DIR__, 3) . '/public/storage/xml/i3-industrial.txt');
      $content = json_decode($content);

      $returnText = '';

      foreach ($content as $item) {

        $counted++;

        $tire = Bigtire::where('article', $item->ArticleId)->first();
        $newTire = false;

        if ($tire == null) {
          $newTire = true;
          $tire = new Bigtire();
        }

        $tire->timestamps = false;

        $imageId = $item->ImageId;

        $brand = Bigbrand::where('title', $item->BrandName)->first();
        $tread = Bigtread::where('title', $item->PatternModelText)->first();


        if ($brand === null) {
          $brand = new Bigbrand;
          $brand->timestamps = false;
          $brand->title = $item->BrandName;
          $brand->slug = Str::slug($brand->title);
          $brand->save();
        }

        if ($tread === null) {
          $tread = new Bigtread;
          $tread->timestamps = false;
          $tread->brand_id = $brand->brand_id;
          $tread->title = $item->PatternModelText;
          $tread->slug = Str::slug($tread->title);
          $tread->save();
        } else {
          if ($tread->brand_id != $brand->brand_id) {
            $tread = new Bigtread;
            $tread->timestamps = false;
            $tread->brand_id = $brand->brand_id;
            $tread->title = $item->PatternModelText;
            $tread->slug = Str::slug($tread->title);
            $tread->save();
          }
        }

        $treadId = $tread->tread_id;

        $quantity = intval($item->QuantityAvailable);
        if ($imageId != null) {
          $outPath = dirname(__DIR__, 3) . '/public/storage/industrial/tread/' . $treadId . '-o.jpg';

          if (!file_exists($outPath)) {
            $this->grab_image(env('I3_IMAGE_URL') . $imageId, $outPath);
          }
        }


        $sizes = $this->getBigSizes($item);

        $tire->make_id = $treadId;
        $tire->d1 = $sizes['d1'];
        $tire->sep = $sizes['sep1'];
        $tire->d2 = $sizes['d2'];
        $tire->sep2 = $sizes['sep2'];
        $tire->d3 = $sizes['d3'];
        $tire->type = 'Truck';
//        $tire->type = ($item->MainGroupName) ? 'AGRO' : 'IND';
        $tire->li = ($item->LoadIndex !== null) ? $item->LoadIndex : '';
        $tire->si = ($item->SpeedIndex !== null) ? $item->SpeedIndex : '';
        $tire->code = null; // PR
        $tire->price1 = ceil((round(($item->NetPrice * 1.21), 2) + 15) / 0.7);
        $tire->price2 = $item->Price;
        $tire->price3 = floor(round($item->RetailPrice * 1.21, 2));
        $tire->implemention = 'Kravas/Autobuss';
        $tire->kind = null;
        $positionText = $item->PositionText;
        $parts = preg_split('/(?=[A-Z])/', $positionText);
        $positionText = implode(' ', $parts);
        $tire->axis = ltrim($positionText, ' ');
        $tire->conditions = null;
        $tire->visible_users = 1;
        $tire->visible_list = 1;
        $tire->available = 0;
        $tire->article = $item->ArticleId;
        $tire->quantity = 0;
        $tire->urs_quantity = 0;
        $tire->krs_quantity = 0;
        $tire->updated_at = Carbon::now()->format('Y-m-d H:i:s');

        $tire->save();

        $metadata = 'price: ' . round(($item->Price * 1.21), 2) . '; pkpcena: ' . round(($item->NetPrice * 1.21), 2) . '; Baseprice: ' . round(($item->RetailPrice * 1.21), 2) . ';';

        $stock = Bigstock::where('tire_id', $tire->tire_id)->first();

        if ($stock == null) $stock = new Bigstock();
        $stock->tire_id = $tire->tire_id;
        $stock->article = $tire->article;
        $stock->quantity = $quantity;
//        $tireVisible = Bigtire::where('article', $stock->article)->first();
//        if (!is_null($tireVisible)) {
//          if ($quantity > 0) {
//            if ($quantity > 4) {
//              $tireVisible->visible_users = 1;
//              $tireVisible->visible_list = 1;
//            } else {
//              $tireVisible->visible_users = 0;
//              $tireVisible->visible_list = 0;
//            }
//          } else {
//            $tireVisible->visible_users = 0;
//            $tireVisible->visible_list = 0;
//          }
//        }
        $stock->itype = 'i3';
        $stock->type = 'truck';
        $stock->metadata = $metadata;
//        $tireVisible->save();
        if ($stock->save()) {
          $updated++;
        }
        $counted++;

      }

      DB::table('sync_times')->where('name', 'i3-big')->update(['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
      Bigtire::clearCatalogCache();
      echo "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)\n";
    }

    public function gy()
    {
      $file = 'GDYR_EE_CONFIDENTIAL_STOCKREPORT_CONSUMER.csv';

      $server = 'ftp.goodyear.eu';
      $acc = 'p22989';
      $passw = 'm4kXUrRWkx8aHDCU';
      $remotePath = 'GDYR_EE_CONFIDENTIAL_STOCKREPORT_CONSUMER.csv';

      $localDir = storage_path('app/tmp');
      if (!is_dir($localDir) && !mkdir($localDir, 0775, true) && !is_dir($localDir)) {
        throw new Exception('Unable to create temporary directory for Goodyear sync');
      }
      $localPath = $localDir . DIRECTORY_SEPARATOR . $file;

      $ftp = ftp_connect($server) or die ();
      ftp_login($ftp, $acc, $passw);
      ftp_pasv($ftp, true);

      if(!ftp_get($ftp, $localPath, $remotePath, FTP_ASCII)) {
        $error = error_get_last();
        ftp_close($ftp);
        throw new Exception('Could not read remote file: '. print_r($error, true));
      }

      ftp_close($ftp);

      $stock = [];

      if (($handle = fopen($localPath, "r")) !== FALSE)
      {
        $i = 0;
        while (($data = fgetcsv($handle, 0, ";")) !== FALSE)
        {
          $stock[((string) $data[2])] = (int) $data[5];
          $i++;
        }
        fclose($handle);
      }
      unlink($localPath);

      file_put_contents(dirname(__DIR__, 3) . '/public/storage/xml/GDYR_EE_CONFIDENTIAL_STOCKREPORT_CONSUMER.csv', $stock);

      echo "Auto riepas<br>";
      Autostock::where('itype', 'gy')->update(['quantity' => 0]);

      $keys = array_keys($stock);
      $values = array_values($stock);

      $intKeys = array_map('intval', $keys);

      $stock = array_combine($intKeys, $values);

      unset($stock[0]);

      $updated = 0;
      $counted = 0;
      foreach ($stock as $item => $quantity){
        $article = $item;
        $quantity = intval($quantity);

        $list = Autostock::where('article', $article)->where('itype', 'gy')->get();

        $metadata = '';
        $discount = @$item->discount; if ($discount!='') $metadata.='discount: '.$discount.'; ';

        foreach ($list as $item){
          $item->quantity = $quantity;
          $item->metadata = $metadata;
          $item->save();
          $updated++;
        }
        $counted++;
      }
      DB::table('sync_times')->where('name', 'gy-auto')->update(['updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
      echo "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)\n";
    }

    public function rzauto()
    {
      $url = 'https://riepuzona.lv/partnerproducts.xml?email=xml@r1.com.lv&password=R1nok1An';

      $opts = ['http' => [
        'method' => 'GET',
        'timeout' => 100,
      ],
      ];

      $context = stream_context_create($opts);
      $xmlString = file_get_contents($url, false, $context);

      file_put_contents(dirname(__DIR__, 3) . '/public/storage/xml/rz.auto.xml', $xmlString);

      $xml = simplexml_load_string($xmlString);

      unset($context);

      Autostock::where('itype', 'rz')->update(['quantity' => 0]);

      $updated = 0;
      $counted = 0;

      foreach ($xml->item as $item) {
        $article = $item->code;
        $quantity = str_replace('>', '', $item->stock_amount);
        $quantity = intval($quantity);

        $list = Autostock::where('article', $article)->where('itype', 'rz')->get();

        $metadata = '';
        $discount = @$item->discount; if ($discount != '') $metadata.='discount: ' . $discount . '; ';

        foreach ($list as $item){
          $item->quantity = $quantity;
          $item->metadata = $metadata;
          $item->save();
          $updated++;
        }
        $counted++;
      }
      DB::table('sync_times')->where('name', 'rz-auto')->update(['updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
      echo "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)\n";
    }

    /**
     * Riepu Garāža (ecom) tyres.xml — same pattern as rzauto(): reset rg quantities, then apply stock per article.
     * URL: env RIEPU_GARAZA_TYRES_XML_URL or default hash URL. Feed may return <error code="E0408"> (30 min rate limit).
     */
    public function rgauto()
    {
      $url = env('RIEPU_GARAZA_TYRES_XML_URL', 'https://ecom.riepugaraza.lv/xml/306c1eae0c022c10c5cf96063f24cb47/tyres.xml');

      $opts = [
        'http' => [
          'method' => 'GET',
          'timeout' => 120,
        ],
      ];

      $context = stream_context_create($opts);
      $xmlString = @file_get_contents($url, false, $context);
      unset($context);

      $storageDir = dirname(__DIR__, 3) . '/public/storage/xml';
      if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0775, true);
      }
      if ($xmlString !== false && $xmlString !== '') {
        file_put_contents($storageDir . '/rg.auto.xml', $xmlString);
      }

      if ($xmlString === false || $xmlString === '') {
        echo "RG: failed to download XML\n";
        return;
      }

      $xml = @simplexml_load_string($xmlString);
      if ($xml === false) {
        echo "RG: invalid XML\n";
        return;
      }

      if (isset($xml->error)) {
        $code = isset($xml->error['code']) ? (string) $xml->error['code'] : '';
        $msg = trim((string) $xml->error);
        echo "RG XML error {$code}: {$msg}\n";
        return;
      }

      if (!isset($xml->tyres) || !isset($xml->tyres->tyre)) {
        echo "RG: no tyres in XML\n";
        return;
      }

      Autostock::where('itype', 'rg')->update(['quantity' => 0]);

      $updated = 0;
      $counted = 0;

      foreach ($xml->tyres->tyre as $tyre) {
        $article = trim((string) $tyre->id);
        if ($article === '') {
          continue;
        }

        $quantity = 0;
        if (isset($tyre->qty->stock)) {
          $raw = str_replace(',', '.', trim((string) $tyre->qty->stock));
          $quantity = (int) round((float) $raw);
        }

        $list = Autostock::where('article', $article)->where('itype', 'rg')->get();

        $metadata = '';

        foreach ($list as $row) {
          $row->quantity = $quantity;
          $row->metadata = $metadata;
          $row->save();
          $updated++;
        }
        $counted++;
      }

      DB::table('sync_times')->updateOrInsert(
        ['name' => 'rg-auto'],
        ['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]
      );

      echo "Mainīti {$updated} ieraksti (sarakstā {$counted} riepas)\n";
    }

    public function rzautoshow()
    {
      echo 'Auto riepas:<br>';
      $stocks = Autostock::where('itype', 'rz')->get();
      foreach ($stocks as $stock) {
        $tire = Autotire::where('tire_id', $stock->tire_id)->first();
        if (!$tire) continue;
        $text = $tire->title . ' ' . $tire->li . $tire->si . ' ' .( $tire->fullSize) . ' [' . $tire->article . ']:[' . $stock->article . ']: ' . $stock->quantity . ' / ' . $stock->metadata . '<br>';
        //dd($text);
        echo $text;
      }
    }

    private static function multiexplode($delimiters, $string) {
      @$ready = str_replace($delimiters, $delimiters[0], $string);
      return explode($delimiters[0], $ready);
    }

    private static function getByArticle($article) {
      $size = Bigtire::where('article', $article)->first();
      if ($size !== NULL) {
        return $size;
      } else {
        return false;
      }
    }

    private static function getBrandId($name) {
      $brand = Bigbrand::where('title', $name)->first();
      if ($brand !== NULL) {
        return $brand->brand_id;
      } else {
        return false;
      }
    }

    private static function getTreadId($name, $brand) {
      $brandID = SyncController::getBrandId($brand);
      if ($brandID === false) return false;

      $list = Bigtread::where('title', $name)->where('brand_id', $brandID)->first();
      if (!empty($list)) {
        return $list->tread_id;
      } else {
        return false;
      }
    }

    /**
     * Starco catalog: resolve tread_id with in-memory caches (cuts repeated brand/tread queries).
     *
     * @param array<string,int|false> $brandTitleCache title => brand_id or false if missing pre-insert
     * @param array<string,int>       $treadPairCache  "brand\0tread" => tread_id
     */
    private static function starcoResolveTreadId(
      string $tread,
      string $brand,
      array &$brandTitleCache,
      array &$treadPairCache
    ) {
      $pairKey = $brand . "\0" . $tread;
      if (array_key_exists($pairKey, $treadPairCache)) {
        return $treadPairCache[$pairKey];
      }

      if (!array_key_exists($brand, $brandTitleCache)) {
        $b = Bigbrand::where('title', $brand)->first();
        $brandTitleCache[$brand] = $b !== null ? $b->brand_id : false;
      }
      $brandID = $brandTitleCache[$brand];
      if ($brandID === false) {
        $brandID = Bigbrand::insertGetId([
          'title' => $brand,
          'slug' => Str::slug($brand),
        ]);
        $brandTitleCache[$brand] = $brandID;
      }

      $list = Bigtread::where('title', $tread)->where('brand_id', $brandID)->first();
      if (!empty($list)) {
        return $treadPairCache[$pairKey] = $list->tread_id;
      }

      $treadId = Bigtread::insertGetId([
        'brand_id' => $brandID,
        'title' => $tread,
        'slug' => Str::slug($tread),
      ]);
      return $treadPairCache[$pairKey] = $treadId;
    }

    private function grab_image($url,$saveto){

      $token_bearer = $this->getI3Token();

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache",
          "authorization: Bearer " . $token_bearer,
        ),
      ));
      $raw = curl_exec($curl);
      $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close ($curl);
      if ($status !== 404) {
        if(file_exists($saveto)){
          unlink($saveto);
        }
        $fp = fopen($saveto,'x');
        fwrite($fp, $raw);
        fclose($fp);
      }
    }

    private static function starco_image($url,$saveto)
    {
      $ch = curl_init ($url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
      $raw=curl_exec($ch);
      $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close ($ch);
      if ($status !== 404) {
        if (file_exists($saveto)) {
          unlink($saveto);
        }
        $fp = fopen($saveto, 'x');
        fwrite($fp, $raw);
        fclose($fp);
      }
    }

    /**
     * GET JSON resource from Starco API; returns decoded ["value"] array.
     *
     * @throws \RuntimeException on network/auth/parse errors
     */
    private function fetchStarcoValue(string $relativePath): array
    {
      $base = rtrim((string) config('services.starco.api_base', 'http://remote.starco.lv:8153/api.rsc/'), '/') . '/';
      $login = (string) config('services.starco.login', '');
      $password = (string) config('services.starco.password', '');
      if ($login === '' || $password === '') {
        throw new \RuntimeException('Starco: set STARCO_LOGIN and STARCO_PASSWORD (config services.starco).');
      }
      $url = $base . ltrim($relativePath, '/');
      $headers = [
        'Authorization: Basic ' . base64_encode($login . ':' . $password),
      ];
      $context = stream_context_create([
        'http' => [
          'header' => $headers,
          'method' => 'GET',
          'timeout' => 300,
          'ignore_errors' => true,
        ],
      ]);
      $string = @file_get_contents($url, false, $context);
      if ($string === false) {
        throw new \RuntimeException('Starco: request failed for ' . $relativePath);
      }
      $decoded = json_decode($string, true);
      if (!is_array($decoded) || !isset($decoded['value']) || !is_array($decoded['value'])) {
        throw new \RuntimeException('Starco: invalid or empty JSON value for ' . $relativePath);
      }
      return $decoded['value'];
    }

    /**
     * Cache catalog JSON for Bigtire::StockLink (reads public/starco.sync.xml).
     * Atomic write via temp file + rename to avoid half-written reads.
     *
     * @throws \RuntimeException if the directory is not writable
     */
    private function writeStarcoSyncCache(string $jsonBody): void
    {
      $path = public_path('starco.sync.xml');
      $dir = dirname($path);
      if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
          throw new \RuntimeException('Starco: cannot create directory ' . $dir);
        }
      }
      if (is_file($path) && !is_writable($path)) {
        throw new \RuntimeException('Starco: not writable ' . $path . ' — chown/chmod for PHP user (www-data).');
      }
      if (!is_writable($dir)) {
        throw new \RuntimeException('Starco: directory not writable ' . $dir . ' — fix permissions for web server user.');
      }
      $tmp = $path . '.' . uniqid('tmp', true);
      if (file_put_contents($tmp, $jsonBody, LOCK_EX) === false) {
        throw new \RuntimeException('Starco: cannot write temp file in ' . $dir);
      }
      if (!@rename($tmp, $path)) {
        @unlink($tmp);
        throw new \RuntimeException('Starco: cannot rename cache to ' . $path);
      }
    }

    /**
     * Batch UPDATE bigstock rows (itype starco) with per-article quantities — avoids N single-row updates.
     *
     * @param array<string,int> $articleToQty
     */
    private function starcoBulkUpdateBigstockQuantities(array $articleToQty, string $updatedAt): void
    {
      if ($articleToQty === []) {
        return;
      }
      $table = (new Bigstock())->getTable();
      $safeTable = str_replace('`', '``', $table);
      $caseParts = [];
      $bindings = [];
      $articles = [];
      foreach ($articleToQty as $article => $qty) {
        $caseParts[] = 'WHEN ? THEN ?';
        $bindings[] = $article;
        $bindings[] = (int) $qty;
        $articles[] = $article;
      }
      $inPlaceholders = implode(',', array_fill(0, count($articles), '?'));
      $bindings[] = $updatedAt;
      $bindings[] = 'starco';
      foreach ($articles as $a) {
        $bindings[] = $a;
      }
      $sql = 'UPDATE `' . $safeTable . '` SET `quantity` = CASE `article` '
        . implode(' ', $caseParts)
        . ' END, `updated_at` = ? WHERE `itype` = ? AND `article` IN (' . $inPlaceholders . ')';
      DB::update($sql, $bindings);
    }

    /**
     * Debug perf: NDJSON line to workspace debug-070f40.log (session 070f40).
     */
    private function starcoDebugLog(string $hypothesisId, string $message, array $data = []): void
    {
      // #region agent log
      $path = dirname(base_path()) . DIRECTORY_SEPARATOR . 'debug-070f40.log';
      $payload = [
        'sessionId' => '070f40',
        'hypothesisId' => $hypothesisId,
        'location' => 'SyncController::starco',
        'message' => $message,
        'data' => $data,
        'timestamp' => (int) round(microtime(true) * 1000),
      ];
      @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
      // #endregion
    }

    public function starco()
    {
      // #region agent log
      $starcoT0 = microtime(true);
      $starcoImgMs = 0.0;
      $starcoImgCalls = 0;
      $starcoImgSkipped = 0;
      $starcoTBeforeStockLoop = null;
      $starcoTBeforePriceLoop = null;
      // #endregion

      $tires = $this->fetchStarcoValue('lva_product_catalog_tyres');
      $this->writeStarcoSyncCache(json_encode($tires));

      // #region agent log
      $this->starcoDebugLog('H3', 'phase_fetch_catalog_and_cache', [
        'ms' => round((microtime(true) - $starcoT0) * 1000, 2),
        'tires_count' => is_array($tires) ? count($tires) : 0,
      ]);
      $starcoTAfterCache = microtime(true);
      // #endregion

      $initial = ["/[0-9.]+/", "/L/", "/S/", "/VF/", "/FI/", "/P/", "/SL/", "/DW/", "/IF/", "/CFO/"];

      Bigtire::query()->update(['visible_users' => 0, 'visible_list' => 0]);

      // #region agent log
      $this->starcoDebugLog('H5', 'phase_reset_all_visibility', [
        'ms' => round((microtime(true) - $starcoTAfterCache) * 1000, 2),
      ]);
      $starcoTBeforeCatalogLoop = microtime(true);
      // #endregion

      $bigtireArticleSet = array_fill_keys(array_filter(array_map(
        'strval',
        Bigtire::query()->whereNotNull('article')->pluck('article')->all()
      )), true);

      $starcoBtByArticle = [];
      $starcoBrandTitleCache = [];
      $starcoTreadPairCache = [];
      $starcoEarlySizeSkip = 0;
      $starcoLoadBigtire = function ($article) use (&$starcoBtByArticle) {
        if (!array_key_exists($article, $starcoBtByArticle)) {
          $row = Bigtire::where('article', $article)->first();
          $starcoBtByArticle[$article] = $row !== null ? $row : false;
        }
        return $starcoBtByArticle[$article];
      };

      $counted = 0;
      $updated = 0;
      foreach ($tires as $item) {

        $article = $item['product_no'];

        if (isset($bigtireArticleSet[$article])) {
          if (strpos($item['Specification'], 'VISUAL DEFECT') !== false) {
            $position = $starcoLoadBigtire($article);
            if ($position !== false) {
              $position->visible_users = 0;
              $position->visible_list = 0;
              $position->save();
              $starcoBtByArticle[$article] = $position;
            }
          }

        }

        $type = $item['segment_description'];

        if ($type === 'AGRO' || $type === 'IND' || $type === 'CONSTR') {

          if ($item['enabled'] == 'YES') {

            if ($type === 'CONSTR') $type = 'IND';

            $size = $item['Size'];
            $size = str_replace(" ", "", $size);
            $size = str_replace(",", ".", $size);
            $size = str_replace("X", "x", $size);
            $size = str_replace("\\", "", $size);

            $brand = ucfirst(strtolower($item['Brand']));
            $tread = $item['Profil'];

            $exploded = SyncController::multiexplode(["/", "-", "R", "x", "D"], $size);

            $ex0 = floatval(strtr(strtr((string)($exploded[0] ?? ''), ['(' => '']), [')' => '']));
            $ex1 = floatval(strtr(strtr((string)($exploded[1] ?? ''), ['(' => '']), [')' => '']));
            $ex2 = isset($exploded[2])
              ? floatval(strtr(strtr((string) $exploded[2], ['(' => '']), [')' => '']))
              : 0.0;

            if ($ex0 === floatval(0)) {
              // #region agent log
              $starcoEarlySizeSkip++;
              // #endregion
              continue;
            }

            $size = preg_replace($initial, "", $size);
            $size = strtr($size, ['(-)' => '']);

            $size = str_split($size);

            for ($x = 0; $x < count($size); $x++) {
              $sepNr = $x + 1;
              ${"sep$sepNr"} = $size[$x];
            }

            $li = $item['LI_1'];
            $si = $item['SI_1'];

            $position = $starcoLoadBigtire($article);
            if ($position === false) {
              $position = new Bigtire();
            }

            $treadId = null;

            if (!empty($brand) && !empty($tread)) {
              $treadId = SyncController::starcoResolveTreadId(
                $tread,
                $brand,
                $starcoBrandTitleCache,
                $starcoTreadPairCache
              );
            }

            $position->make_id = $treadId;

            if ($treadId !== null) {
              $outPath = dirname(__DIR__, 3) . '/public/storage/industrial/tread/' . (int) $treadId . '-o.jpg';

              // Skip HTTP if image already cached (dramatically cuts catalog-loop time).
              $forceImg = (bool) env('STARCO_FORCE_IMAGE_REFRESH', false);
              if (!$forceImg && is_file($outPath) && filesize($outPath) > 512) {
                // #region agent log
                $starcoImgSkipped++;
                // #endregion
              } else {
                // #region agent log
                $imgT0 = microtime(true);
                // #endregion
                Self::starco_image('http://194.19.236.7/Pictures/' . $article . '.jpg', $outPath);
                // #region agent log
                $starcoImgMs += (microtime(true) - $imgT0) * 1000;
                $starcoImgCalls++;
                // #endregion
              }
            }

            $position->d1 = sprintf('%g', $ex0);
            $position->sep = $sep1;
            if (!$ex2) {
              $position->d2 = NULL;
              $position->sep2 = NULL;
              $position->d3 = $ex1;
            } else {
              $position->d2 = sprintf('%g', $ex1);
              $position->sep2 = $sep2;
              $position->d3 = $ex2;
            }

            $position->type = $type;
            $position->li = $li;
            $position->si = $si;
            $position->code = $item['PR'];
            $position->price1 = NULL;
            $position->price2 = NULL;
            $position->implemention = $item['sub_segment_description'];
            $position->kind = NULL;
            $position->axis = NULL;
            $position->conditions = NULL;
            $position->offer = NULL;
            $position->priceoffer = NULL;
            $position->comment = $item['Radial_Diagonal'];
            $position->visible_users = 1;
            $position->visible_list = 1;
            if (strpos($item['Specification'], 'VISUAL DEFECT') !== false) {
              $position->visible_users = 0;
              $position->visible_list = 0;
            }
            $position->available = 1;
            $position->article = $article;
            $position->quantity = 0;

            $position->save();
            $starcoBtByArticle[$article] = $position;
            $bigtireArticleSet[$article] = true;

            if ($article !== '') {
              $position->addSecondaryArticle($article, 'starco');
            }

            $updated++;

          }
        }
        $counted++;
      }

      // #region agent log
      $catalogLoopMs = (microtime(true) - $starcoTBeforeCatalogLoop) * 1000;
      $this->starcoDebugLog('H1', 'phase_catalog_loop_done', [
        'loop_ms' => round($catalogLoopMs, 2),
        'img_total_ms' => round($starcoImgMs, 2),
        'img_calls' => $starcoImgCalls,
        'img_skipped_cached' => $starcoImgSkipped,
        'early_zero_size_skips' => $starcoEarlySizeSkip,
        'catalog_non_img_ms' => round(max(0, $catalogLoopMs - $starcoImgMs), 2),
        'counted' => $counted,
        'updated' => $updated,
      ]);
      $starcoTBeforeStockFetch = microtime(true);
      // #endregion

      echo "Mainīti {$updated} ieraksti (sarakstā {$counted} ieraksti)<br>";

      $stockItems = $this->fetchStarcoValue('current_stock_full');
      $starcoTAfterStockFetch = microtime(true);

      // #region agent log
      $this->starcoDebugLog('H3', 'phase_fetch_stocks', [
        'ms' => round(($starcoTAfterStockFetch - $starcoTBeforeStockFetch) * 1000, 2),
        'stock_rows' => is_array($stockItems) ? count($stockItems) : 0,
      ]);
      // #endregion

      // #region agent log
      $starcoTBeforeStockLoop = microtime(true);
      // #endregion

      $bigtireArticleSet = array_fill_keys(array_filter(array_map(
        'strval',
        Bigtire::query()->whereNotNull('article')->pluck('article')->all()
      )), true);
      $starcoStockArticleSet = array_fill_keys(array_filter(array_map(
        'strval',
        Bigstock::query()->where('itype', 'starco')->pluck('article')->all()
      )), true);

      $stockHideArticles = [];
      $stockQtyByArticle = [];
      foreach ($stockItems as $item) {
        $pno = (string) $item['product_no'];
        if ($pno === '') {
          continue;
        }

        if ((int) $item['RIG_STOCK'] === 0) {
          if (!empty($bigtireArticleSet[$pno])) {
            $stockHideArticles[$pno] = true;
          }
        } else {
          if (!empty($starcoStockArticleSet[$pno])) {
            $stockQtyByArticle[$pno] = (int) $item['RIG_STOCK'];
          }
        }
      }

      $updatedAtStock = date('Y-m-d H:i:s');

      foreach (array_chunk(array_keys($stockHideArticles), 500) as $chunk) {
        if ($chunk !== []) {
          Bigtire::whereIn('article', $chunk)->update([
            'visible_users' => 0,
            'visible_list' => 0,
            'updated_at' => $updatedAtStock,
          ]);
        }
      }

      foreach (array_chunk($stockQtyByArticle, 450, true) as $slice) {
        $this->starcoBulkUpdateBigstockQuantities($slice, $updatedAtStock);
      }

      // #region agent log
      $this->starcoDebugLog('H4', 'phase_stock_loop_done', [
        'ms' => ($starcoTBeforeStockLoop !== null)
          ? round((microtime(true) - $starcoTBeforeStockLoop) * 1000, 2)
          : null,
      ]);
      $starcoTBeforePriceFetch = microtime(true);
      // #endregion

      echo "Preču daudzumi atjaunoti!<br>";

      $starcoLogin = (string) config('services.starco.login', '202562');
      $priceItems = $this->fetchStarcoValue($starcoLogin . '_pl');
      $starcoTAfterPriceFetch = microtime(true);

      // #region agent log
      $this->starcoDebugLog('H3', 'phase_fetch_prices', [
        'ms' => round(($starcoTAfterPriceFetch - $starcoTBeforePriceFetch) * 1000, 2),
        'price_rows' => is_array($priceItems) ? count($priceItems) : 0,
      ]);
      // #endregion

      $starcoTBeforePriceLoop = microtime(true);

      $priceProductNos = [];
      foreach ($priceItems as $row) {
        $a = isset($row['product_no']) ? (string) $row['product_no'] : '';
        if ($a !== '') {
          $priceProductNos[$a] = true;
        }
      }
      $priceArticleKeys = array_keys($priceProductNos);

      $bigtiresByArticle = [];
      if ($priceArticleKeys !== []) {
        foreach (array_chunk($priceArticleKeys, 900) as $chunk) {
          foreach (Bigtire::whereIn('article', $chunk)->get(['article']) as $t) {
            if ($t->article !== null && $t->article !== '') {
              $bigtiresByArticle[(string) $t->article] = true;
            }
          }
        }
      }

      $starcoStockQtyForPrice = [];
      if ($priceArticleKeys !== []) {
        foreach (array_chunk($priceArticleKeys, 900) as $chunk) {
          foreach (Bigstock::where('itype', 'starco')->whereIn('article', $chunk)->get(['article', 'quantity']) as $s) {
            if ($s->article !== null && $s->article !== '') {
              $starcoStockQtyForPrice[(string) $s->article] = (int) $s->quantity;
            }
          }
        }
      }

      $updatedAtPrices = date('Y-m-d H:i:s');

      foreach ($priceItems as $item) {
        $pno = isset($item['product_no']) ? (string) $item['product_no'] : '';
        if ($pno === '' || empty($bigtiresByArticle[$pno])) {
          continue;
        }

        $p = $item['price'];
        $price1 = null;
        $price2 = null;
        $visibilityOff = false;

        if ($p == 0) {
          $price1 = 0;
          $price2 = 0;
          $visibilityOff = true;
        } elseif ($p < 100) {
          $price1 = ($p + 8) / 70 * 100;
          $price2 = $p + 10;
        } elseif ($p >= 100 && $p < 200) {
          $price1 = ($p + 12) / 70 * 100;
          $price2 = $p + 15;
        } elseif ($p >= 200 && $p < 500) {
          $price1 = ($p + 15) / 70 * 100;
          $price2 = $p + 20;
        } elseif ($p >= 500 && $p < 1000) {
          $price1 = ($p + 30) / 70 * 100;
          $price2 = $p + 50;
        } elseif ($p > 1000) {
          $price1 = ($p + 50) / 70 * 100;
          $price2 = $p * 1.07;
        }

        if ($price1 !== null && $price2 !== null) {
          if (
            !$visibilityOff
            && array_key_exists($pno, $starcoStockQtyForPrice)
            && $starcoStockQtyForPrice[$pno] === 0
          ) {
            $visibilityOff = true;
          }

          $payload = [
            'price1' => (int) $price1,
            'price3' => (int) $price2,
            'updated_at' => $updatedAtPrices,
          ];
          if ($visibilityOff) {
            $payload['visible_users'] = 0;
            $payload['visible_list'] = 0;
          }
          Bigtire::where('article', $pno)->update($payload);
        } elseif (!$visibilityOff
          && array_key_exists($pno, $starcoStockQtyForPrice)
          && $starcoStockQtyForPrice[$pno] === 0
        ) {
          Bigtire::where('article', $pno)->update([
            'visible_users' => 0,
            'visible_list' => 0,
            'updated_at' => $updatedAtPrices,
          ]);
        }
      }

      // #region agent log
      $this->starcoDebugLog('H4', 'phase_price_loop_done', [
        'ms' => ($starcoTBeforePriceLoop !== null)
          ? round((microtime(true) - $starcoTBeforePriceLoop) * 1000, 2)
          : null,
      ]);
      $this->starcoDebugLog('H2', 'starco_total', [
        'total_ms' => round((microtime(true) - $starcoT0) * 1000, 2),
      ]);
      // #endregion

      DB::table('sync_times')->where('name', 'starco-big')->update(['updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')]);
      Bigtire::clearCatalogCache();
      echo 'Preču cenas atjaunotas!';

    }

    public function sync_all()
    {
      $this->i3auto();
      $this->gy();
      $this->rzauto();
      $this->rgauto();
      $this->i3moto();
      $this->duellmoto();
      $this->i3quadr();
      $this->duellquadr();
      $this->i3big();
      $this->starco();
      return 'Visas sinhronizācijas notika!';
    }

  }
