<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\User;
use App\Provider;
use App\ProviderDevice;
use Exception;
//use Log;
use Illuminate\Support\Facades\Log;
use Setting;
use App;

use Edujugon\PushNotification\PushNotification;

class SendPushNotification extends Controller
{
    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function RideAccepted($request)
    {

        $user = User::where('id', $request->user_id)->first();
        $language = $user->language;
        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToProvider($request->provider_id, trans('api.push.request_accepted'), $type);
    }

    /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function user_schedule($user)
    {
        $user = User::where('id', $user)->first();
        $language = $user->language;
        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToUser($user->id, trans('api.push.schedule_start'), $type);
    }

    /**
     * New Incoming request
     *
     * @return void
     */
    public function provider_schedule($provider)
    {

        $provider = Provider::where('id', $provider)->with('profile')->first();
        if ($provider->profile) {
            $language = $provider->profile->language;
            if ($language)
                App::setLocale($language);
        }

        $type = 0;

        return $this->sendPushToProvider($provider->id, trans('api.push.schedule_start', $type));

    }

    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function UserCancellRide($request)
    {
        if (!empty($request->provider_id)) {

            $provider = Provider::where('id', $request->provider_id)->with('profile')->first();

            if ($provider->profile) {
                $language = $provider->profile->language;
                if ($language)
                    App::setLocale($language);
            }
            $type = [0];
            return $this->sendPushToProvider($request->provider_id, trans('api.push.user_cancelled', $type));
        }

        return true;
    }
	
		/**
	 * update Payment mode .
	 *
	 * @param $providerId
	 *
	 * @return void
	 */
    public function updatePaymentMode($providerId)    {
      if ($providerId) {
        $provider = Provider::where('id', $providerId)->with('profile')->first();
        if ($provider->profile) {
	        $language = $provider->profile->language;
	        if ( $language ) {
		        App::setLocale( $language );
	        }
        }
        $type = [0];
        $this->sendPushToProvider($providerId, trans('api.push.update_payment_mode', $type));
      }
    }


    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function ProviderCancellRide($request)
    {

        $user = User::where('id', $request->user_id)->first();
        $language = $user->language;
        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToUser($request->user_id, trans('api.push.provider_cancelled'), $type);
    }

    /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function Arrived($request)
    {

        $user = User::where('id', $request->user_id)->first();
        $language = $user->language;
        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToUser($request->user_id, trans('api.push.arrived'), $type);
    }

    /**
     * Driver Picked You  in your location.
     *
     * @return void
     */
    public function Pickedup($request)
    {
        $user = User::where('id', $request->user_id)->first();
        $language = $user->language;
        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToUser($request->user_id, trans('api.push.pickedup'), $type);
    }

    /**
     * Driver Reached  destination
     *
     * @return void
     */
    public function Dropped($request)
    {

        $user = User::where('id', $request->user_id)->first();
        $language = $user->language;
        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToUser($request->user_id, trans('api.push.dropped') . Setting::get('currency') . $request->payment->flat_rate . ' by ' . $request->payment_mode, $type);
    }

    /**
     * Your Ride Completed
     *
     * @return void
     */
    public function Complete($request)
    {

        $user = User::where('id', $request->user_id)->first();
        $language = $user->language;
        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToUser($request->user_id, trans('api.push.complete'), $type);
    }


    /**
     * Rating After Successful Ride
     *
     * @return void
     */
    public function Rate($request)
    {

        $user = User::where('id', $request->user_id)->first();
        $language = $user->language;
        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToUser($request->user_id, trans('api.push.rate'), $type);
    }


    /**
     * Money added to user wallet.
     *
     * @return void
     */
    public function ProviderNotAvailable($user_id)
    {
        $user = User::where('id', $user_id)->first();
        $language = $user->language;
        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToUser($user_id, trans('api.push.provider_not_available'), $type);
    }

    /**
     * New Incoming request
     *
     * @return void
     */
    public function IncomingRequest($provider)
    {

    	  Log::info('IncomingRequest providerId: '. $provider);
        $provider = Provider::where('id', $provider)->with('profile')->first();
        if ($provider->profile) {
            $language = $provider->profile->language;
            if ($language)
                App::setLocale($language);
        }
        $type = 0;
        return $this->sendPushToProvider($provider->id, trans('api.push.incoming_request'), $type);

    }


