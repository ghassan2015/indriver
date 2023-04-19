<?php namespace App\Http\Controllers\ProviderAuth;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Taxi\Encrypter\Encrypter;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Notifications\ResetPasswordOTP;

use Illuminate\Support\Facades\Auth;
use Config;
use JWTAuth;
use Setting;
use Notification;
use Validator;
use Socialite;
use File; 

use App\Provider;
use App\ProviderDevice;
use App\ProviderService;
use App\RequestFilter;
use App\Helpers\Helper;

class TokenController extends Controller
{
	
	private $encrypter;
	
	public function __construct ( Encrypter $encrypter) {
		$this->encrypter = $encrypter;
	}
	
	public function register(Request $request)
    {
	    $validator = Validator::make($request->all(), [
                'device_id' => 'required',
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:providers',
                'mobile' => 'required',
                'password' => 'required|min:6|confirmed',
            ]);
	    if ( $validator->fails() ) {
		    $response = [ 'status'  => false,
		                  'message' => $validator->messages()->all()
		    ];
		    return response()->json( $this->encrypter->encrypt( $response) , 422);
	    }
        try{

            $Provider = $request->all();
            $Provider['password'] = bcrypt($request->password);
            $Provider['is_mobile_verified'] = 1;
            $Provider = Provider::create($Provider);

            if(Setting::get('demo_mode', 0) == 1) {
                //$Provider->update(['status' => 'approved']);
                ProviderService::create([
                    'provider_id' => $Provider->id,
                    'service_type_id' => '1',
                    'status' => 'active',
                    'service_number' => '4pp03ets',
                    'service_model' => 'Audi R8',
                ]);
            }

            ProviderDevice::create([
                    'provider_id' => $Provider->id,
                    'udid' => $request->device_id,
                    'token' => $request->device_token,
                    'type' => $request->device_type,
                ]);

            $ProviderUser=$this->authenticate($request);
            
            if(Setting::get('send_email', 0) == 1) {
                // send welcome email here
                Helper::site_registermail($Provider);
            }    
						return $ProviderUser;


        } catch (QueryException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($this->encrypter->encrypt( ['error' => trans('api.something_went_wrong')]), 500);
            }
            return abort(500);
        }
        
    }   

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function authenticate(Request $request)
    {
	    $validator = Validator::make($request->all(), [
                'device_id' => 'required',
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);
	
	    if ( $validator->fails() ) {
		    $response = [ 'status'  => false,
		                  'message' => $validator->messages()->all()
		    ];
		    return response()->json( $this->encrypter->encrypt( $response) , 422);
	    }
        $credentials = $request->only('email', 'password');
	
		    if (Auth::attempt($credentials)) {
			    $user = Auth::user();
			    $token = $user->createToken($user->first_name)->accessToken;
		    }else{
		        return response()->json($this->encrypter->encrypt( ['error' => 'Username or password is wrong.']), 500);
		    }

        $provider = Provider::with('service', 'device')->find(Auth::id());
        $provider->access_token = $token;
        $provider->currency = Setting::get('currency', '$');
        $provider->sos = Setting::get('sos_number', '911');
        $provider->measurement = Setting::get('distance', 'Kms');

        if($provider->device) {
            ProviderDevice::where('id',$provider->device->id)->update([
        
                'udid' => $request->device_id,
                'token' => $request->device_token,
                'type' => $request->device_type,
            ]);
            
        } else {
            ProviderDevice::create([
                    'provider_id' => $provider->id,
                    'udid' => $request->device_id,
                    'token' => $request->device_token,
                    'type' => $request->device_type,
                ]);
        }

        return response()->json($this->encrypter->encrypt( $provider) );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function logout(Request $request)
    {
        try {
						$providerId = auth()->id();
            ProviderDevice::where('provider_id', $providerId)->update(['udid'=> '', 'token' => '']);
            ProviderService::where('provider_id',$providerId)->update(['status' => 'offline']);
		        foreach (auth()->user()->tokens as $token){
			        $token->revoke();
		        }
		        $LogoutOpenRequest = RequestFilter::with(['request.provider','request'])
                ->where('provider_id', $providerId)
                ->whereHas('request', function($query) use ($providerId){
                    $query->where('status','SEARCHING');
                    $query->where('current_provider_id','<>',$providerId);
                    $query->orWhereNull('current_provider_id');
                    })->pluck('id');

            if(count($LogoutOpenRequest)>0){
                RequestFilter::whereIn('id',$LogoutOpenRequest)->delete();
            }    
            
            return response()->json(['message' => trans('api.logout_success')]);
        } catch (\Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Forgot Password.
     *
     * @return \Illuminate\Http\Response
     */
    public function forgot_password(Request $request){
	
	    $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:providers,email',
            ]);
	
	    if ( $validator->fails() ) {
		    $response = [ 'status'  => false,
		                  'message' => $validator->messages()->all()
		    ];
		    return response()->json( $this->encrypter->encrypt( $response) , 422);
	    }
        try{  
            
            $provider = Provider::where('email' , $request->email)->first();

            $otp = mt_rand(100000, 999999);

            $provider->otp = $otp;
            $provider->save();

            Notification::send($provider, new ResetPasswordOTP($otp));

            return response()->json($this->encrypter->encrypt([
                'message' => 'OTP sent to your email!',
                //'provider' => [$provider->id, $provider->otp]
            ]));

        }catch(Exception $e){
                return response()->json($this->encrypter->encrypt(['error' => trans('api.something_went_wrong')]), 500);
        }
    }


    /**
     * Reset Password.
     *
     * @return \Illuminate\Http\Response
     */

    public function reset_password(Request $request){
	
	    $validator = Validator::make($request->all(), [
	              'otp' => ['required', 'numeric', 'digits:6',
	                  Rule::exists('providers')->where(function ($query) use($request){
		                  $query->where('email', $request['email'])
		                        ->where('otp', $request['otp']);
	                  })],
                'password' => 'required|confirmed|min:6',
                'email' => 'required|email|exists:providers,email'
            ]);
	    if ( $validator->fails() ) {
		    $response = [ 'status'  => false,
		                  'message' => $validator->messages()->all()
		    ];
		    return response()->json( $this->encrypter->encrypt( $response) , 422);
	    }
        try{

            $provider = Provider::where('email', $request['email'])->firstOrFail();

            $provider->password = bcrypt($request->password);
            $provider->otp = mt_rand(100000, 999999);
            $provider->save();
		        foreach ($provider->tokens as $token){
			        $token->revoke();
		        }
            if($request->ajax()) {
                return response()->json($this->encrypter->encrypt(['message' => trans('api.provider.password_updated')]));
            }

        }catch (Exception $e) {
            if($request->ajax()) {
                return response()->json($this->encrypter->encrypt(['error' => trans('api.something_went_wrong')]), 500);
            }
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function facebookViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken'=>'required',
                //'mobile' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:facebook'
            ]
        );
        
        if($validator->fails()) {
            return response()->json($this->encrypter->encrypt( ['status'=>false,'message' => $validator->messages()->all()]), 422);
        }
        $user = Socialite::driver('facebook')->stateless();
        $FacebookDrive = $user->userFromToken( $request->accessToken);
       
        try{
            $FacebookSql = Provider::where('social_unique_id',$FacebookDrive->id);
            if($FacebookDrive->email !=""){
                $FacebookSql->orWhere('email',$FacebookDrive->email);
            }
            $AuthUser = $FacebookSql->first();
            if($AuthUser){
            	Log::info('Already created');
                $AuthUser->social_unique_id=$FacebookDrive->id;
                $AuthUser->login_by="facebook";
                //$AuthUser->mobile=$request->mobile?:'';
	              $AuthUser->is_mobile_verified = 1 ;
                $AuthUser->save();
            }else{
	            Log::info('New created');
                $AuthUser["email"]=$FacebookDrive->email;
                $name = explode(' ', $FacebookDrive->name, 2);
                $AuthUser["first_name"]=$name[0];
                $AuthUser["last_name"]=isset($name[1]) ? $name[1] : '';
                $AuthUser["password"]=bcrypt($FacebookDrive->id);
                $AuthUser["social_unique_id"]=$FacebookDrive->id;
               // $AuthUser["avatar"]=$FacebookDrive->avatar;
                $fileContents = file_get_contents($FacebookDrive->getAvatar());
                if(config('app.env') !== 'local') {
	                File::put( public_path() . '/storage/provider/profile/' . $FacebookDrive->getId() . ".jpg", $fileContents );
                }

                        //To show picture 
                        $picture = 'provider/profile/' . $FacebookDrive->getId() . ".jpg";
                $AuthUser["avatar"]=$picture;        
                $AuthUser["mobile"]=$request->mobile?:'';
                $AuthUser["is_mobile_verified"] =1;
		            $AuthUser["login_by"]="facebook";
                $AuthUser = Provider::create($AuthUser);

                if(Setting::get('demo_mode', 0) == 1) {
                    //$AuthUser->update(['status' => 'approved']);
                    ProviderService::create([
                        'provider_id' => $AuthUser->id,
                        'service_type_id' => '1',
                        'status' => 'active',
                        'service_number' => '4pp03ets',
                        'service_model' => 'Audi R8',
                    ]);
                }

                if(Setting::get('send_email', 0) == 1) {
                    // send welcome email here
                    Helper::site_registermail($AuthUser);
                }    
            }    
            if($AuthUser){ 
                //$userToken = JWTAuth::fromUser($AuthUser);
	              $userToken = $AuthUser->createToken( $AuthUser->first_name)->accessToken;
	
	              $User = Provider::with('service', 'device')->find($AuthUser->id);
                if($User->device) {
                    ProviderDevice::where('id',$User->device->id)->update([
                        
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                    
                } else {
                    ProviderDevice::create([
                        'provider_id' => $User->id,
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                }
                return response()->json($this->encrypter->encrypt( [
                            "status" => true,
                            "token_type" => "Bearer",
                            "access_token" => $userToken,
                            'currency' => Setting::get('currency', '$'),
                            'measurement' => Setting::get('distance', 'Kms'),
                            'sos' => Setting::get('sos_number', '911')
                        ]));
            }else{
                return response()->json($this->encrypter->encrypt( ['status'=>false,'message' => trans('api.invalid')]));
            }  
        } catch (Exception $e) {
            return response()->json($this->encrypter->encrypt( ['status'=>false,'message' => trans('api.something_went_wrong')]));
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function googleViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken'=>'required',
                //'mobile' => 'required|unique:providers',
                //'isMobileVerified' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:google'
            ]
        );
        if($validator->fails()) {
            return response()->json($this->encrypter->encrypt( ['status'=>false,'message' => $validator->messages()->all()]), 422);
        }
        $user = Socialite::driver('google')->stateless();        

        $GoogleDrive = $user->userFromToken($request->accessToken);        
       
        try{
            $GoogleSql = Provider::where('social_unique_id',$GoogleDrive->id);
            if($GoogleDrive->email !=""){
                $GoogleSql->orWhere('email',$GoogleDrive->email);
            }
            $AuthUser = $GoogleSql->first();
            if($AuthUser){
                $AuthUser->social_unique_id=$GoogleDrive->id;
                //$AuthUser->mobile=$request->mobile?:'';
                $AuthUser->login_by="google";
	              $AuthUser->is_mobile_verified = 1 ;
                $AuthUser->save();
            }else{   
                $AuthUser["email"]=$GoogleDrive->email;
                $name = explode(' ', $GoogleDrive->name, 2);
                $AuthUser["first_name"]=$name[0];
                $AuthUser["last_name"]=isset($name[1]) ? $name[1] : '';
                $AuthUser["password"]=($GoogleDrive->id);
                $AuthUser["social_unique_id"]=$GoogleDrive->id;
                //$AuthUser["avatar"]=$GoogleDrive->avatar;
                $fileContents = file_get_contents($GoogleDrive->getAvatar());
								if(config('app.env') !== 'local') {
									File::put( public_path() . '/storage/provider/profile/' . $GoogleDrive->getId() . ".jpg", $fileContents );
								}
                        //To show picture
                        $picture = 'provider/profile/' . $GoogleDrive->getId() . ".jpg";
                $AuthUser["avatar"]=$picture;   
                $AuthUser["mobile"]=$request->mobile?:''; 
                $AuthUser["login_by"]="google";
	              $AuthUser["is_mobile_verified"] = 1;
                $AuthUser = Provider::create($AuthUser);

                if(Setting::get('demo_mode', 0) == 1) {
                    //$AuthUser->update(['status' => 'approved']);
                    ProviderService::create([
                        'provider_id' => $AuthUser->id,
                        'service_type_id' => '1',
                        'status' => 'active',
                        'service_number' => '4pp03ets',
                        'service_model' => 'Audi R8',
                    ]);
                }
                if(Setting::get('send_email', 0) == 1) {
                    // send welcome email here
                    Helper::site_registermail($AuthUser);
                }    
            }    
            if($AuthUser){
                //$userToken = JWTAuth::fromUser($AuthUser);
                $userToken = $AuthUser->createToken( $AuthUser->first_name)->accessToken;
                $User = Provider::with('service', 'device')->find($AuthUser->id);
                if($User->device) {
                    ProviderDevice::where('id',$User->device->id)->update([
                        
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                    
                } else {
                    ProviderDevice::create([
                        'provider_id' => $User->id,
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                }
                return response()->json($this->encrypter->encrypt([
                            "status" => true,
                            "token_type" => "Bearer",
                            "access_token" => $userToken,
                            'currency' => Setting::get('currency', '$'),
                            'measurement' => Setting::get('distance', 'Kms'),
                            'sos' => Setting::get('sos_number', '911')
                        ]));
            }else{
                return response()->json($this->encrypter->encrypt(['status'=>false,'message' => trans('api.invalid')]));
            }  
        } catch (Exception $e) {
            return response()->json($this->encrypter->encrypt(['status'=>false,'message' => trans('api.something_went_wrong')]));
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function refresh_token(Request $request)
    {

        $token = JWTAuth::getToken();
        
        try {
            if (!$newToken = JWTAuth::refresh($token)) {
                return response()->json($this->encrypter->encrypt(['error' => trans('api.unauthenticated')]), 401);
            }
        } catch (JWTException $e) {
            return response()->json($this->encrypter->encrypt(['error' => $e->getMessage()]), 500);
        }

        $user = JWTAuth::toUser($newToken);

        $Provider = Provider::with('service', 'device')->find($user->id);

        $Provider->access_token = $newToken;

        $Provider->currency = Setting::get('currency', '$');
        $Provider->sos = Setting::get('sos_number', '911');
        $Provider->measurement = Setting::get('distance', 'Kms');

        return response()->json($this->encrypter->encrypt($Provider));
        
    }

    /**
     * Show the email availability.
     *
     * @return \Illuminate\Http\Response
     */

    public function verify(Request $request)
    {
	    $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255|unique:providers',
            ]);
	
	    if ( $validator->fails() ) {
		    $response = [ 'status'  => false,
		                  'message' => $validator->messages()->all()
		    ];
		    return response()->json( $this->encrypter->encrypt( $response) , 422);
	    }
        try{
            
            return response()->json($this->encrypter->encrypt( ['message' => trans('api.email_available')]));

        } catch (Exception $e) {
             return response()->json($this->encrypter->encrypt( ['error' => trans('api.something_went_wrong')]), 500);
        }
    }
}
