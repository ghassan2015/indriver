<?php namespace App\Http\Middleware;

use Config;
use Closure;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Log;

class LogsApiMiddleware {
	
	public function handle ( $request, Closure $next ) {
		 Log::info( 'Request url: ' . request()->fullUrl() . ' Method: ' . request()->method(), request()->all());
		return $next( $request );
	}
}
