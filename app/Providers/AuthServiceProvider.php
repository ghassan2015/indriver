<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();
	      
        Route::group(['middleware' => 'passport.provider'], function () {
	        Passport::routes( null, [ 'prefix' => '/api/provider/v1' ] );
        });
	      
        Passport::routes(); // Rider Passport Auth

        Passport::tokensExpireIn(Carbon::now()->addDays(15));

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(90));
		    Passport::tokensCan([
			                        'user' => 'Rider App',
			                        'provider' => 'Driver/Provider App',
		                        ]);
	
	    //Passport::setDefaultScope([
		   //                           'user',
	      //                        ]);
    }
}
