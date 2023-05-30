<?php

namespace App\MallModels;

use App\Models\TicketUser;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

/**
 * 订单核销明细
 */
class OrderCheckLogs extends Model
{
    protected $table = 'mall_order_checklogs';

    static function createLogs(Order $order,OrderCheckCode $codeInfo,int $store_id,int $number){
        $storeInfo = Stores::where('user_id',$store_id)->first();
        $store_name = !empty($storeInfo)?$storeInfo->store_name:'';
        $cl = new self;
        $cl->check_sn = Helpers::makeOrderNo('X');
        $cl->check_code = $codeInfo->code;
        $cl->store_id = $store_id;
        $cl->username = $store_name;
        $cl->order_id = $order->id;
        $cl->order_sn = $order->order_sn;
        $cl->check_number = $number;
        $cl->check_money = $codeInfo->check_money * $number;
        $cl->save();
    }

    /**
     * 根据核销码订单id查询核销记录
     *
     * @param string $code
     * @param integer $order_id
     * @return void
     */
    static function getCheckLogs(string $code,int $order_id){
        return OrderCheckLogs::select(['check_sn','username','check_number','created_at'])
            ->where('check_code',$code)
            ->where('order_id',$order_id)
            ->latest()
            ->get();
    }
}
