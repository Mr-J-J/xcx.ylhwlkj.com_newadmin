<?php

namespace App\Admin\Controllers;

use App\Models\Msg;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MsgController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '通知管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Msg());
        $grid->actions(function ($actions){
           $actions->disableView();
        });
        $grid->column('id', __('Id'));
        $grid->column('title', __('标题'));
//        $grid->column('content', __('内容'));
        $grid->column('usertype', __('通知类型'));
        $grid->column('updated_at', __('修改时间'));
        $grid->column('created_at', __('创建时间'));


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
        $show = new Show(Msg::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('标题'));
        $show->field('content', __('内容'));
        $show->field('usertype', __('通知类型'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Msg());

        $form->text('title', __('标题'));
        $form->select('usertype', __('类型'))->options([1=>'分销商']);
        $form->UEditor('content', __('内容'));


        return $form;
    }
}
