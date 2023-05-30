<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\TicketUser;
use App\Models\user\WithDraw;
use App\Admin\Actions\User\UserWithdrawOK;
use App\Admin\Actions\User\UserWithdrawErr;
use Encore\Admin\Controllers\AdminController;

class UserWithdrawController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户提现';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WithDraw());

        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->quickSearch(function($model,$query){            
            $list = TicketUser::where('mobile','like',"%{$query}%")->paginate(20,['id']);
                $ids = array();
                foreach($list as $item){
                    $ids[] = $item->id;
                }
                
            $model->whereIn('user_id', $ids);
        
        })->placeholder('搜索用户手机号');
        $grid->model()->latest();
        $grid->actions(function($action){
            $action->disableEdit();
            $action->disableDelete();
            if($this->row->state  == 0){
                $action->add(new UserWithdrawOK());
                $action->add(new UserWithdrawErr());
            }
        });


        $grid->column('id', __('序号'));
        $grid->column('user_id', __('用户手机'))->display(function(){
            return $this->user->mobile;
        });
        $grid->column('money', __('提现金额'));
        $grid->column('success_time', __('提现成功时间'))->display(function($successtime){
            if($successtime){
                return date('Y-m-d H:i:s',$successtime);
            }
            return '';
        });;
        $grid->column('remark', '备注');
        $grid->column('state', __('状态'))->using(['提现中','提现成功','提现失败'])->label(['default','success','danger']);
        $grid->column('created_at', __('提现时间'));
        
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
        $show = new Show(WithDraw::findOrFail($id));

        $show->field('id', __('序号'));
        $show->field('user_id', __('用户手机'))->as(function(){
            return $this->user->mobile;
        });;
        $show->field('money', __('提现金额'));
        // $show->field('before_money', __('提现前'));
        // $show->field('after_money', __('提现后'));
        $show->field('trade_name', __('提现方式'));
        $show->field('trade_no', __('平台交易单号'));
        $show->field('success_time', __('提现成功的时间'));
        $show->field('state', __('状态'))->using(['提现中...','提现成功','提现失败']);;;
        $show->field('created_at', __('申请时间'));
        // $show->field('updated_at', __('Updated at'));
        $show->panel()
        ->tools(function ($tools) {
            $tools->disableEdit();
            // $tools->disableList();
            $tools->disableDelete();
        });;
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
