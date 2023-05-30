<?php

namespace App\Admin\Controllers\Card;

use App\CardModels\Cards;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;


class CardController extends AdminController
{
    
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影旅卡管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Cards());
        $grid->disableFilter();
        $grid->actions(function($action){
            $action->disableView();
            $action->disableDelete();
        });
        $grid->header(function($query){
            $str = <<<HTML
            
            <div>
            <p><strong>1. 新人免费赠送影旅卡：</strong></p>
            <p>从未领取或购买过影旅卡的用户进入小程序可免费获得一张影旅卡。</p>
            <p>系统获取默认的影旅卡进行赠送，如果没有则按照设置了免费领取次数且价格最低的影旅卡赠送给用户</p>
            </div>
            
HTML;
            return $str;
        });
        $grid->column('id','ID');
        $grid->column('title', '标题');
        $grid->column('short_title', '副标题');
        $grid->column('image', '背景大图')->image('',120,60);
        $grid->column('list_image', '列表图')->image('',60);
        $grid->column('index_image', '首页展示图')->image('',120,60);
        $grid->column('price', '成本价');
        $grid->column('market_price', '划线价');
        $grid->column('card_money', '卡余额');
        $grid->column('free_num', '免费领取次数');
        $grid->column('is_default', '是否默认')->using(['否','默认卡片'])->label(['default','success']);
        $grid->column('state', '状态')->using(['已停用','已启用'])->label(['default','success']);
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
        $show = new Show(Cards::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('short_title', __('Short title'));
        $show->field('image', __('Image'));
        $show->field('list_image', __('List image'));
        $show->field('index_image', __('Index image'));
        $show->field('price', __('Price'));
        $show->field('card_money', __('Card money'));
        $show->field('market_price', __('Market price'));
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
        $form = new Form(new Cards());

        $form->text('title', '标题');
        $form->text('short_title', '副标题');
        $form->text('free_num','每人免费领取次数')->default(0)->help('设定次数后，用户可以免费领取。设定5次，则用户可以免费领取5次');
        $form->decimal('price','成本价')->default(0.00);
        $form->decimal('card_money', '卡余额')->default(0.00);
        $form->decimal('market_price', '划线价')->default(0.00);
        $form->image('image', '背景大图')->help('建议尺寸600 x 330像素，大小900K以内');
        $form->image('list_image','列表图')->help('建议尺寸200 x 200像素，大小900K以内');;
        $form->image('index_image', '首页展示图')->help('建议尺寸300 x 178像素，大小900K以内');
        // $form->number('sort', __('Sort'));
        
        $form->radio('state','卡片状态')->options(['停用','启用'])->default(1);
        
        $form->radio('is_default','是否为默认卡片')->options(['否','设置为默认'])->default(0)->help('系统默认赠送的影旅卡');

        $form->saved(function($form){
            if($form->is_default){
                $form->model()->where('id','!=',$form->model()->id)->update(['is_default'=>0]);
            }
        });


        return $form;
    }
}
