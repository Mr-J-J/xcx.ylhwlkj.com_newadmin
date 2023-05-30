<?php

namespace App\Models;

use App\Support\Helpers;
use App\CardModels\UserWallet;
use App\Models\user\Commision;
use App\Models\user\TicketCode;
use App\ApiModels\Wangpiao as Api;
use App\ApiModels\Wangpiao\Schedules;
use App\CardModels\OlCard;
use App\CardModels\OlCardExChange;
use Illuminate\Support\Facades\DB;
use App\Models\store\OfferServices;
use App\Models\store\StoreOfferOrder;
use App\Support\Api\ApiHouse;
// use App\Support\WpApi;
use Illuminate\Database\Eloquent\Model;
/**
 * 用户订单模型
 */
class UserOrder1 extends Model
{
    const CANCEL = 0;
    const WAIT_PAY = 10;
    const PAY_SUCCESS = 20;
    const ORDER_SUCCESS = 30;
    const ORDER_BACK = 40;
    protected $table = 'user_orders';
    protected $appends = ['status_txt'];
    // 订单状态 0:取消 10:待付款 20待出票/已付款 30已出票 40退款

    /**
     * 修改订单状态为出票
     *
     * @param [type] $order_no
     * @return void
     */
    public static function outTicket($order_no){
        $order = self::where('order_no',$order_no)->first();
        // $orderStatus = $order->order_status;
        // $codeCount = TicketCode::where("order_id",$order->id)->count();
        // if($codeCount != $order->ticket_count){
        // Helpers::exception("出票数量({$codeCount})与购买数量({$order->ticket_count})不符");
        // }
        $order->order_status = 30;
        $order->save();

        //计算订单佣金
        Commision::clacCommision($order);
        $user = TicketUser::where('id',$order->user_id)->first();
        $user->calcCashMoney($order->amount);
        UserPayDetail::addDetail($user,$order);
        if($order->com_id > 0){
            \App\CardModels\RsStores::settleTicketOrder($order);
        }
    }

    /**
     * 是否可以出票
     *
     * @param string $orderNo
     * @return boolean
     */
    public  function canOutTicket(){
        $info = $this;
        if($info->order_status != self::PAY_SUCCESS) return false;
        return true;
    }
    /**
     * * 订单列表
     *
     *
     * @param [type] $user_id
     * @param integer $type 0全部 1待付款 2待出票 3已出票  4取消  5退款
     * @return void
     */
    public static function orderList($user_id,$type = 0){
        $map = array(
            'user_id'=>$user_id
        );
        if($type == 1){
            $map['order_status'] = self::WAIT_PAY;
        }else if($type == 2){
            $map['order_status'] = self::PAY_SUCCESS;
        }else if($type == 3){
            $map['order_status'] = self::ORDER_SUCCESS;
        }else if($type == 4){
            $map['order_status'] = self::CANCEL;
        }else if($type == 5){
            $map['order_status'] = self::ORDER_BACK;
        }

        $field = array(
            'order_no',
            'buy_type',
            'buyer_phone',
            'amount',
            'ticket_count',
            'movie_name',
            'movie_image',
            'close_time',
            'show_time',
            'citys',
            'cinemas',
            'halls',
            'seat_names',
            'order_status',
            'created_at',
            'refund_status'
            // 'view_time',
            // 'status_txt'
        );

        $orderList = self::select($field)->where($map)->orderBy('created_at','desc')->paginate(10);
        $today = \strtotime(date('Y-m-d'));
        foreach($orderList as &$item){
            $str = '';
            $showtime = strtotime(date('Y-m-d'),$item['show_time']);
            if($today == $showtime){
                $str .= '今天 ' . date('m-d');
            }
            $item['view_time'] = $str .' '. date('H:i',$item['show_time']) .'~' . date('H:i',$item['close_time']);
            $item['seat_names'] = str_replace(',',' ',$item['seat_names']);
        }
        return $orderList;
    }
    public static function statusTxt($status = 0,$refund_status = 1){
        $arr = array(
            0 => '已取消',
            10 => '待付款',
            20 => '待出票',
            30 => '已出票',
            40 => '已退款'
        );
        if($status == 40){
            $refundStatus = ['','退款中','退款成功','退款失败'];
            $arr[$status] = $refundStatus[$refund_status];
        }
        return $arr[$status];
    }

