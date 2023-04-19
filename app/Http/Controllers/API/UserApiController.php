<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\UserRequestStop;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Socialite\Facades\Socialite;
use DB;
use Illuminate\Support\Facades\Log;
use Auth;
use Hash;
use Route;
use Storage;
use Setting;
use Exception;
use Taxi\Twilio\Service;
use Validator;
use Notification;

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

use App\Services\V1\ServiceTypes;
use App\CorUser;
use App\CorporateUsers;


class UserApiController extends Controller
{

	public function __construct () {
		//Log::info('User api controller constructor');
	}

    /**
     * add request by a Rider
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function send_request(Request $request)
    {
	    Log::info('SendRequest: Request Details:', $request->all());
        $this->validate($request, [
            's_latitude' => 'required|numeric',
            'positions' => 'required',
            's_longitude' => 'numeric',
            'service_type' => 'required|numeric|exists:service_types,id',
            //'instructions' => 'required_if:service_type,'. ServiceType::TOW_TRUCK_ID . '|string',
            'instructions' => 'string',
            'is_booster_cable' => 'required_if:service_type,'. ServiceType::BOOSTER_CABLE_SERVICE_ID . '|boolean',
            //'promo_code' => 'exists:promocodes,promo_code',
            'distance' => 'required|numeric',
            'use_wallet' => 'numeric',
            'payment_mode' => 'required|in:CASH,CARD,PAYPAL,ELAVON,CORPORATE_ACCOUNT',
            'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . Auth::user()->id],
        ], ['s_latitude.required' => 'Source address required', 'd_latitude.required' => 'Destination address required']);

        $positions = json_decode($request->positions);

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

        $distance = Setting::get('provider_search_radius', '10');

        $latitude = $request->s_latitude;
        $longitude = $request->s_longitude;
        $service_type = $request->service_type;
	      $Providers = $this->getProviders( $latitude, $longitude, $distance, $service_type );
		      Log::info("SendRequest: First ServiceTypeId: $service_type" );
	        Log::info('SendRequest: First Total Providers:' . count($Providers));
	      if(count($Providers) === 0 && (int) $service_type === ServiceType::SEDAN_SERVICE_ID){
		      $Providers = $this->getProviders( $latitude, $longitude, $distance, ServiceType::MINI_VAN_SERVICE_ID );
		      Log::info("SendRequest: InSide the If ServiceTypeId: $service_type" );
		      Log::info('SendRequest: InSide the If Total Providers:' . count($Providers));
	      }
	
	    //  dd($Providers);
        // List Providers who are currently busy and add them to the filter list.
        if (count($Providers) == 0) {
            if ($request->ajax()) {
                // Push Notification to User
                return response()->json(['error' => trans('api.ride.no_providers_found')], 422);
            } else {
                return back()->with('flash_success', trans('api.ride.no_providers_found'));
            }
        }

        try {
            if(count($positions)>0){
                $temp = (array)$positions;
                $temp = $temp[count($temp)-1];
            }
            $details = "https://maps.googleapis.com/maps/api/directions/json?origin=" .
                       $request->s_latitude . "," . $request->s_longitude . "&destination=" . $temp->d_latitude . "," .
                $temp->d_longitude . "&mode=driving&key=" . Setting::get('map_key');

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
            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

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
            $roundTrip = $request->has('is_round') ? $request->is_round : 0;
            $waitingMinutes = $request->has('waiting_minutes') ? $request->waiting_minutes : 0;
            $UserRequest->is_round = $roundTrip;
            $UserRequest->waiting_time = $waitingMinutes;
            $UserRequest->save();

            /**
             * add stops
             */
            if($UserRequest){
                foreach ($positions as $key => $stop){
                    $newStop = new UserRequestStop();
                    $newStop->user_request_id = $UserRequest->id;
                    $newStop->d_address = $stop->d_address;
                    $newStop->d_latitude = $stop->d_latitude;
                    $newStop->d_longitude = $stop->d_longitude;
                    $newStop->order = $key + 1;
                    $newStop->save();
                }
            }


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
     * calculate fare for the ride
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function estimated_fare(Request $request)
    {
        $this->validate($request, [
            's_latitude' => 'required|numeric',
            's_longitude' => 'numeric',
            'positions' => 'required',
            'service_type' => 'required|numeric|exists:service_types,id',
        ], ['s_latitude.required' => 'Source address required', 'positions.required' => 'Destination address required']);

        try {
            $response = new ServiceTypes();
            $roundTrip = $request->has('round_trip') ? $request->round_trip : 0;
            $waitingMinutes = $request->has('waiting_minutes') ? $request->waiting_minutes : 0;
            $responsedata = $response->calculateFareV1($request->all(), 1 , $roundTrip,$waitingMinutes); // Onchange on admin changes requirement
//            $responsedata = $response->calculateFareV1($request->all(), 1 , $roundTrip,$waitingMinutes);

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
     * cancel a ride
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function cancel_request(Request $request)
    {
				Log::info('RequestCancel from user side with params', $request->all());
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

    
    public function request_status_check()
    {

        try {
            $check_status = ['CANCELLED', 'SCHEDULED', 'SCHEDULES'];

            $UserRequests = UserRequests::UserRequestStatusCheck(Auth::user()->id, $check_status)
                ->get()
                ->toArray();
						//Log::info('RequestStatusCheck: userRequests with auth: ' . auth()->id() . ' Total Requests: ' . count($UserRequests));

            $search_status = ['SEARCHING', 'SCHEDULED', 'SCHEDULES'];
            $UserRequestsFilter = UserRequests::UserRequestAssignProvider(Auth::user()->id, $search_status)->get();

            //Log::info("RequestStatusCheck: userRequestFilter");
            //Log::info($UserRequestsFilter);

            $Timeout = Setting::get('provider_select_timeout', 180);

            if (!empty($UserRequestsFilter)) {
                for ($i = 0; $i < sizeof($UserRequestsFilter); $i++) {
                    $ExpiredTime = $Timeout - (time() - strtotime($UserRequestsFilter[$i]->assigned_at));
                	  //Log::info("RequestStatusCheck: expiredTime: $ExpiredTime");
                    if ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
	                    //Log::info("RequestStatusCheck: assign to next provider");
                        $Providertrip = new TripController();
                        $Providertrip->assign_next_provider($UserRequestsFilter[$i]->id);
                    } else if ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0) {
	                    //Log::info("RequestStatusCheck: status SEARCHING and currency TIME not expired");
                        break;
                    }
                }
            }
	          
            return response()->json(['data' => $UserRequests, 'sos' => Setting::get('sos_number', '911'),
                'cash' => (int)Setting::get('CASH', 1), 'card' => (int)Setting::get('CARD', 0),
                'currency' => Setting::get('currency', '$')]);

        } catch (Exception $e) {
	        //Log::info("RequestStatusCheck: in Exception Catch: with message: ". $e->getMessage());
	        return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * show providers
     * @param Request $request
     * @return Provider[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
     * modify Request
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function modifiy_request(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id,user_id,' . Auth::user()->id,
            'positions' => 'sometimes|nullable',
            'payment_mode' => 'sometimes|nullable|in:CASH,CARD,PAYPAL,CAC',
            'card_id' => ['required_if:payment_mode,CARD,CAC', 'exists:cards,card_id,user_id,' . Auth::user()->id],
        ]);
	    
        try {
						$userRequest  = UserRequests::findOrFail( $request->request_id );
						if($request->has('positions')) {
							$destinations = json_decode( $request->positions );
							if ( ! is_null( $userRequest ) ) {
								foreach ( $destinations as $key => $stop ) {
									if ( $stop->action == "create" ) {
										$newStop                  = new UserRequestStop();
										$newStop->user_request_id = $userRequest->id;
										$newStop->d_address       = $stop->d_address;
										$newStop->d_latitude      = $stop->d_latitude;
										$newStop->d_longitude     = $stop->d_longitude;
										$newStop->order           = $key + 1;
										$newStop->save();
									} elseif ( $stop->action == "update" ) {
										$updateStop = UserRequestStop::find( $stop->stop_id );
										if ( ! is_null( $updateStop ) ) {
											$updateStop->update( [
												                     'd_address'   => $stop->d_address,
												                     'd_latitude'  => $stop->d_latitude,
												                     'd_longitude' => $stop->d_longitude
											                     ] );
										}
									} elseif ( $stop->action == "delete" ) {
										UserRequestStop::where( 'id', $stop->stop_id )
										               ->delete();
									}
								}
							}
							( new SendPushNotification )->changeDestination( $userRequest );
						}
            if (!empty($request->payment_mode)) {
                $userRequest->payment_mode = $request->payment_mode ?: $userRequest->payment_mode;
	              ( new SendPushNotification )->updatePaymentMode( $userRequest->provider_id );
                
                if ($request->payment_mode == 'CARD' && $userRequest->status == 'DROPPED') {
                    //$userRequest->status = 'COMPLETED';
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
     * get trip details
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * update stop details
     * @param Request $request
     * @return mixed
     */
    public function update_stop(Request $request){
        $this->validate($request, [
            'stop_id' => 'required|integer|exists:user_requests_stops,id',
            'd_address' => 'required',
            'd_latitude' => 'required',
            'd_longitude' => 'required',
        ]);

        $ActiveRequests = UserRequests::PendingRequest(Auth::user()->id)->first();

        if (!is_null($ActiveRequests)) {
            $stop = UserRequestStop::where('user_request_id',$ActiveRequests->id)
                ->where('id',$request->stop_id)->where('status','PENDING')->first();

            $stop->update([
                'd_address' => $request->d_address,
                'd_latitude' => $request->d_latitude,
                'd_longitude' => $request->d_longitude
            ]);
        }

        return $ActiveRequests;
    }
	
	private function getProviders ( $latitude, $longitude, $distance, $service_type ) {
		$Providers = Provider::with( 'service' )
		                     ->select( DB::Raw( "(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance" ),
		                               'id' )
		                     ->where( 'status', 'approved' )
		                     ->whereRaw( "(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance" )
		                     ->whereHas( 'service', function ( $query ) use ( $service_type ) {
			                     $query->where( 'status', 'active' );
			                     if ( (int) $service_type !== ServiceType::BOOSTER_CABLE_SERVICE_ID ) {
				                     Log::info( "UserApiController send request: Except Booster and service type: $service_type" );
				                     $query->where( 'service_type_id', $service_type );
			                     } else {
				                     Log::info( 'UserApiController send request: For Booster' );
			                     }
		                     } )
		                     ->orderBy( 'distance', 'asc' )
		                     ->get();
		return $Providers;
	}

}
