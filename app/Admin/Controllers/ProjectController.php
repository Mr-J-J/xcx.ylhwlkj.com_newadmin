<?php

namespace App\Admin\Controllers;

use App\Models\Project;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProjectController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '新项目管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Project());

        $grid->column('id', __('Id'));
        $grid->column('title', __('项目名称'));
        $grid->column('con', __('介绍'));
        $grid->column('user', __('tel/链接'));
//        $grid->column('pwd', __('客服二维码'));
//        $grid->column('img', __('链接二维码'));
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
        $show = new Show(Project::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('项目名称'));
        $show->field('con', __('介绍'));
        $show->field('user', __('tel/链接'));
//        $show->field('img', __('链接二维码'));
//        $show->field('pwd', __('客服'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Project());

        $form->text('title', __('项目名称'));
        $form->textarea('con', __('介绍'));
        $form->text('user', __('tel/链接'));
        $form->image('pwd', __('客服二维码'));
        $form->image('img', __('链接二维码'));
        return $form;
    }
}
