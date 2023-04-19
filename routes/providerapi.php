<?php

// Authentication
Route::post('/register' ,   'ProviderAuth\TokenController@register'); // Encrypted
Route::post('/oauth/token', 'ProviderAuth\TokenController@authenticate'); // Encrypted
Route::post('/verify' ,     'ProviderAuth\TokenController@verify'); // Encrypted
Route::post('/auth/facebook','ProviderAuth\TokenController@facebookViaAPI'); // Encrypted
Route::post('/auth/google',  'ProviderAuth\TokenController@googleViaAPI'); // Encrypted
Route::post('/forgot/password','ProviderAuth\TokenController@forgot_password'); // Encrypted
Route::post('/reset/password', 'ProviderAuth\TokenController@reset_password'); // Encrypted

Route::get('/refresh/token' , 'ProviderAuth\TokenController@refresh_token'); // Encrypted

Route::post('/is-mobile-verified' , 'ProviderResources\ProfileController@isMobileVerified'); // Encrypted
Route::post('/send-mobile-verification-code' , 'ProviderResources\ProfileController@sendMobileVerificationCode'); // Encrypted
Route::post('/verify-mobile-verification-code' , 'ProviderResources\ProfileController@verifyMobileVerificationCode'); // Encrypted

Route::group(['middleware' => ['provider.api', 'auth:api']], function () {
	Route::post('/logout' ,     'ProviderAuth\TokenController@logout');

    //Route::post('/refresh/token' , 'ProviderAuth\TokenController@refresh_token');

    Route::group(['prefix' => 'profile'], function () {

        Route::get ('/' ,         'ProviderResources\ProfileController@index');
        Route::post('/' ,         'ProviderResources\ProfileController@update');
        Route::post('/password' , 'ProviderResources\ProfileController@password');
        Route::post('/location' , 'ProviderResources\ProfileController@location');
        Route::post('/language' , 'ProviderResources\ProfileController@update_language');
        Route::post('/available', 'ProviderResources\ProfileController@available');
        Route::get ('/documents', 'ProviderResources\ProfileController@documents');
        Route::post('/documents/store', 'ProviderResources\ProfileController@documentstore');       
        Route::post ('/{id}', 'ProviderResources\ProfileController@destroy');

    });

    Route::resource('providercard', 'Resource\ProviderCardResource');

    Route::post('/chat' , 'ProviderResources\ProfileController@chatPush');

    Route::get('/target' , 'ProviderResources\ProfileController@target');
    Route::resource('trip','ProviderResources\TripController');
    Route::post('cancel',  'ProviderResources\TripController@cancel');
    Route::post('summary', 'ProviderResources\TripController@summary');
    Route::get('help',     'ProviderResources\TripController@help_details');
    Route::get('/wallettransaction', 'ProviderResources\TripController@wallet_transation');
    Route::get('/transferlist', 'ProviderResources\TripController@transferlist');
    Route::post('/requestamount' ,'ProviderResources\TripController@requestamount');
    Route::get('/requestcancel' ,'ProviderResources\TripController@requestcancel');
   


    Route::group(['prefix' => 'trip'], function () {

        Route::post('{id}',          'ProviderResources\TripController@accept');
        Route::post('{id}/rate',     'ProviderResources\TripController@rate');
        Route::post('{id}/message' , 'ProviderResources\TripController@message');
        Route::post('{id}/calculate','ProviderResources\TripController@calculate_distance');

    });
    
    Route::post('requests/rides' , 'ProviderResources\TripController@request_rides');

    Route::group(['prefix' => 'requests'], function () {

        Route::get('/upcoming' ,       'ProviderResources\TripController@scheduled');
        Route::get('/history',         'ProviderResources\TripController@history');
        Route::get('/history/details', 'ProviderResources\TripController@history_details');
        Route::get('/upcoming/details','ProviderResources\TripController@upcoming_details');

    });
    Route::post('/test/push' ,  'ProviderResources\TripController@test');

    Route::group(['prefix' => 'v1', 'namespace' => 'ProviderResources\V1'], function () {
        Route::resource('trip','TripController');
        Route::get('update-round/{id}','TripController@updateIsRound');
    });
    Route::group(['prefix' => 'v2', 'namespace' => 'V2'], function () {
        Route::get('trip','TripController@index');
        Route::post('trip/quote','TripController@quote');
    });
});

