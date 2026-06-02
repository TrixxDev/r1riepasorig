<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class SessionExpired {
  protected $session;

  public function __construct(Store $session){
    $this->session = $session;
  }

  public function handle($request, Closure $next){
//    $isLoggedIn = Auth::check();
//    $this->session->put('lastActivityTime', time());
//    if(! session('lastActivityTime')) {
//      $this->session->put('lastActivityTime', time());
//    } elseif(time() - $this->session->get('lastActivityTime') > env('session_lifetime')){
//      $this->session->forget('lastActivityTime');
//      $cookie = cookie('intend', $isLoggedIn ? url()->current() : session('url.intended'));
//      auth()->logout();
//    }
//    $isLoggedIn ? $this->session->put('lastActivityTime', time()) : $this->session->forget('lastActivityTime');
    return $next($request);
  }
}
