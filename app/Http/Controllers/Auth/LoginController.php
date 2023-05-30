<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 登录控制器
    |--------------------------------------------------------------------------
    |
    | 此控制器处理应用程序的用户身份验证和
    |将它们重定向到您的主屏幕。控制器使用一个特征
    |方便地为您的应用程序提供其功能。
    |
    */

    use AuthenticatesUsers;

    /**
     *登录后将用户重定向到哪里。
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * 创建一个新的控制器实例。
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
