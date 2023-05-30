<?php

namespace App\Http\Controllers\Stores;

use App\CardModels\CardOrder;
use App\CardModels\CardPrice;
use App\CardModels\Cards;
use App\CardModels\RsStores;
use App\CardModels\RsWithDraw;
use App\CardModels\StoreBalanceDetail;
use App\Http\Controllers\WebController;
use App\ApiModels\Wangpiao\CinemasBrand;
use App\Models\Essay;
use App\Models\Poster;
use App\Models\Project;
use App\Models\Rscarousels;
use App\Models\Rshaibao;
use App\Models\Setting;
use App\Models\Suggestion;
use App\Models\Talk;
use App\Models\TicketUser;
use App\Models\UserOrder;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StoreljController extends WebController
{
    public function __construct()
    {
        $token = request('remember_token','');
        $this->user=RsStores::where('remember_token',$token)->first();
    }
    /**
     * api登录
     * @param Request $request
     * @return string
     */
    public function getinfo(Request $request)
    {
       return $this->user;
    }
    /**
     * 获取系统设置
     */
    public function getsetting(Request $request)
    {
        return Setting::getSettings();
    }
    /**
     * 获取影旅卡列表
     */
    public function getcardlist(Request $request)
    {
        $list = Cards::getList();
        $priceList = CardPrice::getCardPrice($this->user->id);
        return compact('list','priceList');
    }

    /**
     * 修改影旅卡商城价格
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upcard(Request $request){
        $saleprice = round($request->input('saleprice',0),2);
        $cardId = $request->input('id',0);

        $cardInfo = Cards::where('id',$cardId)->first();
        if($saleprice && $saleprice < $cardInfo->price){
//            价格不能小于成本价
             return [
                 'status'    => false,
                 'message'   => '商城价格不能小于成本价',
                 'display'   => [],
             ];
        }
        CardPrice::editCardPrice($this->user->id,$cardInfo,$saleprice);
        return [
            'status'    => true,
            'message'   => '价格已设置',
            'display'   => [],
        ];
    }
    /**
     * 海报
     */
    public function img(Request $request){
        $list = Poster::orderBy('sort','desc')->get(['id','poster as lphoto','title','created_at']);
        return compact('list');
    }
    /**
     * 生成海报
     */
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
        $qrcode_name = '/qrcode/'.$this->user['id'].'_'.$comId . '.png';
        $exists = $storage->exists($qrcode_name);
        if(!$exists){
            $app = $this->getApp(1,$comId);
            $scene = $this->user['id'];
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
        $poster_name = '/share/' . md5($film_id.$this->user['id']) .'.jpg';
        $img = Helpers::poster2($this->user,$poster,$qrcode,($type == 1)?$poster:'');
        return $img;

    }
    /**
     * 数据获取
     */
    public function getdata(){
        $store = $this->user;
        $arr = array('commision'=>0,'order'=>0,'member'=>0);
        //今日数据
        $nowdate = date('Y-m-d');
        $today = $arr;
        $today['commision'] = StoreBalanceDetail::where('com_id',$store->id)->whereDate('created_at',$nowdate)->sum('money');
        $today['order'] = StoreBalanceDetail::where('com_id',$store->id)->whereDate('created_at',$nowdate)->count();
        $today['member'] = TicketUser::where('com_id',$store->id)->whereDate('created_at',$nowdate)->count('id');
        //昨日数据
        $yestdate = date('Y-m-d',strtotime('-1day'));
        $yestday = $arr;
        $yestday['commision'] = StoreBalanceDetail::where('com_id',$store->id)->whereDate('created_at',$yestdate)->sum('money');
        $yestday['order'] = StoreBalanceDetail::where('com_id',$store->id)->whereDate('created_at',$yestdate)->count();
        $yestday['member'] = TicketUser::where('com_id',$store->id)->whereDate('created_at',$yestdate)->count('id');
        //本月数据
        $currentMonth = date('m');

        $month = $arr;
        $month['commision'] = StoreBalanceDetail::where('com_id',$store->id)->whereMonth('created_at',$currentMonth)->sum('money');
        $month['order'] = StoreBalanceDetail::where('com_id',$store->id)->whereMonth('created_at',$currentMonth)->count('id');
        $month['member'] = TicketUser::where('com_id',$store->id)->whereMonth('created_at',$currentMonth)->count('id');
        $movie[2]['order']=$today['order'];
        $movie[2]['commision']=$today['commision'];
        $movie[1]['order']=$yestday['order'];
        $movie[1]['commision']=$yestday['commision'];
        $movie[0]['order']=$month['order'];
        $movie[0]['commision']=$month['commision'];

        return compact('today','yestday','month','store','movie');
    }
    /**
     * 分成订单
     */
    public function account(Request $request){
        $storeInfo = $this->user;
        $comId = $storeInfo->id;
        $date = $request->input('date',[]);
        if(!$comId){
            $list = array();
        }else{
            if($date==[]){
                $currentMonth = date('m');
                $list = StoreBalanceDetail::where('com_id',$comId)->whereMonth('created_at',$currentMonth)->get();
            }else{
                foreach ($date as &$item){
                    $item=date('Y/m/d',$item/1000);
                }
                logger($date);
                $list = StoreBalanceDetail::where('com_id',$comId)->whereBetween('created_at',$date)->get();
            }
        }

        foreach ($list as &$item){

            if($item->remark=='用户购票返佣'){
                $item->info = UserOrder::getOrderByOrderNo($item->order_sn);
            }
//            logger($item);
            $rate = CinemasBrand::where('id',$item->info['brand_id'])->value('rs_order_commision');
            $item->bili = $rate;
        }
        $movie=[];
        $money=[];
        foreach ($list as $item){
            $movie[] = $item['order_sn'];
            $money[] = $item['money'];
        }
        return compact('list','money','movie');
    }
    /**
     * 意见反馈
     *
     * @return void
     */
    public function addsuggestion1(Request $request){
        try {
            logger($request->post());
            Suggestion::addSuggestion($request->post(),$this->user->id,1);
        } catch (\Exception $e) {
            return ['code'=>0,'msg'=>$e->getMessage()];
        }
        return ['code'=>200,'msg'=>'提交成功'];
    }
    /**
     * 粉丝获取
     *
     * @return void
     */
    public function getfans(Request $request){
        $id = $request->input('id','');
        $y = $request->input('y','');
        $x = $request->input('x','');
        try {
            $by='';
            if($id!=''){
                if($id=='descending'){
                    $by='desc';
                }else{
                    $by='asc';
                }
                $main = TicketUser::where('com_id',$this->user->id)->orderBy('id',$by)->get();
                $jian= TicketUser::where('com_id',$this->user->id)->where('inviter_id','!=',0)->orderBy('id',$by)->get();
            }else if($y!=''){
                if($y=='descending'){
                    $by='desc';
                }else{
                    $by='asc';
                }
                $main = TicketUser::where('com_id',$this->user->id)->orderBy('total_balance',$by)->get();
                $jian= TicketUser::where('com_id',$this->user->id)->where('inviter_id','!=',0)->orderBy('total_balance',$by)->get();
            }else if($x!=''){
                if($x=='descending'){
                    $by='desc';
                }else{
                    $by='asc';
                }
                $main = TicketUser::where('com_id',$this->user->id)->orderBy('cash_money',$by)->get();
                $jian= TicketUser::where('com_id',$this->user->id)->where('inviter_id','!=',0)->orderBy('cash_money',$by)->get();
            }else{
                $main = TicketUser::where('com_id',$this->user->id)->get();
                $jian= TicketUser::where('com_id',$this->user->id)->where('inviter_id','!=',0)->get();
            }

        } catch (\Exception $e) {
            return ['code'=>0,'msg'=>$e->getMessage()];
        }
        return ['code'=>200,'msg'=>'','data'=>['main'=>$main,'jian'=>$jian]];
    }

    /**
     * 提现记录
     * @param Request $request
     * @return
     */
    public function withdrawList(Request $request){
        $storeInfo = $this->user;
        $created_at = $request->input('created_at',[]);
        logger($this->user);
        $created_at = array_filter((array)$created_at, function ($val) {
            return $val !== '';
        });
        // dd($created_at);
        $list = $list = RsWithDraw::select('id','store_id','title','money','draw_account','account_name','created_at','state')
            ->when($created_at,function($query,$created_at){
                if(!isset($created_at['start'])){
                    return $query->where('created_at','<=',$created_at['end']);
                }
                if(!isset($created_at['end'])){
                    return $query->where('created_at','>=',$created_at['start']);
                }
                return $query->whereBetween('created_at',$created_at);
            })
            ->where('store_id',$this->user->id)
            ->orderBy('created_at','desc')
            ->get();

        return compact('list');
    }

    /**
     * 提现申请
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function dowithdraw(Request $request){
        $storeInfo = $this->user;

        $alipay_account = $request->input('alipay_account','');
        $alipay_name = $request->input('alipay_name','');
        $money = round($request->input('money',0),2);
        $return = [ 'status'=> 'false', 'message'=>'提现申请失败'];
        if(empty($alipay_account)){
            $return['message']='请填写提现支付宝账号';
            return $return;
        }
        if(empty($alipay_name)){
            $return['message']='请填写提现支付宝账号姓名';
            return $return;
        }
        if(empty($money) || $money < 1){
            $return['message']='提现金额必须大于1元';
            return $return;
        }
        if($money > $storeInfo->balance){
            $return['message']='可提现余额不足';
            return $return;
        }
        $storeInfo->alipay_account = $alipay_account;
        $storeInfo->alipay_name = $alipay_name;
        $storeInfo->save();
        $hasdraw = RsWithDraw::where('store_id',$storeInfo->id)->where('state','!=',2)->whereTime('created_at',date('Y-m-d'))->count();
        if($hasdraw){
            $return['message']='每天只能提现一次';
            return $return;
        }
        try {
            RsWithDraw::addDraw($storeInfo,2,$money);
        } catch (\Throwable $th) {
            return $return;
        }
        $return = [ 'status'=> true, 'message'=>'提现申请已提交' ];
        return $return;
    }
    /**
     * 图片上传
     *
     * @param Request $req
     * @return void
     */
    public function upload(Request $req){
        try {
            $res = Helpers::uploadImage3('rsstore');
        } catch (\Exception $e) {
            logger($e->getMessage());
            return ['code'=>0,'msg'=>$e->getMessage()];
        }
        return ['code'=>200,'msg'=>'上传成功'];
    }
    /**
     * 修改资料
     *
     * @param Request $req
     * @return void
     */
    public function upstore(Request $req){
        try {
            if(empty($this->user->id)||$this->user->remember_token==''){
                return ['code'=>0,'msg'=>'请重新登录'];
            }
            $data=$req->input('data');
            logger($data);
            RsStores::where('remember_token',$this->user->remember_token)->update($data);
        } catch (\Exception $e) {
            logger($e->getMessage());
            return ['code'=>0,'msg'=>$e->getMessage()];
        }
        return ['code'=>200,'msg'=>'修改'];
    }
    /**
     * 获取我录入的分销商
     *
     * @param Request $req
     * @return void
     */
    public function getmystore(Request $req){
        try {
            $list = RsStores::where('parentid',$this->user->id)->get();
        } catch (\Exception $e) {
            logger($e->getMessage());
            return ['code'=>0,'msg'=>$e->getMessage()];
        }
        return ['code'=>200,'msg'=>'','data'=>$list];
    }
    /**
     * 录入分销商
     * @param Request $request
     * @return string
     */
    public function instore(Request $request)
    {
        $data = $request->only('phone', 'password','name','logo');

        if(!empty($data['phone'])&&!empty($data['password'])&&!empty($data['name'])){
            $ok = RsStores::where('phone',$data['phone'])->first();
            if(empty($ok)){
                $user = new RsStores();
                $user->store_name=$data['name'];
                $user->phone = $data['phone'];
                $user->password = Hash::make($data['password']);
                $user->store_logo=$data['logo'];
                $user->parentid=$this->user->id;
                $user->save();
                return array('code'=>200,'data'=>$user);
            }
        }
        return array('code'=>0,'msg'=>'录入失败，手机号已存在');
    }
    /**
     * 设置子分销商统计
     * @param Request $request
     * @return string
     */
    public function upmystore(Request $request)
    {
        $data = $request->only('userid', 'set');
        if(!empty($data['userid'])&&!empty($data['set'])){
            $ok = RsStores::where('id',$data['userid'])->update(['paraentsetmoney'=>$data['set']]);
            return array('code'=>200,'msg'=>'设置成功');
        }
        return array('code'=>0,'msg'=>'参数不能为空');
    }
    /**
     * 获取公众号文章
     * @param Request $request
     * @return string
     */
    public function getessay(Request $request)
    {
        $list = Essay::all();
        return array('code'=>200,'msg'=>'','data'=>$list);
    }
    /**
     * 获取新项目
     * @param Request $request
     * @return string
     */
    public function getproject(Request $request)
    {
        $list = Project::all();
        return array('code'=>200,'msg'=>'','data'=>$list);
    }
    /**
     * 获取群列表
     * @param Request $request
     * @return string
     */
    public function gettalk(Request $request)
    {
        $list = Talk::all();
        return array('code'=>200,'msg'=>'','data'=>$list);
    }
    /**
     * 消息列表返回
     */
    public function getmsg(Request $request){
       $list= \App\Models\Msg::where(function ($query){
           $query->where('userid','')
               ->orWhere('userid',$this->user->id);
       })->get();
        return array('code'=>200,'msg'=>'','data'=>$list);
    }
    /**
     * 消息数量返回
     */
    public function getmsgnum(Request $request){
        $list= \App\Models\Msg::where(function ($query){
            $query->where('userid','')
                ->orWhere('userid',$this->user->id);
        })->count();
        return array('code'=>200,'msg'=>'','data'=>$list);
    }
    /**
     * 获取轮播图
     */
    public function getcarousel(Request $request){
        return Rscarousels::all();
    }
    /**
     * 获取轮播图
     */
    public function gethaibao(Request $request){
        return Rshaibao::all();
    }
}
