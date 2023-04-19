<?php

namespace App\Http\Controllers\Resource;

use App\User;
use App\CorporateUsers;
use App\UserRequests;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Storage;
use Setting;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class UserCorporateResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    { 
        if(isset($request->search)){ 
            $user_ids = User::orWhere('first_name', 'like', '%' . $request->search . '%')->orWhere('last_name', 'like', '%' . $request->search . '%')->orWhere('email', 'like', '%' . $request->search . '%')->orWhere('mobile', 'like', '%' . $request->search . '%')->pluck('id');
            $users = User::wherecorporate_id(Auth::user()->id)->whereIn('id',$user_ids)->orderBy('id','Desc')->paginate(10); 
        }
        else
        {
            $users = User::wherecorporate_id(Auth::user()->id)->orderBy('id','Desc')->paginate(10);
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
            'email' => 'required|unique:users,email|email|max:255',
            'mobile' => 'required|unique:users,mobile',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            // 'password' => 'required|min:6|confirmed',
        ]);

        try{

            $user = $request->all();

            $user['payment_mode'] = 'CORPORATE_ACCOUNT';
            $user['password'] = bcrypt('123456');
            $user['corporate_id'] = Auth::user()->id;
            $user['hit_from'] = 'UserCorporateResource.store';
            if($request->hasFile('picture')) {
                $user['picture'] = $request->picture->store('user/profile');
            }

            $user = User::create($user);

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

            $user = User::findOrFail($id);
 
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
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {

            $user = User::findOrFail($id);

            if($request->hasFile('picture')) {
                Storage::delete($user->picture);
                $user->picture = $request->picture->store('user/profile');
            }

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->mobile = $request->mobile;
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

            User::find($id)->delete();
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

            $requests = UserRequests::with(['user'  => function($query){
                         return $query->where('corporate_id',Auth::user()->id);
                       },'payment','provider'])
                      ->where('payment_mode','CORPORATE_ACCOUNT')
                      ->whereHas('user' , function($q){
                          return $q->where('corporate_id',Auth::user()->id);
                    })
                    ->where('user_requests.user_id',$id)
                    ->orderBy('user_requests.created_at', 'desc')
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
                   $delete = User::findOrFail($value);
                   $delete->delete();
                }
            }

           return response()->json(['success' => 'success']);

    }
}
