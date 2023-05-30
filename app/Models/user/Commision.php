<?php

namespace App\Models\user;

use App\Support\Helpers;
use App\Models\UserOrder;
use App\Models\TicketUser;
use Illuminate\Database\Eloquent\Model;

/**
 * 分销佣金
 */
class Commision extends Model
{
    protected $table = 'retail_commision';

    protected $appends = ['type_str'];

    static $module = [
        'UserOrder' => UserOrder::class, //购票订单
        'Order' => \App\MallModels\Order::class, //卡券订单
        'WithDraw'=> WithDraw::class, //提现
    ];

    static $moduleTips = [
        'UserOrder' =>'购买电影票', //购票订单
        'Order' => '商城消费', //卡券订单
        'WithDraw'=> '提现', //提现
    ];
    
    /**
     * 计算订单佣金
     *
     * @param Model $order
     * @return void
     */
    static function clacCommision(Model $order){
        if(!$order->canCommision()){
            return false;
        }        
        $userInfo = TicketUser::where('id',$order->user_id)->first();
        $groupInfo = $userInfo->group;
        $rules = Helpers::getSetting('retail_setting');
        
        $globalCommisionRules = $rules;
        
        $level1Rate = round($globalCommisionRules['level1_rate'] / 100 ,2);
        $level2Rate = round($globalCommisionRules['level2_rate'] / 100 ,2);

        //会员等级配置的返佣比例优先
        if($groupInfo){            
            $level1Rate = $groupInfo->level1_rate ? round($groupInfo->level1_rate / 100 ,2):$level1Rate;
            $level2Rate = $groupInfo->level2_rate ? round($groupInfo->level2_rate / 100 ,2):$level2Rate;
        }
        $totalCommisionMoney = $order->getTotalCommisionMoney();
        $level1Commision = $totalCommisionMoney * $level1Rate;
        $level2Commision = $totalCommisionMoney * $level2Rate;
        
        $diffCommision = $totalCommisionMoney - $level1Commision;
        if($level2Commision > $diffCommision){
            $level2Commision = $diffCommision;
        }

        logger($order->getOrderNo().'佣金：'.json_encode(compact('level1Commision','level2Commision','totalCommisionMoney')));
        if(empty($level1Commision + $level2Commision)){
            return false;
        }
                      
        $logsData = array();
        $logsData['order_id'] = $order->id;
        $logsData['order_no'] = $order->getOrderNo();
        $logsData['amount']  = $order->getOrderAmount();
        $logsData['endtime']  = $order->getCommisionTime();
        $class = get_class($order);
        $logsData['module'] = basename(str_replace('\\','/',$class));
        $logsData['user_id'] = $userInfo->id;
        $logsData['level1_id']  = $userInfo->inviter_id;
        $logsData['level2_id']  = $userInfo->inviter_id2;
        $logsData['level2_id']  = $userInfo->inviter_id2;
        $logsData['total_money'] = $totalCommisionMoney;
        $logsData['level1_money']  =  $level1Commision;
        $logsData['level2_money']  = $level2Commision;
        $logsData['rules']  = json_encode(compact('level1Rate','level2Rate'),256);
        OrderCommision::addLogs($logsData);
    }

    /**
     * 佣金记录
     *
     * @param TicketUser $user
     * @param [type] $data [ 'order_no','money','order_id']
     * @param integer $type 1收入 2提现
     * @return void
     */
    static function addCommision(TicketUser $user,int $type = 1,$money,int $orderId,string $orderNo,string $module){                                      
        $model = new Commision;
        $model->user_id = $user->id;
        $model->avatar = $user->avatar;
        $model->nickname = $user->nickname;
        $model->level = $user->isretail?'分销商':'普通';
        $model->type = (int)$type;
        $model->order_id = $orderId;
        $model->order_no = $orderNo;
        $model->module = $module;
        $model->money = $money;
        $model->after_money = $user->balance;
        $model->save();
    }

    public function getTypeStrAttribute(){
        return ($this->attributes['type'] == 1)? '收入':'支出';
    }
    
    
    public function user(){
        return $this->hasOne('App\Models\TicketUser','id','user_id');
    }
    
    
}
