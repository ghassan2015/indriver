<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Hash;

class PassportProviderMiddleware {
	
	public function handle ( $request, Closure $next ) {
		//config()->set( 'auth.guards.api.provider', 'providers' );
		config()->set('auth.providers.users.model', 'App\Provider');
		
		return $next( $request );
	}
}
