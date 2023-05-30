<?php

namespace App\Http\Controllers\Stores;

use App\CardModels\RsStores;
use App\Http\Controllers\WebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StoreController extends WebController
{
    public function __construct()
    {

//         $this->middleware('auth:rsstorep');
    }
    /**
     * api登录
     * @param Request $request
     * @return string
     */
    public function dologin(Request $request)
    {
        logger('2');
        $credentials = $request->only('phone', 'password');

        if (Auth::attempt($credentials)) {
            // 身份验证通过...
            $user = RsStores::where('phone',$credentials['phone'])->first();
            logger($user);
            $user->remember_token=Str::random(60);
            $user->save();
            return array('code'=>200,'data'=>$user);
        }else{
            return array('code'=>0,'msg'=>'密码或账号错误');
        }
    }
    /**
     * api注册
     * @param Request $request
     * @return string
     */
    public function doregister(Request $request)
    {
        $data = $request->only('phone', 'password','name');

        if(!empty($data['phone'])&&!empty($data['password'])&&!empty($data['name'])){
            $ok = RsStores::where('phone',$data['phone'])->first();
            if(empty($ok)){
                $user = new RsStores();
                $user->store_name=$data['name'];
                $user->phone = $data['phone'];
                $user->password = Hash::make($data['password']);
                $user->store_logo='images/4b2c914f4a5c21933d92aefa249f091d.jpeg';
                $user->save();
                return array('code'=>200,'data'=>$user);
            }

        }
        return array('code'=>0,'msg'=>'注册失败，手机号已注册');
    }
}
