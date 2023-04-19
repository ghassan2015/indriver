<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Helper;

use Auth;
use Setting;
use Exception;
use Session;

use App\User;
use App\Corporate;
use App\Provider;
use App\UserPayment;
use App\ServiceType;
use App\UserRequests;
use App\ProviderService;
use App\UserRequestRating;
use App\UserRequestPayment; 
use App\WalletPassbook; 
use App\Http\Controllers\SendPushNotification;
use App\Card;
use App\CorporateRechargeHistory;

use Stripe\Charge;
use Stripe\Stripe;
use Stripe\StripeInvalidRequestError;
class CorporateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('corporate');
       
        $this->middleware('demo', ['only' => ['profile_update', 'password_update', 'destory_provider_service']]);
    }


    /**
     * Dashboard.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    { 
        try{

            $user_ids = User::wherecorporate_id(Auth::user()->id)->pluck('id'); 
            $getting_ride = UserRequests::with('user','provider')->whereIn('user_id',$user_ids)->orderBy('id','desc');

            $rides = $getting_ride->get();
            $all_rides = $getting_ride->get()->pluck('id');
            $cancel_rides = UserRequests::where('status','CANCELLED')->whereIn('user_id',$user_ids)->count(); 
            $revenue = UserRequestPayment::whereIn('request_id',$all_rides)->sum('total');
            $users = User::wherecorporate_id(Auth::user()->id)->take(10)->orderBy('rating','desc')->get();

            return view('corporate.dashboard',compact('users','rides','cancel_rides','revenue'));
        }
        catch(Exception $e){
            return redirect()->route('corporate.user.index')->with('flash_error','Something Went Wrong with Dashboard!');
        }
    }

    /**
     * Map of all Users and Drivers.
     *
     * @return \Illuminate\Http\Response
     */
    public function map_index()
    {
        return view('corporate.map.index');
    }

    /**
     * Map of all Users and Drivers.
     *
     * @return \Illuminate\Http\Response
     */
    public function map_ajax()
    {
        try {

            $Providers = Provider::where('latitude', '!=', 0)
                    ->where('longitude', '!=', 0)
                    ->where('corporate', Auth::user()->id)
                    ->with('service')
                    ->get();

            $Users = User::where('latitude', '!=', 0)
                    ->where('longitude', '!=', 0)
                    ->get();

            for ($i=0; $i < sizeof($Users); $i++) { 
                $Users[$i]->status = 'user';
            }

            $All = $Users->merge($Providers);

            return $All;

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return view('corporate.account.profile');
    }

 

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile_update(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'company' => 'required|max:255',
            'mobile' => 'required|digits_between:6,13',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try{
            $corporate = Auth::guard('corporate')->user();
            $corporate->name = $request->name;
            $corporate->mobile = $request->mobile;
            $corporate->company = $request->company;
            if($request->hasFile('logo')){
                $corporate->logo = $request->logo->store('corporate/profile');  
            }
            $corporate->save();

            if($request->ajax()){

                return response()->json(['message' => 'Profile Updated']);

            }else{
                return redirect()->back()->with('flash_success','Profile Updated');
            }

            
        }

        catch (Exception $e) {
            if($request->ajax()){

                return response()->json(['message' => 'Something Went Wrong!'],500);

            }else{

             return back()->with('flash_error','Something Went Wrong!');
            }
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
        return view('corporate.account.change-password');
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

           $corporate = Corporate::find(Auth::guard('corporate')->user()->id);

            if(password_verify($request->old_password, $corporate->password))
            {
                $corporate->password = bcrypt($request->password);
                $corporate->save();

                return redirect()->back()->with('flash_success','Password Updated');
            } else {
                return back()->with('flash_error','Password entered doesn\'t match');
            }
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }  

    public function user_wallet_recharge(Request $request)
    {
        $users = User::all();
        if($request->wallet_amount)
        {
            if(0<$request->wallet_amount)
            {
                foreach ($users as $key => $value) {
                    
                    $user = User::wherecorporate_id(Auth::user()->id)->whereid($value->id)->first();
                    if($user)
                    {
                        WalletPassbook::create([
                            'user_id' => $value->id,
                            'amount' => $request->wallet_amount,
                            'status' => 'CREDITED',
                            'via' => 'Recharged By Admin',
                          ]); 
                        $user->wallet_balance += $request->wallet_amount;
                        $user->save();
                        $type = 0;
                        (new SendPushNotification)->sendPushToUser($value->id, 'Tecapptest recharged amount is Rs.'.$request->wallet_amount, $type);
                    }
                }
                return back()->with('flash_success','Amount Recharged for Users'); 
            }
            else
            {
                foreach ($users as $key => $value) {
                    
                    $user = User::wherecorporate_id(Auth::user()->id)->whereid($value->id)->first();
                    if($user)
                    {
                        WalletPassbook::create([
                            'user_id' => $value->id,
                            'amount' => abs($request->wallet_amount),
                            'status' => 'DEBITED',
                            'via' => 'Debited From Admin',
                          ]); 
                        $user->wallet_balance += $request->wallet_amount;
                        $user->save();
                        $type = 0;
                        (new SendPushNotification)->sendPushToUser($value->id, 'Tecapptest debited amount is Rs.'.$request->wallet_amount, $type);
                    }
                }
                return back()->with('flash_success','Amount debited for Users'); 
            }

            
        } 
            return back()->with('flash_error','Something Went Wrong!'); 
        

    } 
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $wallet recharge
     * @return \Illuminate\Http\Response
     */
    public function recharge_transaction_wallet(Request $request)
    {  
        try{
            $cards = Card::wherecorporate_id(Auth::user()->id)->get();
            $corporate = Corporate::whereid(Auth::user()->id)->first();
            $default_card = Card::wherecorporate_id(Auth::user()->id)->whereis_default(1)->first();
            $tranasction_history = CorporateRechargeHistory::wherecorporate_id(Auth::user()->id)->get(); 

            return view('corporate.recharge',compact('recharges','cards','corporate','default_card','tranasction_history'));  
        } catch(Exception $e){
            if($request->ajax()){
                return response()->json(['error' => $e->getMessage()], 500);
            }else{
                return back()->with('flash_error',$e->getMessage());
            }
        } 
    } 

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function stripe_add(Request $request)
    {  
        $this->validate($request,[
                'stripe_token' => 'required'
            ]);

        try{

                $customer_id = $this->customer_id();
                $this->set_stripe();
                $customer = \Stripe\Customer::retrieve($customer_id);
                $card = $customer->sources->create(["source" => $request->stripe_token]);

                $exist = Card::where('corporate_id',Auth::user()->id)
                                ->where('last_four',$card['last4'])
                                ->where('brand',$card['brand'])
                                ->count();

                if($exist == 0){

                    $create_card = new Card;
                    $create_card->user_id = 0;
                    $create_card->corporate_id = Auth::user()->id;
                    $create_card->card_id = $card['id'];
                    $create_card->last_four = $card['last4'];
                    $create_card->brand = $card['brand'];
                    $create_card->save();

                }else{
                    if($request->ajax()){
                        return response()->json(['message' => 'Card Already Added']); 
                    }else{
                        return back()->with('flash_error','Card Already Added');
                    } 
                }

            if($request->ajax()){
                return response()->json(['message' => 'Card Added']); 
            }else{
                return back()->with('flash_success','Card Added');
            }

        } catch(Exception $e){
            if($request->ajax()){
                return response()->json(['error' => $e->getMessage()], 500);
            }else{
                return back()->with('flash_error',$e->getMessage());
            }
        } 
    }
     /**
     * Get a stripe customer id.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_id()
    {
        if(Auth::user()->stripe_cust_id != null){

            return Auth::user()->stripe_cust_id;

        }else{

            try{

                $stripe = $this->set_stripe();

                $customer = \Stripe\Customer::create([
                    'email' => Auth::user()->email,
                ]);

                Corporate::where('id',Auth::user()->id)->update(['stripe_cust_id' => $customer['id']]);
                return $customer['id'];

            } catch(Exception $e){
                return $e;
            }
        }
    }
    /**
     * setting stripe.
     *
     * @return \Illuminate\Http\Response
     */
    public function set_stripe(){
        return \Stripe\Stripe::setApiKey(Setting::get('stripe_secret_key'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $wallet recharge
     * @return \Illuminate\Http\Response
     */
    public function card_default(Request $request ,$id)
    { 
        
        try {

            Card::where('corporate_id',Auth::user()->id)->update(['is_default' => 0]);

            $card = Card::findOrfail($id); 
            $card->is_default = $request->default; 
            $card->save();  
            
            return redirect()->back()->with('flash_success','Card status updated is successfully');
           
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    } 
     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $wallet recharge
     * @return \Illuminate\Http\Response
     */
    public function card_delete(Request $request ,$id)
    { 
        
        try {

            $card = Card::findOrfail($id); 
            $card->delete();  

            $delete = Auth::user();
            $delete->stripe_cust_id = null;
            $delete->save;
            
            return redirect()->back()->with('flash_success','Card deleted is successfully');
           
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    } 
     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $wallet recharge
     * @return \Illuminate\Http\Response
     */
    public function pay_now(Request $request)
    {  
        $this->validate($request, [
                  'amount' => 'required',
                  'card_id' => 'required|exists:cards,card_id,corporate_id,'.Auth::user()->id
              ]);

          try{
              
                $StripeWalletCharge = $request->amount * 100;

                Stripe::setApiKey(Setting::get('stripe_secret_key'));

                $Charge = Charge::create(array(
                    "amount" => $StripeWalletCharge,
                    "currency" => "cad",
                    "customer" => Auth::user()->stripe_cust_id,
                    "card" => $request->card_id,
                    "description" => "Adding Money for ".Auth::user()->email,
                    "receipt_email" => Auth::user()->email
                )); 

                CorporateRechargeHistory::create([
                    'corporate_id' => Auth::user()->id,
                    'amount' => $request->amount,
                    'recharge_option' => Auth::user()->recharge_option,
                    'payment_status' => 'PAID',
                ]);
                $user_ids = User::wherecorporate_id(Auth::user()->id)->pluck('id');
                $user_request_ids = UserRequests::whereIn('user_id',$user_ids)->whereIn('postpaid_payment_status',['NOTPAID'])->pluck('id');   
                foreach ($user_request_ids as $key => $value) {
                    $update = UserRequests::findOrfail($value);
                    $update->postpaid_payment_status = 'PAID';
                    $update->save(); 
                } 

                $corporate = Auth::user();
                $corporate->wallet_balance += $request->amount;
                $corporate->save();

               return back()->with('flash_success',currency($request->amount).' amount paid successfully'); 

          } catch(StripeInvalidRequestError $e) {
              if($request->ajax()){
                   return response()->json(['error' => $e->getMessage()], 500);
              }else{
                  return back()->with('flash_error',$e->getMessage());
              }
          } catch(Exception $e) {
              if($request->ajax()) {
                  return response()->json(['error' => $e->getMessage()], 500);
              } else {
                  return back()->with('flash_error', $e->getMessage());
              }
          }
    } 
     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $wallet recharge
     * @return \Illuminate\Http\Response
     */
    public function prepaid_recharge(Request $request)
    {  
        $this->validate($request, [
                  'amount' => 'required|integer',
                  'card_id' => 'required|exists:cards,card_id,corporate_id,'.Auth::user()->id
              ]);

          try{
              
                $StripeWalletCharge = $request->amount * 100;

                Stripe::setApiKey(Setting::get('stripe_secret_key'));

                $Charge = Charge::create(array(
                    "amount" => $StripeWalletCharge,
                    "currency" => "cad",
                    "customer" => Auth::user()->stripe_cust_id,
                    "card" => $request->card_id,
                    "description" => "Adding Money for ".Auth::user()->email,
                    "receipt_email" => Auth::user()->email
                )); 

                CorporateRechargeHistory::create([
                    'corporate_id' => Auth::user()->id,
                    'amount' => $request->amount,
                    'recharge_option' => Auth::user()->recharge_option,
                    'payment_status' => 'PAID',
                ]); 

                $corporate = Auth::user();
                $corporate->wallet_balance += $request->amount;
                $corporate->deposit_amount += $request->amount;
                $corporate->save();

               return back()->with('flash_success',currency($request->amount).' amount paid successfully'); 

          } catch(StripeInvalidRequestError $e) {
              if($request->ajax()){
                   return response()->json(['error' => $e->getMessage()], 500);
              }else{
                  return back()->with('flash_error',$e->getMessage());
              }
          } catch(Exception $e) {
              if($request->ajax()) {
                  return response()->json(['error' => $e->getMessage()], 500);
              } else {
                  return back()->with('flash_error', $e->getMessage());
              }
          }
    } 
}
