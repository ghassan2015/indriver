<?php namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Http\Controllers\Controller;

use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Setting;
use Storage;
use Exception;
use Carbon\Carbon;

use App\Provider;

use App\ProviderProfile;
use App\UserRequests;
use App\ProviderService;
use App\Fleet;
use App\RequestFilter;
use App\Document;
use App\Http\Controllers\SendPushNotification;
use App\Http\Controllers\ProviderResources\DocumentController;
use Illuminate\Support\Facades\Hash;
use Taxi\Encrypter\Encrypter;
use Taxi\Twilio\Service;

class ProfileController extends Controller
{
	
	private $encrypter;
	
	/**
     * Create a new user instance.
     *
     * @return void
     */

    public function __construct(Encrypter $encrypter)
    {
        $this->middleware('provider.api', ['except' => ['show', 'store', 'available', 'location_edit',
                                                      'location_update','stripe', 'sendMobileVerificationCode',
                                                      'verifyMobileVerificationCode', 'isMobileVerified']]);
	    $this->encrypter = $encrypter;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            Auth::user()->service = ProviderService::where('provider_id',Auth::user()->id)
                                            ->with('service_type')
                                            ->first();
            Auth::user()->fleet = Fleet::find(Auth::user()->fleet);
            Auth::user()->currency = Setting::get('currency', '$');
            Auth::user()->sos = Setting::get('sos_number', '911');
            Auth::user()->measurement = Setting::get('distance', 'Kms');
            Auth::user()->profile = ProviderProfile::where('provider_id',Auth::user()->id)
                                            ->first();
            Auth::user()->cash =(int)Setting::get('CASH', 1);
            Auth::user()->card =(int)Setting::get('CARD', 0);
            Auth::user()->stripe_secret_key = Setting::get('stripe_secret_key', '');
            Auth::user()->stripe_publishable_key = Setting::get('stripe_publishable_key', '');
            return Auth::user();

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'mobile' => 'required',
                'avatar' => 'mimes:jpeg,bmp,png',
                'language' => 'max:255',
                'address' => 'max:255',
                'address_secondary' => 'max:255',
                'city' => 'max:255',
                'country' => 'max:255',
                'postal_code' => 'max:255',
            ]);

        try {

            $Provider = Auth::user();

            if($request->has('first_name')) 
                $Provider->first_name = $request->first_name;

            if($request->has('last_name')) 
                $Provider->last_name = $request->last_name;

            if ($request->has('mobile'))
                $Provider->mobile = $request->mobile;

            if ($request->hasFile('avatar')) {
                Storage::delete($Provider->avatar);
                $Provider->avatar = $request->avatar->store('provider/profile');
            }

            if($request->has('service_type')) {
                if($Provider->service) {
                    if($Provider->service->service_type_id != $request->service_type) {
                        $Provider->status = 'banned';
                    }
                    //$ProviderService = ProviderService::where('provider_id',Auth::user()->id);
                    $Provider->service->service_type_id = $request->service_type;
                    $Provider->service->service_number = $request->service_number;
                    $Provider->service->service_model = $request->service_model;
                    $Provider->service->save();

                } else {
                    ProviderService::create([
                        'provider_id' => $Provider->id,
                        'service_type_id' => $request->service_type,
                        'service_number' => $request->service_number,
                        'service_model' => $request->service_model,
                    ]);
                    $Provider->status = 'banned';
                }
            }

            if($Provider->profile) {
                $Provider->profile->update([
                        'language' => $request->language ? : $Provider->profile->language,
                        'address' => $request->address ? : $Provider->profile->address,
                        'address_secondary' => $request->address_secondary ? : $Provider->profile->address_secondary,
                        'city' => $request->city ? : $Provider->profile->city,
                        'country' => $request->country ? : $Provider->profile->country,
                        'postal_code' => $request->postal_code ? : $Provider->profile->postal_code,
                    ]);
            } else {
                ProviderProfile::create([
                        'provider_id' => $Provider->id,
                        'language' => $request->language,
                        'address' => $request->address,
                        'address_secondary' => $request->address_secondary,
                        'city' => $request->city,
                        'country' => $request->country,
                        'postal_code' => $request->postal_code,
                    ]);
            }


            $Provider->save();

            return redirect(route('provider.profile.index'));
        }

        catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.provider.provider_not_found')], 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return view('provider.profile.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'mobile' => 'required',
                'avatar' => 'mimes:jpeg,bmp,png',
                'language' => 'max:255',
                'address' => 'max:255',
                'address_secondary' => 'max:255',
                'city' => 'max:255',
                'country' => 'max:255',
                'postal_code' => 'max:255',
            ]);

        try {

            $Provider = Auth::user();

            if($request->has('first_name')) 
                $Provider->first_name = $request->first_name;

            if($request->has('last_name')) 
                $Provider->last_name = $request->last_name;

            if ($request->has('mobile'))
                $Provider->mobile = $request->mobile;

            if ($request->hasFile('avatar')) {
                Storage::delete($Provider->avatar);
                $Provider->avatar = $request->avatar->store('provider/profile');
            }

            if($Provider->profile) {
                $Provider->profile->update([
                        'language' => $request->language ? : $Provider->profile->language,
                        'address' => $request->address ? : $Provider->profile->address,
                        'address_secondary' => $request->address_secondary ? : $Provider->profile->address_secondary,
                        'city' => $request->city ? : $Provider->profile->city,
                        'country' => $request->country ? : $Provider->profile->country,
                        'postal_code' => $request->postal_code ? : $Provider->profile->postal_code,
                    ]);
            } else {
                ProviderProfile::create([
                        'provider_id' => $Provider->id,
                        'language' => $request->language,
                        'address' => $request->address,
                        'address_secondary' => $request->address_secondary,
                        'city' => $request->city,
                        'country' => $request->country,
                        'postal_code' => $request->postal_code,
                    ]);
            }


            $Provider->save();

            return $Provider;
        }

        catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.provider.provider_not_found')], 404);
        }
    }

    /**
     * Update latitude and longitude of the user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function location(Request $request)
    {
        $this->validate($request, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

        if($Provider = Auth::user()){

            $Provider->latitude = $request->latitude;
            $Provider->longitude = $request->longitude;
            $Provider->save();

            return response()->json(['message' => trans('api.provider.location_updated')]);

        } else {
            return response()->json(['error' => trans('api.provider.provider_not_found')]);
        }
    }

    public function update_language(Request $request)
    {
        $this->validate($request, [
               'language' => 'required',
            ]);

        try {

            $Provider = Auth::user();

            if($Provider->profile) {
                $Provider->profile->update([
                        'language' => $request->language ? : $Provider->profile->language
                    ]);
            } else {
                ProviderProfile::create([
                        'provider_id' => $Provider->id,
                        'language' => $request->language,
                    ]);
            }

            return response()->json(['message' => trans('api.provider.language_updated'),'language'=>$request->language]);
        }

        catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.provider.provider_not_found')], 404);
        }
    }

    /**
     * Toggle service availability of the provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function available(Request $request)
    {

        // $requests = UserRequests::where('status', 'SEARCHING')->get();

        // var_dump($requests[0]->id);
        // die('die');


    	Log::info('ProfileControllerAvailable: with request params', $request->all());
        $this->validate($request, [
                'service_status' => 'required|in:active,offline',
            ]);

        $Provider = Auth::user();
        
        if($Provider->service) {
            $providerId = $Provider->id;
            $OfflineOpenRequest = RequestFilter::with(['request.provider','request'])
                ->where('provider_id', $providerId)
                ->whereHas('request', function($query) use ($providerId){
                    $query->where('status','SEARCHING');
                    $query->where('current_provider_id','<>',$providerId);
                    $query->orWhereNull('current_provider_id');
                    })->pluck('id');

            if(count($OfflineOpenRequest)>0) {
	            Log::info('ProfileControllerAvailable: delete provider request from RequestFilter');
	            RequestFilter::whereIn('id',$OfflineOpenRequest)->delete();
            }
	          Log::info('ProfileControllerAvailable: Update provider service status');
            $Provider->service->update(['status' => $request->service_status]);
        } else {
            return response()->json(['error' => trans('api.provider.not_approved')]);
        }

        Log::info('Start Adding');
        $requests = UserRequests::where('status', 'SEARCHING')->get();

        Log::info('Adding New Providers');
        foreach($requests as $request){
            Log::info('Adding New Provider to request#'.$request->id);
            $Filter = new RequestFilter;
            $Filter->request_id = $request->id;
            $Filter->provider_id = $Provider->id;
            $Filter->save();
        }
        Log::info('End Adding');

        return $Provider;
    }

    /**
     * Update password of the provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function password(Request $request)
    {
        $this->validate($request, [
                'password' => 'required|confirmed',
                'password_old' => 'required',
            ]);

        $Provider = Auth::user();

        if(password_verify($request->password_old, $Provider->password))
        {
            $Provider->password = bcrypt($request->password);
            $Provider->save();

            return response()->json(['message' => trans('api.provider.password_updated')]);
        } else {
            return response()->json(['error' => trans('api.provider.change_password')], 422);
        }
    }

    /**
     * Show providers daily target.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function target(Request $request)
    {
        try {
            
            $Rides = UserRequests::where('provider_id', Auth::user()->id)
                    ->where('status', 'COMPLETED')
                    ->where('created_at', '>=', Carbon::today())
                    ->with('payment', 'service_type')
                    ->get();

            return response()->json([
                    'rides' => $Rides,
                    'rides_count' => $Rides->count(),
                    'target' => Setting::get('daily_target','0')
                ]);

        } catch(Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    public function chatPush(Request $request){

        $this->validate($request,[
                'user_id' => 'required|numeric',
                'message' => 'required',
            ]);       

        try{

            $user_id=$request->user_id;
            $message=$request->message;
            $type = 'chat';
           
            // $message = \PushNotification::Message($message,array(
            // 'badge' => 1,
            // 'sound' => 'default',
            // 'custom' => array('type' => 'chat')
            // ));
           
            (new SendPushNotification)->sendPushToProvider($user_id, $message, $type);          

            return response()->json(['success' => 'true']);

        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    //provider document list
    public function documents(Request $request)
    {
        try {

            $provider_id=Auth::user()->id;

            $Documents=Document::select('id','name','type','status', 'is_mandatory')
                        ->with(['providerdocuments' => function ($query) use ($provider_id) {
                        $query->where('provider_id', $provider_id);
                        }])->get();
						Log::info('Provider Documents Response: ', $Documents->toArray());
            return response()->json(['documents' => $Documents]);

        } catch(Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    //provider document list
    public function documentstore(Request $request)
    {
        
        $this->validate($request, [
            
            'document.*' => 'mimes:jpg,jpeg,png|max:20000'
        ]);
        try {         


            if ($request->hasFile('document')) {
                
                foreach($request->file('document') as $ikey => $image)
                {
                    $ids=$request->input('id');
                    $doc_id=$ids[$ikey];
                    
                    $provider_id=Auth::user()->id;
                    (new DocumentController)->documentupdate($image, $doc_id,$provider_id);                    
                }                
                
                // if(Setting::get('CARD', 0) == 1) {
                //     Provider::where('id', Auth::user()->id)->where('status','document')->update(['status'=>'card']);
                // }
                // else{
                    if(Setting::get('demo_mode', 0) == 1) {
                        Provider::where('id', Auth::user()->id)->where('status','document')->update(['status'=>'approved']);
                    }
                    else{                        
                        Provider::where('id', Auth::user()->id)->where('status','document')->update(['status'=>'onboarding']);
                    }    
                // }    

                return $this->documents($request); 
            }

        } catch(Exception $e) {
           
            return response()->json(['error' => trans('api.something_went_wrong')], 422);
        }
    }

    public function stripe(Request $request)
    {
        if(isset($request->code)){
            $post = [
                'client_secret' => Setting::get('stripe_secret_key'),
                'code' => $request->code,
                'grant_type' => 'authorization_code'
            ];
            $curl = curl_init("https://connect.stripe.com/oauth/token");
            curl_setopt($curl, CURLOPT_HEADER, 0); 
            curl_setopt($curl, CURLOPT_POST, 1); 
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
            $result = curl_exec($curl); 
            $curl_error = curl_error($curl);
            curl_close($curl);
            $stripe = json_decode($result);

            if($stripe->stripe_user_id){
                $provider = Provider::where('id', Auth::user()->id)->first();
                $provider->stripe_acc_id = $stripe->stripe_user_id;
                $provider->save();

                if($request->ajax()){
                    return response()->json(['message' => 'Your stripe account connected successfully']);
                }else{
                    return redirect('/provider')->with('flash_success', 'Your stripe account connected successfully');
                }
            }else{
                if($request->ajax()){
                    return response()->json(['message' => $curl_error]);
                }else{
                    return redirect('/provider')->with('flash_error', $curl_error);
                }
            }

        }else{
            if($request->ajax()){
                return response()->json(['message' => $request->error_description]);
            }else{
                return redirect('/provider')->with('flash_error', $request->error_description);
            }
        }
    }
	
	public function isMobileVerified (Request $request) {
		
		$validator = Validator::make( $request->all(), [
			'device_type'  => 'required|in:android,ios',
			'device_token' => 'required',
			'accessToken'  => 'required',
			'device_id'    => 'required',
			'login_by'       => 'required|in:facebook,google',
		] );
		if ( $validator->fails() ) {
			return response()->json( $this->encrypter->encrypt( [ 'status'  => false,
			                           'message' => $validator->messages()
			                                                  ->all()
			                         ] ));
		}
		try {
			$socialite = Socialite::driver( $request['login_by'] )->stateless();
			$socialUser = $socialite->userFromToken( $request[ 'accessToken' ] );
			$user = Provider::where( 'social_unique_id', $socialUser->id );
			if ( $socialUser->email != "" ) {
				$user->orWhere( 'email', $socialUser->email );
			}
			$authUser = $user->first();
			if ( $authUser ) {
				return response()->json($this->encrypter->encrypt( [
					                         "status"     => true,
					                         "isVerified" => $authUser->is_mobile_verified,
				                         ] ));
			} else {
				return response()->json($this->encrypter->encrypt( [ 'status' => false, 'message' => "Invalid credentials!" ] ));
			}
		} catch ( Exception $e ) {
			return response()->json($this->encrypter->encrypt( [ 'status' => false, 'message' => trans( 'api.something_went_wrong' ) ] ));
		}
	}
	
	public function sendMobileVerificationCode(Request $request) {
		Log::info("sendMobileVerificationCode in start.");
		$validator = Validator::make( $request->all(), [
			'mobile_no'     => 'required',
		] );
		$isMobileExists = Provider::where('mobile', $request['mobile_no'])->first();
		if(isset($isMobileExists)){
			return response()->json( $this->encrypter->encrypt( [
				                         "status"  => true,
				                         "isVerified" => true,
				                         "message" => 'Mobile no. already verified.',
			                         ] ));
		}
		Log::info("sendMobileVerificationCode with params: ", $request->all());
		if ( $validator->fails() ) {
			return response()->json( $this->encrypter->encrypt( [ 'status'  => false,
			                           "isVerified" => false,
			                           'message' => $validator->messages()->all()
			                         ]), 422);
		}
		try {
			$twilioService = app(Service::class);
			Log::info("sendMobileVerificationCode validation clear in try. ");
			$verification = $twilioService->startVerification($request['mobile_no'], 'sms');
			Log::info("sendMobileVerificationCode: after send sms.");
			if($verification->isValid()) {
				return response()->json( $this->encrypter->encrypt( [
					                         "status"  => true,
					                         "isVerified" => false,
					                         "message" => trans( 'api.user.mobile_verification_code_sent' ),
				                         ]) );
			}else{
				return response()->json( $this->encrypter->encrypt( [
					                         "status"  => false,
					                         "isVerified" => false,
					                         "message" => $verification->getErrors()[0],
				                         ]) );
			}
		}catch (Exception $e){
			Log::info("sendMobileVerificationCode Twilio fail response: " . $e->getMessage());
			return response()->json( $this->encrypter->encrypt( [ 'status' => false,
			                           "isVerified" => false,
			                           'message' => trans( 'api.something_went_wrong' ) ]));
			
		}
	}
	
	public function verifyMobileVerificationCode(Request $request) {
		$validator = Validator::make( $request->all(), [
			'mobile_no'    => 'required',
			'code'         => 'required',
		] );
		if ( $validator->fails() ) {
			return response()->json($this->encrypter->encrypt( [ 'status'  => false,
			                           'message' => $validator->messages()->all()
			                         ]), 422);
		}
		try {
			$twilioService = app(Service::class);
			$verification = $twilioService->checkVerification($request['mobile_no'], $request['code']);
			if($verification->isValid()) {
				return response()->json($this->encrypter->encrypt( [
					                         "status"  => true,
					                         "message"    => 'Your mobile verified successfully.',
				                         ]) );
			}else{
				return response()->json($this->encrypter->encrypt( [
					                         "status"  => false,
					                         "message" => $verification->getErrors()[0],
				                         ] ));
			}
		}catch (Exception $e){
			Log::info("verifyMobileVerificationCode catch : " . $e->getMessage());
			return response()->json($this->encrypter->encrypt( [ 'status' => false,
			                           'message' => trans( 'api.something_went_wrong' ) ]));
			
		}
	}

    public function destroy(Request $request, $id){
        $this->validate($request, [
            'password' => 'required',
            'reason' => 'required',
        ]);

        $provider = Provider::findOrFail($id);
        if(Hash::check($request['password'], $provider->password)){
            
        $provider->email = "$provider->email-DeletedFromProviderApp";
        $provider->mobile = "$provider->mobile-DeletedFromProviderApp";
        $provider->password = "$provider->password-DeletedFromProviderApp";

        $provider->save();
        return response()->json(["status" => true, 
                            "message" => "$provider->first_name deleted successfully."], 200);
        }else{
            return response()->json(["status" => false, 
                            "message" => "Your password not matched"], 422);
        }

    }

}
