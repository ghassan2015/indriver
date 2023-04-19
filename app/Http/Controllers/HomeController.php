<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Eventcontact;


use App\ServiceType;
use App\UserWallet;
use Auth;
use Setting;
use App\Helpers\Helper;
use App\CorUser;
use App\CorporateUsers;
use App\User;
use App\Corporate;

class HomeController extends Controller
{
    protected $UserAPI;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserApiController $UserAPI)
    {
        $this->middleware('auth');
        $this->middleware('demo', ['only' => [
                'update_password',
            ]]);
        $this->UserAPI = $UserAPI;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $Response = $this->UserAPI->request_status_check()->getData();
        
        if(empty($Response->data))
        { 
         $services = $this->UserAPI->services($request);
            return view('user.dashboard',compact('services'));
        }else{
            return view('user.ride.waiting')->with('request',$Response->data[0]);
        }
    }

    /**
     * Show the application profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return view('user.account.profile');
    }

    public function corprofile()
    {
        $UserId=Auth::user()->id;
        $user=User::where('corp_deleted','=','1')->find($UserId);

        if(!empty($user))
        {
            $exist=$user->corp_deleted;
        }
        else
        {
            $exist=0;
        }
        
        $companyList=Corporate::orderBy('created_at','desc')->get();

        return view('user.account.corprofile',compact('companyList','exist','user'));
    }

    public function check_corporate_pin(Request $request)
    {
        return $this->UserAPI->check_corporate_pin($request);
    }

    public function edit_corprofile(Request $request)
    {
       

       $this->validate($request, [
                'company_id' => 'required',
                'empid'=>'required|unique:users,emp_id',
                'cmobile'=>'required|integer',
                'cpassword'=>'required'
            ]);
       $userId=Auth::user()->id;  


       $where = array(
        'mobile' => $request->cmobile,
        'corporate_id' =>  $request->company_id,
        'employee_id' => $request->empid,
        'password' => $request->cpassword
       );     
       

       $company=CorporateUsers::with('company')->where($where)->first();
       
       if(count($company)!=0)
       {
           // if(\Hash::check($request->cpassword, $company->password)) {
                
                $user=User::find($userId);
                $user->corporate_id=$request->company_id;
                $user->company_name=$company->company->company;
                $user->emp_id=$request->empid;
                $user->corp_deleted=1;
                $user->save();

                if($request->ajax()) {
                   return response()->json(['message' => 'You are become a Corporate User']); 
                } else {
                     return redirect('corprofile')->with('flash_success','You are become a Corporate User');
                }

               

            // }
            // else
            // {
                
            //     if($request->ajax()) {
            //        return response()->json(['message' => 'Password Miss Match'],422); 
            //     } else {
            //         return back()->with('flash_error','Password Miss Match');
            //     }
                
            // }
       }


       else
        {       
                if($request->ajax()) {
                   return response()->json(['message' => 'Company Details is incorrect'],422); 
                } else {
                    return back()->with('flash_error','Company Details is incorrect');
                }


                
        }

    }



    /**
     * Show the application profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit_profile()
    {
        return view('user.account.edit_profile');
    }

    /**
     * Update profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function update_profile(Request $request)
    {
        return $this->UserAPI->update_profile($request);
    }

    /**
     * Show the application change password.
     *
     * @return \Illuminate\Http\Response
     */
    public function change_password()
    {
        return view('user.account.change_password');
    }

    /**
     * Change Password.
     *
     * @return \Illuminate\Http\Response
     */
    public function update_password(Request $request)
    {
        return $this->UserAPI->change_password($request);
    }

    /**
     * Trips.
     *
     * @return \Illuminate\Http\Response
     */
    public function trips()
    {
        $trips = $this->UserAPI->trips();
       
        return view('user.ride.trips',compact('trips'));
    }

     /**
     * Payment.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment()
    {
        $cards = (new Resource\CardResource)->index();
        return view('user.account.payment',compact('cards'));
    }


    /**
     * Wallet.
     *
     * @return \Illuminate\Http\Response
     */
    public function wallet(Request $request) 
    {
        $cards = (new Resource\CardResource)->index();

        $wallet_transation = UserWallet::where('user_id',Auth::user()->id)->orderBy('id','desc')
                                ->paginate(Setting::get('per_page', '10'));
            
        $pagination=(new Helper)->formatPagination($wallet_transation);
        
        return view('user.account.wallet',compact('wallet_transation','pagination','cards'));
    }

    /**
     * Promotion.
     *
     * @return \Illuminate\Http\Response
     */
    public function promotions_index(Request $request)
    {
        $promocodes = $this->UserAPI->promocodes();
        return view('user.account.promotions', compact('promocodes'));
    }

    /**
     * Add promocode.
     *
     * @return \Illuminate\Http\Response
     */
    public function promotions_store(Request $request)
    {
        return $this->UserAPI->add_promocode($request);
    }

    /**
     * Upcoming Trips.
     *
     * @return \Illuminate\Http\Response
     */
    public function upcoming_trips()
    {
        $trips = $this->UserAPI->upcoming_trips();
        return view('user.ride.upcoming',compact('trips'));
    }

    public function incoming()
    {
        $Response = $this->UserAPI->request_status_check()->getData();
        
        if(empty($Response->data))
        { 
            return response()->json(['status' => 0]);
        }else{
            return response()->json(['status' => 1]);
        }
    }

   
}
