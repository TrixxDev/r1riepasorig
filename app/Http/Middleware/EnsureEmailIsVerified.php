<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
  public function handle($request, Closure $next)
  {
//    if (Auth::user() && !is_null(Auth::user()->sms_verification_code) && !$request->is('verify-sms', 'resend-sms', 'logout')) {
//      return redirect()->route('verification.notice');
//    }

//    if (Auth::check() && !Auth::user()->hasVerifiedEmail() && !$request->is('email/*', 'logout')) {
//      $register_date = Carbon::parse(Auth::user()->created_at);
//      $now_date = Carbon::now();
//      if ($now_date->diffInDays($register_date) < 1) {
////        return true;
//      } else {
//        return redirect()->route('verification.notice');
//      }
//    }

    return $next($request);
  }
}