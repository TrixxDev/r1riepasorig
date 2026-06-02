<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\SendSmsVerificationCode;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validate = Validator::make($data, [
            'name' => ['required', 'string', 'max:40'],
            'surname' => ['required', 'string', 'max:40'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users'],
            'phone' => ['required', 'string', 'max:15'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ],[
            'name.required' => 'Lūdzu ievadiet jūsu vārdu!',
            'name.max' => 'Vārds nedrīkst būt garāks par :max rakstzīmēm!',

            'surname.required' => 'Lūdzu ievadiet jūsu uzvārdu!',
            'surname.max' => 'Uzvārds nedrīkst būt garāks par :max rakstzīmēm!',

            'email.required' => 'Lūdzu ievadiet jūsu e-pastu!',
            'email.email' => 'Lūdzu ievadiet korektu e-pastu!',
            'email.max' => 'E-pasts nedrīkst būt garāks par :max rakstzīmēm!',
            'email.unique' => 'Lietotājs ar šādu e-pastu jau ir reģistrēts!',

            'phone.required' => 'Lūdzu ievadiet jūsu kontakttālruni!',
            'phone.max' => 'Kontakttālrunis nevar būt garāks par :max cipariem!',

            'password.required' => 'Lūdzu ievadiet paroli!',
            'password.min' => 'Minimālais paroles garums :min rakstzīmes!',
            'password.confirmed' => 'Paroles nesakrīt!',
        ]);

        return $validate;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {

        $role = Role::findById(config('app.def_user_group', 1));

        $user = User::create([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'phone_number' => $data['phone'],
            'password' => Hash::make($data['password']),
            'sms_verification_code' => rand(100000, 999999), // Generate a random 6-digit code
        ]);
        $user->assignRole($role);

        $user->notify(new SendSmsVerificationCode($user->sms_verification_code)); // Send SMS with the code

        return $user;
    }
}
