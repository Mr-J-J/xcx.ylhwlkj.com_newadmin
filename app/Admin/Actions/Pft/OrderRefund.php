<?php

namespace App\Admin\Actions\Pft;

use App\UUModels\UUTicketOrder as Order;
use Encore\Admin\Actions\RowAction;

class OrderRefund extends RowAction
{
    protected $selector = '.mall-refund';

    public function handle(Order $order)
    {          
        $num = (int)request('num',0);
        if($order->order_status != 20 && $order->order_status != 21){
            return $this->response()->error('订单当前状态无法退票')->refresh();
        }
        
        
        $canUseNum = $order->getOrderTnum();
        
        $localRemark = request('local_remark','');
        if(!$num || $num > $canUseNum){
            return $this->response()->error('退票数量不正确')->refresh();
        }
        $refundAmount = $num * $order->UUlprice;
        $refundFee = 0;
        try {
            $refundOrder = $order->refundOrder($num,$refundAmount,$refundFee,'',$localRemark);
            if(!$refundOrder){
                throw new \Exception('退票申请失败');
            }
            $refundOrder->requestRefundOrder($order);
        } catch (\Throwable $th) {
            return $this->response()->error($th->getMessage())->refresh();
        }
        return $this->response()->success('操作成功')->refresh();
    }

    public function name(){
        return '退票';
    }

    public function form(){
        $model = $this->row;
        $refundTxt = '';
        if($model->refund_status){
                $refundOrder = \App\UUModels\UUOrderRefund::getOrderByOrderNo($model->order_no);
                // dd($refundOrder);
                if($refundOrder){
                    $arr = ['退款中','退款成功', '退款失败'];
                    $refundTxt = $arr[$refundOrder->state];
                }
            }

        $this->text('order_no','订单号')->readonly()->value($model->order_no);
        $this->text('order_pft_no','票付通订单号')->readonly()->value($model->UUordernum);
        $this->text('transaction_id','微信订单号')->readonly()->value($model->transaction_id);
        $this->text('pay_money','支付金额')->readonly()->value(round($model->order_amount/100,2));                        
        $this->text('order_status','订单状态')->readonly()->value($refundTxt?:$model->getStatusTxt());
        $this->text('origin_num','订单数量')->readonly()->value($model->UUorigin_num);
        $this->text('refund_num','已退数量')->readonly()->value($model->UUrefund_num);
        $this->text('num','退票数量');
        $this->text('local_remark','备注')->placeholder(' ');                
    }

    
    public function render()
    {
        if ($href = $this->href()) {
            return "<a href='{$href}' class='btn btn-default btn-xs {$this->getElementClass()}'>{$this->name()}</a>";
        }

        $this->addScript();

        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='btn btn-default btn-xs  %s' {$attributes}>%s</a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );
        
    }
}