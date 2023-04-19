<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\SendPushNotification;

use Stripe\Charge;
use Stripe\Stripe;
use Stripe\StripeInvalidRequestError;

use Auth;
use Setting;
use Exception;

use App\Card;
use App\User;
use App\WalletPassbook;
use App\UserRequests;
use App\UserRequestPayment;
use App\WalletRequests;
use App\Provider;
use App\Fleet;
use Session;

use App\Http\Controllers\ProviderResources\TripController;

class PaymentController extends Controller
{
       /**
     * payment for user.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment(Request $request)
    {

        $this->validate($request, [
                'request_id' => 'required|exists:user_request_payments,request_id|exists:user_requests,id,paid,0,user_id,'.Auth::user()->id
            ]);


        $UserRequest = UserRequests::find($request->request_id);
        
        $tip_amount=0;

        if($UserRequest->payment_mode == 'CARD') {

            $RequestPayment = UserRequestPayment::where('request_id',$request->request_id)->first(); 
            
            if(isset($request->tips) && !empty($request->tips)){
                $tip_amount=round($request->tips,2);
            }
            
            $StripeCharge = ($RequestPayment->payable+$tip_amount) * 100;
            
           
            try {

                $Card = Card::where('user_id',Auth::user()->id)->where('is_default',1)->first();
                $stripe_secret = Setting::get('stripe_secret_key');

                Stripe::setApiKey(Setting::get('stripe_secret_key'));
                
                if($StripeCharge  == 0){

                $RequestPayment->payment_mode = 'CARD';
                $RequestPayment->card = $RequestPayment->payable;
                $RequestPayment->payable = 0;
                $RequestPayment->tips = $tip_amount;                
                $RequestPayment->provider_pay = $RequestPayment->provider_pay+$tip_amount;
                $RequestPayment->save();

                $UserRequest->paid = 1;
                $UserRequest->status = 'COMPLETED';
                $UserRequest->save();

                //for create the transaction
                (new TripController)->callTransaction($request->request_id);

                if($request->ajax()) {
                   return response()->json(['message' => trans('api.paid')]); 
                } else {
                    return redirect('dashboard')->with('flash_success', trans('api.paid'));
                }
               }else{
                
                $Charge = Charge::create(array(
                      "amount" => $StripeCharge,
                      "currency" => "cad",
                      "customer" => Auth::user()->stripe_cust_id,
                      "card" => $Card->card_id,
                      "description" => "Payment Charge for ".Auth::user()->email,
                      "receipt_email" => Auth::user()->email
                    ));

                /*$ProviderCharge = (($RequestPayment->total+$RequestPayment->tips - $RequestPayment->tax) - $RequestPayment->commision) * 100;

                $transfer = Transfer::create(array(
                    "amount" => $ProviderCharge,
                    "currency" => "usd",
                    "destination" => $Provider->stripe_acc_id,
                    "transfer_group" => "Request_".$UserRequest->id,
                  )); */    
                 
                $RequestPayment->payment_id = $Charge["id"];
                $RequestPayment->payment_mode = 'CARD';
                $RequestPayment->card = $RequestPayment->payable;
                $RequestPayment->payable = 0;
                $RequestPayment->tips = $tip_amount;
                $RequestPayment->provider_pay = $RequestPayment->provider_pay+$tip_amount;
                $RequestPayment->save();

                $UserRequest->paid = 1;
                $UserRequest->status = 'COMPLETED';
                $UserRequest->save();

                //for create the transaction
                (new TripController)->callTransaction($request->request_id);

