<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use DB;
use Exception;
use Setting;
use Storage;

use App\Provider;
use App\UserRequestPayment;
use App\UserRequests;
use App\Helpers\Helper;
use App\Document;
use App\Http\Controllers\SendPushNotification;

class ProviderResource extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'store', 'update', 'destroy', 'disapprove']]);
        $this->perpage = Setting::get('per_page', '10');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(!empty($request->page) && $request->page=='all'){
            $allProviders = Provider::with('service','accepted','cancelled');
            if(request()->has('fleet')){
                $providers = $allProviders->where('fleet',$request->fleet)->get();
            }
                $providers = $allProviders->orderBy('id', 'asc')->get();

            return response()->json(array('success' => true, 'data'=>$providers));
        }else{
	        //$allProviders = Provider::with('service','accepted','cancelled');
	        //
	        //if(request()->has('fleet')){
           //     $providers = $allProviders->where('fleet',$request->fleet)->paginate($this->perpage);
           // }
           //   $providers = $allProviders->orderBy('id', 'DESC')->paginate($this->perpage);
	        //
           // $total_documents=Document::where('status',1)->count();
           //
           // $pagination=(new Helper)->formatPagination($providers);
	        //
           // $url = $providers->url($providers->currentPage());
	        //
           // $request->session()->put('providerpage', $url);
	        
	        $params = $this->prepareProvidersQuery();
                        
            return view('admin.providers.index')->with( $params );
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
	    return view('admin.providers.index')->with( $params );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.providers.create');
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
            'email' => 'required|unique:providers,email|email|max:255',
            'mobile' => 'digits_between:6,13',
            'avatar' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

            $provider = $request->all();

            $provider['password'] = bcrypt($request->password);
            if($request->hasFile('avatar')) {
                $provider['avatar'] = $request->avatar->store('provider/profile');
            }

            $provider = Provider::create($provider);

            return back()->with('flash_success', trans('admin.provider_msgs.provider_saved'));

        } 

        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.provider_msgs.provider_not_found'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $provider = Provider::findOrFail($id);
            return view('admin.providers.provider-details', compact('provider'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $provider = Provider::findOrFail($id);
            return view('admin.providers.edit',compact('provider'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'mobile' => 'digits_between:6,13',
            'avatar' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {

            $provider = Provider::findOrFail($id);

            if($request->hasFile('avatar')) {
                if($provider->avatar) {
                    Storage::delete($provider->avatar);
                }
                $provider->avatar = $request->avatar->store('provider/profile');                    
            }

            $provider->first_name = $request->first_name;
            $provider->last_name = $request->last_name;
            $provider->mobile = $request->mobile;
            $provider->save();

            return redirect()->route('admin.provider.index')->with('flash_success', trans('admin.provider_msgs.provider_update'));    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.provider_msgs.provider_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        try {
            Provider::find($id)->delete();
            return back()->with('message', trans('admin.provider_msgs.provider_delete'));
        } 
        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.provider_msgs.provider_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, $id)
    {
        try {            
            $Provider = Provider::findOrFail($id);           
            $total_documents=Document::where('status',1)->count();
            if($Provider->active_documents()>=$total_documents && $Provider->service) {
                if($Provider->status=='onboarding'){
                    // Sending push to the provider
                    (new SendPushNotification)->DocumentsVerfied($id);
                }                
                $Provider->update(['status' => 'approved']);
                $url=$request->session()->pull('providerpage');                
                return redirect()->to($url)->with('flash_success', trans('admin.provider_msgs.provider_approve'));
            } else {
                if($Provider->active_documents()!=$total_documents){
                    $msg=trans('admin.provider_msgs.document_pending');
                }
                if(!$Provider->service){
                    $msg=trans('admin.provider_msgs.service_type_pending');
                }

                if(!$Provider->service && $Provider->active_documents()!=$total_documents){
                    $msg=trans('admin.provider_msgs.provider_pending');
                }
                return redirect()->route('admin.provider.document.index', $id)->with('flash_error',$msg);
            }
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.provider_msgs.provider_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function disapprove($id)
    {
        
        Provider::where('id',$id)->update(['status' => 'banned']);
        return back()->with('flash_success', trans('admin.provider_msgs.provider_disapprove'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request($id){

        try{

            $requests = UserRequests::where('user_requests.provider_id',$id)
                    ->RequestHistory()
                    ->paginate($this->perpage);

            $pagination=(new Helper)->formatPagination($requests);        

            return view('admin.request.index', compact('requests','pagination'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    /**
     * account statements.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement($id){

        try{
            $listname ='';
            //$requests = UserRequests::where('provider_id',$id)
            //            ->where('status','COMPLETED')
            //            ->with('payment')
            //            ->get();

            $rides = UserRequests::where('provider_id',$id)->with('payment')->orderBy('id','desc')->paginate($this->perpage);
            $cancel_rides = UserRequests::where('status','CANCELLED')->where('provider_id',$id)->count();
            $Provider = Provider::find($id);
            $revenue = UserRequestPayment::whereHas('request', function($query) use($id) {
                                    $query->where('provider_id', $id );
                                })->select(\DB::raw(
                                   'SUM(ROUND(provider_pay)) as overall, SUM(ROUND(provider_commission)) as commission' 
                               ))->get();


            $Joined = $Provider->created_at ? '- Joined '.$Provider->created_at->diffForHumans() : '';

            $pagination=(new Helper)->formatPagination($rides);
            $providerId = $Provider->id;
            return view('admin.providers.statement', compact('rides','cancel_rides','revenue','pagination','providerId'))
                        ->with('page',$Provider->first_name."'s Overall Statement ". $Joined)->with('listname',$listname);

        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function Accountstatement($id){

        try{

            $requests = UserRequests::where('provider_id',$id)
                        ->where('status','COMPLETED')
                        ->with('payment')
                        ->get();

            $rides = UserRequests::where('provider_id',$id)->with('payment')->orderBy('id','desc')->paginate($this->perpage);
            $cancel_rides = UserRequests::where('status','CANCELLED')->where('provider_id',$id)->count();
            $Provider = Provider::find($id);
            $revenue = UserRequestPayment::whereHas('request', function($query) use($id) {
                                    $query->where('provider_id', $id );
                                })->select(\DB::raw(
                                   'SUM(ROUND(fixed) + ROUND(distance)) as overall, SUM(ROUND(commision)) as commission' 
                               ))->get();


            $Joined = $Provider->created_at ? '- Joined '.$Provider->created_at->diffForHumans() : '';

            $pagination=(new Helper)->formatPagination($rides);

            return view('account.providers.statement', compact('rides','cancel_rides','revenue','pagination'))
                        ->with('page',$Provider->first_name."'s Overall Statement ". $Joined);

        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }
}
