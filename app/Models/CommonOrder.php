<?php

namespace App\Models;

use App\Models\store\StoreOfferOrder;
use Illuminate\Database\Eloquent\Model;
/**
 * 订单池
 */
class CommonOrder extends Model
{
    //
    
    protected $table = 'common_order';

    /**
     * 添加公共订单
     *
     * @param StoreOfferOrder $offerOrder
     * @param [type] $type 1无竞价订单 2商家转单 3超时转单
     * @return void
     */
    static function addOrder(StoreOfferOrder $offerOrder,$type){
        $model = self::where('order_no',$offerOrder->order_no)->firstOr(function(){
            return new CommonOrder;
        });
        $model->order_no = $offerOrder->order_no;
        $model->order_id = $offerOrder->order_id;
        $model->type = (int)$type;
        $model->state = 0;
        $model->store_id = 0;
        $model->store_name = '';
        $model->save();
    }

    static function deleteOrder(StoreOfferOrder $offerOrder){
        CommonOrder::where('order_id',$offerOrder->order_id)->delete();
    }
    
    public function setStoreIdAttribute($value){
        $this->attributes['store_id'] = (int)$value;
    }

    public function order(){
        return $this->hasOne('App\Models\store\StoreOfferOrder','order_no','order_no');
    }

    public function store(){
        return $this->hasOne('App\Models\Store','id','store_id');
    }
}
