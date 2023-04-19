<?php

namespace App\Http\Controllers\Resource;

use App\User;
use App\UserRequests;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Storage;
use Setting;
use App\CorUser;
use \Carbon\Carbon;

class CorUserResource extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store', 'update','destroy']]);
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
            $corusers = CorUser::orderBy('id' , 'asc')->get();
            return response()->json(array('success' => true, 'data'=>$corusers));
        }
        else{

            $corusers = CorUser::orderBy('created_at' , 'desc')->paginate($this->perpage);
            $pagination=(new Helper)->formatPagination($corusers);
            return view('admin.corporateusers.index', compact('corusers','pagination'));
        }

        
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.corporateusers.create');
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
            'company_name' => 'required|max:255',
            'address' => 'required',
            'email' => 'required|unique:cor_users,email|email|max:255',
            'mobile' => 'digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

            $coruser = $request->all();            
            $coruser['payment_mode'] = 'CASH';
            $coruser['password'] = bcrypt($request->password);
            if($request->hasFile('picture')) {
                $coruser['picture'] = $request->picture->store('corporateuser/profile');
            }

            $coruser = CorUser::create($coruser);

            return back()->with('flash_success', trans('admin.cor_user_msgs.user_saved'));

        } 

        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.cor_user_msgs.user_not_found'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $coruser = CorUser::findOrFail($id);
            return view('admin.corporateusers.user-details', compact('coruser'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $coruser = CorUser::findOrFail($id);
            return view('admin.corporateusers.edit',compact('coruser'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    public function paynow($id)
    {
       $company_id=$id;
       $coruser = CorUser::findOrFail($company_id);
       $coruser->payamount=0.00;
       $coruser->save();
       $usrArray=[];
       $usrrqstArray=[];
       $user=User::where('company_id',$company_id)->get();
       foreach($user as $u)
       {
            $user_id=$u->id;
            array_push($usrArray, $user_id);
       }
       foreach($usrArray as $uA)
       {
            $userrequest=UserRequests::where('user_id',$uA)->get();
            foreach($userrequest as $ur)
            {
                $urid=$ur->id;
                array_push($usrrqstArray, $urid);
            }


       }

       foreach($usrrqstArray as $ua)
       {
            $userrequest=UserRequests::find($ua);
            $userrequest->cor_paid=1;
            $userrequest->save();
       }

        return back()->with('flash_success', trans('admin.cor_user_msgs.user_saved'));

    }

    public function viewUsers($id)
    {
        $company_id=$id;
        
        

        if(!empty($request->page) && $request->page=='all'){
            $corusers=User::where('company_id',$company_id)->where('corp_deleted',1)->get();
            $company=CorUser::find($company_id);
            return response()->json(array('success' => true, 'corusers'=>$corusers, 'company'=>$company));
        }
        else{

            $corusers=User::where('company_id',$company_id)->where('corp_deleted',1)->paginate($this->perpage);
            $company=CorUser::find($company_id);            
            $pagination=(new Helper)->formatPagination($corusers);
            return view('admin.corporateusers.users', compact('corusers','company','pagination'));
        }
    }

    public function deleteUsers($id)
    {
        $userid=$id;
        $DeleteUser=User::find($userid);
        $DeleteUser->corp_deleted=0;       
        $DeleteUser->save();
        return back()->with('flash_error', 'User Removed');

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company_name' => 'required|max:255',
            'address' => 'required',
            'mobile' => 'digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {

            $coruser = CorUser::findOrFail($id);

            if($request->hasFile('picture')) {
                Storage::delete($coruser->picture);
                $coruser->picture = $request->picture->store('corporateuser/profile');
            }

            $coruser->company_name = $request->company_name;
            $coruser->address = $request->address;
            $coruser->mobile = $request->mobile;
            $coruser->save();

            return redirect()->route('admin.corporateusers.index')->with('flash_success', trans('admin.cor_user_msgs.user_update'));    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.cor_user_msgs.user_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        try {

            CorUser::find($id)->delete();
            return back()->with('message', trans('admin.cor_user_msgs.user_delete'));
        } 
        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.cor_user_msgs.user_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request($id){

        try{

            $requests = UserRequests::where('user_requests.user_id',$id)
                    ->RequestHistory()                    
                    ->paginate($this->perpage);

            $pagination=(new Helper)->formatPagination($requests);        

            return view('admin.request.index', compact('requests','pagination'));
        }

        catch (Exception $e) {
             return back()->with('flash_error', trans('admin.something_wrong'));
        }

    }

    public function corhistory($type = '', $request = null)
    {


        try {  
           // dd($type);
           
            $page = 'Overall Ride History'; 

            if($type == 'individual'){

                $page = trans('admin.include.provider_statement');
                
            }elseif($type == 'today'){

                $page = trans('admin.include.today_statement').' - '. date('d M Y');
               
            }elseif($type == 'monthly'){

                $page = trans('admin.include.monthly_statement').' - '. date('F');
                
            }elseif($type == 'yearly'){

                $page = trans('admin.include.yearly_statement').' - '. date('Y');
                
            }elseif($type == 'range'){
                $page = 'Ride History From'.' '.Carbon::createFromFormat('Y-m-d', $request->from_date)->format('d M Y').'  To '.Carbon::createFromFormat('Y-m-d', $request->to_date)->format('d M Y');
            }
          

            $requests = UserRequests::with('user')->where('payment_mode','CAC')->orderBy('id','desc');
            

            if($type == 'range')
            {

                 
                if($request->company_id)

                {
                    
                     $requests->whereHas('user', function($q) use($request){
                        $q->where('company_id','=',$request->company_id);
                    });
                }

                if($request->user_id)

                {
                    
                     $requests->whereHas('user', function($q) use($request){
                        $q->where('id','=',$request->user_id);
                    });
                }

                if($request->from_date == $request->to_date) {
                    $requests->whereDate('created_at', date('Y-m-d', strtotime($request->from_date)));
                    
                } else {
                    $requests->whereBetween('created_at',[Carbon::createFromFormat('Y-m-d', $request->from_date),Carbon::createFromFormat('Y-m-d', $request->to_date)]);
                   
                }
                
                   
                   
                    
                
            }

            $requests = $requests->paginate($this->perpage);
            if ($type == 'range'){
                $path='range?from_date='.$request->from_date.'&to_date='.$request->to_date;
                $requests->setPath($path);
            }
            

            $pagination=(new Helper)->formatPagination($requests);
            $company=CorUser::orderBy('id' , 'asc')->get();
            $users=User::where('company_id','!=','0')->orderBy('id' , 'asc')->get(); 
            return view('admin.corporateusers.corhistory', compact('requests','pagination','company','users','page'));
        } catch (Exception $e) {
            dd($e);
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function statement_range(Request $request)
    {

        return $this->corhistory('range', $request);
    }

}
