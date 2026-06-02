<?php

namespace App\Http\Controllers\Admin\Import;

use App\Http\Controllers\Controller;
use App\Models\Quadrbrand;
use App\Models\Quadrstock;
use App\Models\Quadr;
use App\Models\Quadrtread;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuadrTireImportController extends Controller
{
    public function index()
    {
        return view('admin.import.quadr');
    }

    public function import(Request $request)
    {
        $out = '';

        $data = $request->rows;
        $rows = explode("\n", trim($data));

        foreach ($rows as $idx=>$row){

            $row = trim($row);
            if (($idx>-1)&&($row!='')){
                $fields = explode("\t",$row);

                $tire = Quadr::where('article', $fields[2])->first();
                $new = false;

                if ($tire === null)
                {
                    $tire = new Quadr();
                    $new = true;
                }

                $brand = Quadrbrand::where('title', 'like', '%' . $fields[3] . '%')->first();

                if ($brand === null)
                {
                  $brand = new Quadrbrand();
                  $brand->timestamps = false;
                  $brand->title = $fields[3];
                  $brand->slug = Str::slug($fields[3], '-');
                  $brand->save();
                  $brand_id = $brand->brand_id;
                  $out.='<p>Jauns brends: '.ucfirst($brand->title).'</p>';
                }

                $tread = Quadrtread::where('title', $fields[13])->where('brand_id', $brand->brand_id)->first();

                if ($tread === null)
                {
                  $tread = new Quadrtread();
                  $tread->timestamps = false;
                  $tread->brand_id = ($brand === null) ? $brand_id : $brand->brand_id;
                  $tread->title = $fields[13];
                  $tread->slug = Str::slug($fields[13], '-');
                  $tread->save();
                  $tread_id = $tread->id;
                  $out.='<p>Jauns protektora modelis: '.ucfirst($tread->title).'</p>';
                }

                $tread_id = $tread->tread_id;

                $tire->timestamps = false;
                $tire->make_id = $tread_id;

                $tire->d1 = @$fields[4];
                $tire->sep = @$fields[5];
                $tire->d2 = @$fields[6];
                $tire->sep2 = @$fields[7];
                $tire->d3 = @$fields[8];

                $tire->li = @$fields[10];
                $tire->si = @$fields[11];

                $tire->price1 = @$fields[15];
                $tire->price2 = @$fields[16];

                $tire->comment = @$fields[20];
                $tire->acomment = @$fields[30];
                $tire->code = @$fields[12];

                $tire->quantity = 0;

                $tire->article = @$fields[2];

                $tire->save();
                $tire_id = $tire->id;

                $duell = @$fields[29];

                if ($new === false) {
                    $out .= "<p>Labojam izmēru: \"{$brand->title} {$tread->title}\" {$fields[4]}/{$fields[5]} R{$fields[6]} (LI:{$fields[8]}, SI:{$fields[9]}, kods: {$fields[16]}) - <strong>{$fields[2]}</strong></p>";
                } else {
                    $out .= "<p>Pievienojam izmēru: \"{$brand->title} {$tread->title}\" {$fields[4]}/{$fields[5]} R{$fields[6]} (LI:{$fields[8]}, SI:{$fields[9]}, kods: {$fields[16]}) - <strong>{$fields[2]}</strong></p>";
                }

                $stock = Quadrstock::where('itype', 'duell')->where('article', $duell)->first();


                if ($stock === null)
                {
                    $stock = new Quadrstock();
                    if ($tire_id === null) {
                        $stock->tire_id = $tire->tire_id;
                    } else {
                        $stock->tire_id = $tire_id;
                    }
                    $stock->article = $duell;
                    $stock->quantity = 0;
                    $stock->itype = 'duell';
                    $stock->metadata = '';
                    $stock->save();
                }

            }
        }

        return $out;
    }
}
