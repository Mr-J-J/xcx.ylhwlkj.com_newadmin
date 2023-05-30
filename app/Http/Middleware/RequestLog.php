<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Log;

class RequestLog
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
        $excepts = ['/api/stores/upload','/api/stores/order_list','/admin/products','/api/pw/pwProductNotify'];
        // $excepts = ['/api/stores/upload'];
        $source = strpos($request->userAgent(),'Security Team');
        // logger(json_encode($source));
        $str =  app('request')->getPathInfo();
        if($source > 0){
            $response = $next($request);
            return response()->json(['msg'=>'ok']);
        }
        if(!in_array($str,$excepts)){
            // Log::debug('Request | '.$request->getPathInfo().' |', $request->input());
            Log::debug('Request | '.$request->ip().' | '.$request->getPathInfo().' |', $request->input());
        }
        $response = $next($request);
        return $response;

    }
}