    public function getStatusTxtAttribute(){
        $refundStatus = $this->attributes['refund_status'] ?? 1;
        $txt = self::statusTxt($this->attributes['order_status'],$refundStatus);
        $showtime = $this->getOriginal('show_time');
        if($this->attributes['order_status'] == 30 && $showtime < time()){
            $txt = '已放映';
        }
        return $txt;
    }

    /**
     * 创建订单
     *
     * @param [type] $data
     * @param TicketUser $user
     * @return App\Models\UserOrder
     */
    public static function createOrder(array $data,TicketUser $user){
        extract($data);
        if((int)$agreements == 0){
            Helpers::exception('请先同意购票协议');
        }
        if(empty($phone)){
            Helpers::exception('请填写手机号码');
        }

        if(empty($paiqi_id)){
            Helpers::exception('参数错误');
        }
        if(empty($seat_ids) || empty($seat_names)){
            Helpers::exception('请选择座位下单');
        }
        $paiqidInfo = Newmovie_schedule::where('planKey',$data['paiqi_id'])->first();
        if(empty($paiqidInfo)){
            Helpers::exception('请选择观影场次');
        }

        $stopTime = (int)Helpers::getSetting('stop_order'); //放映前多少分钟
        if($stopTime){
            $showtime = strtotime("- {$stopTime} minute",strtotime($paiqidInfo['startTime']));
            if($showtime <= time()){
                Helpers::exception('影片开场时间太近票商无法为您出票，请换场次');
            }
        }

        $seatsArray = explode(',',$seat_ids);
        if(count($seatsArray) > 4){
            Helpers::exception('最多只能购买4个座位');
        }
        if(!$paiqidInfo->filmCode){
            Helpers::exception('影片信息不存在');
        }
        logger($paiqidInfo);
        $order = new UserOrder;
        $order->order_no = Helpers::makeOrderNo('CK');
        $order->user_id = $user->id;

        $order->buy_type = intval($buy_type); //1特惠购票 2快速购票
        $order->accept_seats = intval($accept_seats); //0不接受 1接受
        $order->buyer_phone = $phone;
        $order->agreements =(int)$agreements;


        $order->halls = $paiqidInfo->hallName;
        $order->cinema_id = $paiqidInfo->cinemaId;
        $cinema = Newcinemas::where('cinemaCode',$paiqidInfo->cinemaId)->first();
        logger($cinema);
        $order->cinemas = $cinema->cinemaName;
        $order->citys = $cinema->city;
        $order->brand_id = 0;
        $order->seat_areas = $section_id;

        $originalPrice = $paiqidInfo->buyPrice;
        $order->ticket_count   =  count($seatsArray);
        $order->market_price = $originalPrice; //快速购票价格
        $order->discount_price = $originalPrice - $paiqidInfo->thirdReferencePrice>0?$paiqidInfo->thirdReferencePrice:$originalPrice; //优惠金额
        $order->use_card = 0;
        $order->amount = $paiqidInfo->market_price;
        //特惠
        // $discount_tehui = round(Helpers::getSetting('tehui_price_rate'),2) / 10; //90% 相对于快速购票
        if($order->buy_type == 1){
//            $order->discount_price = $originalPrice - $paiqidInfo->local_price; //优惠金额
            $order->amount = $order->market_price;  //特惠购票价格
        }

        //影旅卡价格
        if(!empty($com_id)){

            $order->com_id = $com_id;
            $cinemaBrand = Api\CinemasBrand::where('id',0)->first();
//            $cinemaBrand = Api\CinemasBrand::where('id',$paiqidInfo->cinema->brand_id)->first();
            if($cinemaBrand){
                // $order->market_price = $paiqidInfo->local_price;
                $order->discount_price = $discountPrice = 0;
                $userWalletBalance = UserWallet::UserCardList($user->id)->sum('balance');

                if($order->buy_type == 1){
                    $order->discount_price = $discountPrice = $cinemaBrand->calcDiscountMoney($order->market_price);
                }

                if($discountPrice && $userWalletBalance >= $discountPrice){
                    $order->use_card = 1;
                    $order->amount = $order->market_price - $discountPrice;
                }else{
                    $order->discount_price = $discountPrice = 0;
                    $order->amount = $order->market_price;
                }
            }

        }
        $order->market_price = $order->market_price * $order->ticket_count;
        $order->amount = $order->amount * $order->ticket_count;
        $order->discount_price = $order->discount_price * $order->ticket_count;

        if(!empty($com_id) && $order->discount_price && $user->com_id){
            $userWalletBalance = UserWallet::UserCardList($user->id)->sum('balance');
            if($userWalletBalance < $order->discount_price){
                Helpers::exception('影旅卡余额不足');
            }
        }

        $order->vprice = $paiqidInfo['buyPrice'];
        $order->movie_name = $paiqidInfo['filmName'];
        $film = Newmove::where('filmNo',$paiqidInfo['filmCode'])->first();
        $order->movie_image = $film->trailerCover;
        $order->show_time = strtotime($paiqidInfo['startTime']);
        $order->show_version = $paiqidInfo['copyType'];
        $order->close_time = strtotime($paiqidInfo['endTime']);
        // try {
        //     $duration =  $paiqidInfo->film->film_duration;
        //     $order->close_time = strtotime("+ {$duration} minute",$paiqidInfo->show_time);
        // } catch (\Throwable $th) {
        //     logger($th->getMessage().'///UserOrder:196');
        // }

        $order->paiqi_id = $paiqi_id;
        $order->seat_names = $seat_names;
        $order->seat_ids = $seat_ids;

        $order->seat_flag = $seat_flag; //0：普通座位，1：情侣首座(左座)，2：情侣第二座(右座)
        $order->api_order_id = '';
        $order->order_status = 10; //0:取消 10:待付款 20待出票/已付款 30已出票
        $order->pay_status = 1;
        $delay = self::getDelayTime('order_pay_ttl');
        $order->expire_time = time() + $delay;
        try {
            $order->save();
        } catch (\Exception $e) {
            Helpers::exception($e->getMessage());
        }
        try {
            \App\Jobs\UpdateOrder::dispatch($order)->delay($delay);
        } catch (\Throwable $th) {
            logger('UserOrder:212,redis connect fail');
        }
        return $order;
    }

