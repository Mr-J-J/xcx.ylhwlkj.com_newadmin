<?php

namespace App\Admin\Controllers;

use App\Models\store\StoreLevel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StoreLevelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商家类别';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StoreLevel());        
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
            $create->text('title', '类别名称');
        });
        $grid->column('title', __('类别名称'));
        $grid->disableFilter();
        $grid->disableExport();
        $grid->actions(function ($actions) {
                   // 去掉查看
            $actions->disableView();
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
        $show = new Show(StoreLevel::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('title', __('类别名称'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StoreLevel());

        $form->text('title', __('类别名称'));

        return $form;
    }
}
