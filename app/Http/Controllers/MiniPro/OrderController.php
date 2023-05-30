<?php

namespace App\Http\Controllers\MiniPro;

use App\Http\Controllers\NApiController;
use App\Models\Movie;
use App\Models\Cinema;

use App\Models\Newmove;
use App\Models\Newmovie_schedule;
use App\Models\Setting;
use App\Support\Helpers;
use App\Models\UserOrder;
use App\Models\UserOrder1;
use App\Support\NApi;
use Illuminate\Http\Request;
use App\ApiModels\Wangpiao as Api;
use App\CardModels\OlCard;
use App\CardModels\UserWallet;
use App\Models\Store;

class OrderController extends UserBaseController
{
    public function index(Request $req){

        $type = $req->input('type');
        try {
            $result = UserOrder::orderList($this->user->id,intval($type));
        } catch (\Exception $e) {
            return $this->error('失败:'.$e->getMessage());
        }

        return $this->success('成功',$result);
    }

    /**
     * 订单详情
     *
     * @param Request $req
     * @return void
     */
    public function info(Request $req){
        $orderId = $req->input('order_no');

        $info = UserOrder::where('order_no',$orderId)->where('user_id',$this->user->id)->first();

        if(empty($info)){
            return $this->error('失败：订单信息不存在');
        }
        if($info->order_status == 20){
            $apiorder = \App\Models\ApiOrders::getOrder($info->api_lock_sid);
            if(!empty($apiorder)){
                $result = $apiorder->searchOrder();
                if(!$result){
                    $info = UserOrder::where('order_no',$orderId)->where('user_id',$this->user->id)->first();
                }
            }
        }
        if($info->ol_card_id == 0){

            if($info->order_status == 10){
                $info = $info->checkOrder($this->getApp(3,$this->user->com_id),$info);
            }
            // if(request()->ip() == '27.186.194.154'){
            //     $info->ol_card_id = '';
            // }
        }else{
            $info->ol_card_id = OlCard::where('id',$info->ol_card_id)->value('card_no');
        }
        $info->show_date = date('Y-m-d',$info->show_time);
        $info->show_time = date('H:i',$info->show_time);
        $info->close_time = date('H:i',$info->close_time);
        $info->seat_names = str_replace(',',' ',$info->seat_names );
        if(!empty($info->old_seat_names)){
            $info->seat_names = str_replace(',',' ',$info->seat_names ) . ' 已调座';
        }
        $info->kefu_tel = trim(Helpers::getSetting('kefu_tel'));//
        $info->store_tel = '';
        if($info->order_status == 30){
            $res = $info->code;
            $storeId = !empty($res[0]) ?$res[0]->store_id:0;
            $images = \App\Models\user\TicketImg::where('order_no',$info->order_no)->first();
            if($images){
                if(!empty($images->images)){
                    $info->images = explode(',',$images->images);
                }
            }else{
                $imglist = [];
                foreach($res as $code){
                    $imglist[] = url('api/showqrcode?text='.$code->ticket_code.'&r='.time());
                }
                $info->images = $imglist;
            }
            $storeInfo = Store::where('id',$storeId)->first();
            if(!empty($storeInfo)){
                $info->store_tel = $storeInfo->store_phone;
            }

        }
        return $this->success('创建成功',$info);
    }
    /**
     * 购票下单
     *
     * @param Request $request
     * @return void
     */
    public function addOrder(Request $request){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']==1){
            $api = new NApiController();
            return $this->naddOrder($request);
        }
        $data = $request->post();
        // if($_SERVER['REMOTE_ADDR'] != '222.222.168.5' && $_SERVER['REMOTE_ADDR'] != '27.128.47.97'){
        //     return $this->error('系统维护中');
        // }
        $newpaiqiId = $data['paiqi_id']??0;
        $info = Api\Schedules::getSchedulesInfo($newpaiqiId,true);
        if(!empty($info)){
            $data['paiqi_id'] = $info->show_index;
        }

        //com_id
        $ol_card = false;
        if(!empty($data['ol_card'])){
            $ol_card = OlCard::where('id',(int)$data['ol_card'])->where('user_id',$this->user->id)->first();
            if(empty($ol_card)){
                return $this->error('影城卡无效，请重新选择');
            }
            try {
                $ol_card->canUseCard($this->user->id,$data);
            } catch (\Throwable $th) {
                $ol_card = false;
            }
        }

        $pay_param = $result =  array();