    /**
     * 根据订单号获取订单信息
     *
     * @param [type] $order_no
     * @return App\Models\UserOrder
     */
    public static function getOrderByOrderNo($order_no){
        try {
            $order = self::where('order_no',$order_no)->firstOrFail();
        } catch (\Exception $th) {
            Helpers::exception('订单不存在');
        }
        return $order;
    }

    /**
     * 订单支付成功
     *
     * @param [type] $data
     * @param [type] $order_id
     * @return void
     */
    public function paySuccess(UserOrder $order,$transaction_id = ''){
        $order->order_status = 20;//支付成功待出票

        // $delay = (int) self::getDelayTime('order_cancel_ttl');
        // $order->expire_time = $delay ? time() + $delay : 0;
        $order->expire_time = 0; //出票没有超时，必须要出票
        $order->pay_status = 2; //已支付
        $order->pay_name = 'weixin';
        $order->pay_time = time();
        $order->transaction_id = $transaction_id;
        $order->save();
        try {
//            $user = TicketUser::where('id',$order->user_id)->first();
//            UserWallet::walletKouFee($user,$order);
            $isRedirectOut = (int)Helpers::getSetting('redirect_out_ticket');
            $brandIsRedirectOut = (int)Api\CinemasBrand::where('id',$order->brand_id)->value('redirect_out_ticket');
            if(!$isRedirectOut || ($isRedirectOut && !$brandIsRedirectOut) || $order->seat_areas==='-1'){
                $offerService = new OfferServices;
                $offerOrder = (new StoreOfferOrder)->createOfferOrder($order);
                if($offerOrder === false){
                    return false;
                }
                $kususet = (int)Helpers::getSetting('kusu_offer_show');
                if($order->buy_type == 1 || $kususet){ //竞价
                    $delay = StoreOfferOrder::getDelayTime('offer_ttl');
                    $offerService->startOffer($offerOrder,$delay);
                }else{
                    $defaultStoreId = $offerService->getDefaultStore($order->brand_id);
                    $offerService->setOrderStore($defaultStoreId,$offerOrder);
                }
            }else{
                logger('接口');
                //网票网直接出票
                $order->redirectOutTicket();
            }
        } catch (\Throwable $th) {
            logger($th->getMessage());
        }
        return $order;
    }



