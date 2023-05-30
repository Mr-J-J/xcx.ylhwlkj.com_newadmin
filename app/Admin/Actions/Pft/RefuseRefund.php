<?php

namespace App\Admin\Actions\Pft;

use App\UUModels\UUOrderRefund;
use Encore\Admin\Actions\RowAction;
/**
 * 拒绝退款
 */
class RefuseRefund extends RowAction
{
    protected $selector = '.mall-refuse-money';    
    public function handle(UUOrderRefund $refundOrder)
    {
        $refundOrder->state = 2;
        $refundOrder->RefundType = 2;
        $refundOrder->local_remark = '拒绝退款';
        $refundOrder->save();
        return $this->response()->success('操作成功')->refresh();
    }

    public function name(){
        return '拒绝退款';
    }

    public function dialog(){       
        $this->confirm('拒绝此次退款申请？');
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