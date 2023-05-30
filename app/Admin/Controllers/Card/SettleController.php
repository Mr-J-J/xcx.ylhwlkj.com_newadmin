<?php

namespace App\Admin\Controllers\Card;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Support\Helpers;

use App\CardModels\SettleList;
use Illuminate\Support\MessageBag;
use App\Admin\Selectable\SelectCardStore;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Support\Facades\DB;

class SettleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商家结算';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SettleList());
        $grid->model()->orderBy('created_at','desc');
        $grid->filter(function($filter){
            $filter->expand();
            $filter->disableIdFilter();
            $filter->equal('com_id','商家ID')    ;
            $filter->like('settle_sn','结款单号');
            $filter->between('created_at','结款时间')->date();
        });
        $grid->column('settle_sn', '结款单号');
        $grid->column('store.store_name', '商家名称');
        $grid->column('settle_money', '结款金额');
        $grid->column('image', '结款凭证')->image('',50)->link(function(){
            return $this->image;
        });
        $grid->column('created_at', '结款时间');
        $grid->actions(function($action){
            $action->disableView();
            $action->disableDelete();
        });
        // $grid->column('sort', '展示排序');
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        return $grid;
    }    
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SettleList());
        $form->belongsTo('com_id', SelectCardStore::class, '选择要结算的商家')->rules('required',['required'=>'请选择商家']);
        $form->currency('settle_money', '结款金额')->symbol('￥')->help('结款金额不能大于商家剩余结账金额');
        $form->image('image', '结款凭证');
        $form->hidden('settle_sn')->value('');
        $form->saving(function (Form $form) {
            $form->settle_sn = Helpers::makeOrderNo('J');            
        });
        $form->saved(function($form){
            DB::beginTransaction();
            try {
                $form->model()->store->settleMoney($form->model()->settle_money);
                $form->model()->after_balance = $form->model()->store->balance;
                $form->model()->save();
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                $form->model()->delete();
                $error = new MessageBag([
                    'title'   => '结算失败！',
                    'message' => $th->getMessage(),
                ]);
                return back()->with(compact('error'));
            }
        });
        return $form;
    }
}
