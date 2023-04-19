<?php namespace App\Http\Controllers;

use Edujugon\PushNotification\PushNotification;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Facades\Socialite;
use DB;
use Illuminate\Support\Facades\Log;
use Auth;
use Hash;
use Route;
use Storage;
use Setting;
use Exception;
use Taxi\Encrypter\Encrypter;
use Taxi\Twilio\Service;
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\SendPushNotification;
use App\Notifications\ResetPasswordOTP;
use App\Helpers\Helper;

use App\Card;
use App\User;
use App\Work;
use App\Provider;
use App\Settings;
use App\Promocode;
use App\ServiceType;
use App\UserRequests;
use App\RequestFilter;
use App\PromocodeUsage;
use App\WalletPassbook;
use App\UserWallet;
use App\PromocodePassbook;
use App\ProviderService;
use App\UserRequestRating;
use App\UserRequestPayment;

use App\Corporate;
use App\Http\Controllers\ProviderResources\TripController;

use App\Services\ServiceTypes;
use App\CorUser;
use App\CorporateUsers;
use Illuminate\Support\Facades\Hash as FacadesHash;

class UserApiController extends Controller
{
	
	private $encrypter;
	
	public function __construct ( Encrypter $encrypter) {
		//Log::info('User api controller constructor');
		$this->encrypter = $encrypter;
	}
	
	/**  Check Email/Mobile Availablity Of a User  **/
	
