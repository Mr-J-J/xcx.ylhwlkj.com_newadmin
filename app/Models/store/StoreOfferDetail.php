<?php

namespace App\Models\store;

use Illuminate\Database\Eloquent\Model;
/**
 * 竞价流程明细
 */
class StoreOfferDetail extends Model
{
    protected $table = 'store_offer_detail';

    static function createDetail(StoreOfferOrder $order,$remark = ''){
        $detail = new StoreOfferDetail;
        $detail->offer_order_id = $order->id;
        $detail->order_no = $order->order_no;
        $detail->detail = $remark;
        $detail->save();
    }
}