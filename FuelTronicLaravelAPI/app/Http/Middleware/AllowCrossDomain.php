<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class AllowCrossDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        header("Access-Control-Allow-Origin: *");
	    header('Access-Control-Allow-Origin: '.getenv('APP_FRONTEND_URL'));
	    header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH');
        return $next($request);
    }
}
