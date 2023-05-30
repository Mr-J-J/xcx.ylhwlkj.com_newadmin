<?php

namespace App\Models\store;
use Illuminate\Support\Facades\DB;
use App\Support\Helpers;
use App\Models\StoreInfo;
use Illuminate\Database\Eloquent\Model;

/**
 * 商家结算
 */
class StoreCheckOut extends Model
{
    protected $table = 'stores_checkout_logs';


    /**
     * 添加结算单 
     *
     * @param StoreInfo $storeInfo
     * @param StoreOfferOrder $offerOrder
     * @return void
     */
    static function addCheckOrder(StoreInfo $storeInfo,StoreOfferOrder $offerOrder){
        $model = new StoreCheckOut;

        $model->store_id = $offerOrder->store_id;
        $model->money = $offerOrder->success_money;
        $model->offer_order_id = $offerOrder->id;
        $model->order_no = $offerOrder->order_no;
        $model->state = 0; //待结算
        $model->starttime = $offerOrder->show_time;
        // $model->endtime = $offerOrder->close_time;
        $model->endtime = (int)strtotime('+ 2 hours',$offerOrder->close_time);
        $model->save();

        //待结算
        $storeInfo->freeze_money = $storeInfo->freeze_money + $offerOrder->success_money;
        $storeInfo->save();
    }


    /**
     * 商家结算
     *
     * @return void
     */
    public function doCheckOut(){
        $model = $this;
        if($model->endtime > time()){
            return false;
        }
        if($model->state == 1){ 
            return false;
        }
        $storeInfo = StoreInfo::where('store_id',$model->store_id)->first();
        // dump($storeInfo->freeze_money, $model->money);
        DB::beginTransaction();
        try {
            $model->state = 1; //已结算
            $model->endtime = time();
            $model->save();
            if($storeInfo->freeze_money < $model->money){
                throw new \Exception('StoreCheckOut 待结算金额不足:'.$model->order_no.'--'.$storeInfo->freeze_money);
            }
            $storeInfo->decrement('freeze_money',$model->money*100);
            $storeInfo->increment('balance',$model->money * 100);
        } catch (\Exception $e) {
            DB::rollback();
            logger('storecheckout 商家结算出错'.$e->getMessage());
            // Helpers::exception($e->getMessage());
        }
        DB::commit();
    }
    
    public function store(){
        return $this->hasOne('App\Models\Store','id','store_id');
    }

    public function setMoneyAttribute($value){
        $this->attributes['money'] = $value * 100;
    }

    public function getMoneyAttribute($value){
        return $value / 100;
    }
}
