<?php

namespace App\Admin\Controllers;

use App\Models\Store;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\store\WithDraw;
use App\Admin\Actions\Store\StoreWithdrawOK;
use App\Admin\Actions\Store\StoreWithdrawErr;
use Encore\Admin\Controllers\AdminController;

class StoreWithdrawController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商家提现';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WithDraw());
        $grid->model()->latest();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->quickSearch(function($model,$query){            
            $list = Store::where('store_name','like',"%{$query}%")->paginate(20,['id']);
                $ids = array();
                foreach($list as $item){
                    $ids[] = $item->id;
                }
                
            $model->whereIn('store_id', $ids);
        
        })->placeholder('搜索商家名称');
        
        $grid->actions(function($action){
            $action->disableEdit();
            $action->disableDelete();
            if($this->row->state  == 0){
                $action->add(new StoreWithdrawOK());
                $action->add(new StoreWithdrawErr());
            }
        });
        $grid->column('id', __('序号'));

        $grid->column('store_id', __('商家名称'))->display(function(){
            if($this->store){
                return $this->store->store_name;
            }
            return '';
        })->link(function(){
            return url("admin/stores/{$this->store_id}/edit");
        },'');
        $grid->column('title','提现方式');
        $grid->column('draw_account', '提现账号');
        $grid->column('account_name', '账号姓名');
        $grid->column('money', __('提现金额'));
        $grid->column('state', __('状态'))->using(['提现中','提现成功','提现失败'])->label(['default','success','danger']);
        $grid->column('remark', '备注');
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

        // $show->field('id', __('Id'));
        $show->field('store_id', __('商家名称'))->as(function(){
            return $this->store?$this->store->store_name:'';
        });
        $show->field('title', __('描述'));
        $show->field('money', __('提现金额'));
        // $show->field('before_money', __('提现前'));
        // $show->field('after_money', __('提现后'));
        $show->field('trade_name', __('提现方式'));
        $show->field('draw_account', __('提现账号'));
        $show->field('trade_no', __('平台交易号'));
        $show->field('success_time', __('提现成功时间'))->as(function(){
            if($this->success_time){
                return date('Y-m-d H:i:s',$this->success_time);
            }
            return '';
        });
        $show->field('state', __('状态'))->using(['提现中...','提现成功','提现失败']);;
        $show->field('created_at', __('提现时间'));
        // $show->field('updated_at', __('Updated at'));
        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                // $tools->disableList();
                $tools->disableDelete();
            });;
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WithDraw());

        $form->number('store_id', __('Store id'));
        $form->text('title', __('Title'));
        $form->number('money', __('Money'));
        $form->number('before_money', __('Before money'));
        $form->number('after_money', __('After money'));
        $form->text('trade_name', __('Trade name'));
        $form->text('trade_no', __('Trade no'));
        $form->number('success_time', __('Success time'));
        $form->switch('state', __('State'));

        return $form;
    }
}
