<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class WebController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(){

    }
    /**
     * 获取小程序、公众号
     *
     * @param integer $type 1小程序 2公众号 3支付
     * @return void
     */
    protected function getApp($type=1,$comId = 0){
        if($type == 1){
            $config = $comId ? config('wechat.mini_program.default1'):config('wechat.mini_program.default');
            return \EasyWeChat\Factory::miniProgram($config);
        }elseif($type == 2){
            $config = config('wechat.official_account.default');
            return \EasyWeChat\Factory::officialAccount($config);
        }elseif($type == 3){
            $config = $comId ? config('wechat.payment.default1'):config('wechat.payment.default');
            return \EasyWeChat\Factory::payment($config);
        }
    }

}
