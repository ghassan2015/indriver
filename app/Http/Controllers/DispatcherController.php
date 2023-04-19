<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Log;
use Setting;
use Auth;
use Exception;
use Carbon\Carbon;
use App\Helpers\Helper;

use App\User;
use App\Dispatcher;
use App\Document;
use App\Provider;
use App\UserRequests;
use App\RequestFilter;
use App\ProviderService;
use App\UserRequestStop;
use Illuminate\Support\Facades\DB;

class DispatcherController extends Controller
{

    protected $perpage ;
    public function __construct(){
        $this->middleware('demo', ['only' => ['profile_update', 'password_update']]);
        $this->perpage = Setting::get('per_page', '15');
    }

    public function index()
    {
        if(Auth::guard('admin')->user()){
            return view('admin.dispatcher');
        }elseif(Auth::guard('dispatcher')->user()){
            return view('dispatcher.dispatcher');
        }
    }

    public function trips(Request $request)
    {
         
        $Trips = UserRequests::with('user', 'provider', 'provider.service')
                    ->orderBy('id','desc');

        if($request->has('type')){
            $Trips = $Trips->where('status',$request->type);
        }

            // if($request->type == "SEARCHING"){
        //     $Trips = $Trips->where('status',$request->type)->orwhere('status','SCHEDULES')->orwhere('status','SCHEDULED');
        // }else if($request->type == "CANCELLED"){
        //     $Trips = $Trips->where('status',$request->type);
        // }
        
        $Trips =  $Trips->paginate(10);

        return $Trips;
    }

    public function tripLatLong(Request $request, $id){

        $distance = 5;
        $estimated_fare = 50;
        $estimated_time = 5;
        $time_in = 'minutes';
        $source_lati = $request['source']['lati'];
        $source_long = $request['source']['long'];
        $destination_lati = $request['destination']['lati'];
        $destination_long = $request['destination']['long'];


        $details = "https://maps.googleapis.com/maps/api/directions/json?origin=" .
                    "$source_lati,$source_long&destination=$destination_lati,$destination_long&mode=driving&key=" .
                    Setting::get('map_key');

        $json = curl($details);

        $googleMapResponse = json_decode($json, TRUE);
        $googleMapDistanceInMeter = $googleMapResponse['routes'][0]['legs'][0]['distance']['value'];
        $googleMapDurationInMinutes = $googleMapResponse['routes'][0]['legs'][0]['duration']['value'];
        $distance = $googleMapDistanceInMeter ? $googleMapDistanceInMeter / 1000 : 0;
        $estimated_time = $googleMapDurationInMinutes ? $googleMapDurationInMinutes / 60 : 0;

        // $route_key = $details['routes'][0]['overview_polyline']['points'];
        return response()->json(['success' => true, 'distance' => $distance, 
                                 'estimated_fare' => $estimated_fare, 
                                 'estimated_time' => $estimated_time, 
                                 'time_in' => $time_in ]);
    }

    public function users(Request $request)
    {
        $Users = new User;

        if($request->has('mobile')) {
            $Users->where('mobile', 'like', $request->mobile."%");
        }

        if($request->has('first_name')) {
            $Users->where('first_name', 'like', $request->first_name."%");
        }

        if($request->has('last_name')) {
            $Users->where('last_name', 'like', $request->last_name."%");
        }

        if($request->has('email')) {
            $Users->where('email', 'like', $request->email."%");
        }

        return $Users->paginate(10);
    }

