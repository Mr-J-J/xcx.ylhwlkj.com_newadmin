<?php

namespace App\Admin\Actions\Store;

use App\Models\store\StoreLevel;
use Encore\Admin\Actions\RowAction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Audit extends RowAction
{
    public $name = '审核';

    public function handle(Model $model,Request $request)
    {
        $state = (int)$request->get('state');

        $level = (int)$request->get('level');

        if(!$level && $state == 2){
            $level = 1;
        }
        $message = '操作成功';
        
        if(!$model->storeInfo){
            return $this->response()->error('审核失败：商家未注册')->refresh();
        }
        
        $model->store_state = $state;
        $model->store_level = $level;

        $model->save();

        if($state == 2){
            $model->storeInfo->taking_mode = 1;
            $model->storeInfo->save();
        }

        return $this->response()->success($message)->refresh();
    }
    public function form(Model $model)
    {
        $type = [
            0 => '拒绝',
            2 => '通过',
        ];
        $storeLevel = StoreLevel::all(['title','id'])->map(function($level){
            return $level->toArray();
        })->pluck('title','id');
        // $storeLevel = collect([['id'=>0,'title'=>'普通商家']])->merge($storeLevel)->pluck('title','id');
        $this->hidden('_token')->default(csrf_token());
        $this->radio('state', '入驻审核')->options($type)->default(2);
        $this->select('level', '商家等级')->options($storeLevel)->default(0);
        $this->text('remark','拒绝理由');
    }

    // 这个方法来根据`star`字段的值来在这一列显示不同的图标
    public function display($store_state)
    {        
        $arr = ['未注册','待审核','审核通过'];
        return $arr[$store_state];
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