<?php namespace Taxi\Twilio;

interface Service {
	
	public function startVerification ( $phoneNumber, $channel );
	
	public function checkVerification ( $phone_number, $code );
}