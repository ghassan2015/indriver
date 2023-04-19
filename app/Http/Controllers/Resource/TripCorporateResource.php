<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\UserRequests;
use App\Helpers\Helper;
use Auth;
use Setting;

class TripCorporateResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['destroy']]);
        $this->perpage = Setting::get('per_page', '10');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {           
           // dd();
            $requests = UserRequests::with(['user'  => function($query){
                return $query->where('corporate_id',Auth::user()->id);
            },'payment','provider'])->where('payment_mode','CORPORATE_ACCOUNT')
            ->whereHas('user' , function($q){
                return $q->where('corporate_id',Auth::user()->id);
            })

            ->orderBy('user_requests.created_at', 'desc')->paginate($this->perpage);
            $pagination=(new Helper)->formatPagination($requests);
            return view('corporate.request.index', compact('requests','pagination'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function scheduled()
    {
        try{
            $requests = UserRequests::where('status' , 'SCHEDULED')
                        ->RequestHistory()
                        ->get();

            return view('corporate.request.scheduled', compact('requests'));
        } catch (Exception $e) {
             return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $request = UserRequests::findOrFail($id);
            return view('corporate.request.show', compact('request'));
        } catch (Exception $e) {
             return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    
 
}
