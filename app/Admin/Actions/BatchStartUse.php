<?php

namespace App\Admin\Actions;

use App\CardModels\OlCard;
use App\CardModels\RsOlCard;
use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

/**
 * 按批次启用
 */
class BatchStartUse extends RowAction
{
    public $name = '批量启用';

    public function handle(Model $model)
    {
        
        if($model->type == 2){
            RsOlCard::where('batch_id',$model->id)->update(['state'=>2]);
        }else{
            OlCard::where('type',2)->where('batch_id',$model->id)->update(['state'=>2]);
        }

        return $this->response()->success('已启用')->refresh();
    }
    
    public function dialog(){
        $this->confirm('确定要启用这批卡吗？');
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