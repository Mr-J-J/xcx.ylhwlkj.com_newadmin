<?php

namespace App\UUModels;


use App\Support\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通数据类
 */
class UUPayOrder extends Model
{

    protected $table = 'pw_ticket_payorder';
    protected $guarded = [];


    /**
     * 预下单
     *
     * @return model
     */
    static function createPayOrder($order_ids, $orderAmount, $user_id,$paySucess = false)
    {
        $orderNo = Helpers::makeOrderNo('Pay');
        $checked = false;
        while(!$checked){
            $count = UUPayOrder::where('pay_no',$orderNo)->count();
            if($count){
                $orderNo = Helpers::makeOrderNo('Pay');
            }else{
                $checked = true;
            }
        }
        $ttl = (int)Helpers::getSetting('order_pay_ttl');//分钟
        $expire_time = time() + $ttl * 60;   
        $data = array(
            'user_id' => $user_id,
            'pay_no' => $orderNo,
            'order_ids' => $order_ids,
            'pay_status' => 1,
            'transaction_id' => '',
            'pay_money' => $orderAmount,
            'expire_time' => $expire_time,
            'remark' => '',
        );
        if($paySucess){
            $data['pay_status'] = 2;
        }
        $order = UUPayOrder::create($data);
        return $order;
    }
    
    /**
     * 取消未支付的订单
     *
     * @return void
     */
    function cancelOrder(){
        $this->pay_status = 0;
        $this->save();
        UUTicketOrder::whereIn('id',explode(',',$this->order_ids))->update(['order_status'=>0]);
    }

    /**
     * 支付成功
     *
     * @param UserOrder $order
     * @param string $transaction_id
     * @return model
     */
    function paySuccess($transaction_id = ''){    
        $payOrder = $this;   
        
        $successOrder = array(); 
        DB::beginTransaction();
        try {
            $payOrder->pay_status = 2;//支付成功待出票
            $payOrder->transaction_id = $transaction_id;
            $payOrder->expire_time = 0;
            $payOrder->save();

            $orderIds = explode(',',$payOrder->order_ids);
            $orderList = UUTicketOrder::whereIn('id',$orderIds)->get();
            foreach($orderList as $item){
                $successOrder[] = $item->paySuccess($transaction_id);
            }

        } catch (\Throwable $th) {
            logger('UUPayOrder: paysuccess,'.$th->getMessage());
            DB::rollBack();
            return false;
        }
        DB::commit();

        foreach($successOrder as $order){
            if($order->order_status){
                $order->PFT_Order_Submit();
            }
        }
        return $payOrder;
    }
}
