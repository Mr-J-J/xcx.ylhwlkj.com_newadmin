<?php

namespace App\Admin\Controllers\Mall;

use App\MallModels\Partner;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PartnerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '合作伙伴';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Partner());
        $grid->disableFilter();
        // $grid->column('id', __('Id'));
        $grid->column('title', '商家名称');
        $grid->column('image', '商家logo')->image('',50);
        $grid->actions(function($action){
            $action->disableView();
        });
        // $grid->column('sort', '展示排序');
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Partner::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('image', __('Image'));
        $show->field('sort', __('Sort'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Partner());

        $form->text('title', '商家名称')->rules('required',['required'=>'请填写商家名称']);
        $form->image('image', '商家logo')->help('建议图片尺寸:200px * 200px');
        // $form->number('sort', __('Sort'));
        return $form;
    }
}
