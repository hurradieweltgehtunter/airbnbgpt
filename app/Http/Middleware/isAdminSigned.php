<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class isAdminSigned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // get the GET parameter $key
        $key = $request->query('key');

        if($key !== env('ADMIN_SECRET')) {
            Log::debug('isAdminSigned: Key not found or wrong');
            return response('Not Found', 404);
        }


        return $next($request);
    }
}
