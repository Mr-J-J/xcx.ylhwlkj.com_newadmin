<?php

namespace App\Admin\Controllers\Mall;

use App\MallModels\Group;

use Illuminate\Support\MessageBag;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
/**
 * 会员卡
 */
class GroupController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '会员等级';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Group());
        $grid->disableFilter();
        // $grid->column('id', __('Id'));
        
        $grid->column('image', '会员卡图片')->image('',120);
        $grid->column('title', '会员级别');
        $grid->column('discount', '折扣优惠')->display(function($value){
            return $value.'折';
        });
        $grid->column('comision', '消费返利')->display(function(){
            return '一级消费返利：'.$this->level1_rate.'% <br />二级消费返利：'.$this->level2_rate .'%';
        });
        $grid->column('cash_money', '最低累计消费金额');
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
        $show = new Show(Group::findOrFail($id));

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
        $form = new Form(new Group());

        $form->text('title', '会员级别')->rules('required',['required'=>'请填写商家名称']);
        $form->image('image', '会员卡图片');
        $form->text('cash_money', '最低累计消费金额')->withoutIcon()->append('元')->help('达到设定消费金额后成为当前等级会员')->setWidth(2)->default(0);
        $form->text('discount', '折扣优惠')->withoutIcon()->append('折')->help('0~10之间，0代表无折扣')->setWidth(2)->default(0);
        $form->text('level1_rate','一级消费返利')->withoutIcon()->append('%')->help('0~100之间，0代表无返利;')->setWidth(2)->default(0);
        $form->text('level2_rate','二级消费返利')->withoutIcon()->append('%')->help('0~100之间，0代表无返利;')->setWidth(2)->default(0);
        $form->saving(function($form){
            $form->discount = round($form->discount,1);
            if($form->discount > 10){
                $error = new MessageBag([
                    'title'   => '折扣优惠配置错误',
                    'message' => '折扣应设置在0~10之间',
                ]);        
                return back()->with(compact('error'));
            }
            $form->level1_rate = $level1  = round($form->level1_rate,2);
            $form->level2_rate = $level2 = round($form->level2_rate,2);
            if(($level1+$level2) > 100){
                $error = new MessageBag([
                    'title'   => '返利信息配置错误',
                    'message' => '一级返利+二级返利不能大于100%',
                ]);        
                return back()->with(compact('error'));
            }
        });
        // $form->number('sort', __('Sort'));
        return $form;
    }
}
