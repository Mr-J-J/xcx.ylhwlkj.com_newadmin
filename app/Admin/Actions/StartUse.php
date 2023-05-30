<?php

namespace App\Admin\Actions;


use Encore\Admin\Actions\RowAction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * 影城卡单张启用
 */
class StartUse extends RowAction
{
    public $name = '启用';

    public function handle(Model $model,Request $request)
    {
        $model->state = 2;

        $model->save();

        return $this->response()->success('已启用')->refresh();
    }
    
    public function dialog(){
        $this->confirm('确定要启用这张卡吗？');
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