<?php

namespace App\Admin\Actions\Api;


use App\ApiModels\Wangpiao\City;
use App\Support\Api\ApiHouse;
use Encore\Admin\Actions\RowAction;

class SyncMApiCinemaAction extends RowAction
{
    protected $selector = '.cinema_sync';

    public function handle(City $city)
    {        
        $last_key = '';               
        do{
            $apiResult = ApiHouse::cinemaList($city->code,$last_key);
            $list = $apiResult['data']??[];
            $last_key = $apiResult['last_key']??null;
            if(!empty($list)){
                \App\ApiModels\Wangpiao\Cinema::syncData($list,$city->code);
            }
        }while(!empty($last_key));
                
        return $this->response()->success('数据更新成功')->refresh();
    }

    public function name(){
        return '同步影院';
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