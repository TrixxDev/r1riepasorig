<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PromoCodeController extends Controller
{

  public function index()
  {

    $promo_codes = Promo::orderBy('promo_id', 'desc')->get();

    return view('admin.promo.index', compact('promo_codes'));
  }

  public function create() {
    return view('admin.promo.create');
  }

  public function store(Request $request) {

    $errors = [];

    if (is_null($request->name)) $errors['name'] = 'Koda nosaukums nevar būt tukšs!';
    if (is_null($request->code)) $errors['code'] = 'Kods nevar būt tukšs!';
    if (is_null($request->status)) $errors['status'] = 'Jāizvēlas promo koda formāts!';
    if (is_null($request->value)) $errors['value'] = 'Koda vērtība nevar būt tukša!';

    if ($request->end_date === NULL && $request->can_use === '0') {
      $errors[] = 'Ja nav atzīmēts koda beigu datums tad ir jābūt ierakstītam cik reizes ir iespējams izmantot kodu';
    }

    if (!empty($errors)) {
      return redirect()->back()->with('errors', $errors);
    }

    $promo = new Promo;
    $promo->timestamps = false;
    $promo->name = $request->name;
    $promo->code = $request->code;
    $promo->end_date = ($request->end_date === NULL) ? NULL : $request->end_date;
    $promo->status = (string) $request->status;
    $promo->value = $request->value;
    $promo->active = ($request->active === 'on') ? '1' : '0';
    $promo->can_use = ($request->can_use === '0') ? NULL : $request->can_use;
    $promo->used = 0;
    if ($promo->save()) {
      return redirect(route('admin.promo.index'))->with('success', 'Promo kods veiksmīgi izveidots!');
    }
  }

  public function edit($id) {
    $promo = Promo::where('promo_id', $id)->first();

    return view('admin.promo.edit', compact('promo'));
  }

  public function update(Request $request, $id) {
    $promo = Promo::where('promo_id', $id)->first();

    $errors = [];

    if (is_null($request->name)) $errors['name'] = 'Koda nosaukums nevar būt tukšs!';
    if (is_null($request->code)) $errors['code'] = 'Kods nevar būt tukšs!';
    if (is_null($request->status)) $errors['status'] = 'Jāizvēlas promo koda formāts!';
    if (is_null($request->value)) $errors['value'] = 'Koda vērtība nevar būt tukša!';

    if ($request->end_date === NULL && $request->can_use === '0') {
      $errors[] = 'Ja nav atzīmēts koda beigu datums tad ir jābūt ierakstītam cik reizes ir iespējams izmantot kodu';
    }

    if (!empty($errors)) {
      return redirect()->back()->with('errors', $errors);
    }

    $promo->timestamps = false;
    $promo->name = $request->name;
    $promo->code = $request->code;
    $promo->end_date = ($request->end_date === NULL) ? NULL : $request->end_date;
    $promo->status = (string) $request->status;
    $promo->value = $request->value;
    $promo->active = ($request->active === 'on') ? '1' : '0';
    $promo->can_use = ($request->can_use === '0') ? NULL : $request->can_use;
    $promo->used = 0;
    if ($promo->save()) {
      return redirect(route('admin.promo.index'))->with('success', 'Promo kods veiksmīgi labots!');
    }
  }

  public function destroy($id) {
    $promo = Promo::where('promo_id', $id)->first();

    if ($promo) {
      if ($promo->delete()) {
        return redirect(route('admin.promo.index'))->with('success', 'Promo kods veiksmīgi dzēsts!');
      }
    }
  }

  public function checkPromos() {
    $promos = Promo::where('active', '1')->get();

    foreach ($promos as $promo) {
      if (date('d-m-Y', strtotime($promo->end_date)) < date('d-m-Y')) {
        $promo->active = '0';
        $promo->save();
      }
    }

    return 'Promo kodi atjaunoti';
  }

  public function checkPromo(Request $request) {
    $promo = Promo::where('code', $request->promo)->where('active', '1')->first();

    $data = [];

    $total_price = (int) substr(Cart::subTotal(), 0, -3);

    if (!$promo) {
      $data['success'] = 'false';
      $data['discount_price'] = 'false';
      return json_encode($data);
    }
    if (!is_null($promo->can_use)) {
      if ($promo->used >= $promo->can_use) {
        $data['success'] = 'false';
        $data['discount_price'] = 'false';
        return json_encode($data);
      }
    }
    if ($promo->status === '1') {
      $item_sum = $total_price * (1 - $promo->value / 100);
    } else {
      $item_sum = $total_price - $promo->value;
    }
    $item_sum = round($item_sum);

    $data['success'] = 'true';
    $data['discount_price'] = $total_price - $item_sum;
    return json_encode($data);

  }

}