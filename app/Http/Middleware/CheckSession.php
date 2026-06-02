<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Auth;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Gloudemans\Shoppingcart\Cart;

class CheckSession
{

  public function handle($request, Closure $next)
  {

    $response = $next($request);

//    if (\Cart::countItems() == 0) {
//      if (Auth::check()) {
//        Order::where('userId', Auth::user()->id)->where('status', 1)->delete();
////        dd(123);
//      } else {
//        Order::where('userIp', user_ip)->where('status', 1)->delete();
////        dd(321);
//      }
//    }

//    $session_id = Session::getId();
//
//    if (Auth::check()) {
//      $userId = Auth::user()->id;
//    }
//
//    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
//      $ip = $_SERVER['HTTP_CLIENT_IP'];
//    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
//    } else {
//      $ip = $_SERVER['REMOTE_ADDR'];
//    }
//
//    if ($ip) {
//      $order = Order::where('userIp', $ip)->where('status', 1)->first();
//      if (!$order) {
//        if (isset($userId) && $userId) {
//          $order = Order::where('userId', $userId)->where('status', 1)->first();
//        }
//      }
//    }
//
//    if (\Cart::instance($session_id)->content()->count() <= 0) {
//      if ($order) {
//        \Cart::erase();
//        $order->delete();
//        return Redirect::home();
//      } else {
//        \Cart::erase();
//      }
//      Session::setId(session_create_id());
//    } else {
//      if ($order) {
//        $now = strtotime(Carbon::now());
//        $orderTime = strtotime(Carbon::parse($order->timeRemaining));
//        if ($now >= $orderTime) {
//          \Cart::erase();
//          $order->delete();
//          Session::setId(session_create_id());
//          return Redirect::home();
//        } else {
//          $order->timeRemaining = Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s');
//          $order->save();
//        }
//      }
//    }

    return $response;

  }
}
