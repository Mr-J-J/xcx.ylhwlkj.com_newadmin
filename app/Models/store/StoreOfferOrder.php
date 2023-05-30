<?php

namespace App\Models\store;


use App\Support\Helpers;
use App\Models\StoreInfo;
use App\Models\UserOrder;

use App\Models\CommonOrder;

use App\Models\store\OutTicketLog;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;
use App\ApiModels\Wangpiao\CinemasBrand;

/**
 * 商家报价订单模型
 */
class StoreOfferOrder extends Model
{
    protected $table = 'store_offer_orders';



    /**
     * 计算报价、商家中标
     *
     * @return void
     */
    public function calcOfferToOrder(){
        $order = $this;

        $offerRecord = StoreOfferRecord::where('order_id',$order->id)->orderBy('created_at','asc')->get();
        if(empty($offerRecord->toArray())){
            return false; //无商家报价退出
        }

        $offerService = new OfferServices;
        $win_store_offer = $offerService->offerPriceRules($offerRecord->toArray());
        if(empty($win_store_offer)){
            return false;
        }
        DB::beginTransaction();
        try {
            $storeInfo = StoreInfo::where('store_id',$win_store_offer['store_id'])->first();
            $order->editStatusToWaitOutTicket($storeInfo,$win_store_offer['offer_amount']);
            StoreOfferRecord::udpateOfferStatus($order->id,$storeInfo->store_id);
            //待出票
            $storeId = $win_store_offer['store_id'];
            $offerAmount = $win_store_offer['offer_amount'];
            StoreOfferDetail::createDetail($order,"商家ID({$storeId})竞价中标价格:{$offerAmount}");

        } catch (\Exception $e){
            DB::rollback();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 到期关闭报价订单
     * 报价状态 0竞价中 1待出票 2已出票 3已关闭/竞价失败 4退回
     * @param StoreOfferOrder $order
     * @return void
     */
    public function closeOrder(){
        $offerOrder = $this;
        if($offerOrder->offer_status > 1){
            return true;
        }
        $offerService = new OfferServices;

        //待出票的订单
        if($offerOrder->offer_status == 1){
            //出票超时直接关闭
            $offerOrder->offer_status = 4;
            $offerOrder->save();
            $offerService->backOrderToCommon($offerOrder);
            return;
        }


        //竞价中的订单
        if($offerOrder->calcOfferToOrder()){
            return true; //中标计算返回正常则直接返回
        }

        $offerTimes = (int)Helpers::getSetting('offer_times');
        if($offerOrder->offer_times < $offerTimes){
            $offerOrder->offer_times = $offerOrder->offer_times + 1;
            $delay = (int)self::getDelayTime('offer_ttl');
            $offerOrder->expire_time  = $delay + time();
            $offerOrder->save();
            $offerService->startOffer($offerOrder,$delay);
            return false;
        }

        //查找品牌默认服务商
        $store_id = $offerService->getDefaultStore($offerOrder->brand_id);
        if($store_id == 0){
            $offerOrder->offer_status = 4;
            $offerOrder->save();
            StoreOfferRecord::udpateOfferStatus($offerOrder->id); //报价作废
            CommonOrder::addOrder($offerOrder,1);
            return false;
        }
        $storeInfo = StoreInfo::where('store_id',$store_id)->first();
        StoreOfferRecord::addStoreOffer($offerOrder,$storeInfo,'无商家接单,系统指派默认服务商');
        StoreOfferDetail::createDetail($offerOrder,'无商家接单,系统指派默认服务商（'.$store_id.'）出票');
    }



    /**
     * 修改订单状态为待出票状态
     *
     * @param [type] $store_id 中标商家id
     * @return void
     */
    function editStatusToWaitOutTicket(StoreInfo $storeInfo,$success_money = 0){
        $order = $this;
        $order->offer_status = 1;
        $delay = (int)self::getDelayTime('offer_out_ttl');
        $order->expire_time   =  $delay + time();
        $order->store_id = $storeInfo->store_id;
        $order->success_money =  $success_money * 100 * $order->ticket_count;
        $order->save();
        OutTicketLog::addLogs($storeInfo,$order); //添加出票开始标记
        $offerService = new OfferServices;
        $offerService->dispatchQueue($order,$delay);
        //TODO 中标待出票通知
        $storeOpenId = \App\Models\Store::where('id',$storeInfo->store_id)->value('openid');
        $offerService->pushOfferSuccessMsg($storeOpenId,$order);

        $offer_out_ttl = (int)Helpers::getSetting('offer_out_ttl');
        if($offer_out_ttl > 15){
            \App\Jobs\PushStoreMsgJob::dispatch($order,$storeOpenId,'','','pushOfferSuccessMsg2')->delay(now()->addMinutes(15));
        }
    }

    /**
     * 根据订单号获取订单信息
     *
     * @param [type] $order_no
     * @return StoreOfferOrder
     */
    public static function getOrderByOrderNo(string $order_no){
        return self::where('order_no',$order_no)->first();
    }


    /**
     * 获取订单超时时间
     *
     * @param [type] $key
     * @return int
     */
    public static function getDelayTime($key){
        $ttl =(int) Helpers::getSetting($key);//分钟
        return $ttl * 60;
    }

    function getMaxPrice_V2(){
        $order = $this;
        $priceRate = (int)CinemasBrand::where('id',$order->brand_id)->value('offer_price');
        if(!$priceRate){
            $priceRate = (int) Helpers::getSetting('offer_price');
        }
        $maxPrice = round($order->amount / $order->ticket_count * ($priceRate / 100),2);
        return $maxPrice;
    }
    function getMinPrice_V2(){
        $order = $this;
        $priceRate = (int)CinemasBrand::where('id',$order->brand_id)->value('offer_price_min');
        if(!$priceRate){
            $priceRate = (int) Helpers::getSetting('offer_price_min');
        }
        $maxPrice = round($order->amount / $order->ticket_count * ($priceRate / 100),2);
        return $maxPrice;
    }
    /**
     * 创建报价订单
     *
     * @return Model
     */
    function createOfferOrder(UserOrder $order){
        $hasOrder = StoreOfferOrder::getOrderByOrderNo($order->order_no);
        if(!empty($hasOrder)){
            logger('StoreOfferOrder:49: '.$order->order_no.'重复创建竞价');
            return false;
        }
        $offerOrder = $this;
        DB::beginTransaction();
        try {
            $offerOrder->order_id = $order->id;
            $offerOrder->order_no   =    $order->order_no;
            $offerOrder->user_id   =   $order->user_id ;
            $offerOrder->buy_type   =   $order->buy_type; //1特惠购票 2快速购票
            $offerOrder->accept_seats    =   $order->accept_seats ; //0不接受 1接受
            $offerOrder->buyer_phone   =   $order->buyer_phone ;
            $offerOrder->market_price    =   $order->market_price;
            // $offerOrder->discount_price   =    $order->discount_price;
            $offerOrder->amount   =   $order->amount;
            if($order->ol_card_id){
                $offerOrder->amount   =   $order->discount_price;
            }
            $offerOrder->ticket_count   =   $order->ticket_count;
            $offerOrder->citys = $order->citys;
            $offerOrder->movie_image   =   $order->movie_image;
            $offerOrder->cinemas   =   $order->cinemas;
            $offerOrder->cinema_id   =   $order->cinema_id;
            $offerOrder->movie_name   =   $order->movie_name;
            $offerOrder->halls = $order->halls;
            $offerOrder->show_version = $order->show_version;
            // $offerOrder->cinema_id = $order->cinema_id;
            $offerOrder->show_time   =   $order->show_time;
            $offerOrder->close_time   =   $order->close_time;
            $offerOrder->seat_names = $order->seat_names;
            $offerOrder->offer_times = 1;
            $offerOrder->paiqi_id = $order->paiqi_id;
            $offerOrder->seat_ids = $order->seat_ids;
            $offerOrder->seat_areas = $order->seat_areas;
            $offerOrder->seat_flag = $order->seat_flag; //0：普通座位，1：情侣首座(左座)，2：情侣第二座(右座)
            $offerOrder->api_order_id = $order->api_order_id;
            $offerOrder->brand_id = $order->brand_id;
            $offerOrder->offer_status   =   0; //竞价中
            //超时时间
            $delay = self::getDelayTime('offer_ttl');
            $offerOrder->expire_time   =   $delay + time();

            $offerOrder->created_at   =   $order->updated_at;
            $offerOrder->save();

        } catch (\Throwable $th) {
            DB::rollBack();
            // Helpers::exception($e->getMessage());
            logger($order->order_no .'create offer order '.$th->getMessage());
            return false;
        }
        DB::commit();
        return $offerOrder;
    }

    /**
     * 报价列表
     *
     * @return void
     */
    public function offerlist(){
        return $this->hasMany('\App\Models\store\StoreOfferRecord','order_id','id');
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

    public function getSuccessMoneyAttribute($value){
        return $value / 100;
    }


}
