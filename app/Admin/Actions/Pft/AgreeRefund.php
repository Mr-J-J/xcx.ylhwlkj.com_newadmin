<?php

namespace App\Admin\Actions\Pft;

use App\UUModels\UUOrderRefund;
use App\UUModels\UUTicketOrder;
use Encore\Admin\Actions\RowAction;
/**
 * 同意退款
 */
class AgreeRefund extends RowAction
{
    protected $selector = '.mall-agree-money';    
    public function handle(UUOrderRefund $refundOrder)
    {
        if(!$refundOrder->RefundAmount){
            return $this->response()->error('可退款金额为0')->refresh();        
        }
        $order = UUTicketOrder::getOrderByOrderNo($refundOrder->order_no);
        if(empty($order)){
            return $this->response()->error('订单信息不存在')->refresh();  
        }
        $refundFee = $refundOrder->RefundFee;
        $message = '操作成功';
        try {
            $result = $order->agreeRefundOrder($refundOrder);
            if(!$result){
                $message = '操作失败';
            }
        } catch (\Throwable $th) {
            return $this->response()->error($th->getMessage())->refresh();
        }
        return $this->response()->success($message)->refresh();
    }

    public function name(){
        return '同意退款';
    }

    public function dialog(){       
        $this->confirm('继续将原路退款给用户！');
    }

    
    public function render()
    {
        if ($href = $this->href()) {
            return "<a href='{$href}' class='btn btn-twitter btn-xs {$this->getElementClass()}'>{$this->name()}</a>";
        }

        $this->addScript();

        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='btn btn-twitter btn-xs  %s' {$attributes}>%s</a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );
        
    }
}