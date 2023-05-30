<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 密码重置控制器
    |--------------------------------------------------------------------------
    |
    | 该控制器负责处理密码重置电子邮件和
    |包括一个帮助发送这些通知的特征
    |你的应用程序给你的用户。随意探索这个特征。
    |
    */

    use SendsPasswordResetEmails;
}
