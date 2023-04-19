<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
	    $this->app->bind(
		    'Taxi\Twilio\Service',
		    'Taxi\Twilio\Verification'
	    );
//	    Log::getMonolog()->popHandler();
//	    Log::useDailyFiles(storage_path('/logs/laravel-').get_current_user().'.log');
    }

    public function register()
    {
        if ($this->app->environment() == 'local') {
            $this->app->register('Hesto\MultiAuth\MultiAuthServiceProvider');
        }
    }
}
