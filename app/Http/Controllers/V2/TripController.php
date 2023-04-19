<?php

namespace App\Http\Controllers\V2;

use App\RequestFilter;
use App\UserRequests;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SendPushNotification;
use Exception;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class TripController extends Controller
{
    public function index(Request $request)
    {
        // Log::info('TripIndex: with params', $request->all());
        try {
            if (!Auth::guard('provider')->check()) {
                //Log::info("TripIndex: ajax authId: ". auth()->id());
                $Provider = Auth::user();
            } else {
                //Log::info("TripIndex: guard-provider authId: ". auth()->guard$
                $Provider = Auth::guard('provider')->user();
            }
            $providerId = $Provider->id;
            // Log::info("TripIndex: authId: " . $Provider->id);

            $AfterAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request', 'request.service_type', 'request.stops'])
                ->where('provider_id', $providerId)
                ->whereHas('request', function ($query) use ($providerId) {
                    $query->where('status', '<>', 'CANCELLED');
                    $query->where('status', '<>', 'SCHEDULED');
                    $query->where('status', '<>', 'SCHEDULES');
                    // $query->where('provider_id', $providerId );
                    $query->where('current_provider_id', $providerId);
                });
            //$logAfterAssignProvider = $AfterAssignProvider;
            //Log::info('TripIndex: AfterAssignProvider total records: ' . $logAfterAssignProvider->count());
            $BeforeAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request', 'request.service_type', 'request.stops'])
                ->where('provider_id', $providerId)
                ->where('offer_price',0)
                ->whereHas('request', function ($query) use ($providerId) {
                    $query->where('status', '<>', 'CANCELLED');
                    $query->where('status', '<>', 'SCHEDULED');
                    $query->where('status', '<>', 'SCHEDULES');
                    $query->when(Setting::get('broadcast_request') == 1, function ($q) {
                        $q->where('current_provider_id', null);
                    });
                    $query->when(Setting::get('broadcast_request') == 0, function ($q) use ($providerId) {
                        $q->where('current_provider_id', null);
                    });
                });
            //$logBeforeAssignProvider = $BeforeAssignProvider;
            //Log::info('TripIndex: BeforeAssignProvider total records: ' . $logBeforeAssignProvider->count());

            $IncomingRequests = $BeforeAssignProvider->union($AfterAssignProvider)->get();

            if (!empty($request->latitude)) {
                // Log::info('TripIndex: Provider long/lati updated in its table: ' . $Provider->id);
                $Provider->update([
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);

                if (count($IncomingRequests) != 0) {
                    foreach($IncomingRequests as $myRequest){
                        $request_id = $myRequest->request_id;
                        //Log::info('TripIndex: RequestId: ' . $request_id);
                        $UserRequest = UserRequests::whereid($request_id)->first();

                        $details = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" .
                            $request->latitude . "," . $request->longitude .
                            "&destinations=" . $UserRequest->s_latitude . "," . $UserRequest->s_longitude .
                            "&mode=driving&sensor=false&key=" . Setting::get('map_key');
                        Log::error("TripIndex: GoogleMap Url: " . $details);
                        $json = curl($details);

                        $details = json_decode($json, true);
                        $meter = $details['rows'][0]['elements'][0]['distance']['value'];
                        if ($meter <= 500) {
                            UserRequests::whereid($request_id)->update(['reach_radius' => 1]);
                        }
                    }
                }
            }

            if (Setting::get('manual_request', 0) == 0) {
                $Timeout = Setting::get('provider_select_timeout', 180);
                if (!empty($IncomingRequests)) {
                    foreach($IncomingRequests as $IncomingRequest){
                        $IncomingRequest->time_left_to_respond = $Timeout - (time() - strtotime($IncomingRequest->updated_at));
                        if ($IncomingRequest->request->status == 'SEARCHING' && $IncomingRequest->time_left_to_respond < 0) {
                             $this->assign_destroy($IncomingRequest->request->id);
                            // if (Setting::get('broadcast_request', 0) == 1) {
                            //     //Log::info('Trip: index: broadcast request 1');
                            //     //Log::info('');
                            //     // $this->assign_destroy($IncomingRequest->request->id);
                            // } else {
                            //     //Log::info('Tri: index: broad case request 0');
                            //     $this->assign_next_provider($IncomingRequest->request->id);
                            // }
                        }
                    }
                }
            }

            $Response = [
                'account_status' => $Provider->status,
                'service_status' => $Provider->service ? Auth::user()->service->status : 'offline',
                'user_negative_wallet_limit' => Setting::get('user_negative_wallet_limit'), // After Changes
                'requests' => $IncomingRequests,
                'provider_details' => $Provider,
            ];
            return $Response;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    public function quote(Request $request){
        try{
            $UserRequest = UserRequests::whereId($request->request_id)->first();  
            if($UserRequest->filter){
                $filterIds = $UserRequest->filter->where('provider_id',FacadesAuth::user()->id);
            }
            foreach($filterIds as $filter){
                $filter->update(['offer_price'=>$request->offer_price]);
            }
            return response()->json(['message' => trans('api.offer_sent')]);
        }catch(Exception $ex){
            return response()->json(['error' => trans('api.offer_sent_error')]);
        }
    }

    public function assign_destroy($id)
    {
        // Log::info("AssignDestroy: RequestId: $id");
        $UserRequest = UserRequests::find($id);
        try {
            // UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED', 'cancel_reason' => 'Assign Destroy']);
            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->where('provider_id',FacadesAuth::user()->id)->delete();
            //  request push to user provider not available
            // (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }

    public function cancel_request($id)
    {
        // Log::info("AssignDestroy: RequestId: $id");
        $UserRequest = UserRequests::find($id);
        try {
            UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED', 'cancel_reason' => 'Assign Destroy']);
            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();
            //  request push to user provider not available
            (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }
}