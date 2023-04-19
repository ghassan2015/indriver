<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Twilio\Rest\Client;
use Auth;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;
use Twilio\TwiML\VoiceResponse;

use Twilio\Jwt\Grants\VoiceGrant;
use App\Http\Controllers\SendPushNotification;
use App\User;

class VideoRoomsController extends Controller
{
	protected $sid;
	protected $token;
	protected $key;
	protected $secret;
	protected $phone_no;
	protected $app_sid;

	public function __construct()
	{
	   $this->sid = config('services.twilio.sid');
	   $this->token = config('services.twilio.token');
	   $this->key = config('services.twilio.key');
	   $this->secret = config('services.twilio.secret');
	   $this->phone_no = config('services.twilio.phone_no');
	   $this->app_sid = config('services.twilio.app_sid');
	}

	public function index(Request $request)
	{
	   $rooms = [];
	   try {
	       $client = new Client($this->sid, $this->token);
	       $allRooms = $client->video->rooms->read([]);

	        $rooms = array_map(function($room) {
	           return $room->uniqueName;
	        }, $allRooms);

	   } catch (Exception $e) {
	       echo "Error: " . $e->getMessage();
	   }

	   if($request->ajax()){
	   	  return response()->json(['rooms' => $rooms]);

	   }else{

	   	 return view('video', ['rooms' => $rooms]);

	   }

	   
	}

	public function createRoom(Request $request)
	{
	   $client = new Client($this->sid, $this->token);

	   $exists = $client->video->rooms->read([ 'uniqueName' => $request->roomName]);

	   if (empty($exists)) {
	       $client->video->rooms->create([
	           'uniqueName' => $request->roomName,
	           'type' => 'group',
	           'recordParticipantsOnConnect' => false
	       ]);

	       \Log::debug("created new room: ".$request->roomName);
	   }

	   if($request->ajax()){
	   	  return response()->json(['roomName' => $request->roomName]);

	   }else{

	   	 return redirect()->action('VideoRoomsController@joinRoom', [
	       'roomName' => $request->roomName
	     ]);

	   }

	   
	}

	public function joinRoom(Request $request,$roomName)
	{
	   // A unique identifier for this user
	   $identity = "user ".Auth::user()->first_name;



	   \Log::debug("joined with identity: $identity");
	   $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);

	   $videoGrant = new VideoGrant();
	   $videoGrant->setRoom($roomName);

	   $token->addGrant($videoGrant);

	   if($request->ajax()){

	   	  return response()->json(['accessToken' => $token->toJWT(), 'roomName' => $roomName]);

	   }else{
	   	 return view('room', [ 'accessToken' => $token->toJWT(), 'roomName' => $roomName ]);
	   }

	  
	}

	public function accesstoken(Request $request)
	{
	   // A unique identifier for this user
	   $identity = "user_".Auth::user()->first_name;

	   $user_name = Auth::user()->first_name;

	   $roomName = $request->room_id;

    \Log::debug("joined with identity: $identity");
	   $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);

	   $videoGrant = new VideoGrant();
	   $videoGrant->setRoom($roomName);

	   $token->addGrant($videoGrant);

	   $message = "video_call";

	   (new SendPushNotification)->sendPushToProviderVideo($request->id,$message,$user_name,$roomName); 

	  

	   if($request->ajax()){

	   	  return response()->json(['accessToken' => $token->toJWT()]);

	   }else{
	   	 return view('room', [ 'accessToken' => $token->toJWT()]);
	   }

	  
	}

	public function voiceaccesstoken(Request $request)
	{
	   // A unique identifier for this user
	   $identity = "user_".mt_rand(1111,9999);

	  // $user_name = Auth::user()->first_name;

	   $outgoingApplicationSid=$this->app_sid;

       \Log::debug("joined with identity: $identity");
	   
	   $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);

	    $voiceGrant = new VoiceGrant();
		$voiceGrant->setOutgoingApplicationSid($outgoingApplicationSid);

		// Optional: add to allow incoming calls
		$voiceGrant->setIncomingAllow(true);

		// Add grant to token
		$token->addGrant($voiceGrant);

        return response()->json(['accessToken' => $token->toJWT()]);


 		  
	}

	 public function dial_number(Request $request)
    {
	       $twilio_number = $this->phone_no;

			
			$to_number = $request->phone;

			$response = new VoiceResponse();
			$dial = $response->dial('', ['callerId' => $twilio_number]);
			$dial->number($to_number);

			return $response;
	}

}
