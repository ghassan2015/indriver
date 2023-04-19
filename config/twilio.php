<?php

return [
	
	'twilio' => [
		
		'default' => 'twilio',
		
		'connections' => [
			
			'twilio' => [
				
				'sid' => env('TWILIO_SID', ''),
				
				'token' => env('TWILIO_TOKEN', ''),
				
				'from' => env('TWILIO_FROM', ''),

				'verification_sid' => env('TWILIO_VERIFICATION_SID', '')
			],
		],
	],
];