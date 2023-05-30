<?php

namespace App\Admin\Actions\Pft;

use App\UUModels\UUOrderException;

use Encore\Admin\Actions\RowAction;

/**
 * 异常订单操作
 */
class ExceptionAction extends RowAction
{
    protected $selector = '.mall-exception-no';

    public function handle(UUOrderException $order)
    {
        $order->state = 1;
        $order->save();
        return $this->response()->success('操作成功')->refresh();
    }

    public function name(){
        return '标记已处理';
    }
    public function render()
    {
        if ($href = $this->href()) {
            return "<a href='{$href}' class='btn  btn-xs {$this->getElementClass()}'>{$this->name()}</a>";
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