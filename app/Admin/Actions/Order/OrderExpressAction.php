<?php

namespace App\Admin\Actions\Order;

use App\MallModels\Order;
use App\MallModels\OrderExpress;
use Encore\Admin\Actions\RowAction;

class OrderExpressAction extends RowAction
{
    protected $selector = '.order-express';

    public function handle(Order $order)
    {
        $express_name = request('express_name','');
        $express_sn = request('express_sn','');
        if(empty($express_name)){
            return $this->response()->error('请填写快递公司名称');
        }

        if(empty($express_sn)){
            return $this->response()->error('请填写快递单号');
        }
        OrderExpress::createDeviliver($order,$express_name,$express_sn);
        return $this->response()->success('操作成功')->refresh();
    }

    public function name(){
        return '填写快递单号';
    }

    public function form(){
        $model = $this->row;
        $express_name = $express_sn = '';
        $hasExpress = OrderExpress::where('order_id',$model->id)->first();
        if($hasExpress){
            $express_name = $hasExpress->express_name;
            $express_sn = $hasExpress->express_sn;
        }
        $this->text('order_no','订单号')->value($model->getOrderNo())->readonly();
        $this->hidden('id')->value($model->id);
        $this->text('express_name','快递公司')->default($express_name);
        $this->text('express_sn','快递单号')->default($express_sn);
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