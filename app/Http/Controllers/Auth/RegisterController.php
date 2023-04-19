<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\ServiceType;
use App\Helpers\Helper;


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
    protected $redirectTo = '/dashboard';

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
        return Validator::make($data, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'phone_number' => 'required',
            //'country_code' => 'required|regex:/^([+])d{1,3}/',
            'country_code' => 'required|in:+1,+92',
            'email' => 'required|email|max:60|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        if(!empty($data['gender']))
            $gender=$data['gender'];
        else
            $gender='MALE';
        
        $User = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'gender' => $gender,
            'mobile' => $data['country_code'].$data['phone_number'],
            'password' => bcrypt($data['password']),
            'payment_mode' => 'CASH',
            'hit_from' => 'registerController.create'
	
	    ]);

        // send welcome email here
        Helper::site_registermail($User);

        return $User;
    }

    
    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
    	//dd('i m here');
        return view('user.auth.register');
    }

    public function ride()
    {
        $services = ServiceType::get();
        return view('ride' , compact('services'));
    }
}
