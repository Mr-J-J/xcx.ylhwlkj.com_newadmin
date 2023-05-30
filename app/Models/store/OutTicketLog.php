<?php

namespace App\Models\store;

use DB;
use App\Models\StoreInfo;
use App\Models\store\StoreOfferOrder;
use Illuminate\Database\Eloquent\Model;

class OutTicketLog extends Model
{
    protected $table = 'out_ticket_logs';
    
    /**
     * 商家出票记录
     *
     * @param StoreOfferOrder $offerOrder
     * @return void
     */
    static function addLogs(StoreInfo $storeInfo,StoreOfferOrder $offerOrder){
        $model = new OutTicketLog;
        $model->store_id = $offerOrder->store_id;
        $model->offer_order_id = $offerOrder->id;
        $model->starttime = time();
        $model->endtime = 0;
        $model->out_time = 0;
        $model->save();

        $storeInfo->increment('order_count');//接单量 +1        
    }

    /**
     * 修改出票结束时间
     *
     * @param StoreOfferOrder $offerOrder
     * @return void
     */
    static function editLogs(StoreInfo $storeInfo,StoreOfferOrder $offerOrder){
        $logs = self::where('offer_order_id',$offerOrder->id)->first();
        $nowtime = time();
        $logs->endtime = $nowtime;
        $logs->out_time = intval(($nowtime - $logs->starttime) / 60);
        $logs->save();

        $avgTime = self::where('store_id',$storeInfo->store_id)->where('out_time','>',0)->value(DB::raw('avg(out_time)'));

        //商家平均出票时间
        $storeInfo->mean_time = $avgTime?:0;//分钟
        $storeInfo->out_ticket_count = $storeInfo->out_ticket_count + 1; //成功出票数量
        $storeInfo->save();
    }
}