    /**
     * Driver Documents verfied.
     *
     * @return void
     */
    public function DocumentsVerfied($provider_id)
    {

        $provider = Provider::where('id', $provider_id)->with('profile')->first();
        if ($provider->profile) {
            $language = $provider->profile->language;
            if ($language)
                App::setLocale($language);
        }
        $type = 0;
        return $this->sendPushToProvider($provider_id, trans('api.push.document_verfied'), $type);
    }


    /**
     * Money added to user wallet.
     *
     * @return void
     */
    public function WalletMoney($user_id, $money)
    {

        $user = User::where('id', $user_id)->first();
        $language = $user->language;

        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToUser($user_id, $money . ' ' . trans('api.push.added_money_to_wallet'), $type);
    }

    /**
     * Money charged from user wallet.
     *
     * @return void
     */
    public function ChargedWalletMoney($user_id, $money)
    {

        $user = User::where('id', $user_id)->first();
        $language = $user->language;
        if ($language)
            App::setLocale($language);
        $type = 0;
        return $this->sendPushToUser($user_id, $money . ' ' . trans('api.push.charged_from_wallet'), $type);
    }

    /**
     * Change/update destination during ride.
     * Send notification to both Rider & Driver
     */
    public function changeDestination($userRequest)
    {
        $user = User::where('id', $userRequest->user_id)->first();
        $language = $user->language;
        if ($language) {
	        App::setLocale( $language );
        }
        $this->sendPushToUser($userRequest->user_id, trans('user.change_destination'));
	
	    if (!empty($userRequest->provider_id)) {
		
		    $provider = Provider::where('id', $userRequest->provider_id)->with('profile')->first();
		
		    if ($provider->profile) {
			    $language = $provider->profile->language;
			    if ($language)
				    App::setLocale($language);
		    }
        $this->sendPushToProvider($userRequest->provider_id, trans('provider.change_destination'));
	    }
    }

    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToUser($user_id, $push_message, $type = 0)
    {

        try {


            $user = User::findOrFail($user_id);


            if ($user->device_token != "") {


                if ($user->device_type == 'ios') {
                    if (env('IOS_USER_ENV') == 'development') {
                        $crt_user_path = app_path() . '/apns/user/6ixTaxi_Dev.pem';
                        $crt_provider_path = app_path() . '/apns/provider/6ixTaxiDriver.pem';
                        $dry_run = true;
                    } else {
                        $crt_user_path = app_path() . '/apns/user/6ixTaxi_Dev.pem';
                        $crt_provider_path = app_path() . '/apns/provider/6ixTaxiDriver.pem';
                        $dry_run = false;
                    }

                    $push = new PushNotification('apn');

                    $push->setConfig([
                        'certificate' => $crt_user_path,
                        'passPhrase' => env('IOS_USER_PUSH_PASS', 'apple'),
                        'dry_run' => $dry_run
                    ]);

                    if ($type == 0) {
                          $message = [
                            'notification' => [
                                'title' => 'New Notification',
                                'text' => $push_message,
                                'body' => $push_message,
                                "click_action" => '#',
                                'sound' => 'default',

                            ],
                            'to' => $user->device_token,
                        ];

                        $client = new Client([
                            'headers' => [
                                'Content-Type' => 'application/json',
                                'Authorization' => "key=AIzaSyDhro4WMVMO8lTaxlXLiO4vaU8CJ5AE4Fk",
                            ]
                        ]);
                        $response = $client->post('https://fcm.googleapis.com/fcm/send',
                            ['body' => json_encode($message)]
                        );

                        $send = $push->setMessage([
                            'aps' => [
                                'alert' => [
                                    'body' => $push_message
                                ],
                                'sound' => 'default',
                                'badge' => 1

                            ],
                            'extraPayLoad' => [
                                'custom' => $push_message
                            ]
                        ])
                            ->setDevicesToken($user->device_token)->send();
                    } else {
                        $send = $push->setMessage([
                            'aps' => [
                                'alert' => [
                                    'body' => $push_message
                                ],
                                'sound' => 'default',
                                'badge' => 1

                            ],
                            'extraPayLoad' => [
                                'custom' => array('type' => 'chat')
                            ]
                        ])
                            ->setDevicesToken($user->device_token)->send();

                    }

                    return $send;

                } elseif ($user->device_type == 'android') {

                    $push = new PushNotification('fcm');
                    if ($type == 0) {
                        Log::info('Push notification: fcm type 0');
                        
                        $send = $push->setMessage(['message' => $push_message])
                            ->setApiKey('AAAASlvYNr0:APA91bHUD-fO6tkDt86nRU98Lf4JHASo0O6ZUCVdBZVHFOHyzdbBp7HZnEJmPijZfsLUw-RCrcAw7fzMSpsGhAJ-zoW_GMr_gkDjZvUA30l3_ZX7394bHwYSd8Uq7OvMAjgoFLAZBmGD')
                            ->setDevicesToken($user->device_token)->send();
                            Log::info("PushNotification: ");
                    } else {
                        Log::info('Push notification: fcm type 1');
                        $send = $push->setMessage(['message' => $push_message, 'custom' => array('type' => 'chat')])
                            ->setDevicesToken($user->device_token)->send();
                    }

                    return $send;

                }
            }

        } catch (Exception $e) {
            return $e;
        }

    }


