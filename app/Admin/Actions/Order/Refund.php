<?php

namespace App\Admin\Actions\Order;

use App\Models\UserOrder;

use Encore\Admin\Actions\RowAction;

class Refund extends RowAction
{
    protected $selector = '.refund';

    public function handle(UserOrder $order)
    {
                
        try {
            $order->refundOrder($order);
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
        $this->text('order_no','订单号')->value($model->order_no)->readonly();
        $this->text('transaction_id','微信订单号')->value($model->transaction_id)->readonly();
        $this->text('amount','订单金额')->value($model->amount)->readonly();                        
        $this->text('order_status','订单状态')->value(UserOrder::statusTxt($model->order_status,$model->refund_status))->readonly();                
        $this->text('refund_remark','备注')->value($model->refund_remark)->placeholder(' ')->readonly();                
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