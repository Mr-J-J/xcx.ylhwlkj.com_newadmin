<?php

namespace App\Http\Controllers\MiniPro;

use App\CardModels\OlCard;
use App\Models\Avatar;
use App\Support\Helpers;
use App\Models\Suggestion;
use App\Models\TicketUser;
use App\Models\user\FormID;
use Illuminate\Http\Request;
use App\Models\user\WithDraw;
use App\Models\user\Commision;
use App\MallModels as M;
use App\MallModels\GroupLogs;
use App\MallModels\Stores;
use App\Models\Poster;

class UserController extends UserBaseController
{
    /**
     * 会员卡列表
     *
     * @return void
     */
    public function groupList(){
        $list = M\Group::getList();
        $userCreatedTime = date("Y-m-d",strtotime($this->user->created_at));
        foreach($list as $item){
            $item->get_time = '';
            $item->user_cash_money = round($this->user->cash_money);
            if(M\Group::$default_group_id == $item->id){
                $item->get_time = $userCreatedTime;
            }
            $logs = GroupLogs::where('group_id',$item->id)->where('user_id',$this->user->id)->latest()->first();
            if(!empty($logs)){
                $item->get_time = date("Y-m-d",strtotime($logs->created_at));
            }
        }
        return $this->success('',$list);
    }

    public function index(){
        //粉丝数量
        $count = TicketUser::where('inviter_id',$this->user->id)->count('id');
        $this->user->token = $this->user->remember_token;
        $this->user->fans_number = $count;
        $this->user->inviter_info = TicketUser::select(['avatar','mobile'])->where('id',$this->user->inviter_id)->first();
        $this->user->is_store = false;
        $this->user->olcard_number = OlCard::where('user_id',$this->user->id)->count();
        // $this->user->makeHidden(['token']);
        //是否商家
        $store = Stores::where('user_id',$this->user->id)->first();
        if(!empty($store)){
            $this->user->is_store = true;
        }
        if(request('com_id',0)){
            $sendModel = new \App\CardModels\CardSend;
            $sendModel->freeSendCard($this->user);
        }
        return $this->success('成功',$this->user);
    }


    /**
     * 我的粉丝
     *
     * @param Request $req
     * @return void
     */
    public function fans(Request $req){
        $type = $req->input('type'); //type 1直接  2间接

        $list = array();
        $field = [
            'avatar',
            'nickname',
            'mobile',
            'created_at',
            'updated_at'
        ];
        $list = TicketUser::when(($type == 1),function($query){
                        return $query->where('inviter_id',$this->user->id);
                    })
                    ->when(($type == 2),function($query){
                        return $query->where('inviter_id2',$this->user->id);
                    })
                    ->orderBy('created_at','desc')
                    ->paginate(10,$field);
        foreach($list as $item){
            $item->mobile = str_replace(\substr($item->mobile,3,4),'****',$item->mobile);
        }

        return $this->success('成功',$list);
    }


    /**
     * 我的佣金
     *
     * @param Request $req
     * @return void
     */
    public function commision(Request $req){

        $type = $req->input('type'); //type1 收  2支
        $map = array(
            'user_id'=>$this->user->id
        );
        if(!empty($type)){
            $map['type'] = $type;
        }

        $list = Commision::where($map)->latest()->paginate(10);
        foreach($list as $item){
            $item->money = sprintf('%.2f',$item->money / 100);
            if($item->type == 2){
                //提现
                $item->money = '-' . $item->money;
            }
            $item->after_money = sprintf('%.2f',$item->after_money / 100);
        }
        return $this->success('成功',$list);
    }

    /**
     * 提现记录
     *
     * @return void
     */
    public function drawList(){
        $list = WithDraw::where('user_id',$this->user->id)->orderBy('created_at','desc')->paginate(10);
        foreach($list as $item){
            $item->title = '提现';
        }
        return $this->success('成功',$list);
    }

    /**
     * 申请提现
     *
     * @return void
     */
    public function applyWithDraw(Request $request){
        $money = $request->input('money',0);
        $userInfo = $this->user;
        $money = round($money);

        if(!$userInfo->balance){
            return $this->error('申请失败：可提现金额不足');
        }
        $drawMoney = $userInfo->balance;
        if($money > 0){
            if($userInfo->balance < $money){
                return $this->error('申请失败：可提现金额不足');
            }
            $drawMoney = $money;
        }
        $startAllowDraw = 1;
        if($userInfo->balance < $startAllowDraw){
            return $this->error("余额大于{$startAllowDraw}才可以提现");
        }

        WithDraw::addDraw($userInfo,$drawMoney);
        return $this->success('提现申请已提交');
    }