        try {

            //锁座
            $order = UserOrder::createOrder($data,$this->user);
            $result = array(
                'order_no'=>$order->order_no,
                'movie_name'=>$order->movie_name
            );
            $seat_ids = $order->seat_ids;//str_replace(',','|',$order->seat_ids);
            $content = [
                'seat_ids' => $seat_ids,
                'paiqi_id'=> $order->paiqi_id,
                'cinema_id' => $order->cinema_id,
            ];
            $param = [
                'user_id'=> $this->user->id,
                'paiqi_id'=> $order->paiqi_id,
                'cinema_id'=> $order->cinema_id,
                'seat_ids' => $seat_ids
            ];

            // 聚福宝
            $param = array(
                'account_id'=>$this->user->id,
                'seat_names'=>$order->seat_names,
                'paiqi_id'=>$order->paiqi_id,
                'phone_num'=>$order->buyer_phone,
                'seat_areas'=>$order->seat_areas,
                'seat_ids'=>$seat_ids,
            );

            $return = \App\Support\Api\ApiHouse::lockSeat($param);
            logger('2');
            $remark = '';
            if($return['ErrNo'] != 0 || empty($return['Data'])){
                $remark = '座位锁定失败:';
                // $content = [];
                \App\Models\OrderLog::addLogs($order->id,$order->order_no,substr($remark,0,450),json_encode($content,256));
                //锁座失败时候的下策
//                UserOrder::where('id',$order->id)->delete();
//                return $this->error('座位锁定失败'.$return['Msg']);
                $order->seat_areas = -1;
                $order->save();
            }
            if($order->use_card && $order->discount_price){
                UserWallet::walletKouFee($this->user,$order);
                $order->retailCardPaySuccess();
            }

            if(!empty($return['Data'])){
                $remark = '锁座成功';//.json_encode($return,JSON_UNESCAPED_UNICODE);
                $sids = array_column($return['Data'],'SID');
                $order->api_lock_sid = implode(',',$sids);
                $order->save();
                $content = [];
                \App\Models\OrderLog::addLogs($order->id,$order->order_no,$remark,json_encode($content,256));
            }

            if($ol_card){
                $order->olCardPaySuccess($ol_card);
                return $this->success('购买成功',['order_info'=>$result,'pay_param'=>$pay_param]);
            }

            $total_fee =round($order->amount * 100);

            if($total_fee > 0 ){
                $app = $this->getApp(3,$this->user->com_id);
                $payResult = $app->order->unify([
                    'body' => $result['movie_name'].'-电影票',
                    'out_trade_no' => $result['order_no'],
                    'total_fee' => round($order->amount * 100),
                    'notify_url' => route('notify',['comId'=>$this->user->com_id]), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                    'trade_type' => 'JSAPI', //请对应换成你的支付方式对应的值类型
                    'openid' => $this->user->openid,
                ]);
                if(empty($payResult['prepay_id'])){
                    logger('订单创建失败 '.json_encode($payResult,256).','.$nowtime);
                }
                $pay_param = $app->jssdk->bridgeConfig($payResult['prepay_id'], false);
           }
        } catch (\Exception $e) {
            $nowtime = time();
            logger('订单创建失败 '.json_encode($result,256).','.$nowtime.':'.$e->getMessage());
            // throw $e;
            return $this->error($e->getMessage());
            // return $this->error("订单创建失败[{$nowtime}]");
        }
        return $this->success('创建成功',['order_info'=>$result,'pay_param'=>$pay_param]);
    }
    /**
     * 新电影票购票下单
     *
     * @param Request $request
     * @return void
     */
    public function naddOrder(Request $request){
        $data = $request->post();
        // if($_SERVER['REMOTE_ADDR'] != '222.222.168.5' && $_SERVER['REMOTE_ADDR'] != '27.128.47.97'){
        //     return $this->error('系统维护中');
        // }
        $newpaiqiId = $data['paiqi_id']??0;
        $info = Newmovie_schedule::where('id',$data['paiqi_id'])->first();
        if(!empty($info)){
            $data['paiqi_id'] = $info->planKey;
        }

        //com_id
        $ol_card = false;
        if($data['ol_card']!=''){
            $ol_card = OlCard::where('id',(int)$data['ol_card'])->where('user_id',$this->user->id)->first();
            if(empty($ol_card)){
                return $this->error('影城卡无效，请重新选择');
            }
            try {
                $ol_card->canUseCard($this->user->id,$data);
            } catch (\Throwable $th) {
                $ol_card = false;
            }
        }

        $pay_param = $result =  array();

        try {

            //锁座
            $order = UserOrder1::createOrder($data,$this->user);
//            return $order;
            $result = array(
                'order_no'=>$order->order_no,
                'movie_name'=>$order->movie_name
            );
            $seat_ids = $order->seat_ids;//str_replace(',','|',$order->seat_ids);
            $content = [
                'seat_ids' => $seat_ids,
                'paiqi_id'=> $order->paiqi_id,
                'cinema_id' => $order->cinema_id,
            ];
            $param = [
                'user_id'=> $this->user->id,
                'paiqi_id'=> $order->paiqi_id,
                'cinema_id'=> $order->cinema_id,
                'seat_ids' => $seat_ids
            ];

            // 聚福宝
            $param = array(
                'account_id'=>$this->user->id,
                'seat_names'=>$order->seat_names,
                'paiqi_id'=>$order->paiqi_id,
                'phone_num'=>$order->buyer_phone,
                'seat_areas'=>$order->seat_areas,
                'seat_ids'=>$seat_ids,
            );

            $tick = array();
            $arr = explode(',', $order->seat_names);
            foreach ($arr as $item){
                $tick[]=array(
                    'seatNo'=>$item,
                    'buyPrice'=>round($order->vprice,2)
                );
            }
            $paiqidInfo=$info;
            $t = isset($paiqidInfo['artificialBuyPrice'])? 2 : 1;
            $return = NApi::lockseat($order->cinema_id,$order->paiqi_id,$order->order_no,$tick,$order->buyer_phone,$t);
            // $remark = '';
            if($return['ret'] != true || empty($return['result'])){
                $remark = '座位锁定失败: '.json_encode($return,JSON_UNESCAPED_UNICODE);
                \App\Models\OrderLog::addLogs($order->id,$order->order_no,$remark,json_encode($content,256));
                //锁座失败时候的下策
//                UserOrder::where('id',$order->id)->delete();
                // return $this->error('座位锁定失败'.$return);
//                return $this->error('该位置已被选择');
                $order->seat_areas = -1;
                $order->save();
            }
//            return $this->error('该位置已被选择');
            if(!empty($return['result'])){
                $remark = '锁座成功';
                $order->api_lock_sid = $return['result']['orderNo'];
                $order->save();
                \App\Models\OrderLog::addLogs($order->id,$order->order_no,$remark,json_encode($content,256));
            }


            if($order->use_card!=0 && $order->discount_price){
                UserWallet::walletKouFee($this->user,$order);
                $order->retailCardPaySuccess();
            }

            if($ol_card){
                $order->olCardPaySuccess($ol_card);
                return $this->success('购买成功',['order_info'=>$result,'pay_param'=>$pay_param]);
            }
            $total_fee =round($order->amount * 100);

            if($total_fee > 0 ){
                $app = $this->getApp(3,$this->user->com_id);
                $payResult = $app->order->unify([
                    'body' => $result['movie_name'].'-电影票',
                    'out_trade_no' => $result['order_no'],
                    'total_fee' => round($order->amount * 100),
                    'notify_url' => route('notify',['comId'=>$this->user->com_id]), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                    'trade_type' => 'JSAPI', //请对应换成你的支付方式对应的值类型
                    'openid' => $this->user->openid,
                ]);
                if(empty($payResult['prepay_id'])){
                    logger('订单创建失败 '.json_encode($payResult,256).','.$nowtime);
                }
                $pay_param = $app->jssdk->bridgeConfig($payResult['prepay_id'], false);
            }
        } catch (\Exception $e) {
            $nowtime = time();
            logger('订单创建失败 '.json_encode($result,256).','.$nowtime.':'.$e->getMessage());
            // throw $e;
            return $this->error($e->getMessage());
            // return $this->error("订单创建失败[{$nowtime}]");
        }
        return $this->success('创建成功',['order_info'=>$result,'pay_param'=>$pay_param]);
    }
    /**
     * 立即付款
     *
     * @param Request $request
     * @return void
     */
    public function payOrder(Request $request){
        $orderNo = $request->input('order_no','');
        $order = UserOrder::getOrderByOrderNo($orderNo);

        if(empty($order)){
           return $this->error('订单信息不存在');
        }

        if($order->order_status != 10 || $order->amount == 0){
           return $this->error('支付失败,'.UserOrder::statusTxt($order->order_status).'不能支付');
        }
        if($order->use_card && $order->discount_price){
            UserWallet::walletKouFee($this->user,$order);
        }
        $result = array(
            'order_no'=>$order->order_no,
            'movie_name'=>$order->movie_name
        );
        $app = $this->getApp(3,$this->user->com_id);
        $payResult = $app->order->unify([
            'body' => $result['movie_name'].'-电影票',
            'out_trade_no' => $result['order_no'],
            // 'total_fee' => 1,
            'total_fee' => round($order->amount * 100),
            // 'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            'notify_url' => route('notify',['comId'=>$this->user->com_id]), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', //请对应换成你的支付方式对应的值类型
            'openid' => $this->user->openid,
        ]);

        $pay_param = $app->jssdk->bridgeConfig($payResult['prepay_id'], false);
        return $this->success('成功',['order_info'=>$result,'pay_param'=>$pay_param]);
    }

    /**
     * 确认订单信息接口
     *
     * @param Request $req
     * @return void
     */
    public function confirmOrder(Request $req){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']==1){
            $api = new NApiController();
            return $this->nconfirmOrder($req);
        }
        $paiqi_id = $req->input('paiqi_id','');
        $seat_ids = $req->input('seat_ids','');
        $seat_names = $req->input('seat_names','');
        $seat_flag = $req->input('seat_flag','');
        $comId = $req->input('com_id',0);
        $scheulesInfo = Api\Schedules::getSchedulesInfo($paiqi_id,true);
        if(empty($scheulesInfo)){
            return $this->error('请选择观影场次');
        }

        $stopTime = (int)Helpers::getSetting('stop_order'); //放映前多少分钟
        if($stopTime){
            $showtime = strtotime("- {$stopTime} minute",$scheulesInfo->show_time);
            if($showtime <= time()){

              // return $this->error('请选择观影场次');//影片开场时间太近票商无法为您出票，请换场次');
            }
        }


        if(!$scheulesInfo->film){
            return $this->error('电影信息未找到');
        }


        if(empty($seat_ids)){
            return $this->error('请选择座位');
        }

        $discount = Helpers::getSetting('tehui_price_rate') / 10;

        $originalPrice = round($scheulesInfo->getOriginal('price') / 100,2);
        $marketPrice = $originalPrice;
        $kuaisu_price = $scheulesInfo->price;
        $tehui_price = $scheulesInfo->local_price;
        $discountPrice = round($originalPrice-$tehui_price,2);

        $cinemaBrand = Api\CinemasBrand::where('id',$scheulesInfo->cinema->brand_id)->first();
        //影旅卡价格
        if($comId && $cinemaBrand){
            // $marketPrice = $scheulesInfo->local_price;
            $kuaisu_price = $originalPrice;
            $discountPrice = $cinemaBrand->calcDiscountMoney($kuaisu_price);
            // $discountPrice = $cinemaBrand->calcDiscountMoney($tehui_price);
            $userWalletBalance = UserWallet::UserCardList($this->user->id)->sum('balance');
            if($userWalletBalance && $userWalletBalance >= $discountPrice){
                $tehui_price = $kuaisu_price - $discountPrice;
            }else{
                $discountPrice = 0;
                $tehui_price = $kuaisu_price;
            }
        }

        //影城卡
        $myOlCard = OlCard::getCardList($this->user->id,$cinemaBrand->id,$scheulesInfo->cinema->id);

        $result = array(
            'film_name'=>$scheulesInfo->film->show_name,
            'poster'=>$scheulesInfo->film->poster,
            'seat_ids' => $seat_ids,
            'seat_names' => $seat_names,
            'seat_flag' => $seat_flag,
            'seat_names_txt' => str_replace(',',' ',$seat_names),
            'show_time' => $scheulesInfo->show_date . ' ' . $scheulesInfo->show_time_txt,
            'show_version' => $scheulesInfo->show_version,
            'hall_name' => $scheulesInfo->hall_name,
            'cinema_name'=> $scheulesInfo->cinema->cinema_name,
            'market_price'=> $marketPrice,
            'kuaisu_price'=>$kuaisu_price,
            'discount'=> $discountPrice,
            'tehui_price'=> round($tehui_price,2),
            'phone'=> $this->user->mobile,
            'count'=> count(explode(',',$seat_ids)),
            'cardlist'=> $myOlCard?:[],
            'kuaisu_show'=>(int)Helpers::getSetting('kusu_show'), //0不显示快速购票 1显示快速购票
        );
        return $this->success('',$result);

    }
    /**
     * 新确认订单信息接口
     *
     * @param Request $req
     * @return void
     */
    public function nconfirmOrder(Request $req){
        $cinemaName=$req->input('cinemaname','');
        $paiqi_id = $req->input('paiqi_id','');
        $schedule=Newmovie_schedule::where('id',$paiqi_id)->first();
        $paiqi_id=$schedule->planKey;
        $seat_ids = $req->input('seat_ids','');
        $seat_names = $req->input('seat_names','');
        $seat_flag = $req->input('seat_flag','');
        $starttime=$req->input('startTime','');
        $cinemacode=$req->input('cinemacode','');
        $filmid=$req->input('filmid','');
        $comId = $req->com_id;
        logger('confirmOrder:'.json_encode($req->all()));
        $cardId = (int)$req->input('card_id','');
        $pay_type = (int)$req->input('pay_type',1);//1余额支付 2微信支付 4快速出票

        try {
            $scheulesInfo=[];
//            $scheulesInfo = Api\Schedules::where('show_index',$paiqi_id)->firstOrFail();
            $schedulesList = Napi::getplan($cinemacode);
            $list=$schedulesList['result'];
            $key = array_search($paiqi_id, array_column($list, 'planKey'));
            logger($key);
            if ($key !== false) {
                $scheulesInfo=$list[$key];
            } else {
                return $this->error('请选择观影场次');
            }
        } catch (\Exception $e) {
            return $this->error('请选择观影场次'.$e);
        }

        $stopTime = (int)Helpers::getSetting('stop_order'); //放映前多少分钟
        if($stopTime){
            $showtime = strtotime("- {$stopTime} minute",strtotime($starttime));
            logger($showtime);
            if($showtime <= time()){
                return $this->error('影片开场时间太近票商无法为您出票，请换场次');
            }
        }
        if(!$scheulesInfo['filmName']){
            return $this->error('电影信息未找到');
        }
        if(empty($seat_ids)){
            return $this->error('请选择座位');
        }
        $discount = Helpers::getSetting('tehui_price_rate') / 10;
        logger($scheulesInfo);
        $originalPrice = $scheulesInfo['buyPrice'];
        $marketPrice = $originalPrice;
        $kuaisu_price = $scheulesInfo['buyPrice'];
        $tehui_price = $scheulesInfo['thirdReferencePrice'];
        $discountPrice = round($originalPrice-$tehui_price,2);

//        $cinemaBrand = Api\CinemasBrand::where('id',$scheulesInfo->cinema->brand_id)->first();
        $cinemaBrand = Api\CinemasBrand::where('id',0)->first();
        //影旅卡价格
        if($comId && $cinemaBrand){
            // $marketPrice = $scheulesInfo->local_price;
            $kuaisu_price = $originalPrice;
            $discountPrice = $cinemaBrand->calcDiscountMoney($kuaisu_price);
            // $discountPrice = $cinemaBrand->calcDiscountMoney($tehui_price);
            $userWalletBalance = UserWallet::UserCardList($this->user->id)->sum('balance');
            if($userWalletBalance && $userWalletBalance >= $discountPrice){
                logger($kuaisu_price .'|'. $discountPrice);
                $tehui_price = $kuaisu_price - $discountPrice;
            }else{
                $discountPrice = 0;
                $tehui_price = $kuaisu_price;
            }
        }

        //影城卡
        $myOlCard = OlCard::getCardList($this->user->id,$cinemaBrand->id);
        $film = Newmove::where('filmNo',$scheulesInfo['filmCode'])->first();
        logger($film);
        $result = array(
            'film_name'=>$scheulesInfo['filmName'],
            'poster'=>$film->trailerCover,
            'seat_ids' => $seat_ids,
            'seat_names' => $seat_names,
            'seat_flag' => $seat_flag,
            'seat_names_txt' => str_replace(',',' ',$seat_names),
            'show_time' => $scheulesInfo['startTime'],
            'show_version' => $scheulesInfo['copyType'],
            'hall_name' => $scheulesInfo['hallName'],
            'cinema_name'=> $cinemaName,
            'market_price'=> $marketPrice,
            'kuaisu_price'=>$kuaisu_price,
            'discount'=> $discountPrice,
            'tehui_price'=> round($tehui_price,2),
            'phone'=> $this->user->mobile,
            'count'=> count(explode(',',$seat_ids)),
            'cardlist'=> $myOlCard?:[],
            'kuaisu_show'=>(int)Helpers::getSetting('kusu_show'), //0不显示快速购票 1显示快速购票
        );
        return $this->success('',$result);


    }
    /**
     * 取消订单
     *
     * @param Request $req
     * @return void
     */
    public function cancelOrder(Request $req){
        $order_no = $req->input('order_no','');
        $order = UserOrder::getOrderByOrderNo($order_no);
        $order->cancelOrder($order);
        return $this->success('订单取消成功');
    }



    /**
     * 吃喝玩乐订单 [订单列表]
     */
    public function list(Request $requst){
        $list = array();
        return $this->success('',$list);
    }


}
