<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}


//  namespace App\Http\Controllers\Auth;
//
//  use App\Http\Controllers\Controller;
//  use App\Models\User;
//  use App\Notifications\SendSmsVerificationCode;
//  use Illuminate\Http\Request;
//  use Illuminate\Support\Facades\Auth;
//
//  class VerificationController extends Controller
//  {
//    public function showSmsVerificationForm()
//    {
//      return view('auth.verify-sms');
//    }
//
//    public function verifySmsCode(Request $request)
//    {
//      $request->validate(['sms_code' => 'required']);
//
//      $user = Auth::user();
//
//      if ($user->sms_verification_code === $request->sms_code) {
//        $user->sms_verification_code = null; // Clear the code after verification
//        $user->save();
//
//        return redirect()->route('home')->with('status', 'Jūsu numurs veiksmīgi verificējās.');
//      }
//
//      return back()->withErrors(['sms_code' => 'Ievadītais kods ir nepareizs.']);
//    }
//
//    public function resendVerificationCode()
//    {
//      $user = User::where('id', Auth::user()->id)->first();
//      $user->sms_verification_code = rand(100000, 999999);
//      $user->save();
//
//      $user->notify(new SendSmsVerificationCode($user->sms_verification_code)); // Send SMS with the code
//    }
//  }
