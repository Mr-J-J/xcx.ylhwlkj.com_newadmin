<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\Agreement;

class AgreementController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '协议管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Agreement());
        $grid->model()->orderBy('id','desc');
        $grid->column('id', __('Id'));
        $grid->column('title', __('标题'));
        $grid->column('created_at', __('添加时间'));
        $grid->disableExport();
        $grid->actions(function($actions){
            $actions->disableDelete();
            // 去掉查看
            $actions->disableView();
        });
        $grid->disableFilter();
        $grid->quickSearch('title')->placeholder('协议名称');
        // $grid->filter(function($filter){
        //     $filter->disableIdFilter();
        //     $filter->like('title', '标题');
        // });        
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
        $show = new Show(Agreement::findOrFail($id));
        $show->field('title', __('标题'));
        $show->field('created_at', __('添加时间'));
        $show->field('content', __('协议内容'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Agreement());

        $form->text('title', __('标题'));
        $form->UEditor('content', __('协议内容'));

        return $form;
    }
}
