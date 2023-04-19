<?php

return [
  'gcm' => [
      'priority' => 'high',
      'dry_run' => false,
      'apiKey' => env('ANDROID_USER_PUSH_KEY', 'yourAPIKey'),
  ],
  'fcm' => [
        'priority' => 'high',
        'dry_run' => false,
        'apiKey' => env('ANDROID_PROVIDER_PUSH_KEY', 'yourAPIKey'),
  ],
    'apn' => [
        'certificate' => __DIR__ . '/iosCertificates/6ixTaxiDriver_Dev.pem',
        'passPhrase' => '6ixtaxi5261', //Optional
        'dry_run' => true
    ]
];
