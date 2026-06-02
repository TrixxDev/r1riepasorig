<?php

  namespace App\Http\Controllers\Admin;

  use App\Http\Controllers\Controller;
  use App\Models\Quadr;
  use App\Models\Quadrbrand;
  use App\Models\Quadrstock;
  use App\Models\Quadrtread;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Redirect;
  use Illuminate\Support\Str;
  use Intervention\Image\ImageManagerStatic as Image;
  use Storage;

  class QuadrTireController extends Controller
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
          $brand = Quadrbrand::where('b_title', $request->input('brand-name'))->first();
          if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
          $brand = new Quadrbrand;
          $brand->timestamps = false;
          $brand->b_title = $request->input('brand-name');
          $brand->slug = Str::slug($brand->b_title);
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brends ir pievienots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
          }
        } else if ($request->input('edit-brand') == 'true') {
          $brand = Quadrbrand::where('brand_id', $request->input('brand-id'))->first();
          $brand->timestamps = false;
          if ($brand && $brand->b_title == $request->input('brand-name')) {
            return redirect($request->url())->with('danger', 'Brenda nosaukums nav mainīts, ievadīts tāds pats!');
          } else {
            $brand->b_title = $request->input('brand-name');
            $brand->slug = Str::slug($brand->b_title);
            if ($brand->save()) {
              return redirect($request->url())->with('success', 'Brenda nosaukums nomainīts!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, brends nav mainīts!');
            }
          }
        } else if ($request->input('delete-brand') == 'true') {
          $brand = Quadrbrand::where('brand_id', $request->input('brand-id'))->first();
          if (!$brand) return redirect($request->url())->with('danger', 'Tāds brends neeksistē, nevaru izdzēst!');
          if ($brand->delete()) {
            redirect($request->url())->with('success', 'Brends veiksmīgi izdzēsts!');
          } else {
            redirect($request->url())->with('danger', 'Notika kļūda, brends nav izdzēsts!');
          }
        }

        if ($request->input('new-make') == 'true') {
          if (empty($request->input('make-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada modeļa nosaukums!');
          $make = Quadrtread::where('t_title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
          if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
          $make = new Quadrtread;
          $make->timestamps = false;
          $make->brand_id = $request->input('brand-id');
          $make->t_title = $request->input('make-name');
          $make->slug = Str::slug($make->t_title);
          if ($make->save()) {
            return redirect(route('admin.quadr.tires.search', $make->tread_id))->with('success', 'Modelis ir pievienots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, modelis nav pievienots!');
          }
        }
      }

      $brands = Quadrbrand::orderBy('b_title', 'ASC')->get();
      $treads = Quadrtread::orderBy('t_title', 'ASC')->get();

      return view('admin.quadr_tires.index', compact('brands', 'treads'));
    }

    public function tires_search(Request $request, $id)
    {

      if ($request->post()) {
        if ($request->input('new-brand') == 'true') {
          if (empty($request->input('brand-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada brenda nosaukums!');
          $brand = Quadrbrand::where('b_title', $request->input('brand-name'))->first();
          if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
          $brand = new Quadrbrand;
          $brand->timestamps = false;
          $brand->b_title = $request->input('brand-name');
          $brand->slug = Str::slug($brand->b_title);
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brends ir pievienots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
          }
        } else if ($request->input('edit-brand') == 'true') {
          $brand = Quadrbrand::where('brand_id', $request->input('brand-id'))->first();
          $brand->timestamps = false;
//            if ($brand && $brand->title == $request->input('brand-name')) {
//              return redirect(route('admin.auto.tires'))->with('danger', 'Brenda nosaukums nav mainīts, ievadīts tāds pats!');
//            } else {
          $brand->b_title = $request->input('brand-name');
          $brand->slug = Str::slug($brand->b_title);
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brenda nosaukums nomainīts!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brends nav mainīts!');
          }
//            }
        } else if ($request->input('delete-brand') == 'true') {
          $brand = Quadrbrand::where('brand_id', $request->input('brand-id'))->first();
          if (!$brand) return redirect($request->url())->with('danger', 'Tāds brends neeksistē, nevaru izdzēst!');
          if ($brand->delete()) {
            redirect($request->url())->with('success', 'Brends veiksmīgi izdzēsts!');
          } else {
            redirect($request->url())->with('danger', 'Notika kļūda, brends nav izdzēsts!');
          }
        }

        if ($request->input('new-make') == 'true') {
          if (empty($request->input('make-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada modeļa nosaukums!');
          $make = Quadrtread::where('t_title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
          if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
          $make = new Quadrtread();
          $make->timestamps = false;
          $make->brand_id = $request->input('brand-id');
          $make->t_title = $request->input('make-name');
          $make->slug = Str::slug($make->t_title);
          if ($make->save()) {
            return redirect(route('admin.quadr.tires.search', $make->tread_id))->with('success', 'Modelis ir pievienots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, modelis nav pievienots!');
          }
        } else if ($request->input('edit-make') == 'true') {
          $make = Quadrtread::where('brand_id', $request->input('brand-id'))->where('tread_id', $request->tread_id)->first();
          $make->timestamps = false;
//            if ($make && $make->t_title == $request->input('make-name')) {
//              return redirect($request->url())->with('danger', 'Modeļa nosaukums nav mainīts, ievadīts tāds pats!');
//            } else {
          $make->t_title = $request->input('make-name');
          $make->slug = Str::slug($make->t_title);
          if ($make->save()) {
            return redirect($request->url())->with('success', 'Modeļa nosaukums nomainīts!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, modeļis nav mainīts!');
          }
//            }
        } else if ($request->input('delete-make') == 'true') {
          $make = Quadrtread::where('tread_id', $request->tread_id)->first();
          if (!$make) return redirect($request->url())->with('danger', 'Tāds modelis neeksistē, nevaru izdzēst!');
          if ($make->delete()) {
            redirect($request->url())->with('success', 'Modelis veiksmīgi izdzēsts!');
          } else {
            redirect($request->url())->with('danger', 'Notika kļūda, modelis nav izdzēsts!');
          }
        }

        // Komentāra edits
        if ($request->input('tread-comment-edit') == 'true') {
          $tread = Quadrtread::where('tread_id', $request->tread_id)->first();
          $tread->timestamps = false;
          $tread->t_comment = $request->input('tread-comment-text');
          if ($tread->save()) {
            return redirect($request->url())->with('success', 'Modeļa apraksts atjaunots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, modeļa apraksts nav atjaunots!');
          }
        } else if ($request->input('brand-comment-edit') == 'true') {
          $tread = Quadrtread::where('tread_id', $request->tread_id)->first();
          $brand = Quadrbrand::where('brand_id', $tread->brand_id)->first();
          $brand->timestamps = false;
          $brand->b_comment = $request->input('brand-comment-text');
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brenda apraksts atjaunots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brenda apraksts nav atjaunots!');
          }
        }
      }

      $tires = Quadr::with('tread')
                ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
                ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
                ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
                ->where('make_id', $request->tread_id)
                ->get();
      $tread = Quadrtread::where('tread_id', $request->tread_id)->first();
      if (!$tread) return redirect(route('admin.quadr.tires'));
      $brand = Quadrbrand::where('brand_id', $tread->brand_id)->first();
      if (!$brand) return redirect(route('admin.quadr.tires'));
      $brands = Quadrbrand::orderBy('b_title', 'ASC')->get();
      $treads = Quadrtread::orderBy('t_title', 'ASC')->get();

      return view('admin.quadr_tires.index', compact('tires', 'tread', 'brands', 'brand', 'treads'));
    }

    public function tire_create($id)
    {
      $tread = Quadrtread::where('tread_id', $id)->first();
      $brand = Quadrbrand::where('brand_id', $tread->brand_id)->first();

      return view('admin.quadr_tires.tires.create', compact('tread', 'brand'));
    }

    public function tire_store(Request $request, $id)
    {
      $inputs = $request->except(['_token']);

      if (!array_filter($inputs)) {
        return redirect(route('admin.quadr.tires.create', $id))->with('danger', 'Visi lauki ir tukši');
      }

      $tire = new Quadr;
      $tire->timestamps = false;

      $tire->make_id = $id;
      $tire->d1 = ($request->d1 === null) ? '' : $request->d1;
      $tire->sep = ($request->sep === null) ? '' : $request->sep;
      $tire->d2 = ($request->d2 === null) ? '' : $request->d2;
      $tire->sep2 = ($request->sep2 === null) ? '' : $request->sep2;
      $tire->d3 = ($request->d3 === null) ? '' : $request->d3;
      $tire->li = ($request->li === null) ? '' : $request->li;
      $tire->si = ($request->si === null) ? '' : $request->si;
      $tire->price1 = ($request->price1 === null) ? '' : $request->price1;
      $tire->price2 = ($request->price2 === null) ? '' : $request->price2;
      $tire->code = ($request->code === null) ? '' : $request->code;
      $tire->comment = ($request->comment === null) ? '' : $request->comment;
      $tire->acomment = ($request->acomment === null) ? '' : $request->acomment;
      $tire->is_camera = ($request->is_camera === 'off') ? 'off' : $request->is_camera;
      $tire->article = ($request->article === null) ? null : $request->article;
      $tire->quantity = ($request->quantity === null) ? '' : $request->quantity;
      $tire->urs_quantity = ($request->urs_quantity === null) ? '' : $request->urs_quantity;
      $tire->krs_quantity = ($request->krs_quantity === null) ? '' : $request->krs_quantity;

      $tire->save();

      if ($request->i3article !== null) {
        $i3stock = new Quadrstock;
        $i3stock->tire_id = $tire->tire_id;
        $i3stock->article = $request->i3article;
        $i3stock->quantity = 0;
        $i3stock->itype = 'i3';
        $i3stock->metadata = '';
        $i3stock->save();
      }

      if ($request->duellarticle !== null) {
        $duellstock = new Quadrstock;
        $duellstock->tire_id = $tire->tire_id;
        $duellstock->article = $request->duellarticle;
        $duellstock->quantity = 0;
        $duellstock->itype = 'duell';
        $duellstock->metadata = '';
        $duellstock->save();
      }

      if ($request->starcoarticle !== null) {
        $starcostock = new Quadrstock;
        $starcostock->tire_id = $tire->tire_id;
        $starcostock->article = $request->starcoarticle;
        $starcostock->quantity = 0;
        $starcostock->itype = 'starco';
        $starcostock->metadata = '';
        $starcostock->save();
      }

      return redirect(route('admin.quadr.tires.search', $id))->with('success', 'Riepa veiksmīgi pievienota');

    }

    public function tire_edit($id)
    {
      $tire = Quadr::with('tread')->where('tire_id', $id)->first();

      $i3stock = Quadrstock::where('tire_id', $tire->tire_id)->where('itype', 'i3')->first();
      $duellstock = Quadrstock::where('tire_id', $tire->tire_id)->where('itype', 'duell')->first();
      $starcostock = Quadrstock::where('tire_id', $tire->tire_id)->where('itype', 'starco')->first();

      return view('admin.quadr_tires.tires.edit', compact('tire', 'i3stock', 'duellstock', 'starcostock'));
    }

    public function tire_update(Request $request, $id)
    {

      $tire = Quadr::findOrFail($id);

      $tire->d1 = $request->d1;
      $tire->sep = $request->sep;
      $tire->d2 = $request->d2;
      $tire->sep2 = $request->sep2;
      $tire->d3 = $request->d3;
      $tire->li = $request->li;
      $tire->si = $request->si;
      $tire->price1 = $request->price1;
      $tire->price2 = $request->price2;
      $tire->code = $request->code;
      $tire->comment = $request->comment;
      $tire->acomment = $request->acomment;
      $tire->is_camera = $request->is_camera;
      $tire->article = ($request->article === null) ? null : $request->article;
      $tire->quantity = $request->quantity;
      $tire->urs_quantity = $request->urs_quantity;
      $tire->krs_quantity = $request->krs_quantity;

      $tire->save();

      $i3stock = Quadrstock::where('tire_id', $id)->where('itype', 'i3')->first();
      $duellstock = Quadrstock::where('tire_id', $id)->where('itype', 'duell')->first();
      $starcostock = Quadrstock::where('tire_id', $id)->where('itype', 'starco')->first();

      if ($i3stock) {
        if ($request->i3article) {
          $i3stock->article = $request->i3article;
          $i3stock->save();
        } else {
          $i3stock->delete();
        }
      } else {
        if ($request->i3article) {
          $i3stock = new Quadrstock;
          $i3stock->tire_id = $tire->tire_id;
          $i3stock->article = $request->i3article;
          $i3stock->itype = 'i3';
          $i3stock->metadata = '';
          $i3stock->save();
        }
      }

      if ($duellstock) {
        if ($request->duellarticle) {
          $duellstock->article = $request->duellarticle;
          $duellstock->save();
        } else {
          $duellstock->delete();
        }
      } else {
        if ($request->duellarticle) {
          $duellstock = new Quadrstock;
          $duellstock->tire_id = $tire->tire_id;
          $duellstock->article = $request->duellarticle;
          $duellstock->itype = 'duell';
          $duellstock->metadata = '';
          $duellstock->save();
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
          $starcostock->metadata = '';
          $starcostock->save();
        }
      }

      return redirect(route('admin.quadr.tire.edit', $id))->with('success', 'Informācija veiksmīgi atjaunota');
    }

    public function tire_destroy($id)
    {

//      Quadrstock::whereIn('tire_id', $id)->
      Quadr::where('tire_id', $id)->delete();

      return redirect()->back()->with('success', 'Riepa veiksmīgi dzēsta!');

    }

    public function tires_destroy(Request $request)
    {

      Quadr::whereIn('tire_id', $request->tire_id)->delete();
      Quadrstock::whereIn('tire_id', $request->tire_id)->delete();

      $response = (count($request->tire_id) > 1) ? 'Riepas veiksmīgi dzēstas!' : 'Riepa veiksmīgi dzēsta!';

      return redirect()->back()->with('success', $response);
    }

    public function tire_image(Request $request, $id)
    {
      if ($request->hasFile('tread_image')) {
        $image      = $request->file('tread_image');
        $fileName   = $id;
        $fileNameSmall   = $id . '-s';
        $fileNameMed   = $id . '-n';
        $fileNameLarge   = $id . '-o';
//            dd($image);
        $watermark = Image::make('img/r1-riepas-logo-1515661637.jpg')->opacity(50);

        $imageDefault = Image::make($image->getRealPath());
        $imageDefault->insert($watermark, 'bottom-right', 10, 10);
        $imageDefault->save('storage/quadr/tread/' . $fileName . '.jpg');

        Image::make($image->getRealPath())
          ->resize(100, 100, function($constraint) {
            $constraint->aspectRatio();
          })->save('storage/quadr/tread/' . $fileNameSmall . '.jpg');
        Image::make($image->getRealPath())
          ->resize(200, 200, function($constraint) {
            $constraint->aspectRatio();
          })->save('storage/quadr/tread/' . $fileNameMed . '.jpg');
        $imageLarge = Image::make($image->getRealPath())
          ->resize(1500, 1500, function($constraint) {
            $constraint->aspectRatio();
          });
        $imageLarge->insert($watermark, 'bottom-right', 10, 10);
        $imageLarge->save('storage/quadr/tread/' . $fileNameLarge . '.jpg');
      }
      return redirect()->back();
    }

    public function brands_list($paginate = 10)
    {
      \Session::remove('search');
      if (is_numeric($paginate)) {
        $brands = Quadrbrand::orderBy('b_title', 'ASC')->paginate($paginate);
      } else {
        return redirect(route('admin.quadr.brands'));
      }

      return view('admin.quadr_tires.brands.index', compact('brands', 'paginate'));
    }

    public function brand_search(Request $request, $paginate = 10) {

      if ($request->search) {
        \Session::put('search', $request->search);
        $brands = Quadrbrand::orderBy('brand_id', 'DESC')->where('b_title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
      } else {
        if (\Session::has('search')) {
          $brands = Quadrbrand::orderBy('brand_id', 'DESC')->where('b_title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
        } else {
          $brands = Quadrbrand::orderBy('brand_id', 'DESC')->paginate($paginate);
        }
      }

      return view('admin.quadr_tires.brands.index', compact('brands', 'paginate'));
    }

    public function brand_add()
    {
      return view('admin.quadr_tires.brands.add');
    }

    public function brand_store(Request $request)
    {

      $brand = Quadrbrand::where('b_title', $request->brand_title)->first();
      if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');

      $brand = new Quadrbrand();
      $brand->timestamps = false;
      $brand->b_title = $request->brand_title;
      $brand->slug = \Str::slug($request->brand_title, '-');
      if ($brand->save()) {
        return redirect(route('admin.quadr.brands'))->with('success', 'Brends veiksmīgi pievienots!');
      } else {
        return redirect(route('admin.quadr.brands'))->with('danger', 'Notika kļūda, brends nav pievienots!');
      }

    }

    public function brand_edit($id)
    {
      $brand = Quadrbrand::findOrFail($id);

      return view('admin.quadr_tires.brands.edit', compact('brand'));
    }

    public function brand_update(Request $request, $id)
    {
      $brand = Quadrbrand::findOrFail($id);
      $brand->timestamps = false;
      $brand->b_title = $request->brand_title;
      $brand->slug = \Str::slug($request->brand_title, '-');

      $brand->save();

      return view('admin.quadr_tires.brands.edit', compact('brand'));
    }

    public function brand_delete(Request $request, $id)
    {
      $brand = Quadrbrand::findOrFail($id);
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
        $treads = Quadrtread::orderBy('tread_id', 'DESC')->groupBy('tread_id')->paginate($paginate);
      } else {
        return redirect(route('admin.quadr.treads'));
      }

      return view('admin.quadr_tires.treads.index', compact('treads', 'paginate'));
    }

    public function treads_search(Request $request, $paginate = 10)
    {
      if ($request->search) {
        \Session::put('search', $request->search);
        $treads = Quadrtread::orderBy('tread_id', 'DESC')->where('t_title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
      } else {
        if (\Session::has('search')) {
          $treads = Quadrtread::orderBy('tread_id', 'DESC')->where('t_title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
        } else {
          $treads = Quadrtread::orderBy('tread_id', 'DESC')->paginate($paginate);
        }
      }

      return view('admin.quadr_tires.treads.index', compact('treads', 'paginate'));
    }

    public function tread_add()
    {
      $brands = Quadrbrand::orderBy('b_title', 'ASC')->get();

      return view('admin.quadr_tires.treads.add', compact('brands'));
    }

    public function tread_store(Request $request)
    {
      $tread = new Quadrtread();
      $tread->timestamps = false;
      $tread->t_title = $request->tread_title;
      $tread->slug = \Str::slug($request->tread_title, '-');
      $tread->save();

      if ($request->hasFile('tread_image')) {
        $image      = $request->file('tread_image');
        $fileName   = $tread->tread_id;
        $fileNameSmall   = $tread->tread_id . '-s';
        $fileNameMed   = $tread->tread_id . '-n';
        $fileNameLarge   = $tread->tread_id . '-o';
//            dd($image);
        Image::make($image->getRealPath())->save('public/storage/quadr/tread/' . $fileName . '.jpg');
        Image::make($image->getRealPath())
          ->resize(100, 100, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/quadr/tread/' . $fileNameSmall . '.jpg');
        Image::make($image->getRealPath())
          ->resize(200, 200, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/quadr/tread/' . $fileNameMed . '.jpg');
        Image::make($image->getRealPath())
          ->resize(1500, 1500, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/quadr/tread/' . $fileNameLarge . '.jpg');
      }

      return redirect(route('admin.quadr.treads'));
    }

    public function tread_edit($id)
    {
      $tread = Quadrtread::findOrFail($id);
      $brands = Quadrbrand::all();

      return view('admin.quadr_tires.treads.edit', compact('tread', 'brands'));
    }

    public function tread_update(Request $request, $id)
    {
      $tread = Quadrtread::findOrFail($id);
      $tread->timestamps = false;
      $tread->t_title = $request->tread_title;
      $tread->slug = \Str::slug($request->tread_title, '-');
      $tread->brand_id = $request->tread_brand;
      $tread->t_comment = $request->tread_desc;

      if ($request->hasFile('tread_image')) {
        $image      = $request->file('tread_image');
        $fileName   = $tread->tread_id;
        $fileNameSmall   = $tread->tread_id . '-s';
        $fileNameMed   = $tread->tread_id . '-n';
        $fileNameLarge   = $tread->tread_id . '-o';
//            dd($image);
        Image::make($image->getRealPath())->save('public/storage/quadr/tread/' . $fileName . '.jpg');
        Image::make($image->getRealPath())
          ->resize(100, 100, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/quadr/tread/' . $fileNameSmall . '.jpg');
        Image::make($image->getRealPath())
          ->resize(200, 200, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/quadr/tread/' . $fileNameMed . '.jpg');
        Image::make($image->getRealPath())
          ->resize(1500, 1500, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/quadr/tread/' . $fileNameLarge . '.jpg');
      }

      $tread->save();
      $brands = Quadrbrand::all();

      return view('admin.quadr_tires.treads.edit', compact('tread', 'brands'));
    }

    public function tread_delete($id)
    {
      $tread = Quadrtread::findOrFail($id);
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
     * Kvadru riepu atjaunošana (Ajax)
     *
     *
     */

    public function ajaxUpdateTreads(Request $request)
    {
      $treads = Quadrtread::with('tireCount')->where('brand_id', $request->brand_id)->orderBy('t_title', 'ASC')->get();
      return json_encode($treads);
    }

    public function ajaxUpdateTires()
    {
      return 123;
    }
  }
