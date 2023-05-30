<?php

namespace App\UUModels;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通数据类
 */
class UUOrderRefund extends Model
{
    use ApiTrait;
    protected $table = 'pw_order_refund';
    protected $guarded = [];
        
    static function getOrderByOrderNo($orderNo,$state = -1){
        return self::where('order_no',$orderNo)->when($state > -1,function($query)use ($state){
            return $query->where('state',$state);
        })->latest('updated_at')->first();
    }

    static function getOrderByRefundNo($refundNo){
        return self::where('refund_no',$refundNo)->first();
    }
    
    
     /**
     * 向票付通提交退款
     *
     * @param [type]
     * @return model
     */
    function requestRefundOrder($ticketOrder){
        $refundOrder = $this;
        $api = \App\Support\SoapApi::getInstance();
        
        $pftOrderNo = $refundOrder->Order16U;
        $num = $refundOrder->Tnumber;
        $orderTel = '';
        $refundNo = $refundOrder->refund_no;
        if(empty($pftOrderNo)){
            $refundOrder->Refundtype = 1;
            $refundOrder->remark = '退票无需审核';
            // $refundOrder->state = 1;
            $refundOrder->save();
            return true;
        }
        $apiResult = $api->Order_Change_Pro($pftOrderNo,$num,$orderTel,$refundNo);
        logger('退票结果：'.json_encode($apiResult));
        if($apiResult['status'] === false){
            $refundOrder->remark = $apiResult['msg'];
            $refundOrder->ActionTime = date('Y-m-d H:i:s');
            // $refundOrder->state = 2;
            $refundOrder->save();
            
            $ticketOrder->UUrefund_num = max(0,$ticketOrder->UUrefund_num - $refundOrder->refundNum);
            $ticketOrder->save();
            return false;
        }
        if($apiResult['code'] == 1){
            $refundOrder->RefundFee = $apiResult['data']['UUrefund_fee'];
            $refundOrder->RefundAmount = $apiResult['data']['UUrefund_amount'];
            $refundOrder->ActionTime = date('Y-m-d H:i:s');
            $refundOrder->Refundtype = 1;
            $refundOrder->remark = '退票无需审核';
            // $refundOrder->state = 1;
            $refundOrder->save();
            $ticketOrder->agreeRefundOrder($refundOrder);
        }elseif($apiResult['code'] == 2){
            $refundOrder->remark = '退票审核中...';
            $refundOrder->ActionTime = date('Y-m-d H:i:s');
            $refundOrder->save();
        }
        return (int)$apiResult['code'];
        
        // "UUdone": "100",
        // "UUrefund_fee": "0",
        // "UUrefund_amount": "1000",
        // "UUserial_number": "b6f5551fb9a3834dc7296f5db0afc6e1"
        
    }

}
