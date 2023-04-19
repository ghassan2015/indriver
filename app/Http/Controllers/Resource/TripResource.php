<?php

namespace App\Http\Controllers\Resource;

use App\Settings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\UserRequests;
use App\User;
use App\Provider;
use App\ServiceType;
use App\Helpers\Helper;
use Auth;
use Setting;

class TripResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['destroy']]);
        $this->middleware('combo');
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
            if (@$_GET['corporate']) {
                $requests = UserRequests::with(['user' => function ($query) {
                    return $query->where('corporate_id', $_GET['corporate']);
                }, 'payment', 'provider'])->where('payment_mode', 'CORPORATE_ACCOUNT')
                    ->whereHas('user', function ($q) {
                        return $q->where('corporate_id', $_GET['corporate']);
                    })
                    ->orderBy('user_requests.created_at', 'desc')->paginate($this->perpage);
            } else {
                $requests = UserRequests::RequestHistory()->paginate($this->perpage);
            }

            $pagination = (new Helper)->formatPagination($requests);
            return view('admin.request.index', compact('requests', 'pagination'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function Fleetindex()
    {
        try {
            $requests = UserRequests::RequestHistory()
                ->whereHas('provider', function ($query) {
                    $query->where('fleet', Auth::user()->id);
                })->get();
            return view('fleet.request.index', compact('requests'));
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
        try {
            $requests = UserRequests::where('status', 'SCHEDULED')
                ->RequestHistory()
                ->get();

            return view('admin.request.scheduled', compact('requests'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function Fleetscheduled()
    {
        try {
            $requests = UserRequests::where('status', 'SCHEDULED')
                ->whereHas('provider', function ($query) {
                    $query->where('fleet', Auth::user()->id);
                })
                ->get();

            return view('fleet.request.scheduled', compact('requests'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 
    }

    public function show($id)
    {
	    try {
            $setting = Settings::where('key',Settings::RATE_TYPE)->first();
            $request = UserRequests::with('service_type', 'stops')->findOrFail($id);
            return view('admin.request.show', compact('request' , 'setting'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function cancel_refund($id)
    {
        try {
            $UserRequest = UserRequests::findOrFail($id);

            $ServiceType = ServiceType::find($UserRequest->service_type_id);
            $cancellation_charges = $ServiceType->cancellation_charges;

            if($UserRequest->cancelled_by == 'USER'){
                $User = User::find($UserRequest->user_id);
                $User->wallet_balance = $User->wallet_balance + $cancellation_charges;
                $User->save();
            }else if($UserRequest->cancelled_by == 'PROVIDER'){
                $Provider = Provider::find($UserRequest->provider_id);
                $Provider->wallet_balance = $Provider->wallet_balance + $cancellation_charges;
                $Provider->save();
            }
            return back()->with('flash_success', 'Successfully Refunded.');
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }


    public function Fleetshow($id)
    {
        try {
            $request = UserRequests::findOrFail($id);
            return view('fleet.request.show', compact('request'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function Accountshow($id)
    {
        try {
            $request = UserRequests::findOrFail($id);
            return view('account.request.show', compact('request'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $Request = UserRequests::findOrFail($id);
            $Request->delete();
            return back()->with('flash_success', trans('admin.request_delete'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function Fleetdestroy($id)
    {
        try {
            $Request = UserRequests::findOrFail($id);
            $Request->delete();
            return back()->with('flash_success', trans('admin.request_delete'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }
}
