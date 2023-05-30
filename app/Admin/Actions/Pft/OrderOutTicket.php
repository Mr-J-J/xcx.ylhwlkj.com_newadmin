<?php

namespace App\Admin\Actions\Pft;

use App\UUModels\UUTicketOrder as Order;
use Encore\Admin\Actions\RowAction;

class OrderOutTicket extends RowAction
{
    protected $selector = '.mall-out-ticke';

    public function handle(Order $order)
    {
        
        $result = $order->PFT_Order_Submit();
        if(!$result){
            return $this->response()->error('出票失败')->refresh();            
        }
        return $this->response()->success('操作成功')->refresh();
    }

    public function name(){
        return '重新出票';
    }

    public function dialog(){
        $this->confirm('确定要重新提交出票吗？');
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