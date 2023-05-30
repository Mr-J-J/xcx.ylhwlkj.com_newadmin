<?php

namespace App\Admin\Controllers;

use App\Models\Essay;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class EssayController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '公众号文章';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Essay());

        $grid->column('id', __('Id'));
        $grid->column('title', __('标题'));
        $grid->column('con', __('介绍'));
        $grid->column('url', __('文章链接'));


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
        $show = new Show(Essay::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('标题'));
        $show->field('con', __('介绍'));
        $show->field('url', __('文章链接'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Essay());

        $form->text('title', __('标题'));
        $form->text('con', __('介绍'));
        $form->text('url', __('文章链接'));
        $form->image('img','封面');
        return $form;
    }
}
