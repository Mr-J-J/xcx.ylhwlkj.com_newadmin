<?php

namespace App\Admin\Actions\Store;

use App\Support\Helpers;
use Illuminate\Http\Request;

use App\CardModels\SettleList;
use App\Models\store\StoreLevel;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class RsStoreSettle extends RowAction
{
    public $name = '结算';

    public function handle(Model $model,Request $request)
    {
        
        $message = '操作成功';
        
       
       $settle_money = $request->input('settle_money',0);
       
       if(!$settle_money){
            return $this->response()->error('请输入结算金额');
       }
       if($settle_money > $model->balance){
           return $this->response()->error('剩余应结算金额不足');
       }
        $settleList = new SettleList;
        $settleList->com_id = $model->id;
        $settleList->settle_money = round($settle_money,2);
        $settleList->settle_sn = Helpers::makeOrderNo('J');
        $imgFile = $request->file('image');
        $path = '';
        if($imgFile){
            $path = $imgFile->store('images/'.$settleList->settle_sn.'.jpg','admin');
        }
        $settleList->image = $path;
        $settleList->save();
        $model->settleMoney($settleList->settle_money);        
        return $this->response()->success($message)->refresh();
    }
    
    public function form(Model $model)
    {
        $this->text('limit_money','商家名称')->value($model->store_name)->disable();
        $this->text('limit_money','剩余应结金额')->value($model->balance)->disable();;
        $this->text('settle_money', '结款金额')->help('结款金额不能大于商家剩余结账金额');
        $this->image('image', '结款凭证');
        $this->hidden('settle_sn')->value('');
        
        
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