    /**
     * Display a listing of the active trips in the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function providers(Request $request)
    {
        $Providers = new Provider;

        if($request->has('latitude') && $request->has('longitude')) {
            $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)
                    ->get()
                    ->pluck('provider_id');

            $distance = Setting::get('provider_search_radius', '10');
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            $Providers = Provider::whereIn('id', $ActiveProviders)
                ->where('status', 'approved')
                ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                ->with('service', 'service.service_type')
                ->get();

            return $Providers;
        }

        return $Providers;
    }

    /**
     * Create manual request.
     *
     * @return \Illuminate\Http\Response
     */
    public function assign($request_id, $provider_id)
    {
        try {
            $Request = UserRequests::findOrFail($request_id);
            $Provider = Provider::findOrFail($provider_id);

            $Request->provider_id = $Provider->id;

            if($Request->status =='SCHEDULES' || $Request->status =='SCHEDULED')
            {  }
            else
            {
                $Request->status = 'STARTED';
            }
            // $Request->status = 'STARTED';
            $Request->current_provider_id = $Provider->id;
            $Request->save();

            ProviderService::where('provider_id',$Request->provider_id)->update(['status' =>'riding']);

            (new SendPushNotification)->IncomingRequest($Request->current_provider_id);

            try {
                RequestFilter::where('request_id', $Request->id)
                    ->where('provider_id', $Provider->id)
                    ->firstOrFail();
            } catch (Exception $e) {
                $Filter = new RequestFilter;
                $Filter->request_id = $Request->id;
                $Filter->provider_id = $Provider->id; 
                $Filter->status = 0;
                $Filter->save();
            }

            if(Auth::guard('admin')->user()){
                return redirect()
                        ->route('admin.dispatcher.index')
                        ->with('flash_success', trans('admin.dispatcher_msgs.request_assigned'));

            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()
                        ->route('dispatcher.index')
                        ->with('flash_success', trans('admin.dispatcher_msgs.request_assigned'));

            }

        } catch (Exception $e) {
            if(Auth::guard('admin')->user()){
                return redirect()->route('admin.dispatcher.index')->with('flash_error', trans('api.something_went_wrong'));
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()->route('dispatcher.index')->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    public function store(Request $request) {
        $this->validate($request, [
                's_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
                'd_latitude' => 'required|numeric',
                'd_longitude' => 'required|numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                'distance' => 'required|numeric',
            ]);

        try {
            $User = User::where('mobile', $request->mobile)->firstOrFail();
            $User->first_name = $request->first_name;
            $User->first_name = $request->last_name;
            $User->save();
        } catch (Exception $e) {
            try {
                $User = User::where('email', $request->email)->firstOrFail();
                $User->first_name = $request->first_name;
                $User->first_name = $request->last_name;
                $User->save();
            } catch (Exception $e) {
                $User = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'mobile' => $request->mobile,
                    'password' => bcrypt($request->mobile),
                    'payment_mode' => 'CASH',
                    'hit_from' => 'DispatcherController.store'
                     ]);
            }
        }

        if($request->has('schedule_time')){
            try {
                $CheckScheduling = UserRequests::whereIn('status', ['SCHEDULES','SCHEDULED'])
                        ->where('user_id', $User->id)
                        ->where('schedule_at', '>', strtotime($request->schedule_time." - 1 hour"))
                        ->where('schedule_at', '<', strtotime($request->schedule_time." + 1 hour"))
                        ->firstOrFail();
                
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_scheduled')], 500);
                } else {
                    return redirect('dashboard')->with('flash_error', trans('api.ride.request_scheduled'));
                }

            } catch (Exception $e) {
                // Do Nothing
            }
        }

        try{

            $details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$request->s_latitude.",".$request->s_longitude."&destination=".$request->d_latitude.",".$request->d_longitude."&mode=driving&key=".Setting::get('map_key');

            $json = curl($details);

            $details = json_decode($json, TRUE);

            $route_key = $details['routes'][0]['overview_polyline']['points'];

            $UserRequest = new UserRequests;
            $UserRequest->booking_id = Helper::generate_booking_id();
            $UserRequest->user_id = $User->id;
            $UserRequest->current_provider_id = 0;
            $UserRequest->service_type_id = $request->service_type;
            $UserRequest->payment_mode = 'CASH';
            $UserRequest->promocode_id = 0;
            $UserRequest->request_type = $UserRequest->REQEST_TYPE_DISPATCH;
            $UserRequest->dispatcher_payments = ['eta' => $request['eta'], 
                                                 'discount' => $request['discount'] ,
                                                 'extraAmount' => $request['extraAmount'] ,
                                                ];

            // $UserRequest->status = 'SEARCHING';

            if($request->has('schedule_time')){
                if($request->has('provider_auto_assign'))
                    $UserRequest->status = 'SCHEDULES'; 
                else
                    $UserRequest->status = 'SCHEDULED';  
            }
            else {
                $UserRequest->assigned_at = Carbon::now();
                $UserRequest->status = 'SEARCHING'; 
            }

            $UserRequest->s_address = $request->s_address ? : "";
            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            $UserRequest->d_address = $request->d_address ? : "";
            $UserRequest->d_latitude = $request->d_latitude;
            $UserRequest->d_longitude = $request->d_longitude;
            $UserRequest->route_key = $route_key;

            $UserRequest->distance = $request->distance;

            // $UserRequest->assigned_at = Carbon::now();

            $UserRequest->use_wallet = 0;
            $UserRequest->surge = 0;        // Surge is not necessary while adding a manual dispatch

            if($request->has('schedule_time')) {
                $UserRequest->schedule_at = Carbon::parse($request->schedule_time);
            }

            $UserRequest->save();

            if($UserRequest){
                    $newStop = new UserRequestStop();
                    $newStop->user_request_id = $UserRequest->id;
                    $newStop->d_address = $request['d_address'];
                    $newStop->d_latitude = $request['d_latitude'];
                    $newStop->d_longitude = $request['d_longitude'];
                    $newStop->order = 1;
                    $newStop->save();
            }

            if($request->has('provider_auto_assign')) {
                $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)
                        ->get()
                        ->pluck('provider_id');

                $distance = Setting::get('provider_search_radius', '10');
                $latitude = $request->s_latitude;
                $longitude = $request->s_longitude;

                $Providers = Provider::whereIn('id', $ActiveProviders)
                    ->where('status', 'approved')
                    ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                    ->get();

                // List Providers who are currently busy and add them to the filter list.

                if(count($Providers) == 0) {
                    if($request->ajax()) {
                        // Push Notification to User
                        return response()->json(['message' => trans('api.ride.no_providers_found')]); 
                    } else {
                        return back()->with('flash_success', trans('api.ride.no_providers_found'));
                    }
                }

                $Providers[0]->service()->update(['status' => 'riding']);

                $UserRequest->current_provider_id = $Providers[0]->id;
                $UserRequest->save();

                Log::info('New Dispatch : ' . $UserRequest->id);
                Log::info('Assigned Provider : ' . $UserRequest->current_provider_id);

                // Incoming request push to provider
                (new SendPushNotification)->IncomingRequest($UserRequest->current_provider_id);

                foreach ($Providers as $key => $Provider) {
                    $Filter = new RequestFilter;
                    $Filter->request_id = $UserRequest->id;
                    $Filter->provider_id = $Provider->id; 
                    $Filter->save();
                }
            }

            if($request->ajax()) {
                return $UserRequest;
            } else {
                return redirect('dashboard');
            }

        } catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong'), 'message' => $e], 500);
            }else{
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }


    public function profile()
    {
        return view('dispatcher.account.profile');
    }

    public function profile_update(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'mobile' => 'required|digits_between:6,13',
        ]);

        try{
            $dispatcher = Auth::guard('dispatcher')->user();
            $dispatcher->name = $request->name;
            $dispatcher->mobile = $request->mobile;
            $dispatcher->language = $request->language;
            $dispatcher->save();

            return redirect()->back()->with('flash_success', trans('admin.profile_update'));
        }

        catch (Exception $e) {
             return back()->with('flash_error', trans('api.something_went_wrong'));
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password()
    {
        return view('dispatcher.account.change-password');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password_update(Request $request)
    {
        $this->validate($request,[
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        try {

           $Dispatcher = Dispatcher::find(Auth::guard('dispatcher')->user()->id);

            if(password_verify($request->old_password, $Dispatcher->password))
            {
                $Dispatcher->password = bcrypt($request->password);
                $Dispatcher->save();

                return redirect()->back()->with('flash_success', trans('admin.password_update'));
            }
        } catch (Exception $e) {
             return back()->with('flash_error', trans('api.something_went_wrong'));
        }
    }



    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function cancel(Request $request) {

        $this->validate($request, [
            'request_id' => 'required|numeric|exists:user_requests,id',
        ]);

        try{

            $UserRequest = UserRequests::findOrFail($request->request_id);

            if($UserRequest->status == 'CANCELLED')
            {
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_cancelled')], 500); 
                }else{
                    return back()->with('flash_error', trans('api.ride.already_cancelled'));
                }
            }

            if(in_array($UserRequest->status, ['SEARCHING','STARTED','ARRIVED','SCHEDULED','SCHEDULES'])) {


                $UserRequest->status = 'CANCELLED';
                $UserRequest->cancel_reason = "Cancelled by Admin";
                $UserRequest->cancelled_by = 'NONE';
                $UserRequest->save();

                RequestFilter::where('request_id', $UserRequest->id)->delete();

                if($UserRequest->status != 'SCHEDULED' || $UserRequest->status != 'SCHEDULES'){

                    if($UserRequest->provider_id != 0){

                        ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' => 'active']);

                    }
                }

                 // Send Push Notification to User
                (new SendPushNotification)->UserCancellRide($UserRequest);
                (new SendPushNotification)->ProviderCancellRide($UserRequest);

                if($request->ajax()) {
                    return response()->json(['message' => trans('api.ride.ride_cancelled')]); 
                }else{
                    return back()->with('flash_success', trans('api.ride.ride_cancelled'));
                }

            } else {
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_onride')], 500); 
                }else{
                    return back()->with('flash_error', trans('api.ride.already_onride'));
                }
            }
        }

        catch (ModelNotFoundException $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }else{
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }

    }

    public function providerStatus($status=null){

        $allProviders = Provider::with('service')->whereHas('service');
        $providerCounts = DB::select("select s.status, count(*) total from providers p, provider_services s where p.id = s.provider_id and p.id in (select provider_id from provider_services) group by 1");
        // dd(collect($providerCounts)->where('status', 'active')->first()->total);
        $statuses = ProviderService::pluck('status')->unique();
        if(isset($status)){
            $status = strtolower($status); 
            $allProviders->whereHas('service', function ($query) use($status){
                $query->where('status', $status);
            });
        }
        $providers = $allProviders->orderBy('id', 'DESC')->paginate($this->perpage);
    
        $pagination=(new Helper)->formatPagination($providers);
    
        $url = $providers->url($providers->currentPage());
            
        request()->session()->put('providerpage', $url);
        $params = ['providers' => $providers, 'pagination' => $pagination, 'statuses' => $statuses,
                    'providerCounts' => collect($providerCounts) ];
                    
        return view('admin.dispatcher.provider-status')->with( $params );
        
    }
    
    public function map_index()
    {
        return view('admin.dispatcher.map');
    }

    public function map_ajax()
    {
        try {

            $Providers = Provider::where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->with('service')
                ->get();

            $Users = User::where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->get();

            for ($i = 0; $i < sizeof($Users); $i++) {
                $Users[$i]->status = 'user';
            }

            $All = $Users->merge($Providers);

            return $All;

        } catch (Exception $e) {
            return [];
        }
    }

    private function prepareProvidersQuery(){
	    $allProviders = Provider::with('service','accepted','cancelled');
	
	    if(request()->has('fleet')){
		    $allProviders->where('fleet',request()->fleet);
	    }
	    if(request()->has('first-name')){
		    $allProviders->where('first_name', 'like', '%' . request('first-name') . '%');
	    }
	    if(request()->has('last-name')){
		    $allProviders->where('last_name', 'like', '%' . request('last-name') . '%');
	    }
	    if(request()->has('mobile-no')){
		    $allProviders->where('mobile', 'like', '%' . request('mobile-no') . '%');
	    }

	    if(request()->has('cab-no')){
		    $allProviders->whereHas('service', function($query) {
		    	$query->where('service_number',  'like', '%' . request('cab-no') . '%');
		    });
	    }
	    $providers = $allProviders->orderBy('id', 'DESC')->paginate($this->perpage);
	    $total_documents=Document::where('status',1)->count();
	
	    $pagination=(new Helper)->formatPagination($providers);
	
	    $url = $providers->url($providers->currentPage());
			
	    request()->session()->put('providerpage', $url);
	    return ['providers' => $providers, 'pagination' => $pagination, 'total_documents' => $total_documents ];
    }
    
    public function providerSearchFilters(){
	    $params = $this->prepareProvidersQuery();
	    request()->flash();
	    return view('admin.dispatcher.provider-search')->with( $params );
    }
}
