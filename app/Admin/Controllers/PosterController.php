<?php

namespace App\Admin\Controllers;

use App\Models\Poster;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PosterController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '海报管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Poster());
        $grid->actions(function($action){
            $action->disableView();
        });
        $grid->model()->orderBy('id');
        $grid->column('id', __('Id'));
        $grid->column('poster', '图片')->image('',200);
        $grid->column('title', '文案');
        $grid->column('sort', '排序');
        $grid->column('created_at', '添加时间');
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
        $show = new Show(Poster::findOrFail($id));
        
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Poster());

        $form->text('title', '文案')->rules('max:200',['max'=>'最多200字'])->help('最多200字');
        $form->number('sort', '排序')->help('值越大越靠前');
        $form->image('poster', '海报图片')->uniqueName()->help('建议尺寸 640 * 1070,图片大小900Kb以内');
        return $form;
    }
}
