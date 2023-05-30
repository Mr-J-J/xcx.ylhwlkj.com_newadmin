<?php

namespace App\Admin\Controllers\Mall;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\MallModels\Stores;
use App\MallModels\Product;
use Illuminate\Support\Arr;
use App\MallModels\Category;
use App\Admin\Selectable\SelectUser;
use App\Admin\Actions\Store\MallStoreSettle;
use Encore\Admin\Controllers\AdminController;

class MallStoreController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '入驻商家管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Stores());
        $grid->model()->latest();
        $grid->actions(function($action){
            $action->add(new MallStoreSettle());
            $action->disableView();
            $action->disableDelete();
        });
        $grid->filter(function($filter){
            $filter->expand();
            $filter->disableIdFilter();
            $filter->equal('category_id','商家类别')->select(Arr::pluck(Category::getFirstList(),'title','id'));
            $filter->like('store_name','商家名称');
        });
        $grid->column('store_name', '商家名称')->display(function($store_name){
            return "[ID：{$this->user_id}] ".$store_name;
        });
        $grid->column('product_count','商品数量')->display(function(){
            return Product::where('store_id',$this->user_id)->count();
        })->link(function(){
            return '/admin/products?store_id='.$this->user_id;
        },'');
        $grid->column('category.title', '商家类别');
        $grid->column('sale_money', '销售总额')->display(function(){
            return sprintf('%.2f',$this->sale_money - $this->refund_money);
        })->link(function(){
            return '/admin/orders?store_id='.$this->user_id;
        },'');
        $grid->column('freeze_money', '应结金额');
        $grid->column('settle_money', '已结金额')->link(function(){
            return '/admin/mall-settle?store_id='.$this->user_id;
        },'');
        $grid->column('limit_money', '剩余应结金额')->display(function(){
            return $this->freeze_money - $this->settle_money;
        });
        $grid->column('tel','联系电话');
        $grid->column('address','联系地址');

        return $grid;
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Stores());
        $category = Arr::pluck(Category::getFirstList(),'title','id');
        $form->select('category_id', '商家类别')->options($category);
        $form->belongsTo('user_id', SelectUser::class, '用户账号绑定')->help('核销商家需要先注册账号，并在此绑定后才可以，否则就是普通用户');
        $form->text('store_name', '商家名称');
        $provinces = \App\MallModels\Region1::getRegions(0,1)->pluck('city_name','city_code');
        $form->select('province','省')->options($provinces)->load('city','/admin/selectCity/2')->rules('required',['required'=>'请选择省份']);;
        $form->select('city','市')->options(function(){
            if(!$this->province) return array();
            return \App\MallModels\Region1::getRegions($this->province,2)->pluck('city_name','city_code');
        })->load('area','/admin/selectCity/3')->rules('required',['required'=>'请选择城市']);
        $form->select('area','区')->options(function(){
            if(!$this->city) return array();
            return \App\MallModels\Region1::getRegions($this->city,3)->pluck('city_name','city_code');
        });
        $form->text('tel','联系电话');
        $form->text('address','联系地址');

        $form->latlong('latitude', 'longitude', '位置坐标')->default(['lat' => '39.905785', 'lng' => '116.398859'])->height(500);

        return $form;
    }
}
