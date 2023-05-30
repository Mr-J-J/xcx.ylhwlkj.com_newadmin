<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\Code;
use Illuminate\Support\Facades\Auth;

class AuthUserToken
{
    /**
     * 处理传入的请求。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (Auth::guard('users')->guest()) {
            return response()->json(['code' => Code::ERR_HTTP_UNAUTHORIZED,'msg' => '请登录']);
        }
        return $next($request);
    }
}
