<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPayDetail extends Model
{
    protected $table = 'user_pay_detail';

    // static $module = [
    //     'UserOrder' => UserOrder::class, //购票订单
    //     'Order' => \App\MallModels\Order::class //卡券订单
    // ];
    static function addDetail(TicketUser $user,Model $order){
        $detail = new UserPayDetail;
        $detail->user_id = $user->id;
        $detail->group_id = $user->group_id;
        $class = get_class($order);
        $detail->module = basename(str_replace('\\','/',$class));
        $detail->order_id = $order->id;
        $detail->order_amount = $order->getOrderAmount();
        $detail->state = 0; //待定时处理
        $detail->save();
    }


    /**
     * 获取未处理的记录
     *
     * @return collect
     */
    static function getList(){
        return self::where('state',0)->get();
    }

    /**
     * 处理记录、处理会员等级
     *
     * @return void
     */
    static function detailDealTask(){
        $list = self::getList();
        $userList = array();
        foreach($list as $item){
            if(empty($userList[$item->user_id])){
                $userList[$item->user_id] = TicketUser::where('id',$item->user_id)->first();
            }
            $user = $userList[$item->user_id];
            $item->state = 1;
            $item->save();
            \App\MallModels\Group::checkUserGroup($user);
        }
    }
}
