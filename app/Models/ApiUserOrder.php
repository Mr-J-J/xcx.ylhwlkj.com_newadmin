<?php

namespace App\Models;

use App\Support\Helpers;
use App\ApiModels\Wangpiao as Api;
use App\Models\store\OfferServices;
use App\Models\store\StoreOfferOrder;
// use App\Support\WpApi;

/**
 * 接口订单处理
 */
class ApiUserOrder extends UserOrder
{
    /**
     * 接口商扣款成功
     *
     * @return void
     */
    public function apiStorePaySuccess(){
        $order = $this;
        $order->order_status = 20;//支付成功待出票
        // $delay = (int) self::getDelayTime('order_cancel_ttl');
        // $order->expire_time = $delay ? time() + $delay : 0;
        $order->expire_time = 0; //出票没有超时，必须要出票
        $order->pay_status = 2; //已支付
        $order->pay_name = 'api_store';
        $order->pay_time = time();
        $order->transaction_id = '';
        $order->save();        
        try {
            $isRedirectOut = (int)Helpers::getSetting('redirect_out_ticket');
            $brandIsRedirectOut = (int)Api\CinemasBrand::where('id',$order->brand_id)->value('redirect_out_ticket');
            if(!$isRedirectOut || ($isRedirectOut && !$brandIsRedirectOut)){
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
                //网票网直接出票
                $order->redirectOutTicket();
            }
        } catch (\Throwable $th) {
            logger($th->getMessage());
        }
        return $order;
    }
}
