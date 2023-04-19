<?php

namespace App\Http\Controllers\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SendPushNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Auth;
use App\ProviderService;
use Setting;
use Exception;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\Card;
use App\UserRequestStop;
use App\User;
use App\Provider;
use App\ServiceType;
use App\UserRequests;
use App\RequestFilter;
use App\Corporate;

class UserApiController extends Controller
{
    public function send_request(Request $request)
    {
        Log::info('SendRequest: Request Details:', $request->all());
        $this->validate($request, [
            's_latitude' => 'required|numeric',
            'positions' => 'required',
            's_longitude' => 'numeric',
            'service_type' => 'required|numeric|exists:service_types,id',
            'instructions' => 'string',
            'is_booster_cable' => 'required_if:service_type,' . ServiceType::BOOSTER_CABLE_SERVICE_ID . '|boolean',
            'distance' => 'required|numeric',
            'use_wallet' => 'numeric',
            'payment_mode' => 'required|in:CASH,CARD,PAYPAL,ELAVON,CORPORATE_ACCOUNT',
            'offer_price' => 'required',
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
        $Providers = $this->getProviders($latitude, $longitude, $distance, $service_type);
        Log::info("SendRequest: First ServiceTypeId: $service_type");
        Log::info('SendRequest: First Total Providers:' . count($Providers));
        if (count($Providers) === 0 && (int) $service_type === ServiceType::SEDAN_SERVICE_ID) {
            $Providers = $this->getProviders($latitude, $longitude, $distance, ServiceType::MINI_VAN_SERVICE_ID);
            Log::info("SendRequest: InSide the If ServiceTypeId: $service_type");
            Log::info('SendRequest: InSide the If Total Providers:' . count($Providers));
        }

        //  dd($Providers);
        // List Providers who are currently busy and add them to the filter list.
        if (count($Providers) == 0) {
            if ($request->ajax()) {
                // Push Notification to User
                (new SendPushNotification)->ProviderNotAvailable(Auth::user()->id);
                return response()->json(['error' => trans('api.ride.no_providers_found')], 422);
            } else {
                return back()->with('flash_success', trans('api.ride.no_providers_found'));
            }
        }

        try {
            if (count($positions) > 0) {
                $temp = (array)$positions;
                $temp = $temp[count($temp) - 1];
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
                $UserRequest->current_provider_id = null;
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
            $UserRequest->offer_ammount = $request->offer_price;

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
                Log::info('New Request id : ' . $UserRequest->id . ' Assigned to provider : ' . null);
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
            if ($UserRequest) {
                foreach ($positions as $key => $stop) {
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
                    // if (Setting::get('broadcast_request', 0) == 1) {
                    //     Log::info("sendRequest:3- providerId:" .  $Providers[0]->id);
                    //     (new SendPushNotification)->IncomingRequest($Providers[0]->id);
                    // }
                    foreach ($Providers as $key => $Provider) {
                        Log::info('4. RequestFilter created loop with providerId: ' . $Provider->id);
                        $Filter = new RequestFilter;
                        // Send push notifications to the first provider
                        // incoming request push to provider

                        $Filter->request_id = $UserRequest->id;
                        $Filter->provider_id = $Provider->id;
                        $Filter->offer_price = 0;
                        $Filter->save();
                        (new SendPushNotification)->IncomingRequest($Provider->id);
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'New request Created!',
                    'request_id' => $UserRequest->id,
                    'current_provider' => null,
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
            
            $MyRequests = UserRequests::UserRequestStatusCheck(Auth::user()->id, $check_status)
                ->get();

            if($MyRequests->count() > 0){
                $requests = $MyRequests[0]->filter->where('offer_price','!=',0);
                $driver_requests = [];
                foreach($requests as $request){
                    $driver_requests[] = $request->load('provider');
                }
            }

            if (Setting::get('manual_request', 0) == 0) {
                $Timeout = Setting::get('provider_select_timeout', 180);
                if (!empty($driver_requests)) {
                    foreach($driver_requests as $IncomingRequest){
                        $IncomingRequest->time_left_to_respond = $Timeout - (time() - strtotime($IncomingRequest->updated_at));
                        if ($IncomingRequest->time_left_to_respond < 0) {
                            $this->assign_destroy($IncomingRequest->id);
                        }
                    }
                }
            }

            Log::info('total request count' . $UserRequestsFilter->count());
            foreach($UserRequestsFilter as $Urequest){
                Log::info('request filter count per request' . $Urequest->filter->count());
                $Timeout = 120;
                $Urequest->time_left_to_respond = $Timeout - (time() - strtotime($Urequest->updated_at));
                if ($Urequest->status == 'SEARCHING' && $Urequest->time_left_to_respond < 0 && $driver_requests === []) {
                    $Providertrip = new TripController();
                    $Providertrip->cancel_request($Urequest->id);
                }
            }

            return response()->json([
                'data' => $UserRequests, 'sos' => Setting::get('sos_number', '911'),
                'requests'=>$driver_requests ?? null,
                'cash' => (int)Setting::get('CASH', 1), 'card' => (int)Setting::get('CARD', 0),
                'currency' => Setting::get('currency', '$')
            ]);
        } catch (Exception $e) {
            Log::info("RequestStatusCheck: in Exception Catch: with message: ". $e->getMessage());
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function assign_destroy($id)
    {
        // Log::info("AssignDestroy: RequestId: $id");
        $UserRequest = UserRequests::find($id);
        try {
            // UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED', 'cancel_reason' => 'Assign Destroy']);
            // No longer need request specific rows from RequestMeta
            RequestFilter::whereId($id)->update(['offer_price'=>0]);
            //  request push to user provider not available
            // (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }

    private function getProviders($latitude, $longitude, $distance, $service_type)
    {
        $Providers = Provider::with('service')
            ->select(
                DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),
                'id'
            )
            ->where('status', 'approved')
            ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
            ->whereHas('service', function ($query) use ($service_type) {
                $query->where('status', 'active');
                if ((int) $service_type !== ServiceType::BOOSTER_CABLE_SERVICE_ID) {
                    Log::info("UserApiController send request: Except Booster and service type: $service_type");
                    $query->where('service_type_id', $service_type);
                } else {
                    Log::info('UserApiController send request: For Booster');
                }
            })
            ->orderBy('distance', 'asc')
            ->get();
        return $Providers;
    }

    public function accept(Request $request, $id)
    {
        try {

            $UserRequest = UserRequests::with('user')->findOrFail($id);

            if ($UserRequest->status != "SEARCHING") {
                return response()->json(['error' => trans('api.ride.request_inprogress')]);
            }

            $offer_id = RequestFilter::whereId($request->offer_id)->first();

            $UserRequest->provider_id = $offer_id->provider_id;

            if (Setting::get('broadcast_request', 0) == 1) {
                $UserRequest->current_provider_id = $offer_id->provider_id;
            }

            if ($UserRequest->schedule_at != "") {

                $beforeschedule_time = strtotime($UserRequest->schedule_at . "- 1 hour");
                $afterschedule_time = strtotime($UserRequest->schedule_at . "+ 1 hour");

                $CheckScheduling = UserRequests::where('status', 'SCHEDULED')
                    ->where('provider_id', $offer_id->provider_id)
                    ->whereBetween('schedule_at', [$beforeschedule_time, $afterschedule_time])
                    ->count();

                if ($CheckScheduling > 0) {
                    if ($request->ajax()) {
                        return response()->json(['error' => trans('api.ride.request_already_scheduled')]);
                    } else {
                        return redirect('dashboard')->with('flash_error', trans('api.ride.request_already_scheduled'));
                    }
                }

                RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', $offer_id->provider_id)->update(['status' => 2]);

                $UserRequest->status = "SCHEDULED";
                $UserRequest->save();
            } else {

                $UserRequest->status = "STARTED";
                $UserRequest->save();

                ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'riding']);

                // $Filters = RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', '!=', Auth::user()->id)->get();
                // // dd($Filters->toArray());
                // foreach ($Filters as $Filter) {
                //     Log::info("TripAccept: DeletedRequestFilter Loop ProviderId: $Filter->provider_id  auth providerId: " . Auth::id());
                //     $Filter->delete();
                // }
            }

            // $UnwantedRequest = RequestFilter::where('request_id', '!=', $UserRequest->id)
            //     ->where('provider_id', Auth::user()->id)
            //     ->whereHas('request', function ($query) {
            //         $query->where('status', '<>', 'SCHEDULED');
            //     });

            // if ($UnwantedRequest->count() > 0) {
            //     $UnwantedRequest->delete();
            // }

            // Send Push Notification to User
            (new SendPushNotification)->RideAccepted($UserRequest);

            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }

    public function reject(Request $request, $id){
        try {
            $UserRequest = UserRequests::with('user')->whereId($id)->first();
            $offer = RequestFilter::whereId($request->offer_id)->first();
            $offer->update(['offer_price'=>0]);
            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }
}
