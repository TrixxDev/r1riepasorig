<?php

namespace App\Http\Controllers\Admin\Import;

use App\Http\Controllers\Controller;
use App\Models\Motobrand;
use App\Models\Motostock;
use App\Models\Moto;
use App\Models\Mototread;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MotoTireImportController extends Controller
{
    public function index()
    {
        return view('admin.import.moto');
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

                $tire = Moto::where('article', $fields[0])->first();
                $new = false;

                if ($tire === null)
                {
                    $tire = new Moto();
                    $new = true;
                }

                $brand = Motobrand::where('title', 'like', '%' . $fields[1] . '%')->first();

                if ($brand === null)
                {
                  $brand = new Motobrand();
                  $brand->timestamps = false;
                  $brand->title = $fields[1];
                  $brand->slug = Str::slug($fields[1], '-');
                  $brand->save();
                  $brand_id = $brand->brand_id;
                  $out.='<p>Jauns brends: '.ucfirst($brand->title).'</p>';
                }

                $tread = Mototread::where('title', $fields[10])->where('brand_id', $brand->brand_id)->first();

                if ($tread === null)
                {
                  $tread = new Mototread();
                  $tread->timestamps = false;
                  $tread->brand_id = ($brand === null) ? $brand_id : $brand->brand_id;
                  $tread->title = $fields[10];
                  $tread->slug = Str::slug($fields[10], '-');
                  $tread->t_comment = '';
                  $tread->save();
                  $tread_id = $tread->id;
                  $out.='<p>Jauns protektora modelis: '.ucfirst($tread->title).'</p>';
                }

                $tread_id = $tread->tread_id;

                $tire->timestamps = false;
                $tire->make_id = $tread_id;

                $tire->d1 = @$fields[2];
                $tire->d2 = @$fields[3];
                $tire->d3 = @$fields[5];
                $tire->d4 = @$fields[4];

                $tire->type = @$fields[11];

                $tire->li = @$fields[7];
                $tire->si = @$fields[8];

                $tire->price1 = @$fields[12];
                $tire->price2 = @$fields[13];

                $tire->comment = @$fields[14];
                $tire->acomment = @$fields[20];
                $tire->code = @$fields[9];

                $tire->quantity = 0;

                $tire->article = @$fields[0];

                $tire->save();
                $tire_id = $tire->tire_id;

                $i3 = @$fields[18];
                $duell = @$fields[19];

                if ($new === false) {
                    $out .= "<p>Labojam izmēru: \"{$brand->title} {$tread->title}\" {$fields[6]} (LI:{$fields[7]}, SI:{$fields[8]}, kods: {$fields[9]}) - <strong>{$fields[0]}</strong></p>";
                } else {
                    $out .= "<p>Pievienojam izmēru: \"{$brand->title} {$tread->title}\" {$fields[6]} (LI:{$fields[7]}, SI:{$fields[8]}, kods: {$fields[9]}) - <strong>{$fields[0]}</strong></p>";
                }

                if (!empty($i3)) {
                  $tire->addSecondaryArticle($i3, 'i3');
                }

                if (!empty($duell)) {
                  $tire->addSecondaryArticle($duell, 'duell');
                }

            }
        }

        return redirect()->back()->with('out', $out);
    }
}