    /**
     * 影旅卡全额支付
     *
     * @return void
     */
    public function retailCardPaySuccess(){
        $order = $this;
        if($order->amount >0)    {
            return $order;
        }
        //影旅卡全额抵扣时
        DB::beginTransaction();
        try {
            $order->order_status = 20;//支付成功待出票
            $order->expire_time = 0; //出票没有超时，必须要出票
            $order->pay_status = 2; //已支付
            $order->pay_name = 'ol_card_wallet';
            $order->pay_time = time();
            $order->ol_card_id = 0;
            $order->transaction_id = '';
            $order->save();

            $isRedirectOut = (int)Helpers::getSetting('redirect_out_ticket');
            $brandIsRedirectOut = (int)Api\CinemasBrand::where('id',$order->brand_id)->value('redirect_out_ticket');
            if(!$isRedirectOut || ($isRedirectOut && !$brandIsRedirectOut) || $order->seat_areas==-1){
                $offerService = new OfferServices;
                $offerOrder = (new StoreOfferOrder)->createOfferOrder($order);
                if($offerOrder === false){
                    throw new \Exception('下单失败');
                }
                $kususet = (int)Helpers::getSetting('kusu_offer_show');
                if($order->buy_type == 1 || $kususet){ //竞价
                    $delay = StoreOfferOrder::getDelayTime('offer_ttl');
                    $offerService->startOffer($offerOrder,$delay);
                }else{
                    $defaultStoreId = $offerService->getDefaultStore($order->brand_id);
                    $offerService->setOrderStore($defaultStoreId,$offerOrder);
                }
            }else{
                //网票网直接出票
                $order->redirectOutTicket();
            }


        } catch (\Throwable $th) {
            // Helpers::exception('创建失败[error:ol_card]');
            DB::rollback();
            throw $th;
        }
        DB::commit();
        return $order;
    }


    /**
     * 影城卡支付成功
     *
     * @param OlCard $ol_card
     * @return void
     */
    public function olCardPaySuccess(OlCard $ol_card){
        DB::beginTransaction();
        try {
            $order = $this;
            $order->order_status = 20;//支付成功待出票
            $order->expire_time = 0; //出票没有超时，必须要出票
            $order->pay_status = 2; //已支付
            $order->pay_name = 'ol_card';
            $order->pay_time = time();
            $order->ol_card_id = $ol_card->id;
            $order->discount_price = $order->amount + $order->discount_price;
            $order->amount = 0;
            $order->transaction_id = '';
            $order->save();
            $ol_card->use_number = $ol_card->use_number + $order->ticket_count;
            $ol_card->save();
            OlCardExChange::createLog($order);

            $isRedirectOut = (int)Helpers::getSetting('redirect_out_ticket');
            $brandIsRedirectOut = (int)Api\CinemasBrand::where('id',$order->brand_id)->value('redirect_out_ticket');
            if(!$isRedirectOut || ($isRedirectOut && !$brandIsRedirectOut) || $order->seat_areas==-1){
                $offerService = new OfferServices;
                $offerOrder = (new StoreOfferOrder)->createOfferOrder($order);
                if($offerOrder === false){
                    throw new \Exception('下单失败');
                }
                $kususet = (int)Helpers::getSetting('kusu_offer_show');
                if($order->buy_type == 1 || $kususet){ //竞价
                    $delay = StoreOfferOrder::getDelayTime('offer_ttl');
                    $offerService->startOffer($offerOrder,$delay);
                }else{
                    $defaultStoreId = $offerService->getDefaultStore($order->brand_id);
                    $offerService->setOrderStore($defaultStoreId,$offerOrder);
                }
            }else{
                //网票网直接出票
                $order->redirectOutTicket();
            }


        } catch (\Throwable $th) {
            // Helpers::exception('创建失败[error:ol_card]');
            DB::rollback();
            throw $th;
        }
        DB::commit();
        return $order;
    }

