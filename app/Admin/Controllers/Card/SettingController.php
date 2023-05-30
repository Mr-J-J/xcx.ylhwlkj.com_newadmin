<?php

namespace App\Admin\Controllers\Card;

use App\CardModels\CardSetting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SettingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影旅卡配置';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CardSetting());

        $grid->column('id', __('Id'));
        $grid->column('discount', __('Discount'));
        $grid->column('tips', __('Tips'));
        $grid->column('use_rules', __('Use rules'));
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
        $show = new Show(CardSetting::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('discount', __('Discount'));
        $show->field('tips', __('Tips'));
        $show->field('use_rules', __('Use rules'));
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
        $form = new Form(new CardSetting());

        // $form->rate('discount','优惠折扣')->setWidth(1);
        $form->textarea('tips', '使用说明');
        // $form->text('use_rules', '权益');
        $form->table('use_rules','使用权益', function ($table) {
            $table->text('title','标题');
            $table->text('text','权益内容');
        });
        $form->saved(function($form){
            return redirect('/admin/card-settings/1/edit');
        });
        return $form;
    }
}
