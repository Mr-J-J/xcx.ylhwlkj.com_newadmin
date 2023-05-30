<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\ApiModels\Wangpiao\City;
use App\Admin\Actions\Api\SyncMApiCityAction;
use Encore\Admin\Controllers\AdminController;
use App\Admin\Actions\Api\SyncMApiCinemaAction;

class CityController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '城市管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new City());
        $grid->model()->orderBy('id');
        $grid->column('id', __('Id'));
        $grid->column('code', __('城市编码'));
        $grid->column('name', __('名称'));
        // $grid->column('sname', __('简称'));
        // $grid->column('pname', __('上级区域名称'));
        $grid->column('pinyin', __('拼音'));
        $grid->column('cinema_num','影院数量')->display(function(){
            $count = \App\ApiModels\Wangpiao\Cinema::where('city_code',$this->id)->count();
            return $count.'家影院';
        });
        // $grid->column('type', __('业务类型'))->using([1=>'仅线上',2=>'仅线下',3=>'线上线下']);
        // $grid->column('hot', __('是否热门'))->using([0=>'否',1=>'是']);
        $grid->tools(function($tool){
            $tool->append(new SyncMApiCityAction('同步城市数据'));
        });
        $grid->actions(function($action){
            $action->disableView();
            $action->disableDelete();
            $action->add(new SyncMApiCinemaAction());
        });
        $grid->filter(function($filter){
            $filter->like('name','城市名称');
            $filter->equal('code','城市编码');
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
        $show = new Show(City::findOrFail($id));

        // $show->field('id', __('Id'));
        $show->field('code', '城市编码');
        $show->field('name', '城市名称');
        // $show->field('sname', '简称');
        $show->field('pname', '省份');
        $show->field('pinyin', __('Pinyin'));
        // $show->field('type', __('Type'));
        // $show->field('hot', __('Hot'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new City());

        $form->text('code', '城市编码');
        $form->text('name', '城市名称');
        // $form->text('sname', __('Sname'));
        // $form->text('pname', __('Pname'));
        $form->text('pinyin', '拼音首字母');
        // $form->switch('type', __('Type'));
        // $form->switch('hot', __('Hot'));
        $form->saved(function($form){
            // cache('getCityIdByCode',null);
            \Illuminate\Support\Facades\Cache::forget('getCityIdByCode');
        });
        return $form;
    }
}
