<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bigbrand;
use App\Models\Bigstock;
use App\Models\Bigtire;
use App\Models\Bigtread;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;

class BigTireController extends Controller
{

    public $search;
    public $paginate = 10;

    /*
     *
     *
     * Riepu brendi
     *
     *
     */

    public function __construct()
    {
    }

    public function index(Request $request)
    {

        if ($request->post()) {
          if ($request->input('new-brand') == 'true') {
            if (empty($request->input('brand-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada brenda nosaukums!');
            $brand = Bigbrand::where('title', $request->input('brand-name'))->first();
            if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
            $brand = new Bigbrand;
            $brand->timestamps = false;
            $brand->title = $request->input('brand-name');
            $brand->slug = Str::slug($brand->title);
            if ($brand->save()) {
              return redirect($request->url())->with('success', 'Brends ir pievienots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
            }
          } else if ($request->input('edit-brand') == 'true') {
            $brand = Bigbrand::where('brand_id', $request->input('brand-id'))->first();
            $brand->timestamps = false;
            if ($brand && $brand->title == $request->input('brand-name')) {
              return redirect($request->url())->with('danger', 'Brenda nosaukums nav mainīts, ievadīts tāds pats!');
            } else {
              $brand->title = $request->input('brand-name');
              $brand->slug = Str::slug($brand->title);
              if ($brand->save()) {
                return redirect($request->url())->with('success', 'Brenda nosaukums nomainīts!');
              } else {
                return redirect($request->url())->with('danger', 'Notika kļūda, brends nav mainīts!');
              }
            }
          } else if ($request->input('delete-brand') == 'true') {
            $brand = Bigbrand::where('brand_id', $request->input('brand-id'))->first();
            if (!$brand) return redirect($request->url())->with('danger', 'Tāds brends neeksistē, nevaru izdzēst!');
            if ($brand->delete()) {
              redirect($request->url())->with('success', 'Brends veiksmīgi izdzēsts!');
            } else {
              redirect($request->url())->with('danger', 'Notika kļūda, brends nav izdzēsts!');
            }
          }

          if ($request->input('new-make') == 'true') {
            if (empty($request->input('make-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada modeļa nosaukums!');
            $make = Bigtread::where('title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
            if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
            $make = new Bigtread;
            $make->timestamps = false;
            $make->brand_id = $request->input('brand-id');
            $make->title = $request->input('make-name');
            $make->slug = Str::slug($make->title);
            if ($make->save()) {
              return redirect($request->url())->with('success', 'Modelis ir pievienots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, modelis nav pievienots!');
            }
          }
        }

//        $tires = Bigtire::with(ctread')->groupBy('make_id')->paginate($perPage);
        $brands = Bigbrand::orderBy('title', 'ASC')->get();
        $treads = Bigtread::orderBy('title', 'ASC')->get();

        return view('admin.big_tires.index', compact('brands', 'treads'));
    }

    public function tires_search(Request $request)
    {

        if ($request->post()) {
          if ($request->input('new-brand') == 'true') {
            if (empty($request->input('brand-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada brenda nosaukums!');
            $brand = Bigbrand::where('title', $request->input('brand-name'))->first();
            if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
            $brand = new Bigbrand;
            $brand->timestamps = false;
            $brand->title = $request->input('brand-name');
            $brand->slug = Str::slug($brand->title);
            if ($brand->save()) {
              return redirect($request->url())->with('success', 'Brends ir pievienots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
            }
          } else if ($request->input('edit-brand') == 'true') {
            $brand = Bigbrand::where('brand_id', $request->input('brand-id'))->first();
            $brand->timestamps = false;
//            if ($brand && $brand->title == $request->input('brand-name')) {
//              return redirect(route('admin.big.tires'))->with('danger', 'Brenda nosaukums nav mainīts, ievadīts tāds pats!');
//            } else {
              $brand->title = $request->input('brand-name');
              $brand->slug = Str::slug($brand->title);
              if ($brand->save()) {
                return redirect($request->url())->with('success', 'Brenda nosaukums nomainīts!');
              } else {
                return redirect($request->url())->with('danger', 'Notika kļūda, brends nav mainīts!');
              }
//            }
          } else if ($request->input('delete-brand') == 'true') {
            $brand = Bigbrand::where('brand_id', $request->input('brand-id'))->first();
            if (!$brand) return redirect($request->url())->with('danger', 'Tāds brends neeksistē, nevaru izdzēst!');
            if ($brand) {
              if ($brand->delete()) {
                redirect($request->url())->with('success', 'Brends veiksmīgi izdzēsts!');
              } else {
                redirect($request->url())->with('danger', 'Notika kļūda, brends nav izdzēsts!');
              }
            } else {
              redirect($request->url());
            }
          }

          if ($request->input('new-make') == 'true') {
            if (empty($request->input('make-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada modeļa nosaukums!');
            $make = Bigtread::where('title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
            if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
            $make = new Bigtread;
            $make->timestamps = false;
            $make->brand_id = $request->input('brand-id');
            $make->title = $request->input('make-name');
            $make->slug = Str::slug($make->title);
            if ($make->save()) {
              return redirect($request->url())->with('success', 'Modelis ir pievienots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, modelis nav pievienots!');
            }
          } else if ($request->input('edit-make') == 'true') {
            $make = Bigtread::where('brand_id', $request->input('brand-id'))->where('tread_id', $request->tread_id)->first();
            $make->timestamps = false;
//            if ($make && $make->t_title == $request->input('make-name')) {
//              return redirect($request->url())->with('danger', 'Modeļa nosaukums nav mainīts, ievadīts tāds pats!');
//            } else {
            $make->title = $request->input('make-name');
            $make->slug = Str::slug($make->title);
            if ($make->save()) {
              return redirect($request->url())->with('success', 'Modeļa nosaukums nomainīts!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, modeļis nav mainīts!');
            }
//            }
          } else if ($request->input('delete-make') == 'true') {
            $make = Bigtread::where('tread_id', $request->tread_id)->first();
            if (!$make) return redirect($request->url())->with('danger', 'Tāds modelis neeksistē, nevaru izdzēst!');
            if ($make->delete()) {
              redirect($request->url())->with('success', 'Modelis veiksmīgi izdzēsts!');
            } else {
              redirect($request->url())->with('danger', 'Notika kļūda, modelis nav izdzēsts!');
            }
          }

          // Komentāra edits
          if ($request->input('tread-comment-edit') == 'true') {
            $tread = Bigtread::where('tread_id', $request->tread_id)->first();
            $tread->timestamps = false;
            $tread->t_comment = $request->input('tread-comment-text');
            if ($tread->save()) {
              return redirect($request->url())->with('success', 'Modeļa apraksts atjaunots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, modeļa apraksts nav atjaunots!');
            }
          } else if ($request->input('brand-comment-edit') == 'true') {
            $tread = Bigtread::where('tread_id', $request->tread_id)->first();
            $brand = Bigbrand::where('brand_id', $tread->brand_id)->first();
            $brand->timestamps = false;
            $brand->b_comment = $request->input('brand-comment-text');
            if ($brand->save()) {
              return redirect($request->url())->with('success', 'Brenda apraksts atjaunots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, brenda apraksts nav atjaunots!');
            }
          }
        }

        $tires = Bigtire::with('tread')
                  ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
                  ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
                  ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
                  ->where('make_id', $request->tread_id)
                  ->get();
        $tread = Bigtread::where('tread_id', $request->tread_id)->first();
        if (!$tread) return redirect(route('admin.big.tires'));
        $brand = Bigbrand::where('brand_id', $tread->brand_id)->first();
        if (!$brand) return redirect(route('admin.big.tires'));
        $brands = Bigbrand::orderBy('title', 'ASC')->get();
        $treads = Bigtread::orderBy('title', 'ASC')->get();

        return view('admin.big_tires.index', compact('tires', 'tread', 'brands', 'brand', 'treads'));
    }

    public function tire_create($id)
    {
      $tread = Bigtread::where('tread_id', $id)->first();
      $brand = Bigbrand::where('brand_id', $tread->brand_id)->first();

      return view('admin.big_tires.tires.create', compact('tread', 'brand'));
    }

    public function tire_store(Request $request, $id)
    {
        $inputs = $request->except(['_token']);

        if (!array_filter($inputs)) {
          return redirect(route('admin.big.tires.create', $id))->with('danger', 'Visi lauki ir tukši');
        }

        $tire = new Bigtire;
        $tire->timestamps = false;

        $tire->make_id = $id;
        $tire->d1 = ($request->d1 === null) ? '' : $request->d1;
        $tire->sep = ($request->sep === null) ? null : $request->sep;
        $tire->d2 = ($request->d2 === null) ? null : $request->d2;
        $tire->sep2 = ($request->sep2 === null) ? null : $request->sep2;
        $tire->d3 = ($request->d3 === null) ? '' : $request->d3;
        $tire->type = ($request->tire_type === null) ? NULL : $request->tire_type;
        $tire->li = ($request->li === null) ? '' : $request->li;
        $tire->si = ($request->si === null) ? '' : $request->si;
        $tire->code = ($request->code === null) ? '' : $request->code;
        $tire->price1 = ($request->price1 === null) ? '' : $request->price1;
        $tire->price3 = ($request->price2 === null) ? '' : $request->price2;
        $tire->implemention = ($request->implemention === null) ? '' : $request->implemention;
        $tire->comment = ($request->comment === null) ? '' : $request->comment;
        $tire->acomment = ($request->acomment === null) ? '' : $request->acomment;
        $tire->article = ($request->article === null) ? null : $request->article;
        $tire->quantity = ($request->quantity === null) ? '' : $request->quantity;
        $tire->visible_list = 1;
        $tire->visible_users = 1;
        $tire->urs_quantity = ($request->urs_quantity === null) ? '' : $request->urs_quantity;
        $tire->krs_quantity = ($request->krs_quantity === null) ? '' : $request->krs_quantity;

        $tire->save();

        if ($request->i3article !== null) {
          $i3stock = new Bigstock;
          $i3stock->tire_id = $tire->tire_id;
          $i3stock->article = $request->i3article;
          $i3stock->quantity = 0;
          $i3stock->itype = 'i3';
          if ($tire->type == 'AGRO') {
            $i3stock->type = 'agro';
          } else {
            $i3stock->type = 'truck';
          }
          $i3stock->metadata = '';
          $i3stock->save();
        }

        if ($request->starcoarticle !== null) {
          $starcostock = new Bigstock;
          $starcostock->tire_id = $tire->tire_id;
          $starcostock->article = $request->starcoarticle;
          $starcostock->quantity = 0;
          $starcostock->itype = 'starco';
          $starcostock->type = 'null';
          $starcostock->metadata = '';
          $starcostock->save();
        }

        return redirect(route('admin.big.tires.search', $id))->with('success', 'Riepa veiksmīgi pievienota');

    }

    public function tire_edit($id)
    {
        $tire = Bigtire::with('tread')->where('tire_id', $id)->first();

        $i3stock = Bigstock::where('tire_id', $tire->tire_id)->where('itype', 'i3')->first();
        $starcostock = Bigstock::where('tire_id', $tire->tire_id)->where('itype', 'starco')->first();

        return view('admin.big_tires.tires.edit', compact('tire', 'i3stock', 'starcostock'));
    }

    public function tire_update(Request $request, $id)
    {

        $tire = Bigtire::findOrFail($id);

        $tire->d1 = $request->d1;
        $tire->sep = $request->sep;
        $tire->d2 = $request->d2;
        $tire->sep2 = $request->sep2;
        $tire->d3 = $request->d3;
        $tire->type = ($request->tire_type) ? $request->tire_type : 0;
        $tire->li = $request->li;
        $tire->si = $request->si;
        $tire->code = $request->code;
        $tire->price1 = $request->price1;
        $tire->price3 = $request->price2;
        $tire->implemention = $request->implemention;
        $tire->comment = $request->comment;
        $tire->acomment = $request->acomment;
        $tire->article = ($request->article === null) ? null : $request->article;
        $tire->quantity = $request->quantity;
        $tire->visible_list = 1;
        $tire->visible_users = 1;
        $tire->urs_quantity = $request->urs_quantity;
        $tire->krs_quantity = $request->krs_quantity;

        $tire->save();

        $i3stock = Bigstock::where('tire_id', $id)->where('itype', 'i3')->first();
        $starcostock = Bigstock::where('tire_id', $id)->where('itype', 'starco')->first();

        if ($i3stock) {
          if ($request->i3article) {
            $i3stock->article = $request->i3article;
            $i3stock->save();
          } else {
            $i3stock->delete();
          }
        } else {
          if ($request->i3article) {
            $i3stock = new Bigstock;
            $i3stock->tire_id = $tire->tire_id;
            $i3stock->article = $request->i3article;
            $i3stock->itype = 'i3';
            if ($tire->type == 'AGRO') {
              $i3stock->type = 'agro';
            } else {
              $i3stock->type = 'truck';
            }
            $i3stock->metadata = '';
            $i3stock->save();
          }
        }

        if ($starcostock) {
          if ($request->starcoarticle) {
            $starcostock->article = $request->starcoarticle;
            $starcostock->save();
          } else {
            $starcostock->delete();
          }
        } else {
          if ($request->starcoarticle) {
            $starcostock = new Bigstock;
            $starcostock->tire_id = $tire->tire_id;
            $starcostock->article = $request->starcoarticle;
            $starcostock->itype = 'starco';
            $starcostock->type = null;
            $starcostock->metadata = '';
            $starcostock->save();
          }
        }

        return redirect(route('admin.big.tire.edit', $id))->with('success', 'Informācija veiksmīgi atjaunota');
    }

    public function tire_destroy(Request $request)
    {

      Bigtire::whereIn('tire_id', $request->tire_id)->delete();
      Bigstock::whereIn('tire_id', $request->tire_id)->delete();

      return redirect()->back()->with('success', 'Riepa veiksmīgi dzēsta!');

    }

    public function tires_destroy(Request $request)
    {

      Bigstock::whereIn('tire_id', $request->tire_id)->delete();
      Bigtire::whereIn('tire_id', $request->tire_id)->delete();

      $response = (count($request->tire_id) > 1) ? 'Riepas veiksmīgi dzēstas!' : 'Riepa veiksmīgi dzēsta!';

      return redirect()->back()->with('success', $response);
    }

    public function tire_image(Request $request, $id)
    {
        if ($request->hasFile('tread_image')) {
            $image      = $request->file('tread_image');
            $fileName   = $id;
            $fileNameSmall   = $id . '-1s';
            $fileNameMed   = $id . '-1n';
            $fileNameLarge   = $id . '-1o';
//            dd($image);

            $watermark = Image::make('img/r1-riepas-logo-1515661637.jpg')->opacity(50);

            $imageDefault = Image::make($image->getRealPath());
            $imageDefault->insert($watermark, 'bottom-right', 10, 10);
            $imageDefault->save('storage/industrial/tread/' . $fileName . '.jpg');

            Image::make($image->getRealPath())
              ->resize(100, 100, function($constraint) {
                $constraint->aspectRatio();
              })->save('storage/industrial/tread/' . $fileNameSmall . '.jpg');
            Image::make($image->getRealPath())
              ->resize(200, 200, function($constraint) {
                $constraint->aspectRatio();
              })->save('storage/industrial/tread/' . $fileNameMed . '.jpg');
            $imageLarge = Image::make($image->getRealPath())
              ->resize(1500, 1500, function($constraint) {
                $constraint->aspectRatio();
              });
            $imageLarge->insert($watermark, 'bottom-right', 10, 10);
            $imageLarge->save('storage/industrial/tread/' . $fileNameLarge . '.jpg');
        }
        return redirect()->back();
    }

    public function brands_list($paginate = 10)
    {
        \Session::remove('search');
        if (is_numeric($paginate)) {
            $brands = Bigbrand::orderBy('title', 'ASC')->paginate($paginate);
        } else {
            return redirect(route('admin.big.brands'));
        }

        return view('admin.big_tires.brands.index', compact('brands', 'paginate'));
    }

    public function brand_search(Request $request, $paginate = 10) {

        if ($request->search) {
            \Session::put('search', $request->search);
            $brands = Bigbrand::orderBy('brand_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
        } else {
            if (\Session::has('search')) {
                $brands = Bigbrand::orderBy('brand_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
            } else {
                $brands = Bigbrand::orderBy('brand_id', 'DESC')->paginate($paginate);
            }
        }

        return view('admin.big_tires.brands.index', compact('brands', 'paginate'));
    }

    public function brand_add()
    {
        return view('admin.big_tires.brands.add');
    }

    public function brand_store(Request $request)
    {

        $breaks = array("<br />","<br>","<br/>");
        $request->brand_desc = str_ireplace($breaks, "", $request->brand_desc);

        $brand = new Bigbrand();
        $brand->timestamps = false;
        $brand->title = $request->brand_title;
        $brand->slug = \Str::slug($request->brand_title, '-');
        $brand->b_comment = nl2br($request->brand_desc);
        if ($brand->save()) {
          return redirect(route('admin.big.brands'))->with('success', 'Brends veiksmīgi pievienots!');
        } else {
          return redirect(route('admin.big.brands'))->with('danger', 'Notika kļūda, brends nav pievienots!');
        }

//        if ($request->hasFile('brand_image')) {
//            $brand_edit = Bigbrand::findOrFail($brand->brand_id);
//            $brand_edit->timestamps = false;
//
//            $image      = $request->file('brand_image');
//            $fileName   = 'auto_' . $brand->brand_id . '.' . $image->getClientOriginalExtension();
//
//
//            Storage::disk('public')->putFileAs('brands', $image, $fileName);
//            $brand_edit->image = $fileName;
//            $brand_edit->save();
//        }
    }

    public function brand_edit($id)
    {
        $brand = Bigbrand::findOrFail($id);

        return view('admin.big_tires.brands.edit', compact('brand'));
    }

    public function brand_update(Request $request, $id)
    {

        $breaks = array("<br />","<br>","<br/>");
        $request->brand_desc = str_ireplace($breaks, "", $request->brand_desc);

        $brand = Bigbrand::findOrFail($id);
        $brand->timestamps = false;
        $brand->title = $request->brand_title;
        $brand->slug = \Str::slug($request->brand_title, '-');
        $brand->b_comment = nl2br($request->brand_desc);

//        if ($request->hasFile('brand_image')) {
//
//            $image      = $request->file('brand_image');
//            $fileName   = 'auto_' . $id . '.' . $image->getClientOriginalExtension();
//
//
//            Storage::disk('public')->putFileAs('brands', $image, $fileName);
//            $brand->image = $fileName;
//        }

        $brand->save();

        return view('admin.big_tires.brands.edit', compact('brand'));
    }

    public function brand_delete(Request $request, $id)
    {
        $brand = Bigbrand::findOrFail($id);
//        Storage::disk('public')->delete('brands/' . $brand->image);
        if ($brand->delete()) {
          return redirect()->back()->with('success', 'Brends veiksmīgi izdzēsts!');
        } else {
          return redirect()->back()->with('danger', 'Notika kļūda, brends nav izdzēsts!');
        }
    }

    /*
     *
     *
     * Riepu modeļi
     *
     *
     */

    public function treads_list($paginate = 10)
    {

        \Session::remove('search');
        if (is_numeric($paginate)) {
            $treads = Bigtread::orderBy('tread_id', 'DESC')->paginate($paginate);
        } else {
            return redirect(route('admin.big.treads'));
        }

        return view('admin.big_tires.treads.index', compact('treads', 'paginate'));
    }

    public function treads_search(Request $request, $paginate = 10)
    {
        if ($request->search) {
            \Session::put('search', $request->search);
            $treads = Bigtread::orderBy('tread_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
        } else {
            if (\Session::has('search')) {
                $treads = Bigtread::orderBy('tread_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
            } else {
                $treads = Bigtread::orderBy('tread_id', 'DESC')->paginate($paginate);
            }
        }

        return view('admin.big_tires.treads.index', compact('treads', 'paginate'));
    }

    public function tread_add()
    {
        $brands = Bigbrand::orderBy('title', 'ASC')->get();

        return view('admin.big_tires.treads.add', compact('brands'));
    }

    public function tread_store(Request $request)
    {
        $tread = new Bigtread();
        $tread->timestamps = false;
        $tread->title = $request->tread_title;
        $tread->slug = \Str::slug($request->tread_title, '-');
        $tread->save();

        if ($request->hasFile('tread_image')) {
          $image      = $request->file('tread_image');
          $fileName   = $tread->tread_id;
          $fileNameSmall   = $tread->tread_id . '-s';
          $fileNameMed   = $tread->tread_id . '-n';
          $fileNameLarge   = $tread->tread_id . '-o';
//            dd($image);
          Image::make($image->getRealPath())->save('public/storage/industrial/tread/' . $fileName . '.jpg');
          Image::make($image->getRealPath())
            ->resize(100, 100, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/industrial/tread/' . $fileNameSmall . '.jpg');
          Image::make($image->getRealPath())
            ->resize(200, 200, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/industrial/tread/' . $fileNameMed . '.jpg');
          Image::make($image->getRealPath())
            ->resize(1500, 1500, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/industrial/tread/' . $fileNameLarge . '.jpg');
        }

        return redirect(route('admin.big.treads'));
    }

    public function tread_edit($id)
    {
        $tread = Bigtread::findOrFail($id);
        $brands = Bigbrand::orderby('title', 'ASC')->get();

        return view('admin.big_tires.treads.edit', compact('tread', 'brands'));
    }

    public function tread_update(Request $request, $id)
    {

        $breaks = array("<br />","<br>","<br/>");
        $request->tread_desc = str_ireplace($breaks, "", $request->tread_desc);

        $tread = Bigtread::findOrFail($id);
        $tread->timestamps = false;
        $tread->title = $request->tread_title;
        $tread->slug = \Str::slug($request->tread_title, '-');
        $tread->brand_id = $request->tread_brand;
        $tread->t_comment = nl2br($request->tread_desc);

        if ($request->hasFile('tread_image')) {
          $image      = $request->file('tread_image');
          $fileName   = $tread->tread_id;
          $fileNameSmall   = $tread->tread_id . '-s';
          $fileNameMed   = $tread->tread_id . '-n';
          $fileNameLarge   = $tread->tread_id . '-o';
//            dd($image);
          Image::make($image->getRealPath())->save('public/storage/industrial/tread/' . $fileName . '.jpg');
          Image::make($image->getRealPath())
            ->resize(100, 100, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/industrial/tread/' . $fileNameSmall . '.jpg');
          Image::make($image->getRealPath())
            ->resize(200, 200, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/industrial/tread/' . $fileNameMed . '.jpg');
          Image::make($image->getRealPath())
            ->resize(1500, 1500, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/industrial/tread/' . $fileNameLarge . '.jpg');
        }

        if ($tread->save()) {
          return redirect(route('admin.big.treads'))->with('success', 'Riepas modelis veiksmīgi labots!');
        } else {
          return redirect(route('admin.big.treads.edit', $tread->tread_id))->with('danger', 'Notika kļūda, nevaru atjaunot modeli!');
        }
    }

    public function tread_delete($id)
    {
      $tread = Bigtread::findOrFail($id);
//        Storage::disk('public')->delete('brands/' . $brand->image);
      if ($tread->delete()) {
        return redirect()->back()->with('success', 'Brends veiksmīgi izdzēsts!');
      } else {
        return redirect()->back()->with('danger', 'Notika kļūda, modelis nav izdzēsts!');
      }
    }

    /*
     *
     *
     * Auto riepu atjaunošana (Ajax)
     *
     *
     */

    public function ajaxUpdateTreads(Request $request)
    {
        $treads = Bigtread::with('tireCount')->select('bigtire_treads.*', 'bigtire_treads.title as t_title')->where('brand_id', $request->brand_id)->orderBy('title', 'ASC')->get();

        return json_encode($treads);
    }

    public function ajaxUpdateTires()
    {
        return 123;
    }
}
