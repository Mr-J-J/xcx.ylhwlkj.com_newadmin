<?php

namespace App\Http\Controllers\H5;

use App\Models\Store;
use App\Support\Code;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    protected $wxapp;

    public function __construct(){
        // $config = config('wechat.official_account.default');
        $this->wxapp = $this->getApp(2);

        
    }
    /**
     * 微信授权登录
     *
     * @param [type] $code
     * @return void
     */
    public function doLogin(){        
        try{
            $wxuser = $this->wxapp->oauth->user();
        }catch(\Exception $e){
            return Code::setCode(Code::REQ_ERROR,'授权失败',$e->getMessage());
        }

        if(!$wxuser->original){
            return Code::setCode(Code::REQ_ERROR,'授权失败');
        }
        
        $store = new Store;        
        $store->openid = $wxuser->original['openid'];
        try{
            $store = $store->where('openid',$store->openid)->firstOrFail();
        }catch(\Exception $e){
            $store->avatar = $wxuser->avatar;
            $store->nickname = $wxuser->nickname;
            $store->sex = intval($wxuser->original['sex']);
            $store->province = $wxuser->original['province'];
            $store->city = $wxuser->original['city'];
            $store->unionid = $wxuser->original['unionid']??'';
            $store->remember_token = $store->openid.$store->unionid;
            $store->store_state = 0;
            $store->save();            
        }
        
        return Code::setCode(Code::SUCC,'授权成功',['token'=>$store->remember_token]);
    }
    // http://md.93.zhishangez.cn/examples/oauth_callback.php?code=011L2WZv30QypW2Emi0w33NJn13L2WZ6&state=7ffc1841fb804cb59572247bc935b1eb
    public function getCode(){
        $response = $this->wxapp->oauth->scopes(['snsapi_userinfo'])->redirect();
        return $response;
    }
}
