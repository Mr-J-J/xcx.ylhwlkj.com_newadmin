<?php

namespace App\Admin\Controllers;

use App\Models\CinemasBrand;
use App\Models\Newcinemas;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class NcinemaController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影院归属管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Newcinemas());
        $grid->actions(function ($actions){
            $actions->disableView();
        });
        $grid->column('id', __('Id'));
        $grid->column('address', __('地址'));
//        $grid->column('cinemaCode', __('CinemaCode'));
        $grid->column('cinemaName', __('名称'));
//        $grid->column('cinemaNo', __('CinemaNo'));
        $grid->column('city', __('城市'));
        $grid->column('county', __('区域'));
//        $grid->column('latitude', __('Latitude'));
//        $grid->column('longitude', __('Longitude'));
//        $grid->column('province', __('Province'));
//        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));
        $grid->column('brandid', __('属于院线'))->display(function ($brandid){
            $brand = \App\ApiModels\Wangpiao\CinemasBrand::where('id',$brandid)->first();
            if(!empty($brand)){
                return $brand->brand_name;
            }else{
                return '暂无设置院线';
            }
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Newcinemas::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('address', __('Address'));
        $show->field('cinemaCode', __('CinemaCode'));
        $show->field('cinemaName', __('CinemaName'));
        $show->field('cinemaNo', __('CinemaNo'));
        $show->field('city', __('City'));
        $show->field('county', __('County'));
        $show->field('latitude', __('Latitude'));
        $show->field('longitude', __('Longitude'));
        $show->field('province', __('Province'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('brandid', __('Brandid'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Newcinemas());

//        $form->text('address', __('Address'));
//        $form->text('cinemaCode', __('CinemaCode'));
        $form->text('cinemaName', __('影院名称'));
//        $form->number('cinemaNo', __('CinemaNo'));
//        $form->text('city', __('City'));
//        $form->text('county', __('County'));
//        $form->decimal('latitude', __('Latitude'));
//        $form->decimal('longitude', __('Longitude'));
//        $form->text('province', __('Province'));
        $list = \App\ApiModels\Wangpiao\CinemasBrand::brandsOptions()->toArray();
        $brandArr = array_combine(array_column($list,'id'),array_column($list,'brand_name'));
        $form->select('brandid',__('所属院线'))->options($brandArr);

        return $form;
    }
}
