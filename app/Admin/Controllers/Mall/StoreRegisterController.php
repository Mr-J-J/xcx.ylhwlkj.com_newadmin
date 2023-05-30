<?php

namespace App\Admin\Controllers\Mall;

use App\MallModels\StoreRegister;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StoreRegisterController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '卡券商家入驻';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StoreRegister());

        $grid->column('id', __('Id'));
        $grid->column('kefu_qrcode', __('Kefu qrcode'));
        $grid->column('content', __('Content'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(StoreRegister::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('kefu_qrcode', __('Kefu qrcode'));
        $show->field('content', __('Content'));
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
        $form = new Form(new StoreRegister());

        $form->image('kefu_qrcode','入驻二维码');
        $form->UEditor('content', '关于我们');
        $form->UEditor('price_tips', '价格说明');
        $form->saved(function (Form $form) {
            return redirect('/admin/store-registers/1/edit');
        });
        
        return $form;
    }
}