    /**
     * Sending Push to a user Device.
     *
     * @return PushNotification
     */
    public function sendPushToProvider($provider_id, $push_message, $type = 0)
    {

        try {


            $provider = ProviderDevice::where('provider_id', $provider_id)->with('provider')->first();


            if ($provider->token != "") {

                if ($provider->type == 'ios') {

                    if (env('IOS_USER_ENV') == 'development') {
                        $crt_user_path = app_path() . '/apns/user/6ixTaxi_Dev.pem';
                        $crt_provider_path = app_path() . '/apns/provider/6ixTaxiDriver.pem';
                        $dry_run = true;
                    } else {
                        $crt_user_path = app_path() . '/apns/user/6ixTaxi_Dev.pem';
                        $crt_provider_path = app_path() . '/apns/provider/6ixTaxiDriver.pem';
                        $dry_run = false;
                    }

                    $push = new PushNotification('apn');
                    $push->setConfig([
                        'certificate' => $crt_provider_path,
                        'passPhrase' => env('IOS_PROVIDER_PUSH_PASS', 'apple'),
                        'dry_run' => $dry_run
                    ]);

                    if ($type == 0) {


                        $message = [
                            'notification' => [
                                'title' => 'New Notification',
                                'text' => $push_message,
                                'body' => $push_message,
                                "click_action" => '#',
                                'sound' => 'default',

                            ],
                            'to' => $provider->token,
                        ];

                        $client = new Client([
                            'headers' => [
                                'Content-Type' => 'application/json',
                                'Authorization' => "key=AIzaSyDhro4WMVMO8lTaxlXLiO4vaU8CJ5AE4Fk",
                            ]
                        ]);
                        $response = $client->post('https://fcm.googleapis.com/fcm/send',
                            ['body' => json_encode($message)]
                        );


                        $send = $push->setMessage([
                            'aps' => [
                                'alert' => [
                                    'body' => $push_message
                                ],
                                'sound' => 'default',
                                'badge' => 1

                            ],
                            'extraPayLoad' => [
                                'custom' => $push_message
                            ]
                        ])
                            ->setDevicesToken($provider->token)->send();


                    } else {

                        $send = $push->setMessage([
                            'aps' => [
                                'alert' => [
                                	  'title' => 'Title',
                                	  'subtitle' => 'Subtitle',
                                    'body' => $push_message,
                                ],
                                'sound' => 'default',
                                'badge' => 1

                            ],
                            'extraPayLoad' => [
                                'custom' => array('type' => 'chat')
                            ]
                        ])
                            ->setDevicesToken($provider->token)->send();

                    }


                    return $send;

                } elseif ($provider->type == 'android') {

                    $push = new PushNotification('fcm');
                    if ($type == 0) {

                        $send = $push->setMessage(['message' => $push_message])
                            ->setDevicesToken($provider->token)->send();
                    } else {

                        $send = $push->setMessage(['message' => $push_message, 'custom' => array('type' => 'chat')])
                            ->setDevicesToken($provider->token)->send();
                    }

                    return $send;


                }
            }

        } catch (Exception $e) {
            return $e;
        }

    }

}
