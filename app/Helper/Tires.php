<?php

namespace App\Helper;

use App\Models\Code;
use App\Models\Rim;
use App\Models\Rimbrand;
use DB;
use App\Models\Autotire;
use App\Models\Autotread;
use App\Models\Autobrand;
use App\Models\Quadr;
use App\Models\Quadrtread;
use App\Models\Quadrbrand;
use App\Models\Moto;
use App\Models\Mototread;
use App\Models\Motobrand;
use App\Models\Bigtire;
use App\Models\Bigtread;
use App\Models\Bigbrand;
use App\Models\Studbrand;
use App\Models\Studtread;
use Illuminate\Support\Facades\Cache;

class Tires
{

    public static function getAllAutoBrands($season = 1) {
        return Autobrand::selectRaw('auto_brands.brand_id, auto_brands.title as brand_title')
                          ->join('auto_treads', 'auto_brands.brand_id', '=', 'auto_treads.brand_id')
                          ->whereRaw('auto_brands.title <> ""')
                          ->where('auto_treads.season', $season)
                          ->orderBy('brand_title')
                          ->groupBy('auto_brands.title')
                          ->get();
    }

    public static function getAllAutoBrands1($season = 1) {
	$brand_list = [];

	$tires = Autotire::select('make_id')->get();
	foreach ($tires as $tire) {
	  $brand = Autotread::select('auto_treads.brand_id', 'auto_treads.tread_id', 'auto_treads.season', 'auto_brands.*')
			        ->leftJoin('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
				->where('tread_id', $tire->make_id)
				->where('season', $season)
				->first();
	  if (!$brand) continue;
	  $brand_list[$brand->brand_id] = ucwords(strtolower($brand->title));
	}

	$brand_list = array_unique($brand_list);
	asort($brand_list);

	return $brand_list;

    }

//    public static function getAllAutoTreads() {
//        return DB::table('auto_treads')->select('title')->whereRaw('')
//    }

    public static function getAllBigBrands() {
      $brands = Bigbrand::query()
        ->whereHas('treads.tires', function ($query) {
          $query->where('visible_users', 1)->where('visible_list', 1);
        })
        ->orderBy('title')
        ->get(['brand_id', 'title']);

      $brandList = [];
      foreach ($brands as $brand) {
        $brandList[$brand->brand_id] = ucwords(strtolower($brand->title));
      }

      asort($brandList, SORT_NATURAL | SORT_FLAG_CASE);

      return $brandList;
    }

    public static function getAllQuadrBrands() {
        //return Quadrbrand::selectRaw('quadr_brands.brand_id as id, title')->whereRaw('title <> ""')->orderBy('brand_id')->groupBy('title')->get();

	$brand_list = [];

        $tires = Quadr::select('make_id')->get();
        foreach ($tires as $tire) {
          $brand = Quadrtread::select('quadr_treads.brand_id', 'quadr_treads.tread_id', 'quadr_brands.*')
				->leftJoin('quadr_brands', 'quadr_treads.brand_id', '=', 'quadr_brands.brand_id')
				->where('tread_id', $tire->make_id)
				->first();
          if (!$brand) continue;
          $brand_list[$brand->brand_id] = ucwords(strtolower($brand->title));
        }

        $brand_list = array_unique($brand_list);
        asort($brand_list);

        return $brand_list;
    }

    public static function getAllMotoBrands() {
        //return Motobrand::selectRaw('moto_brands.brand_id as id, title')->whereRaw('title <> ""')->orderBy('brand_id')->groupBy('title')->get();

	$brand_list = [];

        $tires = Moto::select('make_id')->get();
        foreach ($tires as $tire) {
          $brand = Mototread::select('moto_treads.brand_id', 'moto_treads.tread_id', 'moto_brands.*')
                                ->leftJoin('moto_brands', 'moto_treads.brand_id', '=', 'moto_brands.brand_id')
                                ->where('tread_id', $tire->make_id)
				->where('moto_brands.title', '!=', '')
                                ->first();
          if (!$brand) continue;
          $brand_list[$brand->brand_id] = ucwords(strtolower($brand->title));
        }

        $brand_list = array_unique($brand_list);
        asort($brand_list);

        return $brand_list;
    }

    public static function getAutoTiresSize($column, $season = 1) {
      return Autotire::join('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
        ->select($column)
        ->where($column, '<>', '""')
        ->where('auto_tires.visible_users', '<>', 0)
        ->where('auto_treads.season', $season)
        ->orderByRaw("CASE WHEN {$column} >= 100 THEN 0 ELSE 1 END, {$column}")
        ->groupBy($column)
        ->get();
    }

    public static function getQuadrTiresD1() {
        return Quadr::select('d1')->whereRaw('d1 <> ""')->orderByRaw('cast(d1 as decimal(7,2)) ASC')->groupBy('d1')->get();
    }

    public static function getQuadrTiresD2() {
        return Quadr::select('d2')->whereRaw('d2 <> ""')->orderByRaw('cast(d2 as decimal(7,2)) ASC')->groupBy('d2')->get();
    }

    public static function getQuadrTiresD3() {
        return Quadr::select('d3')->whereRaw('d3 <> ""')->orderByRaw('cast(d3 as decimal(7,2)) ASC')->groupBy('d3')->get();
    }

    public static function getMotoTiresD1() {
        return Moto::select('d1')->whereRaw('d1 <> ""')->orderByRaw('cast(d1 as decimal(7,2)) ASC')->groupBy('d1')->get();
    }

    public static function getMotoTiresD2() {
        return Moto::select('d2')->whereRaw('d2 <> ""')->orderByRaw('cast(d2 as decimal(7,2)) ASC')->groupBy('d2')->get();
    }

    public static function getMotoTiresD3() {
        return Moto::select('d3')->whereRaw('d3 <> ""')->orderByRaw('cast(d3 as decimal(7,2)) ASC')->groupBy('d3')->get();
    }

    public static function getBigTiresSize($column) {
      $allowed = ['d1', 'd2', 'd3'];
      if (!in_array($column, $allowed, true)) {
        return [];
      }

      return Cache::remember('big_tires_sizes_' . $column, 3600, function () use ($column) {
        return Bigtire::query()
          ->where('visible_users', 1)
          ->where('visible_list', 1)
          ->whereNotNull($column)
          ->where($column, '<>', '')
          ->orderByRaw('CAST(' . $column . ' AS DECIMAL(10,2)) ASC')
          ->distinct()
          ->pluck($column)
          ->map(function ($value) {
            return (string) $value;
          })
          ->values()
          ->all();
      });
    }

    public static function getBigTiresD1() {
      return array_map(function ($value) {
        return (object) ['d1' => $value];
      }, self::getBigTiresSize('d1'));
    }

    public static function getBigTiresD2() {
      return array_map(function ($value) {
        return (object) ['d2' => $value];
      }, self::getBigTiresSize('d2'));
    }

    public static function getBigTiresD3() {
      return array_map(function ($value) {
        return (object) ['d3' => $value];
      }, self::getBigTiresSize('d3'));
    }

    public static function getStudTread($tread_id) {
      return Studtread::select('*')->where('tread_id', $tread_id)->first();
    }

    public static function getStudBrand($brand_id) {
      return Studbrand::select('*')->where('brand_id', $brand_id)->first();
    }

    public static function getAutoTireTread($tread_id) {
        return Autotread::select('*')->where('tread_id', $tread_id)->first();
    }

    public static function getAutoTireBrand($brand_id) {
        return Autobrand::select('*')->where('brand_id', $brand_id)->first();
    }

    public static function getAutoRimBrand($brand_id) {
      return Rimbrand::select('*')->where('brand_id', $brand_id)->first();
    }

    public static function getQuadrTireTread($tread_id) {
        return Quadrtread::select('*')->where('tread_id', $tread_id)->first();
    }

    public static function getQuadrTireBrand($brand_id) {
        return Quadrbrand::select('*')->where('brand_id', $brand_id)->first();
    }

    public static function getMotoTireTread($tread_id) {
        return Mototread::select('*')->where('tread_id', $tread_id)->first();
    }

    public static function getMotoTireBrand($brand_id) {
        return Motobrand::select('*')->where('brand_id', $brand_id)->first();
    }

    public static function getBigTireTread($tread_id) {
        return Bigtread::select('*')->where('tread_id', $tread_id)->first();
    }

    public static function getBigTireBrand($brand_id) {
        return Bigbrand::select('*')->where('brand_id', $brand_id)->first();
    }

    public function GCD($a, $b)
    {
        if ($a == 0) return $b;
        return $this->GCD($b % $a, $a);
    }

    /**
     * Atrod lielāko kopīgo dalītāju masīvam
     * @param array $array	Masīvs, kam nepieciešams atrast lielāko kopīgo dalītāju
     * @param integer $n	Elementu skaits
     * @return mixed
     */

    public function arrayGCD($array, $n=0)
    {
        $array = array_map('strval', $array);
        $result = $array[0];
        if ($n==0) $n = count($array);
        if ($n==0) return false;
        for ($i = 1; $i < $n; $i++)
            $result = $this->GCD($array[$i], $result);

        return $result;
    }

    public static function codeExplain($param)
    {
      $return = '';

      $code = Code::where('name', $param)->first();
      if (!$code) return $return;

      $return = $code->explanation;

      return $return;
    }

    /**
     *
     * @param string $text Saīsināmais teksts
     * @param type $limit Maksimālais simbolu skaits tekstā
     * @param type $ellipsis Ar ko aizstāt maksimālo simbolu skaitu
     * @param type $strip Par cik saīsināt tekstu, ja pārsniegts maksimālais simbolu skaits (noklusētais = 0)
     * @return string
     */
    public static function truncateCharacters($text,$limit,$ellipsis='...',$strip=0){
        if(strlen($text) > $limit) $text = trim(substr($text, 0, $limit-$strip)).$ellipsis;
        return $text;
    }

    public static function zero_pad($i,$c){
        while (strlen($i)<$c){
            $i = '0'.$i;
        }
        return $i;
    }

}
