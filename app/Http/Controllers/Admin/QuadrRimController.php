<?php

  namespace App\Http\Controllers\Admin;

  use App\Http\Controllers\Controller;
  use App\Models\Quadrim;
  use App\Models\Quadrimbrand;
  use App\Models\Quadrimmake;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Facades\View;
  use Illuminate\Support\Str;
  use Intervention\Image\ImageManagerStatic as Image;

  class QuadrRimController extends Controller
  {

    public $brands;
    public $treads;

    public function __construct()
    {
      $this->brands = Quadrimbrand::orderBy('b_title', 'ASC')->get();
      $this->treads = Quadrimmake::orderBy('t_title', 'ASC')->get();

      View::share('brands', $this->brands);
      View::share('treads', $this->treads);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
    public function index(Request $request)
    {

      if ($request->post()) {
        if ($request->input('new-brand') == 'true') {
          if (empty($request->input('brand-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada brenda nosaukums!');
          $brand = Quadrimbrand::where('b_title', $request->input('brand-name'))->first();
          if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
          $brand = new Quadrimbrand;
          $brand->timestamps = false;
          $brand->b_title = $request->input('brand-name');
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brends ir pievienots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
          }
        } else if ($request->input('edit-brand') == 'true') {
          $brand = Quadrimbrand::where('brand_id', $request->input('brand-id'))->first();
          $brand->timestamps = false;
          if ($brand && $brand->title == $request->input('brand-name')) {
            return redirect($request->url())->with('danger', 'Brenda nosaukums nav mainīts, ievadīts tāds pats!');
          } else {
            $brand->b_title = $request->input('brand-name');
            if ($brand->save()) {
              return redirect($request->url())->with('success', 'Brenda nosaukums nomainīts!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, brends nav mainīts!');
            }
          }
        } else if ($request->input('delete-brand') == 'true') {
          $brand = Quadrimbrand::where('brand_id', $request->input('brand-id'))->first();
          if (!$brand) return redirect($request->url())->with('danger', 'Tāds brends neeksistē, nevaru izdzēst!');
          if ($brand->delete()) {
            redirect($request->url())->with('success', 'Brends veiksmīgi izdzēsts!');
          } else {
            redirect($request->url())->with('danger', 'Notika kļūda, brends nav izdzēsts!');
          }
        }

        if ($request->input('new-make') == 'true') {
          if (empty($request->input('make-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada modeļa nosaukums!');
          $make = Quadrimmake::where('t_title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
          if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
          $make = new Quadrimmake;
          $make->timestamps = false;
          $make->brand_id = $request->input('brand-id');
          $make->t_title = $request->input('make-name');
          if ($make->save()) {
            return redirect($request->url())->with('success', 'Modelis ir pievienots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, modelis nav pievienots!');
          }
        }
      }

      return view('admin.quadrims.index');
    }

    public function quadrims_search(Request $request)
    {

      if ($request->post()) {
        if ($request->input('new-brand') == 'true') {
          if (empty($request->input('brand-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada brenda nosaukums!');
          $brand = Quadrimbrand::where('b_title', $request->input('brand-name'))->first();
          if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
          $brand = new Quadrimbrand;
          $brand->timestamps = false;
          $brand->b_title = $request->input('brand-name');
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brends ir pievienots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
          }
        } else if ($request->input('edit-brand') == 'true') {
          $brand = Quadrimbrand::where('brand_id', $request->input('brand-id'))->first();
          $brand->timestamps = false;
          //            if ($brand && $brand->title == $request->input('brand-name')) {
          //              return redirect(route('admin.auto.tires'))->with('danger', 'Brenda nosaukums nav mainīts, ievadīts tāds pats!');
          //            } else {
          $brand->title = $request->input('brand-name');
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brenda nosaukums nomainīts!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brends nav mainīts!');
          }
          //            }
        } else if ($request->input('delete-brand') == 'true') {
          $brand = Quadrimbrand::where('brand_id', $request->input('brand-id'))->first();
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
          $make = Quadrimmake::where('t_title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
          if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
          $make = new Quadrimmake;
          $make->timestamps = false;
          $make->brand_id = $request->input('brand-id');
          $make->t_title = $request->input('make-name');
          if ($make->save()) {
            return redirect($request->url())->with('success', 'Modelis ir pievienots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, modelis nav pievienots!');
          }
        } else if ($request->input('edit-make') == 'true') {
          $make = Quadrimmake::where('brand_id', $request->input('brand-id'))->where('make_id', $request->tread_id)->first();
          $make->timestamps = false;
          //            if ($make && $make->t_title == $request->input('make-name')) {
          //              return redirect($request->url())->with('danger', 'Modeļa nosaukums nav mainīts, ievadīts tāds pats!');
          //            } else {
          $make->t_title = $request->input('make-name');
          if ($make->save()) {
            return redirect($request->url())->with('success', 'Modeļa nosaukums nomainīts!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, modeļis nav mainīts!');
          }
          //            }
        } else if ($request->input('delete-make') == 'true') {
          $make = Quadrimmake::where('make_id', $request->tread_id)->first();
          if (!$make) return redirect($request->url())->with('danger', 'Tāds modelis neeksistē, nevaru izdzēst!');
          if ($make->delete()) {
            redirect($request->url())->with('success', 'Modelis veiksmīgi izdzēsts!');
          } else {
            redirect($request->url())->with('danger', 'Notika kļūda, modelis nav izdzēsts!');
          }
        }

        // Komentāra edits
        if ($request->input('tread-comment-edit') == 'true') {
          $tread = Quadrimmake::where('make_id', $request->tread_id)->first();
          $tread->timestamps = false;
          $tread->t_comment = $request->input('tread-comment-text');
          if ($tread->save()) {
            return redirect($request->url())->with('success', 'Modeļa apraksts atjaunots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, modeļa apraksts nav atjaunots!');
          }
        } else if ($request->input('brand-comment-edit') == 'true') {
          $tread = Quadrimmake::where('make_id', $request->tread_id)->first();
          $brand = Quadrimbrand::where('brand_id', $tread->brand_id)->first();
          $brand->timestamps = false;
          $brand->b_comment = $request->input('brand-comment-text');
          if ($brand->save()) {
            return redirect($request->url())->with('success', 'Brenda apraksts atjaunots!');
          } else {
            return redirect($request->url())->with('danger', 'Notika kļūda, brenda apraksts nav atjaunots!');
          }
        }
      }

      $rims = Quadrim::orderByRaw('cast(d3 as decimal(7,2)) ASC')
        ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
        ->where('make_id', $request->tread_id)
        ->get();
      $tread = Quadrimmake::where('make_id', $request->tread_id)->first();
      if (!$tread) return redirect(route('admin.quadrims'));
      $brand = Quadrimbrand::where('brand_id', $tread->brand_id)->first();
      if (!$brand) return redirect(route('admin.quadrims'));

      return view('admin.quadrims.index', compact('rims', 'tread', 'brand'));
    }

    public function quadrims_create($id)
    {
      $tread = Quadrimmake::where('make_id', $id)->first();
      $brand = Quadrimbrand::where('brand_id', $tread->brand_id)->first();

      return view('admin.quadrims.create', compact('tread', 'brand'));
    }

    public function quadrims_store(Request $request, $id)
    {
      $inputs = $request->except(['_token']);

      if (!array_filter($inputs)) {
        return redirect(route('admin.quadrims.create', $id))->with('danger', 'Visi lauki ir tukši');
      }

      $rim = new Quadrim;
      $rim->timestamps = false;

      $rim->make_id = $id;
      $rim->d1 = ($request->d1 === null) ? '' : $request->d1;
      $rim->d3 = ($request->d3 === null) ? '' : $request->d3;
      $rim->price1 = ($request->price1 === null) ? '' : $request->price1;
      $rim->price2 = ($request->price2 === null) ? '' : $request->price2;
      $rim->skr = ($request->skr === null) ? '' : $request->skr;
      $rim->pcd = ($request->pcd === null) ? '' : $request->pcd;
      $rim->et = ($request->et === null) ? '' : $request->et;
      $rim->color = ($request->color === null) ? '' : $request->color;
      $rim->comment = ($request->comment === null) ? '' : $request->comment;
      $rim->acomment = ($request->acomment === null) ? '' : $request->acomment;
      $rim->article = ($request->article === null) ? null : $request->article;
      $rim->quantity = ($request->quantity === null) ? '' : $request->quantity;
      $rim->visible_list = 1;
      $rim->visible_users = 1;
      $rim->urs_quantity = ($request->urs_quantity === null) ? '' : $request->urs_quantity;
      $rim->krs_quantity = ($request->krs_quantity === null) ? '' : $request->krs_quantity;

      $rim->save();

      return redirect(route('admin.quadrims.search', $id))->with('success', 'Disks veiksmīgi pievienots');

    }

    public function quadrims_edit($id)
    {
      $rim = Quadrim::where('rim_id', $id)->first();

      return view('admin.quadrims.edit', compact('rim'));
    }

    public function quadrims_update(Request $request, $id)
    {

      $rim = Quadrim::findOrFail($id);

      $rim->d1 = $request->d1;
      $rim->d3 = $request->d3;
      $rim->price1 = $request->price1;
      $rim->price2 = $request->price2;
      $rim->skr = $request->skr;
      $rim->pcd = $request->pcd;
      $rim->et = $request->et;
      $rim->color = $request->color;
      $rim->comment = $request->comment;
      $rim->acomment = $request->acomment;
      $rim->article = ($request->article === null) ? null : $request->article;
      $rim->quantity = $request->quantity;
      $rim->urs_quantity = $request->urs_quantity;
      $rim->krs_quantity = $request->krs_quantity;

      $rim->save();

      return redirect(route('admin.quadrims.edit', $id))->with('success', 'Informācija veiksmīgi atjaunota');
    }

    public function quadrims_destroy($id)
    {

      Quadrim::where('rim_id', $id)->delete();

      return redirect()->back()->with('success', 'Disks veiksmīgi dzēsts!');

    }

    public function quadrims_image(Request $request, $id)
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
        $imageDefault->save('storage/quadrims/tread/' . $fileName . '.jpg');

        Image::make($image->getRealPath())
          ->resize(100, 100, function($constraint) {
            $constraint->aspectRatio();
          })->save('storage/quadrims/tread/' . $fileNameSmall . '.jpg');
        Image::make($image->getRealPath())
          ->resize(200, 200, function($constraint) {
            $constraint->aspectRatio();
          })->save('storage/quadrims/tread/' . $fileNameMed . '.jpg');
        $imageLarge = Image::make($image->getRealPath())
          ->resize(1500, 1500, function($constraint) {
            $constraint->aspectRatio();
          });
        $imageLarge->insert($watermark, 'bottom-right', 10, 10);
        $imageLarge->save('storage/quadrims/tread/' . $fileNameLarge . '.jpg');
      }
      return redirect()->back();
    }

    /*
     *
     *
     * Disku atjaunošana (Ajax)
     *
     *
     */

    public function ajaxUpdateTreads(Request $request)
    {
      $treads = Quadrimmake::with('tireCount')->select('quadrim_makes.*', 'quadrim_makes.make_id as tread_id')->where('brand_id', $request->brand_id)->orderBy('t_title', 'ASC')->get();
      return json_encode($treads);
    }

    public function ajaxUpdateTires()
    {
      return true;
    }
  }
