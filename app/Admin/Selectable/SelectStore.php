<?php
namespace App\Admin\Selectable;

use App\Models\Store;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class SelectStore extends Selectable
{
    public $model = Store::class;

    public function make(){
        $this->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('store_phone','手机号码');
            $filter->like('store_name','商家名称');
        });
        $this->column('avatar','头像')->image('',45);
        $this->column('store_phone','手机号');
        $this->column('store_name','商家名称');
    }


    public static function display()
    {
        return function ($value) {
            $store_name = '点击分配订单给商家';
            if($value){
                $store_name = Store::where('id',$value)->value('store_name');
            }
            // $storeInfo = optional($this)->store;
            // $store_name = $storeInfo?$storeInfo->store_name:'';

            if(empty($store_name)){
                return '点击分配订单给商家';
            }
            return $store_name;
        };
    }
}