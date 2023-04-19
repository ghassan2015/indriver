<?php

Route::get('/', 'DispatcherController@index')->name('index');

Route::group(['as' => 'dispatcher.', 'prefix' => 'dispatcher'], function () {
	Route::get('/', 'DispatcherController@index')->name('index');
	Route::post('/', 'DispatcherController@store')->name('store');
	Route::get('/trips', 'DispatcherController@trips')->name('trips');
	Route::post('/calculate/{id}', 'DispatcherController@tripLatLong');
	// Route::get('/trips/{id}/{lati}/{long}', 'DispatcherController@tripLatLong');
	Route::get('/trips/{trip}/{provider}', 'DispatcherController@assign')->name('assign');
	Route::get('/users', 'DispatcherController@users')->name('users');
	Route::get('/providers', 'DispatcherController@providers')->name('providers');
	Route::get('/cancel', 'DispatcherController@cancel')->name('cancel');
	
});

//Admin And dispatcher Duplicate

Route::get('map', 'DispatcherController@map_index')->name('map.index');
Route::get('map/ajax', 'DispatcherController@map_ajax')->name('map.ajax');

Route::any('providers/search', 'DispatcherController@providerSearchFilters')->name('provider.search');
// End Admin and dispatcher Duplicate

Route::resource('service', 'Resource\ServiceResource');

Route::get('password', 'DispatcherController@password')->name('password');
Route::post('password', 'DispatcherController@password_update')->name('password.update');

Route::get('profile', 'DispatcherController@profile')->name('profile');
Route::post('profile', 'DispatcherController@profile_update')->name('profile.update');
Route::get('provider-status/{status?}', 'DispatcherController@providerStatus')->name('providers.status');

// statements
Route::get('/statement/monthly', 'AdminController@statement_monthly')->name('ride.statement.monthly');
Route::get('/statement/range', 'AdminController@statement_range')->name('ride.statement.range');

Route::resource('/requests', 'Resource\TripResource');