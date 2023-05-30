<?php

namespace App\Models\store;
use App\ApiModels\Wangpiao\CinemasBrand;
use App\Support\Helpers;
use App\Models\StoreInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
/**
 * 报价记录
 */
class StoreOfferRecord extends Model
{
    protected $table = "store_offer_record";


    /**
     * 创建报价
     *
     * @param [type] $data
     * @param [type] $store_id
     * @param StoreOfferOrder $order
     * @return void
     */
    public static function saveOffer(array $data,StoreInfo $store,StoreOfferOrder $order){
        // if($order->offer_status !=0){
        //     Helpers::exception('竞价已结束');
        // }
        extract($data);
        $record = new StoreOfferRecord;
        $map = array(
            'store_id'=>$store->store_id,
            'order_id'=>$order->id
        );
                
        $hasRecord = $record->where($map)->first();
        if(!empty($hasRecord)){
            $record = $hasRecord;
        }
        $record->order_id = $order->id;
        $record->store_id = $store->store_id;
        $record->offer_times = $order->offer_times;
        $record->offer_amount = $price;
        $record->remark = $remark??'';
        $record->ticket_count = $order->ticket_count;
        $record->offer_status = $status ?? 0;
        $record->draw_rate = $store->draw_rate;
        $record->save();           
    }

    /**
     * 计算最高报价[作废]
     *
     * @param [type] $amount
     * @return float
     */
    static function getMaxPrice($amount){
        $priceRate = (int) Helpers::getSetting('offer_price');
        $maxPrice = round($amount * ($priceRate / 100),2);
        return $maxPrice;
    }
    
    // function getMaxPrice_V2(){
    //     $order = $this;
    //     $priceRate = (int)CinemasBrand::where('id',$order->brand_id)->value('offer_price');
    //     if(!$priceRate){
    //         $priceRate = (int) Helpers::getSetting('offer_price');
    //     }
    //     $maxPrice = round($order->amount / $order->ticket_count * ($priceRate / 100),2);
    //     return $maxPrice;
    // }

    /**
     * 更新竞价报价状态
     *
     * @param integer $offer_order_id
     * @param integer $win_store_id
     * @return void
     */
    static function udpateOfferStatus(int $offer_order_id,int $win_store_id = 0){
        if($win_store_id){
            StoreOfferRecord::where('order_id',$offer_order_id)->where('store_id',$win_store_id)->update(['offer_status'=>1]);
        }
        StoreOfferRecord::where('order_id',$offer_order_id)->where('store_id','<>',$win_store_id)->update(['offer_status'=>3,'remark'=>'']);
    }

    /**
     * 给指定商家添加报价
     *
     * @param StoreOfferOrder $order
     * @param StoreInfo $storeInfo
     * @param string $remark
     * @return void
     */
    static function addStoreOffer(StoreOfferOrder $order,StoreInfo $storeInfo,$remark = ''){        
        // $data['price'] = self::getMaxPrice($order->amount / $order->ticket_count);
        $data['price'] = $order->getMaxPrice_V2();
        $data['status'] = 1; //直接中标状态
        $data['remark'] = $remark;//'无商家接单,系统指派默认服务商'
        try {
            self::saveOffer($data,$storeInfo,$order);
        } catch (\Throwable $th) {
            logger('storeOfferOrder:235,'.$th->getMessage());
            return false;
        }
        self::udpateOfferStatus($order->id,$storeInfo->store_id);
        //添加报价后直接修改为待出票状态
        $order->editStatusToWaitOutTicket($storeInfo,$data['price']);
    }

    public function setOfferAmountAttribute($value){
        $this->attributes['offer_amount'] = $value * 100;
    }

    public function getOfferAmountAttribute($value){
        return $value / 100;
    }

    public function store(){
        return $this->hasOne('\App\Models\Store','id','store_id');
    }
}
