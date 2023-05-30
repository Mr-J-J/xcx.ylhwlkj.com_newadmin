<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;

class RspAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {

        if (Auth::guard('rsstoresp')->guest()) {
            return response()->json(['code' => Code::ERR_HTTP_UNAUTHORIZED,'msg' => '请登录']);
        }

        return $next($request);
    }


    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        // 下面的路由不验证登陆
        $excepts = array('/stores/dologin','/stores/dologout');
        return collect($excepts)
            ->contains(function ($except) use ($request) {
                if ($except !== '/') {
                    $except = trim($except, '/');
                }
                return $request->is($except);
            });
    }
}
