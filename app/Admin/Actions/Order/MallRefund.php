<?php

namespace App\Admin\Actions\Order;

use App\MallModels\Order;
use Encore\Admin\Actions\RowAction;

class MallRefund extends RowAction
{
    protected $selector = '.mall-refund';

    public function handle(Order $order)
    {
                
        try {
            $order->refundOrder();
        } catch (\Throwable $th) {
            return $this->response()->error($th->getMessage())->refresh();
        }
        return $this->response()->success('操作成功')->refresh();
    }

    public function name(){
        return '订单退款';
    }

    public function form(){
        $model = $this->row;
        $this->text('order_no','订单号')->value($model->getOrderNo())->readonly();
        $this->text('transaction_id','微信订单号')->value($model->transaction_id)->readonly();
        $this->text('pay_money','支付金额')->value($model->pay_money)->readonly();                        
        $this->text('order_status','订单状态')->value(Order::$status[$model->order_status])->readonly();                
        $this->text('remark','备注')->value($model->remark)->placeholder(' ')->readonly();                
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