    /**
     * 聚福宝直接出票
     *
     * @return void
     */
    public function redirectOutTicketJfb(){
        $order = $this;
        if(empty($order->api_lock_sid)){
            return false;
        }

        $paiqidInfo = Api\Schedules::where('show_index',$order->paiqi_id)->first();
        if(empty($paiqidInfo)){
            return false;
        }

        try {
            $vprice = $order->vprice;
            if($order->seat_areas != ''){
                $vprice = 0;
                $areasIds= explode(',',$order->seat_areas);
                foreach($areasIds as $value){
                    $sectionPrice = $paiqidInfo->getScetionInfo($value,true);
                    if(!is_numeric($sectionPrice)){
                        $sectionPrice = 0;
                    }
                    $vprice += $sectionPrice;
                }
            }
            $orderAmount = $vprice?:$order->market_price;
            $orderAmount = round($orderAmount / 100,2);
            $apiOrderModel = new ApiOrders;
            // // $mobile = WpApi::ssl_encrypt($order->buyer_phone);
            $mobile = $order->buyer_phone;
            $pay_no = $order->transaction_id?:$order->getOrderNo();
            $api_order = $apiOrderModel->createOrder($order->api_lock_sid,$order->order_no,$mobile,$orderAmount,$orderAmount,$pay_no);
            ApiHouse::payOrder($order->api_lock_sid,$order->order_no);
            // \App\Jobs\Wangpiao\OutTicketJob::dispatch($api_order)->delay(1);
            // logger('进入自动出票任务：'.$order->api_lock_sid.' '.$order->order_no);

        } catch (\Throwable $th) {
            logger('订单号'.$order->getOrderNo().',聚福宝直接出票：'.$th->getMessage());
        }
    }

    /**
     * 网票网直接出票
     *
     * @return void
     */
    public function redirectOutTicket(){
        return $this->redirectOutTicketJfb();
        $order = $this;
        if(empty($order->api_lock_sid)){
            return false;
        }
        $paiqidInfo = Api\Schedules::where('show_index',$order->paiqi_id)->first();
        if(empty($paiqidInfo)){
            return false;
        }
        try {
            $vprice = $order->vprice;
            if($order->seat_areas != ''){
                $vprice = 0;
                $areasIds= explode(',',$order->seat_areas);
                foreach($areasIds as $value){
                    $sectionPrice = $paiqidInfo->getScetionInfo($value,true);
                    $vprice += $sectionPrice;
                }
            }
            $orderAmount = $vprice?:$order->market_price;
            $apiOrderModel = new ApiOrders;
            // $mobile = WpApi::ssl_encrypt($order->buyer_phone);
            $mobile = $order->buyer_phone;
            $pay_no = $order->transaction_id?:$order->getOrderNo();
            $api_order = $apiOrderModel->createOrder($order->api_lock_sid,$order->order_no,$mobile,$orderAmount,$orderAmount,$pay_no);
            \App\Jobs\Wangpiao\OutTicketJob::dispatch($api_order)->delay(1);
            logger('进入自动出票任务：'.$order->api_lock_sid.' '.$order->order_no);
        } catch (\Throwable $th) {
            logger('订单号'.$order->getOrderNo().',网票网自动出票异常：'.$th->getMessage());
        }
    }

