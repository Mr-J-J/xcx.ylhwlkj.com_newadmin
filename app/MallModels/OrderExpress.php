<?php

namespace App\MallModels;

use Illuminate\Database\Eloquent\Model;

class OrderExpress extends Model
{
    protected $table = 'mall_orders_express';

    /**
     * 创建发货单 
     *
     * @param Order $order
     * @return OrderExpress
     */
    static function createExpress(Order $order,$express_name='',$express_sn=''){
        $oe = new self;
        $oe->order_id =  $order->id;
        $oe->express_flag = '';
        $oe->express_sn = $express_sn;
        $oe->express_name = $express_name;
        $oe->area = $order->area;
        $oe->address = $order->address;
        $oe->mobile = $order->mobile;
        $oe->user_remark = $order->user_remark;
        $oe->order_status = 10;
        $oe->save();
        return $oe;
    }

    /**
     * 发货 
     *
     * @param Order $order
     * @return OrderExpress
     */
    static function createDeviliver(Order $order,$express_name='',$express_sn=''){
        $oe = new self;
        $hasExpress = $oe::where('order_id',$order->id)->first();
        if($hasExpress){
            $oe = $hasExpress;
        }
        $oe->order_id =  $order->id;
        $oe->express_flag = '';
        $oe->express_sn = $express_sn;
        $oe->express_name = $express_name;
        $oe->area = $order->area;
        $oe->address = $order->address;
        $oe->mobile = $order->mobile;
        $oe->user_remark = $order->user_remark;
        $oe->order_status = 20;
        $oe->save();
        return $oe;
    }
}