                if($request->ajax()) {
                   return response()->json(['message' => trans('api.paid')]); 
                } else {
                    return redirect('dashboard')->with('flash_success', trans('api.paid'));
                }
              }

            } catch(StripeInvalidRequestError $e){
              
                if($request->ajax()){
                    return response()->json(['error' => $e->getMessage()], 500);
                } else {
                    return back()->with('flash_error', $e->getMessage());
                }
            } catch(Exception $e) {
                if($request->ajax()){
                    return response()->json(['error' => $e->getMessage()], 500);
                } else {
                    return back()->with('flash_error', $e->getMessage());
                }
            }
        }
    }


    /**
     * add wallet money for user.
     *
     * @return \Illuminate\Http\Response
     */
    public function add_money(Request $request){

        $this->validate($request, [
                'amount' => 'required|integer',
                'card_id' => 'required|exists:cards,card_id,user_id,'.Auth::user()->id
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

            Card::where('user_id',Auth::user()->id)->update(['is_default' => 0]);
            Card::where('card_id',$request->card_id)->update(['is_default' => 1]);

            //sending push on adding wallet money
            (new SendPushNotification)->WalletMoney(Auth::user()->id,currency($request->amount));

            //for create the user wallet transaction
            (new TripController)->userCreditDebit($request->amount,Auth::user()->id,1);

            $wallet_balance=Auth::user()->wallet_balance+$request->amount;

            if($request->ajax()){
                return response()->json(['success' => currency($request->amount)." ".trans('api.added_to_your_wallet'), 'message' => currency($request->amount)." ".trans('api.added_to_your_wallet'), 'balance' => $wallet_balance]); 
            } else {
                return redirect('wallet')->with('flash_success',currency($request->amount).trans('admin.payment_msgs.amount_added'));
            }

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
     * send money to provider or fleet.
     *
     * @return \Illuminate\Http\Response
     */
    public function send_money(Request $request, $id){
            
        try{

            $Requests = WalletRequests::where('id',$id)->first();

            if($Requests->request_from=='provider'){
              $provider = Provider::find($Requests->from_id);
              $stripe_cust_id=$provider->stripe_cust_id;
              $email=$provider->email;
            }
            else{
              $fleet = Fleet::find($Requests->from_id);
              $stripe_cust_id=$fleet->stripe_cust_id;
              $email=$fleet->email;
            }

            if(empty($stripe_cust_id)){              
              throw new Exception(trans('admin.payment_msgs.account_not_found'));              
            }

            $StripeCharge = $Requests->amount * 100;

            Stripe::setApiKey(Setting::get('stripe_secret_key'));

            $tranfer = \Stripe\Transfer::create(array(
                     "amount" => $StripeCharge,
                     "currency" => "cad",
                     "destination" => $stripe_cust_id,
                     "description" => "Payment Settlement for ".$email                     
                 ));           

            //create the settlement transactions
            (new TripController)->settlements($id);

             $response=array();
            $response['success']=trans('admin.payment_msgs.amount_send');
           
        } catch(Exception $e) {
            $response['error']=$e->getMessage();           
        }

        return $response;
    }

       public function elavonpayment(Request $request)
       { 



            $UserRequest = UserRequests::find($request->request_id);
            $request_pay_id=$request->request_id;

            if($UserRequest->payment_mode == 'ELAVON'){

            $payment = UserRequestPayment::where('request_id',$request->request_id)->first();

            $merchantID = Setting::get('elavon_merchant_id', '');
            $merchantUserID = Setting::get('elavon_user_id', '');
            $merchantPIN = Setting::get('elavon_pin', '');                
            $elavonmode = Setting::get('elavon_mode', '');


           

            $reference_code = '6ixtaxi'.mt_rand();

            if($elavonmode == 'demo') {

            $url = "https://demo.convergepay.com/hosted-payments/transaction_token";
            $hppurl = "https://demo.convergepay.com/hosted-payments"; 
            } 

            else {

            $url = "https://www.convergepay.com/hosted-payments/transaction_token";
            $hppurl = "https://www.convergepay.com/hosted-payments";
            }

            $tip_amount=0;
            if(isset($request->tips) && !empty($request->tips)){
                $tip_amount=round($request->tips,2);
            }

            $StripeCharge = ($payment->payable+$tip_amount);
            $payment->card = $payment->payable;                
            $payment->tips = $tip_amount;                
            $payment->provider_pay = $payment->provider_pay+$tip_amount;
            $amount = $StripeCharge;
             
            
            $payment->save();

            $ch = curl_init();    // initialize curl handle
            curl_setopt($ch, CURLOPT_URL,$url); // set POST target URL
            curl_setopt($ch,CURLOPT_POST, true); // set POST method
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);




            curl_setopt($ch,CURLOPT_POSTFIELDS,
            "ssl_merchant_id=$merchantID".
            "&ssl_user_id=$merchantUserID".
            "&ssl_pin=$merchantPIN".
            "&ssl_transaction_type=CCSALE".
            "&ssl_amount=$amount".
            "&ssl_invoice_number=$request_pay_id"
            );
            $result = curl_exec($ch);
            curl_close($ch);
            $sessiontoken= urlencode($result);

            if($request->ajax()) {
                if($elavonmode == 'demo') 
                {
//                  return "https://demo.convergepay.com/hosted-payments?ssl_txn_auth_token=$sessiontoken";
                        /*return response()->json(['message' => "https://demo.convergepay.com/hosted-payments?ssl_txn_auth_token=$sessiontoken";*/
                }
                else
                {
                    return "https://www.convergepay.com/hosted-payments?ssl_txn_auth_token=$sessiontoken";
                }
            }
            else
            {
                if($elavonmode == 'demo') 
                {
                    header("Location: https://demo.convergepay.com/hosted-payments?ssl_txn_auth_token=$sessiontoken");
                }
                else
                {
                    header("Location: https://www.convergepay.com/hosted-payments?ssl_txn_auth_token=$sessiontoken");
                }
            }

              exit;
            }
      }

  public function elavon_add_money(Request $request)
  {
      $this->validate($request, [
                'amount' => 'required|integer',
                
            ]);
            $StripeWalletCharge = $request->amount;
            $merchantID = Setting::get('elavon_merchant_id', '');
            $merchantUserID = Setting::get('elavon_user_id', '');
            $merchantPIN = Setting::get('elavon_pin', '');                
            $elavonmode = Setting::get('elavon_mode', '');

            if($elavonmode == 'demo') {

            $url = "https://demo.convergepay.com/hosted-payments/transaction_token";
            $hppurl = "https://demo.convergepay.com/hosted-payments"; 
            } 

            else 
            {

            $url = "https://www.convergepay.com/hosted-payments/transaction_token";
            $hppurl = "https://www.convergepay.com/hosted-payments";
            }           
            $amount = $StripeWalletCharge;
            $ch = curl_init();    // initialize curl handle
            curl_setopt($ch, CURLOPT_URL,$url); // set POST target URL
            curl_setopt($ch,CURLOPT_POST, true); // set POST method
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch,CURLOPT_POSTFIELDS,
            "ssl_merchant_id=$merchantID".
            "&ssl_user_id=$merchantUserID".
            "&ssl_pin=$merchantPIN".
            "&ssl_transaction_type=CCSALE".
            "&ssl_amount=$amount"
            );
            $result = curl_exec($ch);
            curl_close($ch);
            $sessiontoken= urlencode($result);

            if($elavonmode == 'demo') {
            header("Location: https://demo.convergepay.com/hosted-payments?ssl_txn_auth_token=$sessiontoken");
            }
            else
            {
            header("Location: https://www.convergepay.com/hosted-payments?ssl_txn_auth_token=$sessiontoken");
            }
            exit;          

  }

  public function successConverge(Request $request){
    
    $request_id = $request->ssl_invoice_number;
    if($request_id!=''||$request_id!=null)
    {
      try{

              if($request->ssl_result_message=='APPROVAL'){     
          
            
             $UserRequest = UserRequests::find($request_id);
             //dd($UserRequest);
             $RequestPayment = UserRequestPayment::where('request_id',$request_id)->first();

              $RequestPayment->payment_id = $request->ssl_txn_id;
              //$RequestPayment->transaction_id = $request->ssl_txn_id;
              $RequestPayment->payment_mode = 'ELAVON';
              $RequestPayment->save();
              $UserRequest->paid = 1;
              //$UserRequest->status = 'COMPLETED';
              $UserRequest->save();
              $id=$request_id;
              return redirect('/confirmation/'.$id);
            }

            
            else
            {
              return redirect('/rejected');
            }
                
      }
      
      catch(Exception $e)
      {
        dd($e->getMessage());
      }
    }
    else
    {
          try
          {

              (new SendPushNotification)->WalletMoney(Auth::user()->id,currency($request->ssl_amount));

              //for create the user wallet transaction
              (new TripController)->userCreditDebit($request->ssl_amount,Auth::user()->id,1);

              $wallet_balance=Auth::user()->wallet_balance+$request->ssl_amount;

              if($request->ajax()){
              return response()->json(['success' => currency($request->ssl_amount)." ".trans('api.added_to_your_wallet'), 'message' => currency($request->ssl_amount)." ".trans('api.added_to_your_wallet'), 'balance' => $wallet_balance]); 
              } else {
              return redirect('wallet')->with('flash_success',currency($request->ssl_amount).trans('admin.payment_msgs.amount_added'));
              }

          }  
          catch(Exception $e) 
          {
            if($request->ajax()) 
            {
              return response()->json(['error' => $e->getMessage()], 500);
            } 
            else 
            {
              return back()->with('flash_error', $e->getMessage());
            }
          }
    }

    
  }

  public function cancelConverge(Request $request){

      return redirect('/rejected');
  }

  public function confirmation(Request $request,$id){


        //for create the transaction
          (new TripController)->callTransaction($id);
          if($request->ajax()){
             return response()->json(['message' => trans('api.paid')]); 
          }else{
              return redirect('/dashboard')->with('flash_success','Paid');
          }

  }

  public function rejected(Request $request){



          if($request->ajax()){
             return response()->json(['message' => trans('api.unpaid')]); 
          }else{
              return redirect('/dashboard')->with('flash_danger','Not Paid');
          }

  }

  public function tips(Request $request){

   

      $this->validate($request,[
        'request_id' => 'required|exists:user_request_payments,request_id|exists:user_requests,id,paid,0,user_id,'.Auth::user()->id,
          'tip' => 'required'

       ]);

        try{

           $update = UserRequestPayment::where('request_id',$request->request_id)->first();
           
           $update->tips = $request->tip ;
           $update->total =  $request->tip + $update->payable ;
            $update->save();          
           
          if($request->ajax()){
               
               return response()->json(['message' => 'Tips added','tip_amount'=> $update->tips]); 

          }else{
                
                return redirect('dashboard')->with('flash_success','Tips added');
          }


        }catch(Exception $e){
               
               if($request->ajax()){
                     return response()->json(['error' => $e->getMessage()], 500);
                    }else{
                   return back()->with('flash_error',$e->getMessage());
              }

        }


    }

    

    
}
