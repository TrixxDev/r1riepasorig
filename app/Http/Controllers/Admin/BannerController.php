<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bannerimage;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManagerStatic as Image;

class BannerController extends Controller
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
    $banners = Bannerimage::orderBy('id', 'desc')->get();
    return view('admin.settings.banners', compact('banners'));
  }

  public function upload(Request $request)
  {
    $validatedData = $request->validate([
      'formFile' => 'required|file|mimes:jpg,png,jpeg|max:2048',
    ]);

    $name = strtolower($request->file('formFile')->getClientOriginalName());
    $fileName = pathinfo($name, PATHINFO_FILENAME);
    $fileExtension = pathinfo($name, PATHINFO_EXTENSION);

    $name = $fileName . '.' . $fileExtension;

//    $path = $request->file('formFile')->store('images/banners');
//    dd($image);

    $save = new Bannerimage;
    $save->name = $name;
    $save->enabled = 0;

    if ($save->save()) {
      if ($request->hasFile('formFile')) {
        $image = $request->file('formFile');

        $height = Image::make($image)->height();
        $width = Image::make($image)->width();

//      dd($width, $height);
//      if ($width != 760 && $height != 100) return redirect()->back()->with('error', 'Bildes izmēram jābūt 760px x 100px');
//      Image::make($image->getRealPath())->save('public/storage/banners/' . $name);
        Image::make($image->getRealPath())->fit(540, 80)->save('storage/banners/' . $save->id . '.' . $fileExtension);
      }
      $save->name = $save->id . '.' . $fileExtension;
      $save->save();
    }

    Cache::forget('r1_view_shared_banners');

    return redirect()->back()->with('status', 'Banneris pievienots veiksmīgi!');
  }

  public function update(Request $request, $id) {
    $banner = Bannerimage::findOrFail($id);
    $banner->url = $request->url;
    $banner->save();
    Cache::forget('r1_view_shared_banners');

    return redirect()->back()->with('success', 'Bannerim veiksmīgi izmainīts links');
  }

  public function enable(Request $request, $id) {
    if ($request->enabled == 1) {
      $banners = Bannerimage::where('enabled', 1)->get();
      if (count($banners) == 4) {
        echo json_encode(['error' => 'Maksimāli atļautais banneru skaits - 4']);
        die;
      }
    }
    $banner = Bannerimage::findOrFail($id);
    $banner->enabled = $request->enabled;
    $banner->save();
    Cache::forget('r1_view_shared_banners');
  }

  public function delete($id) {
    $stock = Bannerimage::find($id);
    $stock->delete();
    Cache::forget('r1_view_shared_banners');

    return redirect()->back()->with('success', 'Banneris dzēsts.');
  }
}
