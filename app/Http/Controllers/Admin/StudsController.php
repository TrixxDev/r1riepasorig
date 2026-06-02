<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Stud;
use App\Models\Studbrand;
use App\Models\Studtread;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class StudsController extends Controller {

  public function index(Request $request) {

    if ($request->post()) {
      if ($request->input('new-brand') == 'true') {
        if (empty($request->input('brand-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada brenda nosaukums!');
        $brand = Studbrand::where('b_title', $request->input('brand-name'))->first();
        if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
        $brand = new Studbrand();
        $brand->timestamps = false;
        $brand->b_title = $request->input('brand-name');
        if ($brand->save()) {
          return redirect($request->url())->with('success', 'Brends ir pievienots!');
        } else {
          return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
        }
      } else if ($request->input('edit-brand') == 'true') {
        $brand = Studbrand::where('brand_id', $request->input('brand-id'))->first();
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
        $brand = Studbrand::where('brand_id', $request->input('brand-id'))->first();
        if (!$brand) return redirect($request->url())->with('danger', 'Tāds brends neeksistē, nevaru izdzēst!');
        if ($brand->delete()) {
          redirect($request->url())->with('success', 'Brends veiksmīgi izdzēsts!');
        } else {
          redirect($request->url())->with('danger', 'Notika kļūda, brends nav izdzēsts!');
        }
      }

      if ($request->input('new-make') == 'true') {
        if (empty($request->input('make-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada modeļa nosaukums!');
        $make = Studtread::where('t_title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
        if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
        $make = new Studtread();
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

//        $tires = Autotire::with(ctread')->groupBy('make_id')->paginate($perPage);
    $brands = Studbrand::orderBy('b_title', 'ASC')->get();
    $treads = Studtread::orderBy('t_title', 'ASC')->get();

    return view('admin.studs.index', compact('brands', 'treads'));
  }

  public function tires_search(Request $request)
  {

    if ($request->post()) {
      if ($request->input('new-brand') == 'true') {
        if (empty($request->input('brand-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada brenda nosaukums!');
        $brand = Studbrand::where('b_title', $request->input('brand-name'))->first();
        if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
        $brand = new Studbrand();
        $brand->timestamps = false;
        $brand->b_title = $request->input('brand-name');
        if ($brand->save()) {
          return redirect($request->url())->with('success', 'Brends ir pievienots!');
        } else {
          return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
        }
      } else if ($request->input('edit-brand') == 'true') {
        $brand = Studbrand::where('brand_id', $request->input('brand-id'))->first();
        $brand->timestamps = false;
//            if ($brand && $brand->title == $request->input('brand-name')) {
//              return redirect(route('admin.auto.tires'))->with('danger', 'Brenda nosaukums nav mainīts, ievadīts tāds pats!');
//            } else {
        $brand->b_title = $request->input('brand-name');
        if ($brand->save()) {
          return redirect($request->url())->with('success', 'Brenda nosaukums nomainīts!');
        } else {
          return redirect($request->url())->with('danger', 'Notika kļūda, brends nav mainīts!');
        }
//            }
      } else if ($request->input('delete-brand') == 'true') {
        $brand = Studbrand::where('brand_id', $request->input('brand-id'))->first();
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
        $make = Studtread::where('t_title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
        if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
        $make = new Studtread();
        $make->timestamps = false;
        $make->brand_id = $request->input('brand-id');
        $make->t_title = $request->input('make-name');
        if ($make->save()) {
          return redirect($request->url())->with('success', 'Modelis ir pievienots!');
        } else {
          return redirect($request->url())->with('danger', 'Notika kļūda, modelis nav pievienots!');
        }
      } else if ($request->input('edit-make') == 'true') {
        $make = Studtread::where('brand_id', $request->input('brand-id'))->where('tread_id', $request->tread_id)->first();
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
        $make = Studtread::where('tread_id', $request->tread_id)->first();
        if (!$make) return redirect($request->url())->with('danger', 'Tāds modelis neeksistē, nevaru izdzēst!');
        if ($make->delete()) {
          redirect($request->url())->with('success', 'Modelis veiksmīgi izdzēsts!');
        } else {
          redirect($request->url())->with('danger', 'Notika kļūda, modelis nav izdzēsts!');
        }
      }

      // Komentāra edits
      if ($request->input('tread-comment-edit') == 'true') {
        $tread = Studtread::where('tread_id', $request->tread_id)->first();
        $tread->timestamps = false;
        $tread->t_comment = $request->input('tread-comment-text');
        if ($tread->save()) {
          return redirect($request->url())->with('success', 'Modeļa apraksts atjaunots!');
        } else {
          return redirect($request->url())->with('danger', 'Notika kļūda, modeļa apraksts nav atjaunots!');
        }
      } else if ($request->input('brand-comment-edit') == 'true') {
        $tread = Studtread::where('tread_id', $request->tread_id)->first();
        $brand = Studbrand::where('brand_id', $tread->brand_id)->first();
        $brand->timestamps = false;
        $brand->b_comment = $request->input('brand-comment-text');
        if ($brand->save()) {
          return redirect($request->url())->with('success', 'Brenda apraksts atjaunots!');
        } else {
          return redirect($request->url())->with('danger', 'Notika kļūda, brenda apraksts nav atjaunots!');
        }
      }
    }

    $studs = Stud::where('make_id', $request->tread_id)->get();
    $tread = Studtread::where('tread_id', $request->tread_id)->first();
    if (!$tread) return redirect(route('admin.studs.index'));
    $brand = Studbrand::where('brand_id', $tread->brand_id)->first();
    if (!$brand) return redirect(route('admin.studs.index'));
    $brands = Studbrand::orderBy('b_title', 'ASC')->get();
    $treads = Studtread::orderBy('t_title', 'ASC')->get();

    return view('admin.studs.index', compact('studs', 'tread', 'brands', 'brand', 'treads'));
  }

  public function studs_create($id)
  {
    $tread = Studtread::where('tread_id', $id)->first();
    $brand = Studbrand::where('brand_id', $tread->brand_id)->first();

    return view('admin.studs.create', compact('tread', 'brand'));
  }

  public function studs_store(Request $request, $id)
  {
    $inputs = $request->except(['_token']);

    if (!array_filter($inputs)) {
      return redirect(route('admin.studs.create', $id))->with('danger', 'Visi lauki ir tukši');
    }

    $stud = new Stud;
    $stud->timestamps = false;

    $stud->make_id = $id;
    $stud->application = ($request->application === null) ? '' : implode(',', $request->application);
    $stud->stud_length = ($request->stud_length === null) ? '' : $request->stud_length;
    $stud->stud_count = ($request->stud_count === null) ? '' : $request->stud_count;
    $stud->price1 = ($request->price1 === null) ? '' : $request->price1;
    $stud->price2 = ($request->price2 === null) ? '' : $request->price2;
    $stud->comment = ($request->comment === null) ? '' : $request->comment;
    $stud->acomment = ($request->acomment === null) ? '' : $request->acomment;
    $stud->article = ($request->article === null) ? null : $request->article;
    $stud->quantity = ($request->quantity === null) ? '' : $request->quantity;
    $stud->visible_list = 1;
    $stud->visible_users = 1;
    $stud->urs_quantity = ($request->urs_quantity === null) ? '' : $request->urs_quantity;
    $stud->krs_quantity = ($request->krs_quantity === null) ? '' : $request->krs_quantity;

    $stud->save();

    return redirect(route('admin.studs.search', $id))->with('success', 'Radzes veiksmīgi pievienotas');

  }

  public function studs_edit($id) {
    $stud = Stud::where('stud_id', $id)->first();

    $applications = explode(',', $stud->application);

    return view('admin.studs.edit', compact('stud', 'applications'));
  }

  public function studs_update(Request $request, $id)
  {

    $stud = Stud::findOrFail($id);

    $stud->application = implode(',', $request->application);
    $stud->stud_length = $request->stud_length;
    $stud->stud_count = $request->stud_count;
    $stud->price1 = $request->price1;
    $stud->price2 = $request->price2;
    $stud->comment = $request->comment;
    $stud->acomment = $request->acomment;
    $stud->article = ($request->article === null) ? null : $request->article;
    $stud->quantity = $request->quantity;
    $stud->urs_quantity = $request->urs_quantity;
    $stud->krs_quantity = $request->krs_quantity;

    $stud->save();

    return redirect(route('admin.studs.edit', $id))->with('success', 'Informācija veiksmīgi atjaunota');
  }

  public function studs_destroy($id)
  {

    Stud::where('stud_id', $id)->delete();

    return redirect()->back()->with('success', 'Riepa veiksmīgi dzēsta!');

  }

  public function studs_image(Request $request, $id)
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
      $imageDefault->save('storage/stud/tread/' . $fileName . '.jpg');

      Image::make($image->getRealPath())
        ->resize(100, 100, function($constraint) {
          $constraint->aspectRatio();
        })->save('storage/stud/tread/' . $fileNameSmall . '.jpg');
      Image::make($image->getRealPath())
        ->resize(200, 200, function($constraint) {
          $constraint->aspectRatio();
        })->save('storage/stud/tread/' . $fileNameMed . '.jpg');
      $imageLarge = Image::make($image->getRealPath())
        ->resize(1500, 1500, function($constraint) {
          $constraint->aspectRatio();
        });
      $imageLarge->insert($watermark, 'bottom-right', 10, 10);
      $imageLarge->save('storage/stud/tread/' . $fileNameLarge . '.jpg');
    }
    return redirect()->back();
  }

  public function ajaxUpdateTreads(Request $request)
  {
    $treads = Studtread::where('brand_id', $request->brand_id)->orderBy('t_title', 'ASC')->get();
    return json_encode($treads);
  }

  public function ajaxUpdateTires()
  {
    return 123;
  }

}