    /**
     * 微信支付手动查单
     *
     * @param UserOrder $order
     * @return void
     */
    public function checkOrder($app,UserOrder $order){
        if($order->pay_status != 1) return $order;
        $result = $app->order->queryByOutTradeNumber($order->order_no);

        if($result['return_code'] == 'SUCCESS'){
            if($result['result_code'] == 'SUCCESS' ){
                if($result['trade_state'] == 'SUCCESS'){
                    return $order->paySuccess($order,$result['transaction_id']);
                }
            }
        }

        return $order;

    }

    /**
     * 取消订单
     *
     * @param UserOrder $order
     * @return void
     */
    public function cancelOrder(){
        $order = $this;
        if($order->order_status > 10){
            Helpers::exception(self::statusTxt($order->order_status).'订单无法取消');
        }
        $order->order_status = 0;//取消
        $order->save();

        if($order->use_card){ //使用了影旅卡
            UserWallet::walletBackBalance($order,'订单取消');
        }
    }

    /**
     * 订单退款
     *
     * @param UserOrder $order
     * @return void
     */
    public function refundOrder(UserOrder $order){
        if($order->order_status == 40 && $order->refund_status == 2){
            Helpers::exception($order->order_no.'订单已退款');
        }
        if($order->order_status !=20 && $order->order_status != 40){
            Helpers::exception(self::statusTxt($order->order_status).'订单无法退款');
        }
        //TODO 退款
        $order->order_status = 40;
        $order->refund_status = 1;//退款中
        if(empty($order->transaction_id)){
            Helpers::exception('微信支付订单号不存在');
        }

        $config = config('wechat.payment.default');
        $app = \EasyWeChat\Factory::payment($config);
        $refundNo = 'REFUND-' . $order->order_no;
        $orderFee = $order->amount * 100;
        logger($order->transaction_id);
        logger($refundNo);
        logger($orderFee);
        if(!$orderFee){
            Helpers::exception('可退款金额为0');
        }
        // $orderFee = 1;
        $result = $app->refund->byTransactionId($order->transaction_id,$refundNo, $orderFee, $orderFee, [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'notify_url'=> route('refundnotify'),
            'refund_desc' => '电影票退款',
        ]);

        if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'FAIL'){
            $order->refund_remark = $result['err_code_des'];
            $order->refund_status = 3; //退款失败
        }
        $order->save();
    }

    /**
     * 获取订单超时时间
     *
     * @param [type] $key
     * @return int
     */
    public static function getDelayTime($key){
        $ttl = (int)Helpers::getSetting($key);//分钟
        return $ttl * 60;
    }

    /**
     * 订单号
     *
     * @return string
     */
    public function getOrderNo(){
        return $this->order_no;
    }

    /**
     * 佣金结算时间
     *
     * @return int
     */
    public function getCommisionTime(){
        return 0;
    }

    /**
     * 是否可以计算佣金
     *
     * @return boolean
     */
    public function canCommision(){
        return $this->order_status == 30;
    }

    /**
     * 订单金额
     *
     * @return void
     */
    public function getTotalCommisionMoney(){
        $rules = Helpers::getSetting('retail_setting');
        $totalRate = round($rules['total_rate'] / 100,2);
        return round($this->amount * $totalRate,2);
    }

    public function getOrderAmount(){
        return $this->amount;
    }


    public function getMarketPriceAttribute($value){
        return floatval($value /100);
    }

    public function setMarketPriceAttribute($value){
        $this->attributes['market_price'] = $value * 100;
    }

    public function getDiscountPriceAttribute($value){
        return floatval($value /100);
    }

    public function setDiscountPriceAttribute($value){
        $this->attributes['discount_price'] = $value * 100;
    }

    public function getAmountAttribute($value){
        return floatval($value /100);
    }

    public function setAmountAttribute($value){
        $this->attributes['amount'] = $value * 100;
    }


    public function code(){
        return $this->hasMany('\App\Models\user\TicketCode','order_id','id');
    }
}
