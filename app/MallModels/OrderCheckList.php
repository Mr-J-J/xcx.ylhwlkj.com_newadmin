<?php

namespace App\MallModels;

use App\Support\Helpers;
use App\Models\TicketUser;
use Illuminate\Database\Eloquent\Model;

/**
 * 核销记录（总的）
 */
class OrderCheckList extends Model
{
    protected $table = 'mall_order_checklist';

    static function createRecord(Order $order,OrderCheckCode $codeInfo,int $store_id,int $number){
        $user = TicketUser::where('id',$order->user_id)->first();
        $cl = OrderCheckList::where('order_id',$order->id)->firstOr(function(){
                    $newCl = new OrderCheckList;
                    return $newCl;
                });
        $cl->store_id = $store_id;
        $cl->order_id = $order->id;
        $cl->order_sn = $order->order_sn;
        $cl->check_code = $codeInfo->code;
        $cl->check_money = $cl->check_money + $codeInfo->check_money * $number;
        $cl->order_amount = $order->pay_money;
        $cl->product_id = $order->product_id;
        $cl->product_title = $order->product_title;
        $cl->order_time = $order->getOriginal('created_at');
        $cl->avatar = $user->avatar;
        $cl->nickname = $user->nickname;
        $cl->mobile = $order->mobile;
        $cl->save();
        OrderCheckLogs::createLogs($order,$codeInfo,$store_id,$number);                
    }
}
