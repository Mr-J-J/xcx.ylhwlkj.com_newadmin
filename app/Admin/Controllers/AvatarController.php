<?php

namespace App\Admin\Controllers;

use App\Models\Avatar;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AvatarController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '默认头像管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Avatar());

        $grid->column('id', __('Id'));
        $grid->column('img', __('头像'))->image();
        $grid->column('title', __('名称'));
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
        $show = new Show(Avatar::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('img', __('头像'))->image();
        $show->field('title', __('名称'));
        $show->field('updated_at', __('修改时间'));
        $show->field('created_at', __('创建时间'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Avatar());

        $form->image('img', __('头像'));
        $form->text('title', __('名称'));

        return $form;
    }
}
