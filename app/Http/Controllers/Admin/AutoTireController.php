<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Autobrand;
use App\Models\Autostock;
use App\Models\Autotire;
use App\Models\Autotread;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use Storage;

class AutoTireController extends Controller
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
            $brand = Autobrand::where('title', $request->input('brand-name'))->first();
            if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
            $brand = new Autobrand;
            $brand->timestamps = false;
            $brand->title = $request->input('brand-name');
            $brand->slug = Str::slug($brand->title);
            if ($brand->save()) {
              return redirect($request->url())->with('success', 'Brends ir pievienots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
            }
          } else if ($request->input('edit-brand') == 'true') {
            $brand = Autobrand::where('brand_id', $request->input('brand-id'))->first();
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
            $brand = Autobrand::where('brand_id', $request->input('brand-id'))->first();
            if (!$brand) return redirect($request->url())->with('danger', 'Tāds brends neeksistē, nevaru izdzēst!');
            if ($brand->delete()) {
              redirect($request->url())->with('success', 'Brends veiksmīgi izdzēsts!');
            } else {
              redirect($request->url())->with('danger', 'Notika kļūda, brends nav izdzēsts!');
            }
          }

          if ($request->input('new-make') == 'true') {
            if (empty($request->input('make-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada modeļa nosaukums!');
            $make = Autotread::where('t_title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
            if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
            $make = new Autotread;
            $make->timestamps = false;
            $make->season = $request->input('make-season');
            $make->brand_id = $request->input('brand-id');
            $make->t_title = $request->input('make-name');
            $make->slug = Str::slug($make->t_title);
            if ($make->save()) {
              return redirect($request->url())->with('success', 'Modelis ir pievienots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, modelis nav pievienots!');
            }
          }
        }

//        $tires = Autotire::with(ctread')->groupBy('make_id')->paginate($perPage);
        $brands = Autobrand::orderBy('title', 'ASC')->get();
        $treads = Autotread::orderBy('t_title', 'ASC')->get();

        return view('admin.auto_tires.index', compact('brands', 'treads'));
    }

    public function tires_search(Request $request)
    {

        if ($request->post()) {
          if ($request->input('new-brand') == 'true') {
            if (empty($request->input('brand-name'))) return redirect($request->url())->with('danger', 'Sākumā jāievada brenda nosaukums!');
            $brand = Autobrand::where('title', $request->input('brand-name'))->first();
            if ($brand) return redirect($request->url())->with('danger', 'Brends ar šādu nosaukumu jau eksistē!');
            $brand = new Autobrand;
            $brand->timestamps = false;
            $brand->title = $request->input('brand-name');
            $brand->slug = Str::slug($brand->title);
            if ($brand->save()) {
              return redirect($request->url())->with('success', 'Brends ir pievienots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, brends nav pievienots!');
            }
          } else if ($request->input('edit-brand') == 'true') {
            $brand = Autobrand::where('brand_id', $request->input('brand-id'))->first();
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
            $brand = Autobrand::where('brand_id', $request->input('brand-id'))->first();
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
            $make = Autotread::where('t_title', $request->input('make-name'))->where('brand_id', $request->input('brand-id'))->first();
            if ($make) return redirect($request->url())->with('danger', 'Modelis ar šādu nosaukumu jau eksistē!');
            $make = new Autotread;
            $make->timestamps = false;
            $make->season = $request->input('make-season');
            $make->brand_id = $request->input('brand-id');
            $make->t_title = $request->input('make-name');
            $make->slug = Str::slug($make->t_title);
            if ($make->save()) {
              return redirect($request->url())->with('success', 'Modelis ir pievienots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, modelis nav pievienots!');
            }
          } else if ($request->input('edit-make') == 'true') {
            $make = Autotread::where('brand_id', $request->input('brand-id'))->where('tread_id', $request->tread_id)->first();
            $make->timestamps = false;
//            if ($make && $make->t_title == $request->input('make-name')) {
//              return redirect($request->url())->with('danger', 'Modeļa nosaukums nav mainīts, ievadīts tāds pats!');
//            } else {
            $make->season = $request->input('make-season');
            $make->t_title = $request->input('make-name');
            $make->slug = Str::slug($make->t_title);
            if ($make->save()) {
              return redirect($request->url())->with('success', 'Modeļa nosaukums nomainīts!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, modeļis nav mainīts!');
            }
//            }
          } else if ($request->input('delete-make') == 'true') {
            $make = Autotread::where('tread_id', $request->tread_id)->first();
            if (!$make) return redirect($request->url())->with('danger', 'Tāds modelis neeksistē, nevaru izdzēst!');
            if ($make->delete()) {
              redirect($request->url())->with('success', 'Modelis veiksmīgi izdzēsts!');
            } else {
              redirect($request->url())->with('danger', 'Notika kļūda, modelis nav izdzēsts!');
            }
          }

          // Komentāra edits
          if ($request->input('tread-comment-edit') == 'true') {
            $tread = Autotread::where('tread_id', $request->tread_id)->first();
            $tread->timestamps = false;
            $tread->t_comment = $request->input('tread-comment-text');
            if ($tread->save()) {
              return redirect($request->url())->with('success', 'Modeļa apraksts atjaunots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, modeļa apraksts nav atjaunots!');
            }
          } else if ($request->input('brand-comment-edit') == 'true') {
            $tread = Autotread::where('tread_id', $request->tread_id)->first();
            $brand = Autobrand::where('brand_id', $tread->brand_id)->first();
            $brand->timestamps = false;
            $brand->b_comment = $request->input('brand-comment-text');
            if ($brand->save()) {
              return redirect($request->url())->with('success', 'Brenda apraksts atjaunots!');
            } else {
              return redirect($request->url())->with('danger', 'Notika kļūda, brenda apraksts nav atjaunots!');
            }
          }
        }

        $tires = Autotire::with('tread')
                  ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
                  ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
                  ->orderByRaw('cast(d2 as decimal(7,2)) ASC')
                  ->where('make_id', $request->tread_id)
                  ->get();
        $tread = Autotread::where('tread_id', $request->tread_id)->first();
        if (!$tread) return redirect(route('admin.auto.tires'));
        $brand = Autobrand::where('brand_id', $tread->brand_id)->first();
        if (!$brand) return redirect(route('admin.auto.tires'));
        $brands = Autobrand::orderBy('title', 'ASC')->get();
        $treads = Autotread::orderBy('t_title', 'ASC')->get();

        return view('admin.auto_tires.index', compact('tires', 'tread', 'brands', 'brand', 'treads'));
    }

    public function tire_create($id)
    {
      $tread = Autotread::where('tread_id', $id)->first();
      $brand = Autobrand::where('brand_id', $tread->brand_id)->first();

      return view('admin.auto_tires.tires.create', compact('tread', 'brand'));
    }

    public function tire_store(Request $request, $id)
    {
        $inputs = $request->except(['_token']);

        if (!array_filter($inputs)) {
          return redirect(route('admin.auto.tires.create', $id))->with('danger', 'Visi lauki ir tukši');
        }

        $tire = new Autotire;
        $tire->timestamps = false;

        $tire->make_id = $id;
        $tire->d1 = ($request->d1 === null) ? '' : $request->d1;
        $tire->d2 = ($request->d2 === null) ? '' : $request->d2;
        $tire->d3 = ($request->d3 === null) ? '' : $request->d3;
        $tire->type = ($request->tire_type === null) ? 0 : $request->tire_type;
        $tire->li = ($request->li === null) ? '' : $request->li;
        $tire->si = ($request->si === null) ? '' : $request->si;
        $tire->price1 = ($request->price1 === null) ? '' : $request->price1;
        $tire->price2 = ($request->price2 === null) ? '' : $request->price2;
        $tire->comment = ($request->comment === null) ? '' : $request->comment;
        $tire->acomment = ($request->acomment === null) ? '' : $request->acomment;
        $tire->code = ($request->code === null) ? '' : $request->code;
        $tire->eco = ($request->eco === null) ? '' : $request->eco;
        $tire->wet = ($request->wet === null) ? '' : $request->wet;
        $tire->noise = ($request->noise === null) ? '' : $request->noise;
        $tire->article = ($request->article === null) ? null : $request->article;
        $tire->quantity = ($request->quantity === null) ? '' : $request->quantity;
        $tire->visible_list = 1;
        $tire->visible_users = 1;
        $tire->top = 1;
        $tire->urs_quantity = ($request->urs_quantity === null) ? '' : $request->urs_quantity;
        $tire->krs_quantity = ($request->krs_quantity === null) ? '' : $request->krs_quantity;

        $tire->save();

        $tid = (int) $tire->tire_id;
        $this->syncPartnerAutostock(null, $request->i3article, 'i3', $tid);
        $this->syncPartnerAutostock(null, $request->gyarticle, 'gy', $tid);
        $this->syncPartnerAutostock(null, $request->rzarticle, 'rz', $tid);
        $this->syncPartnerAutostock(null, $request->rgarticle, 'rg', $tid);

        Autotire::clearFilterCache();

        return redirect(route('admin.auto.tires.search', $id))->with('success', 'Riepa veiksmīgi pievienota');

    }

    public function tire_edit($id)
    {
        $tire = Autotire::with('tread')->where('tire_id', $id)->first();

        $i3stock = Autostock::where('tire_id', $tire->tire_id)->where('itype', 'i3')->first();
        $gystock = Autostock::where('tire_id', $tire->tire_id)->where('itype', 'gy')->first();
        $rzstock = Autostock::where('tire_id', $tire->tire_id)->where('itype', 'rz')->first();
        $rgstock = Autostock::where('tire_id', $tire->tire_id)->where('itype', 'rg')->first();

        return view('admin.auto_tires.tires.edit', compact('tire', 'i3stock', 'gystock', 'rzstock', 'rgstock'));
    }

    public function tire_update(Request $request, $id)
    {

        $tire = Autotire::findOrFail($id);

        $tire->d1 = $request->d1;
        $tire->d2 = $request->d2;
        $tire->d3 = $request->d3;
        $tire->type = ($request->tire_type) ? $request->tire_type : 0;
        $tire->li = $request->li;
        $tire->si = $request->si;
        $tire->price1 = $request->price1;
        $tire->price2 = $request->price2;
        $tire->comment = $request->comment;
        $tire->acomment = $request->acomment;
        $tire->code = $request->code;
        $tire->eco = $request->eco;
        $tire->wet = $request->wet;
        $tire->noise = $request->noise;
        $tire->article = ($request->article === null) ? null : $request->article;
        $tire->quantity = $request->quantity;
        $tire->urs_quantity = $request->urs_quantity;
        $tire->krs_quantity = $request->krs_quantity;

        $tire->save();

        $i3stock = Autostock::where('tire_id', $id)->where('itype', 'i3')->first();
        $gystock = Autostock::where('tire_id', $id)->where('itype', 'gy')->first();
        $rzstock = Autostock::where('tire_id', $id)->where('itype', 'rz')->first();
        $rgstock = Autostock::where('tire_id', $id)->where('itype', 'rg')->first();

        $tid = (int) $tire->tire_id;
        $this->syncPartnerAutostock($i3stock, $request->i3article, 'i3', $tid);
        $this->syncPartnerAutostock($gystock, $request->gyarticle, 'gy', $tid);
        $this->syncPartnerAutostock($rzstock, $request->rzarticle, 'rz', $tid);
        $this->syncPartnerAutostock($rgstock, $request->rgarticle, 'rg', $tid);

        Autotire::clearFilterCache();

        return redirect(route('admin.auto.tire.edit', $id))->with('success', 'Informācija veiksmīgi atjaunota');
    }

    /**
     * Partner auto_stock: update article, unlink (tire_id null, keep row/qty), re-link orphan with same article+itype, or create new row.
     */
    protected function syncPartnerAutostock(?Autostock $stock, $articleRaw, string $itype, int $tireId): void
    {
        $article = $articleRaw === null ? '' : trim((string) $articleRaw);
        $hasArticle = $article !== '';

        if ($stock) {
            if ($hasArticle) {
                $stock->article = $article;
                $stock->save();
            } else {
                $stock->tire_id = null;
                $stock->save();
            }

            return;
        }

        if (!$hasArticle) {
            return;
        }

        // Match orphan even if article is stored as INT vs string, with stray spaces, or tire_id is 0
        $orphan = Autostock::query()
            ->where('itype', $itype)
            ->where(function ($q) {
                $q->whereNull('tire_id')->orWhere('tire_id', 0);
            })
            ->whereRaw('TRIM(CAST(`article` AS CHAR)) = ?', [$article])
            ->orderBy('stock_id')
            ->first();

        if ($orphan) {
            $orphan->tire_id = $tireId;
            $orphan->save();
            // Drop stray duplicate row(s) created earlier when orphan was not matched (same tire + itype)
            Autostock::query()
                ->where('tire_id', $tireId)
                ->where('itype', $itype)
                ->where('stock_id', '!=', $orphan->stock_id)
                ->delete();

            return;
        }

        $row = new Autostock;
        $row->tire_id = $tireId;
        $row->article = $article;
        $row->quantity = 0;
        $row->itype = $itype;
        $row->metadata = '';
        $row->save();
    }

    public function tire_destroy($id)
    {

      Autotire::where('tire_id', $id)->delete();
      Autotire::clearFilterCache();

      return redirect()->back()->with('success', 'Riepa veiksmīgi dzēsta!');

    }

    public function tires_destroy(Request $request)
    {

      Autostock::whereIn('tire_id', $request->tire_id)->delete();
      Autotire::whereIn('tire_id', $request->tire_id)->delete();
      Autotire::clearFilterCache();

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
            $imageDefault->save('storage/auto/tread/' . $fileName . '.jpg');
            Image::make($image->getRealPath())
              ->resize(100, 100, function($constraint) {
                $constraint->aspectRatio();
              })->save('storage/auto/tread/' . $fileNameSmall . '.jpg');
            Image::make($image->getRealPath())
              ->resize(200, 200, function($constraint) {
                $constraint->aspectRatio();
              })->save('storage/auto/tread/' . $fileNameMed . '.jpg');
            $imageLarge = Image::make($image->getRealPath())
              ->resize(1500, 1500, function($constraint) {
                $constraint->aspectRatio();
              });
            $imageLarge->insert($watermark, 'bottom-right', 10, 10);
            $imageLarge->save('storage/auto/tread/' . $fileNameLarge . '.jpg');
        }
        return redirect()->back();
    }

    public function brands_list($paginate = 10)
    {
        \Session::remove('search');
        if (is_numeric($paginate)) {
            $brands = Autobrand::orderBy('title', 'ASC')->paginate($paginate);
        } else {
            return redirect(route('admin.auto.brands'));
        }

        return view('admin.auto_tires.brands.index', compact('brands', 'paginate'));
    }

    public function brand_search(Request $request, $paginate = 10) {

        if ($request->search) {
            \Session::put('search', $request->search);
            $brands = Autobrand::orderBy('brand_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
        } else {
            if (\Session::has('search')) {
                $brands = Autobrand::orderBy('brand_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
            } else {
                $brands = Autobrand::orderBy('brand_id', 'DESC')->paginate($paginate);
            }
        }

        return view('admin.auto_tires.brands.index', compact('brands', 'paginate'));
    }

    public function brand_add()
    {
        return view('admin.auto_tires.brands.add');
    }

    public function brand_store(Request $request)
    {

        $breaks = array("<br />","<br>","<br/>");
        $request->brand_desc = str_ireplace($breaks, "", $request->brand_desc);

        $brand = new Autobrand();
        $brand->timestamps = false;
        $brand->title = $request->brand_title;
        $brand->slug = \Str::slug($request->brand_title, '-');
        $brand->b_comment = nl2br($request->brand_desc);
        if ($brand->save()) {
          return redirect(route('admin.auto.brands'))->with('success', 'Brends veiksmīgi pievienots!');
        } else {
          return redirect(route('admin.auto.brands'))->with('danger', 'Notika kļūda, brends nav pievienots!');
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
        $brand = Autobrand::findOrFail($id);

        return view('admin.auto_tires.brands.edit', compact('brand'));
    }

    public function brand_update(Request $request, $id)
    {

        $breaks = array("<br />","<br>","<br/>");
        $request->brand_desc = str_ireplace($breaks, "", $request->brand_desc);

        $brand = Autobrand::findOrFail($id);
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

        return view('admin.auto_tires.brands.edit', compact('brand'));
    }

    public function brand_delete(Request $request, $id)
    {
        $brand = Autobrand::findOrFail($id);
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
            $treads = Autotread::orderBy('tread_id', 'DESC')->paginate($paginate);
        } else {
            return redirect(route('admin.auto.treads'));
        }

        return view('admin.auto_tires.treads.index', compact('treads', 'paginate'));
    }

    public function treads_search(Request $request, $paginate = 10)
    {
        if ($request->search) {
            \Session::put('search', $request->search);
            $treads = Autotread::orderBy('tread_id', 'DESC')->where('t_title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
        } else {
            if (\Session::has('search')) {
                $treads = Autotread::orderBy('tread_id', 'DESC')->where('title', 'LIKE', '%' . \Session::get('search') . '%')->paginate($paginate);
            } else {
                $treads = Autotread::orderBy('tread_id', 'DESC')->paginate($paginate);
            }
        }

        return view('admin.auto_tires.treads.index', compact('treads', 'paginate'));
    }

    public function tread_add()
    {
        $brands = Autobrand::orderBy('title', 'ASC')->get();

        return view('admin.auto_tires.treads.add', compact('brands'));
    }

    public function tread_store(Request $request)
    {
        $tread = new Autotread();
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
          Image::make($image->getRealPath())->save('public/storage/auto/tread/' . $fileName . '.jpg');
          Image::make($image->getRealPath())
            ->resize(100, 100, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/auto/tread/' . $fileNameSmall . '.jpg');
          Image::make($image->getRealPath())
            ->resize(200, 200, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/auto/tread/' . $fileNameMed . '.jpg');
          Image::make($image->getRealPath())
            ->resize(1500, 1500, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/auto/tread/' . $fileNameLarge . '.jpg');
        }

        return redirect(route('admin.auto.treads'));
    }

    public function tread_edit($id)
    {
        $tread = Autotread::findOrFail($id);
        $brands = Autobrand::orderby('title', 'ASC')->get();

        return view('admin.auto_tires.treads.edit', compact('tread', 'brands'));
    }

    public function tread_update(Request $request, $id)
    {

        $breaks = array("<br />","<br>","<br/>");
        $request->tread_desc = str_ireplace($breaks, "", $request->tread_desc);

        $tread = Autotread::findOrFail($id);
        $tread->timestamps = false;
        $tread->t_title = $request->tread_title;
        $tread->slug = \Str::slug($request->tread_title, '-');
        $tread->season = $request->tread_season;
        $tread->brand_id = $request->tread_brand;
        $tread->t_comment = nl2br($request->tread_desc);

        if ($request->hasFile('tread_image')) {
          $image      = $request->file('tread_image');
          $fileName   = $tread->tread_id;
          $fileNameSmall   = $tread->tread_id . '-s';
          $fileNameMed   = $tread->tread_id . '-n';
          $fileNameLarge   = $tread->tread_id . '-o';
//            dd($image);
          Image::make($image->getRealPath())->save('public/storage/auto/tread/' . $fileName . '.jpg');
          Image::make($image->getRealPath())
            ->resize(100, 100, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/auto/tread/' . $fileNameSmall . '.jpg');
          Image::make($image->getRealPath())
            ->resize(200, 200, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/auto/tread/' . $fileNameMed . '.jpg');
          Image::make($image->getRealPath())
            ->resize(1500, 1500, function($constraint) {
              $constraint->aspectRatio();
            })->save('public/storage/auto/tread/' . $fileNameLarge . '.jpg');
        }

        if ($tread->save()) {
          return redirect(route('admin.auto.treads'))->with('success', 'Riepas modelis veiksmīgi labots!');
        } else {
          return redirect(route('admin.auto.treads.edit', $tread->tread_id))->with('danger', 'Notika kļūda, nevaru atjaunot modeli!');
        }
    }

    public function tread_delete($id)
    {
      $tread = Autotread::findOrFail($id);
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
        $treads = Autotread::with('tireCount')->where('brand_id', $request->brand_id)->orderBy('t_title', 'ASC')->get();

        return json_encode($treads);
    }

    public function ajaxUpdateTires()
    {
        return 123;
    }

    public function toggletop(Request $request)
    {

      $tire_id = $request->input()['tire_id'];

      $tire = Autotire::where('tire_id', $tire_id)->first();
      if (!$tire) return json_encode(['success' => false]);
      $tire->top = ($tire->top == 0) ? 1 : 0;
      if ($tire->save()) {
        return json_encode(['success' => true]);
      } else {
        return json_encode(['success' => false]);
      }

    }
}
