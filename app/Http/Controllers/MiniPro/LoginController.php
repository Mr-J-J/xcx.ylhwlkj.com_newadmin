<?php

namespace App\Http\Controllers\MiniPro;

use App\Support\Code;
use EasyWeChat\Factory;
use App\Models\TicketUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    protected $wxapp;

    public function __construct(){

        $comId = (int)request('com_id',0);
        // $config = config('wechat.mini_program.default');
        $this->wxapp = (Object)$this->getApp(1,$comId);
    }

    public function wxLogin(Request $request){
        $code = $request->input('code');
        if(empty($code)){
            return Code::setCode(Code::REQ_ERROR,'code不能为空');
        }
        try{
            $session = $this->wxapp->auth->session((string)$code);

            if(!empty($session['errcode'])){
                throw new \Exception('请重新登录');
            }
        }catch(\Exception $e){

            return Code::setCode(Code::REQ_ERROR,'授权失败'.$e->getMessage());
        }

        $tuser = TicketUser::where('openid',$session['openid'])->first();
        if(empty($tuser)){
            return Code::setCode(Code::REQ_ERROR,'用户不存在');
        }

        $tuser->fans_number = TicketUser::where('inviter_id',$tuser->id)->count();
        $tuser->inviter_info = TicketUser::select(['avatar','mobile'])->where('id',$tuser->inviter_id)->first();
        $tuser->token = $tuser->remember_token;
        return Code::setCode(Code::SUCC,'登录成功',$tuser);
    }

    public function doLogin(Request $req){
        $code = $req->input('code');
        $iv = $req->input('iv');
        $encryptedData = $req->input('encryptedData','');
        $avatarUrl = $req->input('avatarUrl','');
        $nickName = $req->input('nickName','');
        $gender = $req->input('gender',0);
        $province = $req->input('province','');
        $city = $req->input('city','');
        $inviter_id = (int)$req->input('inviter_id','');
        $decryptedData = [];
        $from_com_id = (int)$req->input('com_id',0);
        $com_id = $from_com_id?:0;
        try{
            $session = $this->wxapp->auth->session((string)$code);
            // dd($session);
            if(empty($session['session_key'])){
                return Code::setCode(Code::REQ_ERROR,'授权失败');
            }

            if(empty($encryptedData)){
                return Code::setCode(Code::REQ_ERROR,'授权失败');
            }

            $decryptedData = $this->wxapp->encryptor->decryptData($session['session_key'], $iv, $encryptedData);

        }catch(\Exception $e){
            logger('登录授权失败：'.$e->getMessage());
            return Code::setCode(Code::REQ_ERROR,'授权失败'.$e->getMessage());
        }

        if(empty($decryptedData)){
            return Code::setCode(Code::REQ_ERROR,'授权失败');
        }
        $tuser = new TicketUser;
        $tuser->openid = $session['openid'];

        try {
            $tuser = $tuser->where('openid',$tuser->openid)->firstOrFail();
        } catch (\Exception $e) {
            if(!empty($session['unionid'])){
                $tuser->unionid = $session['unionid'];
            }
            $tuser->com_id = $com_id;
            $tuser->avatar = $avatarUrl?:env('APP_URL').'/upload/images/avatar.jpg';
            $tuser->nickname = $nickName;
            $tuser->province = $province;
            $tuser->city = $city;
            $tuser->sex = intval($gender);
            $tuser->is_retail = 0; //成为分销商
            $tuser->inviter_id = $inviter_id ?: 0;
            $tuser->inviter_id2 = 0;
            $tuser->group_id = \App\MallModels\Group::$default_group_id;//普通会员
            if($inviter_id > 0){
                $parentUser = TicketUser::where('id',$inviter_id)->first();
                if(!empty($parentUser)){
                    $tuser->inviter_id2 = $parentUser->inviter_id;
                }
            }
            $tuser->mobile = $decryptedData['phoneNumber'];
            $tuser->remember_token = $tuser->openid;
            $tuser->save();
        }

        return Code::setCode(Code::SUCC,'授权成功',['token'=>$tuser->remember_token]);
    }
}
