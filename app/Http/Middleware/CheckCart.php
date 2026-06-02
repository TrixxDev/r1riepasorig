<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class CheckCart
{

  public function handle($request, Closure $next)
  {

    $now = date_format(now(), 'Y-m-d H:i:s');

    // Check if the user is logged in
    $userId = Auth::id();
    // Get the current session ID (instead of using a cookie)
    $sessionId = $userId ? null : $request->cookie('persistent_session_id');

    $existingOrder = DB::table('orders_')
      ->where('session_id', $sessionId)
      ->orWhere('user_id', $userId)
      ->where('order_status', 1) // Assuming 'pending' means not completed
      ->first();

    if ($existingOrder) {
      if ($existingOrder->delete_at >= $now) {
        DB::table('orders_')->where('id', $existingOrder->id)->update([
          'delete_at' => date('Y-m-d', strtotime('+1 month')),
          'updated_at' => now(),
        ]);
      } else {
        //$existingOrder->delete();
	DB::table('orders_')->where('id', $existingOrder->id)->delete();
      }
    }
//    $request->merge(['cookie_data' => $cookieValue]);

    return $next($request);

  }
}
