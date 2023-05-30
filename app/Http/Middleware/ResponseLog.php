<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Log;

class ResponseLog
{
    

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
            // Log::debug('Response | '.$request->getPathInfo());
            Log::debug('Response | '.$request->getPathInfo().' | ' . $response->getContent());

    }
}
