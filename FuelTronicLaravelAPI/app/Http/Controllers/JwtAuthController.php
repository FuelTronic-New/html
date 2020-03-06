<?php namespace App\Http\Controllers;
use JWTAuth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtAuthController extends Controller
{
    protected $user;

	protected function __construct() {
		try {
	     	if (!$this->user = JWTAuth::parseToken()->authenticate()) {
	       		return response('Not Found', 404);
	     	}
	   	} catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
	    	return response('Token Expired', 503);
	   	} catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
	    	return response('Token Invalid', 504);
	   	} catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
	    	return response('JWT Exception', 504);
	   	} catch (Exception $e) {
	    	return response('Token Error', 505);
	  	}
	}
}