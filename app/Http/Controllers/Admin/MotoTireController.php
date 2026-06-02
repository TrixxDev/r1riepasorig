<?php

  namespace App\Http\Controllers\Admin;

  use App\Http\Controllers\Controller;
  use App\Models\Motobrand;
  use App\Models\Moto;
  use App\Models\Motostock;
  use App\Models\Mototread;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Redirect;
  use Illuminate\Support\Str;
  use Intervention\Image\ImageManagerStatic as Image;
  use Storage;

  class MotoTireController extends Controller
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
          $brand = Motobrand::where('title', $request->input('brand-name'))->first();
          if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
          $brand = new Motobrand;
          $brand->timestamps = false;
          $brand->title = $request->input('brand-name');
          $brand->slug = Str::slug($brand->title);
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brends ir pievienots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
          }
        } else if ($request->input('edit-brand') == 'true') {
          $brand = Motobrand::where('brand_id', $request->input('brand-id'))->first();
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
          $brand = Motobrand::where('brand_id', $request->input('brand-id'))->first();
          if (!$brand) return redirect($request->url())->with('danger', 'Tāds brends neeksistē, nevaru izdzēst!');
          if ($brand->delete()) {
            redirect($request->url())->with('success', 'Brends veiksmīgi izdzēsts!');
          } else {
            redirect($request->url())->with('danger', 'Notika kļūda, brends nav izdzēsts!');
          }
        }

        if ($request->input('new-make') == 'true') {
          if (empty($request->input('make-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada modeļa nosaukums!');
          $make = Mototread::where('title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
          if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
          $make = new Mototread;
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

//        $tires = Autotire::with(ctread')->groupBy('make_id')->paginate($perPage);
      $brands = Motobrand::orderBy('title', 'ASC')->get();
      $treads = Mototread::orderBy('title', 'ASC')->get();

      return view('admin.moto_tires.index', compact('brands', 'treads'));
    }

    public function tires_search(Request $request)
    {

      if ($request->post()) {
        if ($request->input('new-brand') == 'true') {
          if (empty($request->input('brand-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada brenda nosaukums!');
          $brand = Motobrand::where('title', $request->input('brand-name'))->first();
          if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
          $brand = new Motobrand;
          $brand->timestamps = false;
          $brand->title = $request->input('brand-name');
          $brand->slug = Str::slug($brand->title);
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brends ir pievienots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
          }
        } else if ($request->input('edit-brand') == 'true') {
          $brand = Motobrand::where('brand_id', $request->input('brand-id'))->first();
          $brand->timestamps = false;
//            if ($brand && $brand->title == $request->input('brand-name')) {
//              return redirect(route('admin.auto.tires'))->with('danger', 'Brenda nosaukums nav mainīts, ievadīts tāds pats!');
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
          $brand = Motobrand::where('brand_id', $request->input('brand-id'))->first();
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
          $make = Mototread::where('title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
          if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
          $make = new Mototread();
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
          $make = Mototread::where('brand_id', $request->input('brand-id'))->where('tread_id', $request->tread_id)->first();
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
          $make = Mototread::where('tread_id', $request->tread_id)->first();
          if (!$make) return redirect($request->url())->with('danger', 'Tāds modelis neeksistē, nevaru izdzēst!');
          if ($make->delete()) {
            redirect($request->url())->with('success', 'Modelis veiksmīgi izdzēsts!');
          } else {
            redirect($request->url())->with('danger', 'Notika kļūda, modelis nav izdzēsts!');
          }
        }

        // Komentāra edits
        if ($request->input('tread-comment-edit') == 'true') {
          $tread = Mototread::where('tread_id', $request->tread_id)->first();
          $tread->timestamps = false;
          $tread->t_comment = $request->input('tread-comment-text');
          if ($tread->save()) {
            return redirect($request->url())->with('success', 'Modeļa apraksts atjaunots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, modeļa apraksts nav atjaunots!');
          }
        } else if ($request->input('brand-comment-edit') == 'true') {
          $tread = Mototread::where('tread_id', $request->tread_id)->first();
          $brand = Motobrand::where('brand_id', $tread->brand_id)->first();
          $brand->timestamps = false;
          $brand->b_comment = $request->input('brand-comment-text');
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brenda apraksts atjaunots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brenda apraksts nav atjaunots!');
          }
        }
      }

      $tires = Moto::with('tread')
                ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
                ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
                ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
                ->orderby('d4', 'ASC')
                ->where('make_id', $request->tread_id)
                ->get();
      $tread = Mototread::where('tread_id', $request->tread_id)->first();
      if (!$tread) return redirect(route('admin.moto.tires'));
      $brand = Motobrand::where('brand_id', $tread->brand_id)->first();
      if (!$brand) return redirect(route('admin.moto.tires'));
      $brands = Motobrand::orderBy('title', 'ASC')->get();
      $treads = Mototread::select('moto_treads.*', 'moto_treads.title as t_title')->orderBy('t_title', 'ASC')->get();

      return view('admin.moto_tires.index', compact('tires', 'tread', 'brands', 'brand', 'treads'));
    }

    public function tire_create($id)
    {
      $tread = Mototread::where('tread_id', $id)->first();
      $brand = Motobrand::where('brand_id', $tread->brand_id)->first();

      return view('admin.moto_tires.tires.create', compact('tread', 'brand'));
    }

    public function tire_store(Request $request, $id)
    {
      $inputs = $request->except(['_token']);

      if (!array_filter($inputs)) {
        return redirect(route('admin.moto.tires.create', $id))->with('danger', 'Visi lauki ir tukši');
      }

      $tire = new Moto;
      $tire->timestamps = false;

      $tire->make_id = $id;
      $tire->d1 = ($request->d1 === null) ? '' : $request->d1;
      $tire->d2 = ($request->d2 === null) ? '' : $request->d2;
      $tire->d4 = ($request->d4 === null) ? '' : $request->d4;
      $tire->d3 = ($request->d3 === null) ? '' : $request->d3;
      $tire->type = ($request->tire_type === null) ? 'trail' : $request->tire_type;
      $tire->li = ($request->li === null) ? '' : $request->li;
      $tire->si = ($request->si === null) ? '' : $request->si;
      $tire->is_camera = $request->has('is_camera') ? 1 : 0;
      $tire->price1 = ($request->price1 === null) ? '' : $request->price1;
      $tire->price2 = ($request->price2 === null) ? '' : $request->price2;
      $tire->comment = ($request->comment === null) ? '' : $request->comment;
      $tire->acomment = ($request->acomment === null) ? '' : $request->acomment;
      $tire->code = ($request->code === null) ? '' : $request->code;
      $tire->article = ($request->article === null) ? null : $request->article;
      $tire->quantity = ($request->quantity === null) ? '' : $request->quantity;
      $tire->visible_list = 1;
      $tire->visible_users = 1;
      $tire->urs_quantity = ($request->urs_quantity === null) ? '' : $request->urs_quantity;
      $tire->krs_quantity = ($request->krs_quantity === null) ? '' : $request->krs_quantity;

      $tire->save();
      Moto::clearFilterCache();

      if ($request->i3article !== null) {
        $i3stock = new Motostock;
        $i3stock->tire_id = $tire->tire_id;
        $i3stock->article = $request->i3article;
        $i3stock->quantity = 0;
        $i3stock->itype = 'i3';
        $i3stock->metadata = '';
        $i3stock->save();
      }

      if ($request->duellarticle !== null) {
        $duellstock = new Motostock;
        $duellstock->tire_id = $tire->tire_id;
        $duellstock->article = $request->duellarticle;
        $duellstock->quantity = 0;
        $duellstock->itype = 'duell';
        $duellstock->metadata = '';
        $duellstock->save();
      }

      return redirect(route('admin.moto.tires.search', $id))->with('success', 'Riepa veiksmīgi pievienota');

    }

    public function tire_edit($id)
    {
      $tire = Moto::with('tread')->where('tire_id', $id)->first();

      $i3stock = Motostock::where('tire_id', $tire->tire_id)->where('itype', 'i3')->first();
      $duellstock = Motostock::where('tire_id', $tire->tire_id)->where('itype', 'duell')->first();

      return view('admin.moto_tires.tires.edit', compact('tire', 'i3stock', 'duellstock'));
    }

    public function tire_update(Request $request, $id)
    {

      $tire = Moto::findOrFail($id);

      $tire->d1 = $request->d1;
      $tire->d2 = $request->d2;
      $tire->d4 = $request->d4;
      $tire->d3 = $request->d3;
      $tire->type = ($request->tire_type) ? $request->tire_type : '';
      $tire->li = $request->li;
      $tire->si = $request->si;
      $tire->is_camera = $request->has('is_camera') ? 1 : 0;
      $tire->price1 = $request->price1;
      $tire->price2 = $request->price2;
      $tire->comment = $request->comment;
      $tire->acomment = $request->acomment;
      $tire->code = $request->code;
      $tire->article = ($request->article === null) ? null : $request->article;
      $tire->quantity = $request->quantity;
      $tire->urs_quantity = $request->urs_quantity;
      $tire->krs_quantity = $request->krs_quantity;

      $tire->save();

      $i3stock = Motostock::where('tire_id', $id)->where('itype', 'i3')->first();
      $duellstock = Motostock::where('tire_id', $id)->where('itype', 'duell')->first();

      if ($i3stock) {
        if ($request->i3article) {
          $i3stock->article = $request->i3article;
          $i3stock->save();
        } else {
          $i3stock->delete();
        }
      } else {
        if ($request->i3article) {
          $i3stock = new Motostock;
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
          $duellstock = new Motostock;
          $duellstock->tire_id = $tire->tire_id;
          $duellstock->article = $request->duellarticle;
          $duellstock->itype = 'duell';
          $duellstock->metadata = '';
          $duellstock->save();
        }
      }

      Moto::clearFilterCache();

      return redirect(route('admin.moto.tire.edit', $id))->with('success', 'Informācija veiksmīgi atjaunota');
    }

    public function tire_destroy($id)
    {

      Moto::where('tire_id', $id)->delete();
      Moto::clearFilterCache();

      return redirect()->back()->with('success', 'Riepa veiksmīgi dzēsta!');

    }

    public function tires_destroy(Request $request)
    {

      Moto::whereIn('tire_id', $request->tire_id)->delete();
      Motostock::whereIn('tire_id', $request->tire_id)->delete();
      Moto::clearFilterCache();

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
        $imageDefault->save('storage/moto/tread/' . $fileName . '.jpg');

        Image::make($image->getRealPath())
          ->resize(100, 100, function($constraint) {
            $constraint->aspectRatio();
          })->save('storage/moto/tread/' . $fileNameSmall . '.jpg');
        Image::make($image->getRealPath())
          ->resize(200, 200, function($constraint) {
            $constraint->aspectRatio();
          })->save('storage/moto/tread/' . $fileNameMed . '.jpg');
        $imageLarge = Image::make($image->getRealPath())
          ->resize(1500, 1500, function($constraint) {
            $constraint->aspectRatio();
          });
        $imageLarge->insert($watermark, 'bottom-right', 10, 10);
        $imageLarge->save('storage/moto/tread/' . $fileNameLarge . '.jpg');
      }
      return redirect()->back();
    }

    public function brands_list($paginate = 10)
    {
      \Session::remove('search');
      if (is_numeric($paginate)) {
        $brands = Motobrand::orderBy('title', 'ASC')->paginate($paginate);
      } else {
        return redirect(route('admin.moto.brands'));
      }

      return view('admin.moto_tires.brands.index', compact('brands', 'paginate'));
    }

    public function brand_search(Request $request, $paginate = 10) {

      if ($request->search) {
        \Session::put('search', $request->search);
        $brands = Motobrand::orderBy('brand_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
      } else {
        if (\Session::has('search')) {
          $brands = Motobrand::orderBy('brand_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
        } else {
          $brands = Motobrand::orderBy('brand_id', 'DESC')->paginate($paginate);
        }
      }

      return view('admin.moto_tires.brands.index', compact('brands', 'paginate'));
    }

    public function brand_add()
    {
      return view('admin.moto_tires.brands.add');
    }

    public function brand_store(Request $request)
    {
      $brand = new Motobrand();
      $brand->timestamps = false;
      $brand->title = $request->brand_title;
      $brand->slug = \Str::slug($request->brand_title, '-');
      if ($brand->save()) {
        return redirect(route('moto.moto.brands'))->with('success', 'Brends veiksmīgi pievienots!');
      } else {
        return redirect(route('moto.moto.brands'))->with('danger', 'Notika kļūda, brends nav pievienots!');
      }

//        if ($request->hasFile('brand_image')) {
//            $brand_edit = Autobrand::findOrFail($brand->brand_id);
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
      $brand = Motobrand::findOrFail($id);

      return view('admin.moto_tires.brands.edit', compact('brand'));
    }

    public function brand_update(Request $request, $id)
    {
      $brand = Autobrand::findOrFail($id);
      $brand->timestamps = false;
      $brand->title = $request->brand_title;
      $brand->slug = \Str::slug($request->brand_title, '-');

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

      return view('admin.moto_tires.brands.edit', compact('brand'));
    }

    public function brand_delete(Request $request, $id)
    {
      $brand = Motobrand::findOrFail($id);
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
        $treads = Mototread::orderBy('tread_id', 'DESC')->groupBy('tread_id')->paginate($paginate);
      } else {
        return redirect(route('admin.moto.treads'));
      }

      return view('admin.moto_tires.treads.index', compact('treads', 'paginate'));
    }

    public function treads_search(Request $request, $paginate = 10)
    {
      if ($request->search) {
        \Session::put('search', $request->search);
        $treads = Mototread::orderBy('tread_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
      } else {
        if (\Session::has('search')) {
          $treads = Mototread::orderBy('tread_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
        } else {
          $treads = Mototread::orderBy('tread_id', 'DESC')->paginate($paginate);
        }
      }

      return view('admin.moto_tires.treads.index', compact('treads', 'paginate'));
    }

    public function tread_add()
    {
      $brands = Motobrand::orderBy('title', 'ASC')->get();

      return view('admin.moto_tires.treads.add', compact('brands'));
    }

    public function tread_store(Request $request)
    {
      $tread = new Mototread();
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
        Image::make($image->getRealPath())->save('public/storage/moto/tread/' . $fileName . '.jpg');
        Image::make($image->getRealPath())
          ->resize(100, 100, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/moto/tread/' . $fileNameSmall . '.jpg');
        Image::make($image->getRealPath())
          ->resize(200, 200, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/moto/tread/' . $fileNameMed . '.jpg');
        Image::make($image->getRealPath())
          ->resize(1500, 1500, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/moto/tread/' . $fileNameLarge . '.jpg');
      }

      return redirect(route('admin.auto.treads'));
    }

    public function tread_edit($id)
    {
      $tread = Mototread::findOrFail($id);
      $brands = Motobrand::all();

      return view('admin.moto_tires.treads.edit', compact('tread', 'brands'));
    }

    public function tread_update(Request $request, $id)
    {
      $tread = Mototread::findOrFail($id);
      $tread->timestamps = false;
      $tread->title = $request->tread_title;
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
        Image::make($image->getRealPath())->save('public/storage/moto/tread/' . $fileName . '.jpg');
        Image::make($image->getRealPath())
          ->resize(100, 100, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/moto/tread/' . $fileNameSmall . '.jpg');
        Image::make($image->getRealPath())
          ->resize(200, 200, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/moto/tread/' . $fileNameMed . '.jpg');
        Image::make($image->getRealPath())
          ->resize(1500, 1500, function($constraint) {
            $constraint->aspectRatio();
          })->save('public/storage/moto/tread/' . $fileNameLarge . '.jpg');
      }

      $tread->save();
      $brands = Motobrand::all();

      return view('admin.moto_tires.treads.edit', compact('tread', 'brands'));
    }

    public function tread_delete($id)
    {
      $tread = Mototread::findOrFail($id);


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
      $treads = Mototread::with('tireCount')->select('moto_treads.*', 'moto_treads.title as t_title')->where('brand_id', $request->brand_id)->orderBy('title', 'ASC')->get();
      return json_encode($treads);
    }

    public function ajaxUpdateTires()
    {
      return 123;
    }
  }
