<?php

namespace App\Admin\Controllers;


use Encore\Admin\Grid;
use Encore\Admin\Show;

use App\Models\user\Commision;

use Encore\Admin\Controllers\AdminController;

class UserCommisionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户佣金明细';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Commision());


        $grid->model()->latest();
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableBatchActions();
        
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('order_no','订单号');
            $filter->like('user.mobile','手机号');
            $filter->equal('type','类型')->radio([1=>'收入','2'=>'提现']);
        });
        $grid->column('order_no', '订单号');
        $grid->column('module','描述')->display(function($module){
            if(!empty(Commision::$moduleTips[$module])){
                return Commision::$moduleTips[$module];
            }
            return '';
        });
        $grid->column('user_id', __('用户手机'))->display(function(){
            return $this->user->mobile;
        });        
        $grid->column('money', __('金额'));        
        $grid->column('type', '类型')->using([1=>'收入','2'=>'提现'])->label(['success','default']);
        $grid->column('created_at', __('时间'));
        
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
        $show = new Show(Commision::findOrFail($id));

        
        return $show;
    }

    // /**
    //  * Make a form builder.
    //  *
    //  * @return Form
    //  */
    // protected function form()
    // {
    //     $form = new Form(new WithDraw());

    //     $form->number('user_id', __('User id'));
    //     $form->number('money', __('Money'));
    //     $form->number('before_money', __('Before money'));
    //     $form->number('after_money', __('After money'));
    //     $form->text('trade_name', __('Trade name'));
    //     $form->text('trade_no', __('Trade no'));
    //     $form->number('success_time', __('Success time'));
    //     $form->switch('state', __('State'));

    //     return $form;
    // }
}
