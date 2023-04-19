<?php

namespace App\Http\Controllers;

use App\User;
use App\UserRequests;
use App\CorporateUsers;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Storage;
use Setting;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class CorporateUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        if(isset($request->search)){ 
            $user_ids = CorporateUsers::orWhere('first_name', 'like', '%' . $request->search . '%')->orWhere('last_name', 'like', '%' . $request->search . '%')->orWhere('email', 'like', '%' . $request->search . '%')->orWhere('mobile', 'like', '%' . $request->search . '%')->orWhere('employee_id', 'like', '%' . $request->search . '%')->pluck('id');
            $users = CorporateUsers::wherecorporate_id(Auth::user()->id)->whereIn('id',$user_ids)->orderBy('id','Desc')->paginate(10); 
        }
        else
        {
            $users = CorporateUsers::wherecorporate_id(Auth::user()->id)->orderBy('id','Desc')->paginate(10);
        } 
        return view('corporate.users.index',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('corporate.users.create');
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
            'email' => 'required|email|max:255',
            'mobile' => 'required',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'employee_id' => 'required|unique:corporate_users,employee_id|max:255',
            'password' => 'required',
            'pin' => 'required',
            // 'password' => 'required|min:6|confirmed',
        ]);

        try{

            $user = $request->all();  
            $user['corporate_id'] =Auth::user()->id;
            $user = CorporateUsers::create($user);

            return back()->with('flash_success','User Details Saved Successfully');

        } 

        catch (Exception $e) {  
            return back()->with('flash_error', 'User Not Found');
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
            $user = User::findOrFail($id);
 
            return view('corporate.users.user-details', compact('user')); 
            
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {

            $user = CorporateUsers::findOrFail($id);
 
            return view('corporate.users.edit',compact('user')); 
            
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'mobile' => 'digits_between:6,13', 
            // 'employee_id' => 'required|max:255',
            'email' => 'required|max:255',
        ]);

        try {

            $user = CorporateUsers::findOrFail($id); 

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->mobile = $request->mobile;
            // $user->employee_id = $request->employee_id;
            $user->email = $request->email;
            $user->pin = $request->pin;
            $user->save();
             
            return redirect()->route('corporate.user.index')->with('flash_success', 'User Updated Successfully'); 
                
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'User Not Found');
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

            $corp_user = CorporateUsers::find($id);

            $update = User::whereemp_id($corp_user->employee_id)->first();
            if($update){
             $update->corporate_id = 0;
             $update->company_id = 0;
             $update->company_name = null;
             $update->emp_id = null;
             $update->corp_deleted = 0;
             $update->save();
            }

             CorporateUsers::find($id)->delete();
             
            return back()->with('message', 'User deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'User Not Found');
        }
    }
     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request(Request $request, $id){

        try{

            $requests = UserRequests::where('user_requests.user_id',$id)
                    ->RequestHistory()
                    ->paginate(10); 

            return view('corporate.request.index', compact('requests'));
             
            
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

    }
    /**
     * Seleted user delete
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function user_seleted_delete(Request $request)
    {
            // print_r($request->deleted_id);

            foreach ($request->deleted_id as $key => $value) {
               if($value)
               {
                   $delete = CorporateUsers::findOrFail($value);
                   $delete->delete();
                }
            }

           return response()->json(['success' => 'success']);

    }
}
