<?php

/*
|--------------------------------------------------------------------------
| Corporate Routes
|--------------------------------------------------------------------------
*/

Route::get('/', 'CorporateController@dashboard')->name('index');
Route::get('/dashboard', 'CorporateController@dashboard')->name('dashboard');

// Route::resource('user', 'Resource\UserCorporateResource');
Route::resource('user', 'CorporateUsersController');

// Route::resource('vehicle', 'CorporateVehicleController');

Route::group(['as' => 'user.'], function () { 
    Route::get('user/{id}/approve', 'Resource\UserCorporateResource@approve')->name('approve');
    Route::get('user/{id}/disapprove', 'Resource\UserCorporateResource@disapprove')->name('disapprove');
    Route::get('user/{id}/request', 'Resource\UserCorporateResource@request')->name('request');  
});

Route::post('user/wallet/recharge', 'CorporateController@user_wallet_recharge')->name('user.wallet.recharge');
//// user seleted delete
Route::post('user/seleted_delete', 'Resource\UserCorporateResource@user_seleted_delete'); 
Route::resource('requests', 'Resource\TripCorporateResource'); 
// Route::get('user/{id}/request', 'Resource\UserResource@request')->name('user.request');

// Route::get('map', 'CorporateController@map_index')->name('map.index');
// Route::get('map/ajax', 'CorporateController@map_ajax')->name('map.ajax');

Route::get('profile', 'CorporateController@profile')->name('profile');
Route::post('profile', 'CorporateController@profile_update')->name('profile.update');

Route::get('password', 'CorporateController@password')->name('password');
Route::post('password', 'CorporateController@password_update')->name('password.update');

Route::get('recharge', 'CorporateController@recharge_transaction_wallet')->name('wallet.recharge');

Route::post('stripe/add', 'CorporateController@stripe_add')->name('stripe.add');

Route::get('card/default/{id}', 'CorporateController@card_default')->name('card.default');
Route::post('card/delete/{id}', 'CorporateController@card_delete')->name('card.delete');

Route::post('pay/now', 'CorporateController@pay_now')->name('pay.now');

Route::post('prepaid/recharge', 'CorporateController@prepaid_recharge')->name('prepaid.recharge');

// // Static Pages - Post updates to pages.update when adding new static pages.

// Route::get('requests', 'Resource\TripResource@Corporateindex')->name('requests.index');
// Route::delete('requests/{id}', 'Resource\TripResource@Corporatedestroy')->name('requests.destroy');
// Route::get('requests/{id}', 'Resource\TripResource@Corporateshow')->name('requests.show');
// Route::get('scheduled', 'Resource\TripResource@Corporatescheduled')->name('requests.scheduled');
