<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

  public $status_enum = [
    2 => 'Iesniegts',
    6 => 'Atcelts',
    7 => 'Atcelts',
    10 => 'Atcelts',
    9 => 'Apstrādē',
    5 => 'Pabeigts'
  ];

  public $pay_enum = [
    0 => '',
    1 => 'Apmaksa saņemšanas brīdī',
    2 => 'Bankas pārskaitījums',
    3 => 'Tiešsaistes apmaksa'
  ];

  public function my_account()
  {
    return view('auth.profile');
  }

  public function identity()
  {
    return view('auth.sub.identity');
  }

  public function identity_update(Request $request)
  {
    $userId = Auth::user()->id;

    $validate = Validator::make($request->input(), [
      'firstname' => ['required', 'string', 'max:40'],
      'lastname' => ['required', 'string', 'max:40'],
      'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email,' . $userId],
      'phone_number' => ['required', 'string', 'max:15'],
      'password' => ['nullable', 'required_with:new_password'],
      'new_password' => ['nullable', 'string', 'min:8', 'required_with:password'],
    ],[
      'firstname.required' => 'Lūdzu ievadiet jūsu vārdu!',
      'firstname.max' => 'Vārds nedrīkst būt garāks par :max rakstzīmēm!',
      'lastname.required' => 'Lūdzu ievadiet jūsu uzvārdu!',
      'lastname.max' => 'Uzvārds nedrīkst būt garāks par :max rakstzīmēm!',
      'email.required' => 'Lūdzu ievadiet jūsu e-pastu!',
      'email.email' => 'Lūdzu ievadiet korektu e-pastu!',
      'email.max' => 'E-pasts nedrīkst būt garāks par :max rakstzīmēm!',
      'phone_number.required' => 'Lūdzu ievadiet jūsu kontakttālruni!',
      'phone_number.max' => 'Kontakttālrunis nevar būt garāks par :max cipariem!',
      'password.required_with' => 'Lūdzu ievadiet jūsu pašreizējo paroli!',
      'new_password.required_with' => 'Lūdzu ievadiet jauno paroli!',
      'new_password.min' => 'Minimālais paroles garums :min rakstzīmes!',
    ]);

    $validate->validate();

    $user = Auth::user(); // Fetch authenticated user

    // Check if the current password is provided and is correct
    if ($request->filled('password') && $request->filled('new_password')) {
      if (!Hash::check($request->password, $user->password)) {
        return redirect()->back()->withErrors(['password' => 'Pašreizējā parole ir nepareiza!']);
      }

      // Update password if the current password is correct
      $user->password = bcrypt($request->new_password);
    }

    // Update other user details
    $user->name = $request->firstname;
    $user->surname = $request->lastname;
    $user->email = $request->email;
    $user->phone_number = $request->phone_number;

    $user->save();

    return redirect()->back()->with('success', 'Profils veiksmīgi atjaunots!');
  }

  public function address()
  {
    return view('auth.sub.address');
  }

  public function address_update(Request $request)
  {
    $validate = Validator::make($request->input(), [
      'company' => ['nullable', 'string', 'max:100'],
      'vat_number' => ['nullable', 'string', 'max:13'],
      'address' => ['string', 'max:255'],
      'postcode' => ['string', 'regex:/^LV-\d{4}$/'],
      'city' => ['string', 'max:20'],
    ],[
      'company.max' => 'Uzņēmuma nosaukums nedrīkst būt garāks par :max rakstzīmēm!',
      'vat_number.max' => 'PVN numurs nedrīkst būt garāks par :max rakstzīmēm!',
      'address.max' => 'Uzņēmuma adrese nedrīkst būt garāka par :max rakstzīmēm!',
      'postcode.regex' => 'Pasta indeksam jābūt formātā "LV-xxxx", kur "xxxx" ir četri cipari!',
      'city.max' => 'Pilsētas nosaukums nedrīkst būt garāks par :max rakstzīmēm!',
    ]);

    $validate->validate();

    $user = Auth::user(); // Fetch authenticated user

    // Update other user details
    $user->company_name = $request->company;
    $user->company_vat = $request->vat_number;
    $user->company_address = $request->address;
    $user->company_postcode = substr($request->postcode, 3);
    $user->company_city = $request->city;

    $user->save();

    return redirect()->back()->with('success', 'Uzņēmuma informācija veiksmīgi atjaunota!');
  }

  public function history()
  {
    $orders = Order::where('userId', Auth::user()->id)->where('status', '!=', 1)->orderBy('id', 'desc')->get();

    $status_enum = $this->status_enum;
    $pay_enum = $this->pay_enum;



    return view('auth.sub.history.index', compact('orders', 'status_enum', 'pay_enum'));
  }

  public function history_show($id)
  {
    $order = Order::where('id', $id)->first();

    @$userData = json_decode(json_encode(unserialize($order->info)));
    //dd($order);
    if ($userData == false || !isset($userData->items)) return redirect()->back()->with('danger', 'Neizdevās atvērt pasūtījumu. Lūgums sazināties ar mums');

    if (isset($userData->fitting_address)) {
      $receipt = 'Saņemšana veikalā';
      $address = Office::where('office_id', $userData->fitting_address)->first()->shipping;
    }
    if (isset($userData->shipping_city)) {
      $receipt = 'Piegāde';
      if ($userData->shipping_city == 1) {
        $address = 'Rīga, ' . $userData->shipping_address;
      } else {
        $address = $userData->shipping_address;
      }

    }

    $tires = $userData->items;

    $promo = NULL;
    $item_sum = round($order->price);
    if ($order->used_promo != 0) {
      $promo = \App\Models\Promo::where('promo_id', $order->used_promo)->first();
      if ($promo->status === '1') {
        $item_sum = $order->price * (1 - $promo->value / 100);
      } else {
        $item_sum = $order->price - $promo->value;
      }
      $item_sum = round($item_sum);
    }
    if ($order->delivery_price > 0) {
      $item_sum = $item_sum + (int) substr($order->delivery_price, 0, -2);
    } else if ($order->fit_price > 0) {
      $item_sum = $item_sum + (int) substr($order->fit_price, 0, -2);
    }

    $status_enum = $this->status_enum;
    $pay_enum = $this->pay_enum;

    return view('auth.sub.history.show', compact('order', 'userData', 'status_enum', 'pay_enum', 'item_sum', 'receipt', 'address', 'tires', 'promo'));
  }

  public function order_slip()
  {
    return view('auth.sub.order_slip');
  }

}