    /**
     * 保存formid
     *
     * @param Request $req
     * @return void
     */
    public function saveFormId(Request $req){
        $formid = $req->input('formid','');
        if(empty($formid)){
            return $this->error('formid不能为空');
        }
        FormID::saveFormId($formid,$this->user->id);
        return $this->success('成功');
    }


    /**
     * 海报列表
     *
     * @return void
     */
    public function posterList(Request $req){
        $list = Poster::orderBy('sort','desc')->get(['id','poster as lphoto','title','created_at']);
        $com_id =  (int)$req->input('com_id',0);
        if(!$com_id){
            $com_id = 10001;
        }
        $appid = config('wechat.mini_program.default1.app_id');
        $page = 'pages/index/index?com_id='.$com_id;
        return $this->success('',compact('list'));
    }

    public function myCode(Request $req){
        $film_id = (int)$req->input('film_id',1);
        $poster = $req->input('poster','');
        $comId = (int)$req->input('com_id',0);
        $type = (int)$req->input('type',0);
        if(empty($poster)){
            return $this->success('图片未找到',[]);
        }

        $storage = \Illuminate\Support\Facades\Storage::disk('admin');
        // $qrcode_name = '/qrcode/'.md5($this->user->openid.$comId) . '.png';
        $qrcode_name = '/qrcode/'.$this->user->openid.'_'.$comId . '.png';
        $exists = $storage->exists($qrcode_name);
        if(!$exists){
            $app = $this->getApp(1,$comId);
            $scene = $this->user->id;
            if($comId){
                $scene = "inviter_id={$scene}&com_id={$comId}";
            }
            $response = $app->app_code->getUnlimit($scene, [
                // 'page'  => 'path/to/page',
                'is_hyaline'=> false,
                'width' => 500,
            ]);

            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                $storage->put($qrcode_name, $response);
            }
        }
        $qrcode = Helpers::formatPath($qrcode_name,'admin');
        $poster_name = '/share/' . md5($film_id.$this->user->openid) .'.jpg';
        $img = Helpers::poster2($this->user,$poster,$qrcode,($type == 1)?$poster:'');
        return $this->success('',compact('img'));

    }

    /**
     * 图片上传
     *
     * @param Request $req
     * @return void
     */
    public function upload(Request $req){
        try {
            $res = Helpers::uploadImageFormData('user');
        } catch (\Exception $e) {
            return $this->error('失败：'.$e->getMessage());
        }
        return $this->success('上传成功',$res);
    }

    /**
     * 会员信息修改
     *
     * @param Request $req
     * @return void
     */
    public function updateInfo(Request $req){
        $avatar = $req->input('avatar','');
        $nickname = $req->input('nickname','');
        $needsave = false;
        if(!empty($avatar)){
            $this->user->avatar = $avatar;
            $needsave = true;
        }
        if(!empty($nickname)){
            $this->user->nickname = $nickname;
            $needsave = true;
        }
        $needsave && $this->user->save();
        return $this->success('修改成功');
    }

    /**
     * 修改手机号
     *
     * @param Request $req
     * @return void
     */
    public function updateMobile(Request $req){
        $code = $req->input('code');

        $iv = $req->input('iv');
        $encryptedData = $req->input('encryptedData','');
        $decryptedData = [];
        $app = $this->getApp(1,$this->user->com_id);
        try{
            $session = $app->auth->session((string)$code);
            // dd($session);
            if(!$session['session_key']){
                return $this->error('授权失败');
            }
            $decryptedData = $app->encryptor->decryptData($session['session_key'], $iv, $encryptedData);

        }catch(\Exception $e){
            return $this->error('授权失败');
        }

        if(empty($decryptedData)){
            return $this->error('授权失败');
        }

        $this->user->mobile = $decryptedData['phoneNumber'];
        $this->user->save();

        return $this->error('手机号码已修改');
    }

    /**
     * 意见反馈
     *
     * @return void
     */
    public function suggestion(Request $request){

        try {
            Suggestion::addSuggestion($request->post(),$this->user->id,2);
        } catch (\Exception $e) {
            return $this->error('提交失败:'.$e->getMessage());
        }
        return $this->success('提交成功');
    }
    /**
     * 获取默认头像列表
     */
    public function getavatar(Request $request){
        return Avatar::all();
    }
}