	public function verify(Request $request)
	{
		$this->validate($request, [
			'email' => 'required|email|unique:users',
		
		]);
		
		try {
			
			return response()->json(['message' => trans('api.email_available')]);
			
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}
	
	public function checkUserEmail(Request $request)
	{
		$this->validate($request, [
			'email' => 'required|email',
		]);
		
		try {
			
			$email = $request->email;
			
			$results = User::where('email', $email)->first();
			
			if (empty($results))
				return response()->json(['message' => trans('api.email_available'), 'is_available' => true]);
			else
				return response()->json(['message' => trans('api.email_not_available'), 'is_available' => false]);
			
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}
	
	public function login(Request $request)
	{
		$tokenRequest = $request->create('/oauth/token', 'POST', $request->all());
		$request->request->add([
			                       "client_id" => $request->client_id,
			                       "client_secret" => $request->client_secret,
			                       "grant_type" => 'password',
			                       "code" => '*',
		                       ]);
		$response = Route::dispatch($tokenRequest);
		$json = (array)json_decode($response->getContent());
				
		if (!empty($json['error'])) {
			$json['error'] = $json['message'];
		}
		
		// $json['status'] = true;
		$response->setContent(json_encode($json));
		
		User::where('email', $request->username)->update(['device_token' => $request->device_token,
		                                                            'device_id' => $request->device_id,
		                                                            'device_type' => $request->device_type]);
		//return $response;
		$data = (array) json_decode( $response->getContent()) ;
		return response()->json($data, $response->getStatusCode());
		// return  $this->encrypter->encrypt( $data );
	}
	
	public function signup(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'social_unique_id' => ['required_if:login_by,facebook,google', 'unique:users'],
			'device_type' => 'required|in:android,ios',
			'device_token' => 'required',
			'device_id' => 'required',
			'login_by' => 'required|in:manual,facebook,google',
			'first_name' => 'required|max:255',
			'last_name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users',
			'mobile' => 'required',
			'password' => 'required|min:6',
		]);
		if ( $validator->fails() ) {
			$response = [ 'status'  => false,
			              'message' => $validator->messages()->all()
			];
			return response()->json( $this->encrypter->encrypt( $response) , 422);
			//return response()->json(  $response, 422);
		}
		$User = $request->all();
		$User['payment_mode'] = 'CASH';
		$User['password'] = bcrypt($request->password);
		$User['is_mobile_verified '] = 1;
		$User['hit_from'] = 'UserApiController.signup';
		$User = User::create($User);
		
		$User = Auth::loginUsingId($User->id);
		$UserToken = $User->createToken('AutoLogin');
		$User['access_token'] = $UserToken->accessToken;
		$User['currency'] = Setting::get('currency');
		$User['sos'] = Setting::get('sos_number', '911');
		$User['app_contact'] = Setting::get('app_contact', '5777');
		$User['measurement'] = Setting::get('distance', 'Kms');
		
		if (Setting::get('send_email', 0) == 1) {
			// send welcome email here
			Helper::site_registermail($User);
		}
		
		return  $this->encrypter->encrypt(  $User);
		
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function logout(Request $request)
	{
		try {
			$user = User::where('id', $request->id)
			            ->update(['device_id' => '', 'device_token' => '']);
			//foreach ($user->tokens as $token){
			//	$token->revoke();
			//}
			return response()->json(['message' => trans('api.logout_success')]);
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}
	
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function change_password(Request $request)
	{
		
		$this->validate($request, [
			'password' => 'required|confirmed|min:6',
			'old_password' => 'required',
		]);
		
		$User = Auth::user();
		
		if (Hash::check($request->old_password, $User->password)) {
			$User->password = bcrypt($request->password);
			$User->save();
			
			if ($request->ajax()) {
				return response()->json(['message' => trans('api.user.password_updated')]);
			} else {
				return back()->with('flash_success', trans('api.user.password_updated'));
			}
			
		} else {
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.user.incorrect_old_password')], 422);
			} else {
				return back()->with('flash_error', trans('api.user.incorrect_old_password'));
			}
		}
		
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function update_location(Request $request)
	{
		
		$this->validate($request, [
			'latitude' => 'required|numeric',
			'longitude' => 'required|numeric',
		]);
		
		if ($user = User::find(Auth::user()->id)) {
			
			$user->latitude = $request->latitude;
			$user->longitude = $request->longitude;
			$user->save();
			
			return response()->json(['message' => trans('api.user.location_updated')]);
			
		} else {
			
			return response()->json(['error' => trans('api.user.user_not_found')], 422);
			
		}
		
	}
	
	public function update_language(Request $request)
	{
		
		$this->validate($request, [
			'language' => 'required',
		]);
		
		if ($user = User::find(Auth::user()->id)) {
			
			$user->language = $request->language;
			$user->save();
			
			return response()->json(['message' => trans('api.user.language_updated'), 'language' => $request->language]);
			
		} else {
			
			return response()->json(['error' => trans('api.user.user_not_found')], 422);
			
		}
		
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function details(Request $request)
	{
		$this->validate($request, [
			'device_type' => 'in:android,ios',
		]);
		try {
			if ($user = User::find(Auth::user()->id)) {
				if ($request->has('device_token')) {
					$user->device_token = $request->device_token;
				}
				if ($request->has('device_type')) {
					$user->device_type = $request->device_type;
				}
				if ($request->has('device_id')) {
					$user->device_id = $request->device_id;
				}
				$user->save();
				$user->currency = Setting::get('currency');
				$user->corporate_pin = (Auth::user()->corporate_id != 0) ? CorporateUsers::whereemployee_id(Auth::user()->emp_id)->first()->pin : 0;
				$user->sos = Setting::get('sos_number', '911');
				$user->app_contact = Setting::get('app_contact', '5777');
				$user->measurement = Setting::get('distance', 'Kms');
				$user->stripe_secret_key = Setting::get('stripe_secret_key', '');
				$user->stripe_publishable_key = Setting::get('stripe_publishable_key', '');
				$user->user_negative_wallet_limit = Setting::get('user_negative_wallet_limit');
				$user->ride_cancellation_minutes = Setting::get('ride_cancellation_minutes');
				return $user;
				
			} else {
				return response()->json(['error' => trans('api.user.user_not_found')], 422);
			}
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
		
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function update_profile(Request $request)
	{
		
		$this->validate($request, [
			'first_name' => 'required|max:255',
			'last_name' => 'max:255',
			'email' => 'email|unique:users,email,' . Auth::user()->id,
			'mobile' => 'required',
			'picture' => 'mimes:jpeg,bmp,png',
		]);
		
		try {
			
			$user = User::findOrFail(Auth::user()->id);
			
			if ($request->has('first_name')) {
				$user->first_name = $request->first_name;
			}
			
			if ($request->has('last_name')) {
				$user->last_name = $request->last_name;
			}
			
			if ($request->has('email')) {
				$user->email = $request->email;
			}
			
			if ($request->has('mobile')) {
				$user->mobile = $request->mobile;
			}
			
			if ($request->has('gender')) {
				$user->gender = $request->gender;
			}
			
			if ($request->has('language')) {
				$user->language = $request->language;
			}
			
			if ($request->picture != "") {
				Storage::delete($user->picture);
				$user->picture = $request->picture->store('user/profile');
			}
			
			$user->save();
			
			$user->currency = Setting::get('currency');
			$user->sos = Setting::get('sos_number', '911');
			$user->app_contact = Setting::get('app_contact', '5777');
			$user->measurement = Setting::get('distance', 'Kms');
			
			if ($request->ajax()) {
				return response()->json($user);
			} else {
				return back()->with('flash_success', trans('api.user.profile_updated'));
			}
		} catch (ModelNotFoundException $e) {
			return response()->json(['error' => trans('api.user.user_not_found')], 422);
		}
		
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function services(Request $request)
	{
		try {
			
			
			if ($serviceList = ServiceType::all()) {
				
				$distance = Setting::get('provider_search_radius', '10');
				
				$latitude = $request->s_latitude;
				$longitude = $request->s_longitude;
				
				$Providers = Provider::with('service')
				                     ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 'id', 'latitude', 'longitude')
				                     ->where('status', 'approved')
				                     ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
				                     ->whereHas('service', function ($query) {
					                     $query->where('status', 'active');
				                     })
				                     ->orderBy('distance', 'asc')
				                     ->get();
				Log::info($Providers);
				$request = $request->all();
				if (!empty($Providers['items'])) {
					$service_type_ids = array();
					foreach ($Providers as $key => $value) {
						
						$request['d_latitude'] = $value->latitude;
						$request['d_longitude'] = $value->longitude;
						$response = new ServiceTypes();
						
						$responsedata[] = $response->getLocationDistance($request);
						Log::info($responsedata);
						$service_type_ids[] = $value->service->service_type_id;
					}
					
					$check_service_id = [];
					foreach ($service_type_ids as $key => $value) {
						Log::info($key);
						if (!in_array($value, $check_service_id)) {
							$check_service_id[] = $value;
							
							foreach ($serviceList as $key1 => $value1) {
								
								if (in_array($value1->id, $check_service_id)) {
                                    $serviceList[$key1]->duration = $responsedata[$key]['time'];
                                    if (Setting::get('distance', 'Kms') == 'Kms'){
                                        $serviceList[$key1]->kilometer = round($responsedata[$key]['meter'] / 1000, 1); //TKM
                                        Log::info($serviceList[$key1]->kilometer);
                                    }else {
                                        $serviceList[$key1]->kilometer = round($responsedata[$key]['meter'] / 1609.344, 1); //TMi
                                        Log::info($serviceList[$key1]->kilometer);
                                    }
                                } else {
									$serviceList[$key1]->duration = null;
									$serviceList[$key1]->kilometer = null;
								}
								
								
							}
						}
						
					}
					
				} else {
					
					foreach ($serviceList as $key1 => $value1) {
						$serviceList[$key1]->duration = null;
						$serviceList[$key1]->kilometer = null;
					}
				}
				
				return $serviceList;
			} else {
				return response()->json(['error' => trans('api.services_not_found')], 422);
			}
			
		} catch (\Exception $e) {
			Log::error($e->getMessage() . "on line" . $e->getLine());
			return response()->json(array('error' => true, 'message' => $e->getMessage() . "on line" . $e->getLine()));
		}
		
	}
	
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function send_request(Request $request)
	{
		Log::info('SendRequest: Request Details:', $request->all());
		$this->validate($request, [
			's_latitude' => 'required|numeric',
			'd_latitude' => 'required|numeric',
			's_longitude' => 'numeric',
			'd_longitude' => 'numeric',
			'service_type' => 'required|numeric|exists:service_types,id',
			'instructions' => 'required_if:service_type,'. ServiceType::TOW_TRUCK_ID . '|string',
			'is_booster_cable' => 'required_if:service_type,'. ServiceType::BOOSTER_CABLE_SERVICE_ID . '|boolean',
			//'promo_code' => 'exists:promocodes,promo_code',
			'distance' => 'required|numeric',
			'use_wallet' => 'numeric',
			'payment_mode' => 'required|in:CASH,CARD,PAYPAL,ELAVON,CORPORATE_ACCOUNT',
			'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . Auth::user()->id],
		], ['s_latitude.required' => 'Source address required', 'd_latitude.required' => 'Destination address required']);
		
		/*Log::info('New Request from User: '.Auth::user()->id);*/
		
		$ActiveRequests = UserRequests::PendingRequest(Auth::user()->id)->count();
		Log::info('SendRequest: Active Request:' . $ActiveRequests . ', UserId: ' . auth()->id());
		
		if ($ActiveRequests > 0) {
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.ride.request_inprogress')], 422);
			} else {
				return redirect('dashboard')->with('flash_error', trans('api.ride.request_inprogress'));
			}
		}
		
		if ($request->has('schedule_date') && $request->has('schedule_time')) {
			$beforeschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->subHour(1);
			$afterschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->addHour(1);
			
			$CheckScheduling = UserRequests::where('status', 'SCHEDULED')
			                               ->where('user_id', Auth::user()->id)
			                               ->whereBetween('schedule_at', [$beforeschedule_time, $afterschedule_time])
			                               ->count();
			
			
			if ($CheckScheduling > 0) {
				if ($request->ajax()) {
					return response()->json(['error' => trans('api.ride.request_scheduled')], 422);
				} else {
					return redirect('dashboard')->with('flash_error', trans('api.ride.request_scheduled'));
				}
			}
			
		}
		//// Corporate User function check
		// if(Auth::user()->corporate_id !=0){
		//     $corporate = Corporate::findOrFail(Auth::user()->corporate_id);
		
		//     if($corporate->recharge_option == 'POSTPAID')
		//     {
		//         $user_ids = User::wherecorporate_id($corporate->id)->pluck('id');
		//         $user_request_ids = UserRequests::whereIn('user_id',$user_ids)->wherepostpaid_payment_status('NOTPAID')->pluck('id');
		//         $check_amount = UserRequestPayment::whereIn('request_id',$user_request_ids)->sum('total');
		
		//         if(($corporate->limit_amount - $check_amount) < $request->estimated_fare)
		//         {
		//             if($request->ajax()) {
		//                 return response()->json(['error' => 'Your Compony wallet amount to low, so you cant ride'], 500);
		//             }else{
		//                 return redirect('dashboard')->with('flash_error', 'Your Compony wallet amount to low, so you cant ride');
		//             }
		//         }
		//     }
		//     else
		//     {
		//         $user_ids = User::wherecorporate_id($corporate->id)->pluck('id');
		//         $user_request_ids = UserRequests::whereIn('user_id',$user_ids)->wherepostpaid_payment_status('PAID')->pluck('id');
		//         $check_amount = UserRequestPayment::whereIn('request_id',$user_request_ids)->sum('total');
		
		//         if(($corporate->deposit_amount - $check_amount) < $request->estimated_fare)
		//         {
		//             if($request->ajax()) {
		//                 return response()->json(['error' => 'Your Compony wallet amount to low, so you cant ride'], 500);
		//             }else{
		//                 return redirect('dashboard')->with('flash_error', 'Your Compony wallet amount to low, so you cant ride');
		//             }
		//         }
		//     }
		// }
		
		$distance = Setting::get('provider_search_radius', '10');
		
		$latitude = $request->s_latitude;
		$longitude = $request->s_longitude;
		$service_type = $request->service_type;
		
		$Providers = Provider::with('service')
		                     ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 'id')
		                     ->where('status', 'approved')
		                     ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
		                     ->whereHas('service', function ($query) use ($service_type) {
			                     $query->where('status', 'active');
			                     if((int) $service_type !== ServiceType::BOOSTER_CABLE_SERVICE_ID) {
				                     Log::info('UserApiController send request: Except Booster');
				                     $query->where( 'service_type_id', $service_type );
			                     }else{
				                     Log::info('UserApiController send request: For Booster');
			                     }
		                     })
		                     ->orderBy('distance', 'asc')
		                     ->get();
		//  dd($Providers);
		// List Providers who are currently busy and add them to the filter list.
		Log::info('SendRequest: Total Providers:' . count($Providers));
		
		if (count($Providers) == 0) {
			if ($request->ajax()) {
				// Push Notification to User
				return response()->json(['error' => trans('api.ride.no_providers_found')], 422);
			} else {
				return back()->with('flash_success', trans('api.ride.no_providers_found'));
			}
		}
		
		try {
			
			$details = "https://maps.googleapis.com/maps/api/directions/json?origin=" .
			           $request->s_latitude . "," . $request->s_longitude . "&destination=" . $request->d_latitude . "," .
			           $request->d_longitude . "&mode=driving&key=" . Setting::get('map_key');
			
			$json = curl($details);
			
			$details = json_decode($json, TRUE);
			
			$route_key = $details['routes'][0]['overview_polyline']['points'];
			
			$UserRequest = new UserRequests;
			$UserRequest->booking_id = Helper::generate_booking_id();
			
			
			$UserRequest->user_id = Auth::user()->id;
			
			if ((Setting::get('manual_request', 0) == 0) && (Setting::get('broadcast_request', 0) == 1)) {
				$UserRequest->current_provider_id = $Providers[0]->id;
			} else {
				$UserRequest->current_provider_id = 0;
			}
			
			$UserRequest->service_type_id = $request->service_type;
			$UserRequest->instructions = ($request->has('instructions')) ? $request['instructions'] : '';
			$UserRequest->is_booster_cable = ($request->has('is_booster_cable')) ? $request['is_booster_cable'] : '0';
			$UserRequest->rental_hours = $request->rental_hours;
			$UserRequest->payment_mode = $request->payment_mode;
			$UserRequest->promocode_id = $request->promocode_id ?: 0;
			$UserRequest->description = $request->description;
			
			// $UserRequest->status = 'SEARCHING';
			if ($request->has('schedule_date') && $request->has('schedule_time')) {
				$UserRequest->status = 'SCHEDULES';
			} else {
				$UserRequest->assigned_at = Carbon::now();
				$UserRequest->status = 'SEARCHING';
			}
			
			$UserRequest->s_address = $request->s_address ?: "";
			$UserRequest->d_address = $request->d_address ?: "";
			
			$UserRequest->s_latitude = $request->s_latitude;
			$UserRequest->s_longitude = $request->s_longitude;
			
			$UserRequest->d_latitude = $request->d_latitude;
			$UserRequest->d_longitude = $request->d_longitude;
			$UserRequest->distance = $request->distance;
			$UserRequest->unit = Setting::get('distance', 'Kms');
			
			if (Auth::user()->wallet_balance > 0) {
				$UserRequest->use_wallet = $request->use_wallet ?: 0;
			}
			
			if (Setting::get('track_distance', 0) == 1) {
				$UserRequest->is_track = "YES";
			}
			
			$UserRequest->otp = mt_rand(1000, 9999);
			
			// $UserRequest->assigned_at = Carbon::now();
			$UserRequest->route_key = $route_key;
			
			if ($Providers->count() <= Setting::get('surge_trigger') && $Providers->count() > 0) {
				$UserRequest->surge = 1;
			}
			
			if ($request->has('schedule_date') && $request->has('schedule_time')) {
				$UserRequest->schedule_at = date("Y-m-d H:i:s", strtotime("$request->schedule_date $request->schedule_time"));
				$UserRequest->is_scheduled = 'YES';
			}
			
			if ((Setting::get('manual_request', 0) == 0) && (Setting::get('broadcast_request', 0) == 0)) {
				Log::info('New Request id : '. $UserRequest->id .' Assigned to provider : '. $UserRequest->current_provider_id);
				(new SendPushNotification)->IncomingRequest($Providers[0]->id);
			}
			
			$corporate_check_postpaid = Corporate::whererecharge_option('POSTPAID')->whereid(Auth::user()->corporate_id)->first();
			if ($corporate_check_postpaid && $request->payment_mode == 'CORPORATE_ACCOUNT')
				$UserRequest->postpaid_payment_status = 'NOTPAID';
			
			$corporate_check_prepaid = Corporate::whererecharge_option('PREPAID')->whereid(Auth::user()->corporate_id)->first();
			if ($corporate_check_prepaid && $request->payment_mode == 'CORPORATE_ACCOUNT')
				$UserRequest->postpaid_payment_status = 'PAID';
			
			$UserRequest->save();
			
			
			// update payment mode
			User::where('id', Auth::user()->id)->update(['payment_mode' => $request->payment_mode]);
			
			if ($request->has('card_id')) {
				
				Card::where('user_id', Auth::user()->id)->update(['is_default' => 0]);
				Card::where('card_id', $request->card_id)->update(['is_default' => 1]);
			}
			if ($UserRequest->status != 'SCHEDULES') {
				if (Setting::get('manual_request', 0) == 0) {
					Log::info('sendRequest:1- status != schedules and manualRequest is zero');
					//	Log::info('sendRequest: before providers push notification loop.');
//                	Log::info('sendRequest:2- Providers is:', $Providers->toArray());
					// (new SendPushNotification)->IncomingRequest($Providers[0]->id);
//			Log::info("sendRequest: providerId:".  $Providers[0]->id);
					if (Setting::get('broadcast_request', 0) == 1) {
						Log::info("sendRequest:3- providerId:".  $Providers[0]->id);
						(new SendPushNotification)->IncomingRequest($Providers[0]->id);
					}
					foreach ($Providers as $key => $Provider) {
						Log::info('4. RequestFilter created loop with providerId: '. $Provider->id);
						$Filter = new RequestFilter;
						// Send push notifications to the first provider
						// incoming request push to provider
						
						$Filter->request_id = $UserRequest->id;
						$Filter->provider_id = $Provider->id;
						$Filter->save();
					}
				}
			}
			
			if ($request->ajax()) {
				return response()->json([
					                        'message' => 'New request Created!',
					                        'request_id' => $UserRequest->id,
					                        'current_provider' => $UserRequest->current_provider_id,
				                        ]);
			} else {
				if ($UserRequest->status == 'SEARCHING') {
					return redirect('dashboard');
				} else {
					return redirect('dashboard')->with('flash_success', 'Your ride is Scheduled');
				}
			}
			
		} catch (Exception $e) {
			
			
			Log::info($e->getMessage());
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.something_went_wrong')], 500);
			} else {
				return back()->with('flash_error', trans('api.something_went_wrong'));
			}
		}
	}
	
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function cancel_request(Request $request)
	{
		
		$this->validate($request, [
			'request_id' => 'required|numeric|exists:user_requests,id,user_id,' . Auth::user()->id,
		]);
		
		try {

			$UserRequest = UserRequests::findOrFail($request->request_id);

			//< apply customer cancellation charges

			// $UserRequest = UserRequests::findOrFail(5503);
			
			$ride_cancellation_minutes = Setting::get('ride_cancellation_minutes');
			$ServiceType = ServiceType::find($UserRequest->service_type_id);
			$cancellation_charges = $ServiceType->cancellation_charges;

			$assigned_at = $UserRequest->assigned_at;
			$now = date("Y-m-d H:i:s");

			$diff_minutes = round(abs(strtotime($now) - strtotime($assigned_at)) / 60,2);

			if($diff_minutes >= $ride_cancellation_minutes){
				$User = User::find($UserRequest->user_id);
				$User->wallet_balance = $User->wallet_balance - $cancellation_charges;
				$User->save();
			}
			//> apply custommer cancellation charges
			
			if ($UserRequest->status == 'CANCELLED') {
				if ($request->ajax()) {
					return response()->json(['error' => trans('api.ride.already_cancelled')], 422);
				} else {
					return back()->with('flash_error', trans('api.ride.already_cancelled'));
				}
			}
			
			if (in_array($UserRequest->status, ['SEARCHING', 'STARTED', 'ARRIVED', 'SCHEDULED', 'SCHEDULES'])) {
				
				if ($UserRequest->status != 'SEARCHING') {
					$this->validate($request, [
						'cancel_reason' => 'max:255',
					]);
				}
				
				$UserRequest->status = 'CANCELLED';
				$UserRequest->cancel_reason = $request->cancel_reason;
				$UserRequest->cancelled_by = 'USER';
				$UserRequest->save();
				
				RequestFilter::where('request_id', $UserRequest->id)->delete();
				
				if ($UserRequest->status != 'SCHEDULED') {
					
					if ($UserRequest->provider_id != 0) {
						
						ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'active']);
						
					}
				}
				
				// Send Push Notification to User
				(new SendPushNotification)->UserCancellRide($UserRequest);
				
				if ($request->ajax()) {
					return response()->json(['message' => trans('api.ride.ride_cancelled')]);
				} else {
					return redirect('dashboard')->with('flash_success', trans('api.ride.ride_cancelled'));
				}
				
			} else {
				if ($request->ajax()) {
					return response()->json(['error' => trans('api.ride.already_onride')], 422);
				} else {
					return back()->with('flash_error', trans('api.ride.already_onride'));
				}
			}
		} catch (ModelNotFoundException $e) {
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.something_went_wrong')], 500);
			} else {
				return back()->with('flash_error', trans('api.something_went_wrong'));
			}
		}
		
	}
	
	/**
	 * Show the request status check.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function request_status_check()
	{
		
		try {
			$check_status = ['CANCELLED', 'SCHEDULED', 'SCHEDULES'];
			
			$UserRequests = UserRequests::UserRequestStatusCheck(Auth::user()->id, $check_status)
			                            ->get()
			                            ->toArray();
			Log::info('Rider request check: auth id: ' . auth()->id() . ' userRequests: ', $UserRequests);
			
			$search_status = ['SEARCHING', 'SCHEDULED', 'SCHEDULES'];
			$UserRequestsFilter = UserRequests::UserRequestAssignProvider(Auth::user()->id, $search_status)->get();
			
			Log::info('Rider request check: UserRequestFilter');
			Log::info($UserRequestsFilter);
			
			$Timeout = Setting::get('provider_select_timeout', 180);
			
			if (!empty($UserRequestsFilter)) {
				for ($i = 0; $i < sizeof($UserRequestsFilter); $i++) {
					$ExpiredTime = $Timeout - (time() - strtotime($UserRequestsFilter[$i]->assigned_at));
					if ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
						$Providertrip = new TripController();
						$Providertrip->assign_next_provider($UserRequestsFilter[$i]->id);
					} else if ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0) {
						break;
					}
				}
			}
			
			return response()->json(['data' => $UserRequests, 'sos' => Setting::get('sos_number', '911'),
			                         'cash' => (int)Setting::get('CASH', 1), 'card' => (int)Setting::get('CARD', 0),
			                         'currency' => Setting::get('currency', '$')]);
			
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	
	public function rate_provider(Request $request)
	{
		
		$this->validate($request, [
			'request_id' => 'required|integer|exists:user_requests,id,user_id,' . Auth::user()->id,
			'rating' => 'required|integer|in:1,2,3,4,5',
			'comment' => 'max:255',
		]);
		
		$UserRequests = UserRequests::where('id', $request->request_id)
		                            ->where('status', 'COMPLETED')
		                            ->where('paid', 0)
		                            ->first();
		
		if ($UserRequests) {
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.user.not_paid')], 422);
			} else {
				return back()->with('flash_error', trans('api.user.not_paid'));
			}
		}
		
		try {
			
			$UserRequest = UserRequests::findOrFail($request->request_id);
			
			if ($UserRequest->rating == null) {
				UserRequestRating::create([
					                          'provider_id' => $UserRequest->provider_id,
					                          'user_id' => $UserRequest->user_id,
					                          'request_id' => $UserRequest->id,
					                          'user_rating' => $request->rating,
					                          'user_comment' => $request->comment,
				                          ]);
			} else {
				$UserRequest->rating->update([
					                             'user_rating' => $request->rating,
					                             'user_comment' => $request->comment,
				                             ]);
			}
			
			$UserRequest->user_rated = 1;
			$UserRequest->save();
			
			$average = UserRequestRating::where('provider_id', $UserRequest->provider_id)->avg('user_rating');
			
			Provider::where('id', $UserRequest->provider_id)->update(['rating' => $average]);
			
			// Send Push Notification to Provider
			if ($request->ajax()) {
				return response()->json(['message' => trans('api.ride.provider_rated')]);
			} else {
				return redirect('dashboard')->with('flash_success', trans('api.ride.provider_rated'));
			}
		} catch (Exception $e) {
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.something_went_wrong')], 500);
			} else {
				return back()->with('flash_error', trans('api.something_went_wrong'));
			}
		}
		
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	
	public function modifiy_request(Request $request)
	{
		
		$this->validate($request, [
			'request_id' => 'required|integer|exists:user_requests,id,user_id,' . Auth::user()->id,
			'latitude' => 'sometimes|nullable|numeric',
			'longitude' => 'sometimes|nullable|numeric',
			'address' => 'sometimes|nullable',
			'payment_mode' => 'sometimes|nullable|in:CASH,CARD,PAYPAL,CAC',
			'card_id' => ['required_if:payment_mode,CARD,CAC', 'exists:cards,card_id,user_id,' . Auth::user()->id],
		]);
		
		try {
			
			$userRequest = UserRequests::findOrFail($request->request_id);
			
			if (!empty($request->latitude) && !empty($request->longitude)) {
				$userRequest->d_latitude = $request->latitude ?: $userRequest->d_latitude;
				$userRequest->d_longitude = $request->longitude ?: $userRequest->d_longitude;
				$userRequest->d_address = $request->address ?: $userRequest->d_address;
				(new SendPushNotification)->changeDestination($userRequest);
			}
			
			if (!empty($request->payment_mode)) {
				$userRequest->payment_mode = $request->payment_mode ?: $userRequest->payment_mode;
				if ($request->payment_mode == 'CARD' && $userRequest->status == 'DROPPED') {
					$userRequest->status = 'COMPLETED';
				}
			}
			
			$userRequest->save();
			
			
			if ($request->has('card_id')) {
				
				Card::where('user_id', Auth::user()->id)->update(['is_default' => 0]);
				Card::where('card_id', $request->card_id)->update(['is_default' => 1]);
			}
			
			// Send Push Notification to Provider
			if ($request->ajax()) {
				return response()->json(['message' => trans('api.ride.request_modify_location')]);
			} else {
				return redirect('dashboard')->with('flash_success', trans('api.ride.request_modify_location'));
			}
		} catch (Exception $e) {
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.something_went_wrong')], 500);
			} else {
				return back()->with('flash_error', trans('api.something_went_wrong'));
			}
		}
		
	}
	
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function trips()
	{
		
		
		try {
			$UserRequests = UserRequests::UserTrips(Auth::user()->id)->get();
			if (!empty($UserRequests)) {
				$map_icon = asset('asset/img/marker-start.png');
				foreach ($UserRequests as $key => $value) {
					$UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
					                                  "autoscale=1" .
					                                  "&size=520x330" .
					                                  "&maptype=terrian" .
					                                  "&format=png" .
					                                  "&visual_refresh=true" .
					                                  "&markers=icon:" . $map_icon . "%7C" . $value->s_latitude . "," . $value->s_longitude .
					                                  "&markers=icon:" . $map_icon . "%7C" . $value->d_latitude . "," . $value->d_longitude .
					                                  "&path=color:0x191919|weight:3|enc:" . $value->route_key .
					                                  "&key=" . Setting::get('map_key');
				}
			}
			return $UserRequests;
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')]);
		}
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function estimated_fare(Request $request)
	{
		$this->validate($request, [
			's_latitude' => 'required|numeric',
			's_longitude' => 'numeric',
			'd_latitude' => 'required|numeric',
			'd_longitude' => 'numeric',
			'service_type' => 'required|numeric|exists:service_types,id',
		], ['s_latitude.required' => 'Source address required', 'd_latitude.required' => 'Destination address required']);
		
		try {
			$response = new ServiceTypes();
			Log::info("EstimatedFare:  request params: ", $request->all);
			$responsedata = $response->calculateFare($request->all(), 1);
			if (!empty($responsedata['errors'])) {
				throw new Exception($responsedata['errors']);
			} else {
				return response()->json($responsedata['data']);
			}
			
		} catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function trip_details(Request $request)
	{
		
		$this->validate($request, [
			'request_id' => 'required|integer|exists:user_requests,id',
		]);
		
		try {
			$UserRequests = UserRequests::UserTripDetails(Auth::user()->id, $request->request_id)->get();
			if (!empty($UserRequests)) {
				$map_icon = asset('asset/img/marker-start.png');
				foreach ($UserRequests as $key => $value) {
					$UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
					                                  "autoscale=1" .
					                                  "&size=520x330" .
					                                  "&maptype=terrian" .
					                                  "&format=png" .
					                                  "&visual_refresh=true" .
					                                  "&markers=icon:" . $map_icon . "%7C" . $value->s_latitude . "," . $value->s_longitude .
					                                  "&markers=icon:" . $map_icon . "%7C" . $value->d_latitude . "," . $value->d_longitude .
					                                  "&path=color:0x191919|weight:3|enc:" . $value->route_key .
					                                  "&key=" . Setting::get('map_key');
				}
			}
			return $UserRequests;
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')]);
		}
	}
	
	/**
	 * get all promo code.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function promocodes()
	{
		try {
			//$this->check_expiry();
			
			return PromocodeUsage::Active()
			                     ->where('user_id', Auth::user()->id)
			                     ->with('promocode')
			                     ->get();
			
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}
	
	
	/*public function check_expiry(){
			try{
					$Promocode = Promocode::all();
					foreach ($Promocode as $index => $promo) {
							if(date("Y-m-d") > $promo->expiration){
									$promo->status = 'EXPIRED';
									$promo->save();
									PromocodeUsage::where('promocode_id', $promo->id)->update(['status' => 'EXPIRED']);
							}else{
									PromocodeUsage::where('promocode_id', $promo->id)
													->where('status','<>','USED')
													->update(['status' => 'ADDED']);

									PromocodePassbook::create([
													'user_id' => Auth::user()->id,
													'status' => 'ADDED',
													'promocode_id' => $promo->id
											]);
							}
					}
			} catch (Exception $e) {
					return response()->json(['error' => trans('api.something_went_wrong')], 500);
			}
	}*/
	
	
	/**
	 * add promo code.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function list_promocode(Request $request)
	{
		try {
			
			$promo_list = Promocode::where('expiration', '>=', date("Y-m-d H:i"))
			                       ->whereDoesntHave('promousage', function ($query) {
				                       $query->where('user_id', Auth::user()->id);
			                       })
			                       ->get();
			if ($request->ajax()) {
				return response()->json([
					                        'promo_list' => $promo_list
				                        ]);
			} else {
				return $promo_list;
			}
		} catch (Exception $e) {
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.something_went_wrong')], 500);
			} else {
				return back()->with('flash_error', trans('api.something_went_wrong'));
			}
		}
	}
	
	
	public function add_promocode(Request $request)
	{
		
		$this->validate($request, [
			'promocode' => 'required|exists:promocodes,promo_code',
		]);
		
		try {
			
			$find_promo = Promocode::where('promo_code', $request->promocode)->first();
			
			if ($find_promo->status == 'EXPIRED' || (date("Y-m-d") > $find_promo->expiration)) {
				
				if ($request->ajax()) {
					
					return response()->json([
						                        'message' => trans('api.promocode_expired'),
						                        'code' => 'promocode_expired'
					                        ]);
					
				} else {
					return back()->with('flash_error', trans('api.promocode_expired'));
				}
				
			} elseif (PromocodeUsage::where('promocode_id', $find_promo->id)->where('user_id', Auth::user()->id)->whereIN('status', ['ADDED', 'USED'])->count() > 0) {
				
				if ($request->ajax()) {
					
					return response()->json([
						                        'message' => trans('api.promocode_already_in_use'),
						                        'code' => 'promocode_already_in_use'
					                        ]);
					
				} else {
					return back()->with('flash_error', trans('api.promocode_already_in_use'));
				}
				
			} else {
				
				$promo = new PromocodeUsage;
				$promo->promocode_id = $find_promo->id;
				$promo->user_id = Auth::user()->id;
				$promo->status = 'ADDED';
				$promo->save();
				
				$count_id = PromocodePassbook::where('promocode_id', $find_promo->id)->count();
				//dd($count_id);
				if ($count_id == 0) {
					
					PromocodePassbook::create([
						                          'user_id' => Auth::user()->id,
						                          'status' => 'ADDED',
						                          'promocode_id' => $find_promo->id
					                          ]);
				}
				if ($request->ajax()) {
					
					return response()->json([
						                        'message' => trans('api.promocode_applied'),
						                        'code' => 'promocode_applied'
					                        ]);
					
				} else {
					return back()->with('flash_success', trans('api.promocode_applied'));
				}
			}
			
		} catch (Exception $e) {
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.something_went_wrong')], 500);
			} else {
				return back()->with('flash_error', trans('api.something_went_wrong'));
			}
		}
		
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function upcoming_trips()
	{
		
		try {
			$UserRequests = UserRequests::UserUpcomingTrips(Auth::user()->id)->get();
			if (!empty($UserRequests)) {
				$map_icon = asset('asset/img/marker-start.png');
				foreach ($UserRequests as $key => $value) {
					$UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
					                                  "autoscale=1" .
					                                  "&size=520x330" .
					                                  "&maptype=terrian" .
					                                  "&format=png" .
					                                  "&visual_refresh=true" .
					                                  "&markers=icon:" . $map_icon . "%7C" . $value->s_latitude . "," . $value->s_longitude .
					                                  "&markers=icon:" . $map_icon . "%7C" . $value->d_latitude . "," . $value->d_longitude .
					                                  "&path=color:0x000000|weight:3|enc:" . $value->route_key .
					                                  "&key=" . Setting::get('map_key');
				}
			}
			return $UserRequests;
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function upcoming_trip_details(Request $request)
	{
		
		$this->validate($request, [
			'request_id' => 'required|integer|exists:user_requests,id',
		]);
		
		try {
			$UserRequests = UserRequests::UserUpcomingTripDetails(Auth::user()->id, $request->request_id)->get();
			if (!empty($UserRequests)) {
				$map_icon = asset('asset/img/marker-start.png');
				foreach ($UserRequests as $key => $value) {
					$UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
					                                  "autoscale=1" .
					                                  "&size=520x330" .
					                                  "&maptype=terrian" .
					                                  "&format=png" .
					                                  "&visual_refresh=true" .
					                                  "&markers=icon:" . $map_icon . "%7C" . $value->s_latitude . "," . $value->s_longitude .
					                                  "&markers=icon:" . $map_icon . "%7C" . $value->d_latitude . "," . $value->d_longitude .
					                                  "&path=color:0x000000|weight:3|enc:" . $value->route_key .
					                                  "&key=" . Setting::get('map_key');
				}
			}
			return $UserRequests;
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}
	
	
	/**
	 * Show the nearby providers.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function show_providers(Request $request)
	{
		
		$this->validate($request, [
			'latitude' => 'required|numeric',
			'longitude' => 'required|numeric',
			'service' => 'numeric|exists:service_types,id',
		]);
		
		try {
			
			$distance = Setting::get('provider_search_radius', '10');
			$latitude = $request->latitude;
			$longitude = $request->longitude;
			
			if ($request->has('service')) {
				
				$ActiveProviders = ProviderService::AvailableServiceProvider($request->service)
				                                  ->get()->pluck('provider_id');
				
				$Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
				                     ->where('status', 'approved')
				                     ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
				                     ->get();
				
			} else {
				
				$ActiveProviders = ProviderService::where('status', 'active')
				                                  ->get()->pluck('provider_id');
				
				$Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
				                     ->where('status', 'approved')
				                     ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
				                     ->get();
			}
			
			
			return $Providers;
			
		} catch (Exception $e) {
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.something_went_wrong')], 500);
			} else {
				return back()->with('flash_error', trans('api.something_went_wrong'));
			}
		}
	}
	
	
	/**
	 * Forgot Password.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	
	public function forgot_password(Request $request)
	{
		
		$validator = Validator::make($request->all(), [
			'email' => 'required|email|exists:users,email',
		]);
		
		if ( $validator->fails() ) {
			$response = [ 'status'  => false,
			              'message' => $validator->messages()->all()
			];
			return response()->json( $this->encrypter->encrypt( $response) , 422);
		}
		
		try {
			
			$user = User::where('email', $request->email)->first();
			
			$otp = mt_rand(100000, 999999);
			
			$user->otp = $otp;
			$user->save();
			
			Notification::send($user, new ResetPasswordOTP($otp));
			
			return response()->json($this->encrypter->encrypt( [
				                        'message' => 'OTP sent to your email!',
				                        'status' => true,
				                        //'user' => [
				                        //	        'id' => $user->id,
				                        //          'first_name' => $user->first_name,
				                        //          'last_name' => $user->last_name,
				                        //          'otp' => $user->otp
				                        //]
			                        ]));
			
		} catch (Exception $e) {
			Log::info('Rider: Exception: forgot password ' . $e->getMessage());
			return response()->json( $this->encrypter->encrypt( ['error' => trans('api.something_went_wrong'), 'status' => false]), 500);
		}
	}
	
	
	/**
	 * Reset Password.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function reset_password(Request $request)
	{
		
		$validator = Validator::make($request->all(), [
			'otp' => ['required', 'numeric', 'digits:6',
			          Rule::exists('users')->where(function ($query) use($request){
				          $query->where('email', $request['email'])
				          ->where('otp', $request['otp']);
			          })],
			'password' => 'required|confirmed|min:6',
			//'id' => 'required|numeric|exists:users,id',
			'email' => 'required|email|exists:users,email'
		
		]);
		if ( $validator->fails() ) {
			$response = [ 'status'  => false,
			              'message' => $validator->messages()->all()
			];
			return response()->json( $this->encrypter->encrypt( $response) , 422);
		}
		try {
			
			//$User = User::findOrFail($request->id);
			$User = User::where('email', $request['email'])->firstOrFail();
			// $UpdatedAt = date_create($User->updated_at);
			// $CurrentAt = date_create(date('Y-m-d H:i:s'));
			// $ExpiredAt = date_diff($UpdatedAt,$CurrentAt);
			// $ExpiredMin = $ExpiredAt->i;
			$User->password = bcrypt($request->password);
			$User->otp = mt_rand(100000, 999999);
			$User->save();
			foreach ($User->tokens as $token){
				$token->revoke();
			}
			if ($request->ajax()) {
				return response()->json($this->encrypter->encrypt(  ['message' => trans('api.user.password_updated')]));
			}
			
			
		} catch (Exception $e) {
			if ($request->ajax()) {
				return response()->json($this->encrypter->encrypt(  ['error' => trans('api.something_went_wrong')]), 500);
			}
		}
	}
	
	/**
	 * help Details.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function help_details(Request $request)
	{
		
		try {
			
			if ($request->ajax()) {
				return response()->json([
					                        'contact_number' => Setting::get('contact_number', ''),
					                        'contact_email' => Setting::get('contact_email', '')
				                        ]);
			}
			
		} catch (Exception $e) {
			if ($request->ajax()) {
				return response()->json(['error' => trans('api.something_went_wrong')], 500);
			}
		}
	}
	
	
	/**
	 * Show the wallet usage.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function wallet_passbook(Request $request)
	{
		try {
			$start_node = $request->start_node;
			$limit = $request->limit;
			
			$wallet_transation = UserWallet::where('user_id', Auth::user()->id);
			if (!empty($limit)) {
				$wallet_transation = $wallet_transation->offset($start_node);
				$wallet_transation = $wallet_transation->limit($limit);
			}
			
			$wallet_transation = $wallet_transation->orderBy('id', 'desc')->get();
			
			return response()->json(['wallet_transation' => $wallet_transation, 'wallet_balance' => Auth::user()->wallet_balance]);
			
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}
	
	
	/**
	 * Show the promo usage.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	public function promo_passbook(Request $request)
	{
		try {
			
			return PromocodePassbook::where('user_id', Auth::user()->id)->with('promocode')->get();
			
		} catch (Exception $e) {
			
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function test(Request $request)
	{
		//$push =  (new SendPushNotification)->IncomingRequest($request->id);
		$push = (new SendPushNotification)->Arrived($request->id);
		
		dd($push);
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function wallet_validate(Request $request)
	{
		$wallet_balance = User::find(Auth::user()->id)->wallet_balance;
		// $wallet_balance = User::find(1)->wallet_balance;
		$user_negative_wallet_limit = Setting::get('user_negative_wallet_limit');

		if($user_negative_wallet_limit < $wallet_balance){
			return response()->json(['message' => true]);
		}else{
			return response()->json(['error' => 'Please add balance to your wallet before booking a new ride.'], 500);
		}
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function pricing_logic($id)
	{
		//return $id;
		$logic = ServiceType::select('calculator')->where('id', $id)->first();
		return $logic;
		
	}
	
	public function fare(Request $request)
	{
		
		$this->validate($request, [
			's_latitude' => 'required|numeric',
			's_longitude' => 'numeric',
			'd_latitude' => 'required|numeric',
			'd_longitude' => 'numeric',
			'service_type' => 'required|numeric|exists:service_types,id',
		], ['s_latitude.required' => 'Source address required', 'd_latitude.required' => 'Destination address required']);
		
		try {
			$response = new ServiceTypes();
			$responsedata = $response->calculateFare($request->all());
			
			if (!empty($responsedata['errors'])) {
				throw new Exception($responsedata['errors']);
			} else {
				return response()->json($responsedata['data']);
			}
			
		} catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
		
	}
	
	/**
	 * Show the wallet usage.
	 *
	 * @return \Illuminate\Http\Response
	 */
	
	/*public function check(Request $request)
	{

			$this->validate($request, [
							'name' => 'required',
							'age' => 'required',
							'work' => 'required',
					]);
			 return Work::create(request(['name', 'age' ,'work']));
	}*/
	
	public function chatPush(Request $request)
	{
		
		$this->validate($request, [
			'user_id' => 'required|numeric',
			'message' => 'required',
		]);
		
		try {
			
			$user_id = $request->user_id;
			$message = $request->message;
			//$sender = $request->sender;
			$type = 'chat';
			 
           
            // $message = \PushNotification::Message($message,array(
            // 'badge' => 1,
            // 'sound' => 'default',
            // 'custom' => array('type' => 'chat')
            // ));
           
            (new SendPushNotification)->sendPushToUser($user_id, $message, $type);  



			
			
			return response()->json(['success' => 'true']);
			
		} catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
		
	}
	
	public function CheckVersion(Request $request)
	{
		
		$this->validate($request, [
			'sender' => 'in:user,provider',
			'device_type' => 'in:android,ios',
			'version' => 'required',
		]);
		
		try {
			
			$sender = trim($request->sender);
			$device_type = trim($request->device_type);
			$version = trim($request->version);
			
			$curversion = Setting::get("version_$device_type" . "_$sender");
			$url = Setting::get("store_link_$device_type" . "_$sender");
			$forceUpdate = false;
			if ($sender == 'user' &&  $device_type == 'ios') {
				if ($curversion > $version) {
					$forceUpdate = true;
				}
			} elseif ($sender == 'user' &&  $device_type == 'android') {
				if ($curversion > $version) {
					$forceUpdate = true;
				}
			} elseif ($sender == 'provider' &&  $device_type == 'ios' ) {
				if ($curversion > $version) {
					$forceUpdate = true;
				}
			} elseif ($sender == 'provider' &&  $device_type == 'android' ) {
				if ($curversion > $version) {
					$forceUpdate = true;
				}
			}
			return response()->json(['force_update' => $forceUpdate, 'url' => $url, 'currentVersion' => $curversion]);

		} catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
		
	}
	
	public function checkapi(Request $request)
	{
		Log::info('Request Details:', $request->all());
		return response()->json(['sucess' => true]);
		
	}
	
	public function companyList()
	{
		$companyList = Corporate::orderBy('created_at', 'desc')->get();
		return response()->json($companyList);
	}
	
	public function check_corporate_pin(Request $request)
	{
		$CorporateUsers = CorporateUsers::whereemployee_id(Auth::user()->emp_id)->first();
		
		if ($request->pin == $CorporateUsers->pin) {
			$status = 'success';
		} else {
			$status = 'failed';
		}
		
		return response()->json($status);
	}
	
	public function eta_time_service(Request $request)
	{
		$distance = Setting::get('provider_search_radius', '10');
		
		$latitude = $request->s_latitude;
		$longitude = $request->s_longitude;
		
		$Providers = Provider::with('service')
		                     ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 'id', 'latitude', 'longitude')
		                     ->where('status', 'approved')
		                     ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
		                     ->whereHas('service', function ($query) {
			                     $query->where('status', 'active');
		                     })
		                     ->orderBy('distance', 'asc')
		                     ->get();
		
		$request = $request->all();
		
		foreach ($Providers as $key => $value) {
			$request['d_latitude'] = $value->latitude;
			$request['d_longitude'] = $value->longitude;
			$response = new ServiceTypes();
			
			$responsedata[] = $response->getLocationDistance($request);
			
			$service_type_ids[] = $value->service->service_type_id;
		}
		$check_service_id = [];
		$services = [];
		foreach ($service_type_ids as $key => $value) {
			
			if (!in_array($value, $check_service_id)) {
				$check_service_id[] = $value;
				$services[$key] = ServiceType::findOrFail($value);
				$services[$key]['duration'] = $responsedata[$key]['time'];
				if (Setting::get('distance', 'Kms') == 'Kms')
					$services[$key]['kilometer'] = round($responsedata[$key]['meter'] / 1000, 1); //TKM
				else
					$services[$key]['kilometer'] = round($responsedata[$key]['meter'] / 1609.344, 1); //TMi
			}
		}
		
		return response()->json($services);
	}
	public function isMobileVerified (Request $request) {
		
		$validator = Validator::make( $request->all(), [
			'device_type'  => 'required|in:android,ios',
			'device_token' => 'required',
			'accessToken'  => 'required',
			'device_id'    => 'required',
			'login_by'       => 'required|in:facebook,google,apple',
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
			Log::info('Is Mobile Verified social unique id: '. $socialUser->id );
			$user = User::where( 'social_unique_id', $socialUser->id );
			if ( $socialUser->email != "" ) {
				Log::info('Is Mobile Verified email: '. $socialUser->email );
				$user->orWhere( 'email', $socialUser->email );
			}
			$authUser = $user->first();
			Log::info("Check if User Exists or not");
			Log::info($authUser);
			if ( $authUser ) {
				return response()->json(  $this->encrypter->encrypt( [
					                         "status"     => true,
					                         "isVerified" => $authUser->is_mobile_verified,
				                         ] ));
			} else {
				return response()->json(  $this->encrypter->encrypt( [ 'status' => false, 'message' => "Rider not found..." ]) );
			}
		} catch ( Exception $e ) {
			Log::info('Catch Exception: isMobileVerified ' . $e->getMessage());
			return response()->json(  $this->encrypter->encrypt( [ 'status' => false, 'message' => trans( 'api.something_went_wrong' ) ]) );
		}
	}
	
	public function sendMobileVerificationCode(Request $request) {
		Log::info("sendMobileVerificationCode in start.");
		$validator = Validator::make( $request->all(), [
			'mobile_no'     => 'required',
		] );
		$isMobileExists = User::where('mobile', $request['mobile_no'])->first();
		if(isset($isMobileExists)){
			return response()->json(  $this->encrypter->encrypt( [
				                         "status"  => true,
				                         "isVerified" => true,
				                         "message" => 'Mobile no. already verified.',
			                         ] ));
		}
		Log::info("sendMobileVerificationCode with params: ", $request->all());
		if ( $validator->fails() ) {
			return response()->json(  $this->encrypter->encrypt( [ 'status'  => false,
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
				return response()->json(  $this->encrypter->encrypt( [
					                         "status"  => true,
					                         "isVerified" => false,
					                         "message" => trans( 'api.user.mobile_verification_code_sent' ),
				                         ]) );
			}else{
				return response()->json(  $this->encrypter->encrypt( [
					                         "status"  => false,
					                         "isVerified" => false,
					                         "message" => $verification->getErrors()[0],
				                         ]) );
			}
		}catch (Exception $e){
			Log::info("sendMobileVerificationCode Twilio fail response: " . $e->getMessage());
			return response()->json(  $this->encrypter->encrypt( [ 'status' => false,
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
			$response = [ 'status'  => false,
			              'message' => $validator->messages()->all()
			];
			return response()->json( $this->encrypter->encrypt( $response) , 422);
		}
		try {
			$twilioService = app(Service::class);
			$verification = $twilioService->checkVerification($request['mobile_no'], $request['code']);
			if($verification->isValid()) {
				return response()->json( $this->encrypter->encrypt([
					                         "status"  => true,
					                         "message"    => 'Your mobile verified successfully.',
				                         ]));
			}else{
				return response()->json( $this->encrypter->encrypt([
					                         "status"  => false,
					                         "message" => $verification->getErrors()[0],
				                         ] ));
			}
		}catch (Exception $e){
			Log::info("verifyMobileVerificationCode catch : " . $e->getMessage());
			return response()->json( $this->encrypter->encrypt([ 'status' => false,
			                           'message' => trans( 'api.something_went_wrong' ) ]));
			
		}
	}

	public function testPushNoti(){


        $message = [
            'notification' => [
                'title' => 'New Notification',
                'text' => "This is test",
                'body' => "Test ",
                "click_action" => '#',
                'sound' => 'default',

            ],
            'to' => 'dI1n-zhK8Ex9gKsXGA9R6P:APA91bFdz5VsB5p6vQ342k0IZjdwPjK_CLzzAQuVbiBGnZKRmupJ_cwWlnMAyCkb3OEVM1tXV9DeZy6nZ5myO9wcMZ41gZA9ST_G380uQJZ3Q0rmX2NrEAayX7p_0Zga8QgaiL4HdzXg',
        ];

        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "key=AAAASlvYNr0:APA91bHUD-fO6tkDt86nRU98Lf4JHASo0O6ZUCVdBZVHFOHyzdbBp7HZnEJmPijZfsLUw-RCrcAw7fzMSpsGhAJ-zoW_GMr_gkDjZvUA30l3_ZX7394bHwYSd8Uq7OvMAjgoFLAZBmGD",
            ]
        ]);
        $response = $client->post('https://fcm.googleapis.com/fcm/send',
            ['body' => json_encode($message)]
        );


		//        $fields = [
		//            'notification' => [
		//                "title" => "test from server",
		//                "body" => "test lorem ipsum"
		//            ]
		//        ];
		//
		//        $push = new PushNotification('fcm');
		//
		//        $send = $push->setMessage([
		//            $fields
		//        ])->setApiKey('AAAASlvYNr0:APA91bHUD-fO6tkDt86nRU98Lf4JHASo0O6ZUCVdBZVHFOHyzdbBp7HZnEJmPijZfsLUw-RCrcAw7fzMSpsGhAJ-zoW_GMr_gkDjZvUA30l3_ZX7394bHwYSd8Uq7OvMAjgoFLAZBmGD')
		//            ->setDevicesToken('dI1n-zhK8Ex9gKsXGA9R6P:APA91bFdz5VsB5p6vQ342k0IZjdwPjK_CLzzAQuVbiBGnZKRmupJ_cwWlnMAyCkb3OEVM1tXV9DeZy6nZ5myO9wcMZ41gZA9ST_G380uQJZ3Q0rmX2NrEAayX7p_0Zga8QgaiL4HdzXg')->send();
		//        dd($send);
		//	    $push = new PushNotification('fcm');
		//        $send = $push->setMessage(['notification' => "Hello this is Test message"])
		//            ->setApiKey('AAAASlvYNr0:APA91bHUD-fO6tkDt86nRU98Lf4JHASo0O6ZUCVdBZVHFOHyzdbBp7HZnEJmPijZfsLUw-RCrcAw7fzMSpsGhAJ-zoW_GMr_gkDjZvUA30l3_ZX7394bHwYSd8Uq7OvMAjgoFLAZBmGD')
		//            ->setDevicesToken('dI1n-zhK8Ex9gKsXGA9R6P:APA91bFdz5VsB5p6vQ342k0IZjdwPjK_CLzzAQuVbiBGnZKRmupJ_cwWlnMAyCkb3OEVM1tXV9DeZy6nZ5myO9wcMZ41gZA9ST_G380uQJZ3Q0rmX2NrEAayX7p_0Zga8QgaiL4HdzXg')->send();
		//        Log::info("PushNotification: ");
		//        dd($send);
    }

	public function destroy(Request $request,$id){
		$this->validate($request, [
            'password' => 'required',
            'reason' => 'required',
        ]);

        $user = User::findOrFail($id);
        if(FacadesHash::check($request['password'], $user->password)){
			$user->email = "$user->email-DeletedFromUserApp";
			$user->mobile = "$user->mobile-DeletedFromUserApp";
			$user->password = "$user->password-DeletedFromUserApp";
			$user->save();

			return response()->json(["status" => true, 
									"message" => "$user->first_name deleted successfully."], 200);
		}else{
			return response()->json(["status" => false, 
								"message" => "Your password not matched"], 422);
		}
    }

}