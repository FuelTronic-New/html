<?php

namespace App\Http\Middleware;

use Closure;

class AllowAccess
{
    public function handle($request, Closure $next)
    {
        // Define whom not to allow which modules
        $adminArray = []; // allowing all modules // Role ID 1
        $siteAdminArray = ['siteusers','sites']; // allowing all modules // Role ID 2
        $userArray = ['siteusers', 'tags', 'suppliers','vehicles','customers','attendants','hoses','pumps','tanks',
                      'grades','sites','user','jobs','atgdata','payments'];
	    // allowing limited modules // Role ID 3
//        $userArray = ['fueldrops', 'atgreadings', 'fueltransfers']; // allowing limited modules // Role ID 3

        if (auth()->user()->role == '3') {
            if (in_array(\Request::segment(2), $userArray)) {
                return response('Unauthorised to access this page', 404);
            } else {
                return $next($request);
            }
        }

        if (auth()->user()->role == '2') {
            if (in_array(\Request::segment(2), $siteAdminArray)) {
                return response('Unauthorised to access this page', 404);
            } else {
                return $next($request);
            }
        }

	    return $next($request);

    }
}
