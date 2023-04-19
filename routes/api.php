<?php

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Taxi\Encrypter\Encrypter;

if(version_compare( PHP_VERSION, '7.2.0', '>=')) {
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
}

Route::get('/test-push-noti' , 'UserApiController@testPushNoti');

Route::post('/verify' , 'UserApiController@verify'); // Encrypted
Route::post('/checkemail' , 'UserApiController@checkUserEmail');
Route::post('/is-mobile-verified' , 'UserApiController@isMobileVerified');  // Encrypted
Route::post('/send-mobile-verification-code' , 'UserApiController@sendMobileVerificationCode'); // Encrypted
Route::post('/verify-mobile-verification-code' , 'UserApiController@verifyMobileVerificationCode'); // Encrypted

Route::post('/oauth/token' , 'UserApiController@login'); // Encrypted
Route::post('/signup' , 'UserApiController@signup'); // Encrypted
Route::post('/logout' , 'UserApiController@logout');
Route::get('/checkapi' , 'UserApiController@checkapi');
Route::post('/checkversion' , 'UserApiController@CheckVersion');


Route::post('/auth/facebook', 		'Auth\SocialLoginController@facebookViaAPI'); // Encrypted
Route::post('/auth/google', 		'Auth\SocialLoginController@googleViaAPI'); // Encrypted
Route::post('/forgot/password',     'UserApiController@forgot_password'); // Encrypted
Route::post('/reset/password',      'UserApiController@reset_password'); // Encrypted

Route::post('dial', 'VideoRoomsController@dial_number');
//twilio access token
Route::get('/call/token', 'VideoRoomsController@voiceaccesstoken');

Route::group(['middleware' => ['auth:api']], function () {

	// user profile
	Route::post('/change/password' , 	'UserApiController@change_password');
	Route::post('/update/location' , 	'UserApiController@update_location');
	Route::post('/update/language' , 	'UserApiController@update_language');
	Route::get('/details' ,  'UserApiController@details');
	Route::post('/update/profile' , 	'UserApiController@update_profile');
	Route::post('/{id}' , 	'UserApiController@destroy');
	
	// services
	Route::get('/services' , 'UserApiController@services');
	// provider
	Route::post('/rate/provider' , 'UserApiController@rate_provider');

	// request
	Route::post('/send/request' , 	'UserApiController@send_request');
	Route::post('/cancel/request' , 'UserApiController@cancel_request');
	Route::get('/request/check' , 	'UserApiController@request_status_check');
	Route::get('/show/providers' , 	'UserApiController@show_providers');
	Route::post('/update/request' , 'UserApiController@modifiy_request');
	// history
	Route::get('/trips' , 				'UserApiController@trips');
	Route::get('upcoming/trips' , 		'UserApiController@upcoming_trips');
	Route::get('/trip/details' , 		'UserApiController@trip_details');
	Route::get('upcoming/trip/details' ,'UserApiController@upcoming_trip_details');
	// payment
	Route::post('/payment' , 	'PaymentController@payment');
	Route::post('/add/money' , 	'PaymentController@add_money');
	// estimated
	Route::get('/estimated/fare' , 'UserApiController@estimated_fare');
	// help
	Route::get('/help' , 'UserApiController@help_details');
	// promocode
	Route::get('/promocodes_list','UserApiController@list_promocode');
	Route::get('/promocodes' , 		'UserApiController@promocodes');
	Route::post('/promocode/add' , 	'UserApiController@add_promocode');
	// card payment
    Route::resource('card', 		'Resource\CardResource');
    // card payment
    Route::resource('location', 'Resource\FavouriteLocationResource');
    // passbook
	Route::get('/wallet/passbook' , 'UserApiController@wallet_passbook');
	Route::get('/promo/passbook' , 	'UserApiController@promo_passbook');

	Route::post('/test/push' , 	'UserApiController@test');

	Route::post('/wallet/validate', 'UserApiController@wallet_validate');

	Route::post('/chat' , 'UserApiController@chatPush');

	Route::post('cancelConverge', 'PaymentController@cancelConverge');
	Route::post('successConverge', 'PaymentController@successConverge');

	Route::post('/elavonpayment', 'PaymentController@elavonpayment');
	Route::any('/response/{id}', 'PaymentController@response');
	Route::any('/confirmation/{id}', 'PaymentController@confirmation');
	Route::any('/rejected', 'PaymentController@rejected');

	Route::post('/payment/tips' ,   'PaymentController@tips');

	Route::get('/companyList', 'UserApiController@companyList');
	Route::post('/edit/corprofile', 'HomeController@edit_corprofile');
	Route::post('/corprofile', 'HomeController@update_corprofile');
	Route::get('/video/access/token', 'VideoRoomsController@accesstoken');


	Route::post('/check-corporate-pin', 'UserApiController@check_corporate_pin');

	Route::post('/eta-time/service', 'UserApiController@eta_time_service');
    Route::group(['prefix' => 'v1', 'namespace' => 'API'], function () {
        Route::post('/send/request', 'UserApiController@send_request');
        Route::post('/cancel/request', 'UserApiController@cancel_request');
        Route::get('/request/check', 'UserApiController@request_status_check');
        Route::get('/show/providers', 'UserApiController@show_providers');
        Route::post('/update/request', 'UserApiController@modifiy_request');
        Route::get('/estimated/fare', 'UserApiController@estimated_fare');
        Route::get('/trip/details' , 		'UserApiController@trip_details');
        Route::patch('/stops/{id}' , 		'UserApiController@update_stop');
    });
	Route::group(['prefix' => 'v2', 'namespace' => 'V2'], function () {
        Route::post('/send/request', 'UserApiController@send_request');
		Route::get('/request/check', 'UserApiController@request_status_check');
		Route::post('/request/accept/{id}', 'UserApiController@accept');
		Route::post('/request/reject/{id}', 'UserApiController@reject');
    });

});


//Route::get('test/{case}/{value}' , function(Request $request, $case, $value) {
//	$data = [
//		'name' => 'waqas' ,
//		'father' => 'muMuhammad Khalid' ,
//		'Friend' => 'Khaleeq' ,
//		'Brother' => 'Abbas' ,
//		'Cousin' => 'Sumair' ,
//	];
//	switch ($case){
//		case 'one':
//			//return response()->json(encrypt(['data' => $data]));
//			return response()->json(encrypt('I m string before encryption'));
//			break;
//		case 'two':
//			return response(encrypt(['data' => $data]));
//			break;
//		case 'three':
//			return encrypt(['data' => $data]);
//			break;
//		case 'encrypt':
//			$string =  $value;
//			$encrypter = new Encrypter();
//			return $encrypted = $encrypter->encrypt( $string );
//			break;
//		case 'encryptarray':
//			$string = $data;
//			$encrypter = new Encrypter();
//			return $encrypted = $encrypter->encrypt( $string );
//			break;
//		case 'decrypt':
//			$encrypter = new Encrypter();
//			return $encrypter->decrypt( $value );
//			break;
//		default:
//			return $response = [];
//	}
//});
