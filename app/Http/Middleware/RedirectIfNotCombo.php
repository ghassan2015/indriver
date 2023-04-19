<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotCombo
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = ['admin','dispatcher'])
	{
		// dd(Auth::guard($guard[0])->check(),Auth::guard($guard[1])->check());	
	    if (!Auth::guard($guard[0])->check() && !Auth::guard($guard[1])->check()) {
	        return redirect('admin/login');
	    }

	    return $next($request);
	}
}