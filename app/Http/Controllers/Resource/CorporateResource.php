<?php

namespace App\Http\Controllers\Resource;

use App\Corporate;
use App\CorporateRechargeHistory;
use App\UserRequests;
use App\User;
use App\CorporateUsers;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Setting;
use Auth;

class CorporateResource extends Controller
{
     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $corporates = Corporate::orderBy('created_at' , 'desc')->get();

        return view('admin.corporate.index', compact('corporates'));
               
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.corporate.create');
             
        
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
            'name' => 'required|max:255',
            'company' => 'required|max:255',
            'email' => 'required|unique:corporates,email|email|max:255',
            'mobile' => 'digits_between:6,13',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

            $corporate = $request->all();
            $corporate['password'] = bcrypt($request->password);
            if($request->hasFile('logo')) 
                $corporate['logo'] = $request->logo->store('corporate'); 

            $corporate = Corporate::create($corporate);

            return back()->with('flash_success','Corporate Details Saved Successfully');

        } 

        catch (Exception $e) {  
            return back()->with('flash_error', 'Corporate Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\corporate  $corporate
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\corporate  $corporate
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $corporate = Corporate::findOrFail($id);

            return view('admin.corporate.edit',compact('corporate'));
             
            
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\corporate  $corporate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $this->validate($request, [
            'name' => 'required|max:255',
            'company' => 'required|max:255',
            'mobile' => 'digits_between:6,13',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {

            $corporate = Corporate::findOrFail($id);

            if($request->hasFile('logo')) {
                \Storage::delete($corporate->logo);
                $corporate->logo = $request->logo->store('corporate');
            }
            // if($request->recharge_option == 'PREPAID') 
            //     $corporate->wallet_balance += $request->deposit_amount; 
            // $corporate->deposit_amount = $request->deposit_amount;
            // $corporate->limit_amount = 0;
            // $corporate->recharge_option = 0;
            $corporate->name = $request->name;
            $corporate->company = $request->company;
            $corporate->mobile = $request->mobile;
            // $corporate->pin = $request->pin;
            $corporate->save();

            if(Auth::guard('admin')->user()){
                return redirect()->route('admin.corporate.index')->with('flash_success', 'Corporate Updated Successfully'); 
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()->route('dispatcher.corporate.index')->with('flash_success', 'Corporate Updated Successfully'); 
            } 
            
               
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Corporate Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\corporate  $corporate
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        try {
            
            Corporate::find($id)->delete();

            $users =User::wherecorporate_id($id)->get();

            foreach ($users as $key => $value) {
                 $update = User::find($value->id);
                 $update->corporate_id = 0;
                 $update->company_id = 0;
                 $update->company_name = null;
                 $update->emp_id = null;
                 $update->corp_deleted = 0;
                 $update->save();
            }

            CorporateUsers::wherecorporate_id($id)->delete();


            return back()->with('message', 'Corporate deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'Corporate Not Found');
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\corporate  $Clear Postpaid payment
     * @return \Illuminate\Http\Response
     */
    public function clear_postpaid_payment(Request $request)
    {
        $user_request_ids = json_decode($request->user_request_ids);

        foreach ($user_request_ids as $key => $value) {
            $update = UserRequests::findOrFail($value);
            $update->postpaid_payment_status = 'PAID';
            $update->save();
        }

            return back()->with('flash_success', 'Postpaid payment cleared'); 
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\corporate  $Clear Postpaid payment
     * @return \Illuminate\Http\Response
     */
    public function transaction_history(Request $request)
    {
        $tranasction_history = CorporateRechargeHistory::wherecorporate_id($request->corporate)->get(); 

        return view('admin.corporate.transaction_history',compact('tranasction_history'));
    }
}
