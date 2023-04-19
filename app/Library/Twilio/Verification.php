<?php namespace Taxi\Twilio;

use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class Verification implements Service {
	
	private $client;
	private $verification_sid;
	
	public function __construct ( ) {
		//if ( $client === null ) {
			$sid = config('twilio.twilio.connections.twilio.sid');
			$token = config('twilio.twilio.connections.twilio.token');
			$client = new Client( $sid, $token );
		//}
		$this->client           = $client;
		$this->verification_sid = config( 'twilio.twilio.connections.twilio.verification_sid' );
	}
	
	public function startVerification ( $phoneNumber, $channel ) {
		try {
			$verification =
				$this->client->verify->v2->services( $this->verification_sid )->verifications->create( $phoneNumber, $channel );
			
			return new Result( $verification->sid );
		} catch ( TwilioException $exception ) {
			return new Result( [ "Verification failed to start: {$exception->getMessage()}" ] );
		}
	}
	
	public function checkVerification ( $phone_number, $code ) {
		try {
			$verification_check = $this->client->verify->v2->services( $this->verification_sid )->verificationChecks->create( $code,
			                                                                                                                  [ 'to' => $phone_number ] );
			if ( $verification_check->status === 'approved' ) {
				return new Result( $verification_check->sid );
			}
			return new Result( [ 'Verification check failed: Invalid code.' ] );
		} catch ( TwilioException $exception ) {
			return new Result( [ "Verification check failed: {$exception->getMessage()}" ] );
		}
	}
}