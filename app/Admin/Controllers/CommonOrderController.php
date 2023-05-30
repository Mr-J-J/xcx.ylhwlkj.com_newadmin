<?php

namespace App\Admin\Controllers;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\UserOrder;

use App\Models\CommonOrder;
use App\Admin\Selectable\SelectStore;
use App\Models\Store;
use App\Models\store\OfferServices;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Support\Facades\DB;

class CommonOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '竞价订单池';

    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CommonOrder());
        
        $grid->model()->orderBy('created_at','desc');
        
        $grid->expandFilter(); 
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->tools(function($tool){
            $tool->append("<a class='btn btn-sm btn-primary' href='/admin/common-orders'>全部订单</a>");
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('order_no','订单号')->placeholder('请输入订单号');
            $filter->equal('state','分配状态')->radio(['待分配','已分配']);
        });
         
        // $grid->column('id', __('Id'));
        $grid->column('order_no', '订单号')->link(function(){
            $id = $this->order_id ?: '';
            $offerOrder = \App\Models\store\StoreOfferOrder::getOrderByOrderNo($this->order_no);
            if($offerOrder){
                return '/admin/offer-orders/'. $offerOrder->id;
            }
            return '/admin/user-orders/'.$id;
        },'');;
    
        $grid->column('type', '订单类型')->using(['','系统分配','商家转单','出票超时订单'])->label(['default','default','success','danger']);;
        $grid->column('order.amount','订单金额');
        $grid->column('order.ticket_count','数量');
        $grid->column('state', '派单状态')->using(['待分配','已分配'])->label(['default','success']);
        $grid->column('store_id', '接单商家')->belongsTo(SelectStore::class);
        $grid->column('updated_at', '更新时间');
        
        $grid->actions(function($action){
            $action->disableDelete();
            $action->disableEdit();
        });
        
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
        $show = new Show(CommonOrder::findOrFail($id));

        // $show->field('id', __('Id'));
        $show->field('order_no', '订单号');
        $show->field('type', '订单类型')->using(['','系统分配','商家转单','出票超时订单']);;
        $show->field('state', '派单状态')->using(['待分配','已分配'])->label();
        $show->field('order.amount','订单金额');
        $show->field('order.ticket_count','数量');
        $show->field('store.store_name', '接单商家');
        
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CommonOrder());

        $form->text('order_no', '订单号');
        $form->switch('type','订单类型')->options(['','系统分配','商家转单','出票超时订单']);;
        $form->switch('state', '派单状态');
        $form->number('store_id', '接单商家');
        // $form->text('store_name', __('Store name'));

        $form->saving(function (Form $form) {           
            if($form->model()->state == 1){
                return response()->json([
                    'status'  => false,
                    'message' => '订单已分配过商家',
                    'display'=>[]
                ]);
            }
            if($form->store_id > 0){
                $form->state = 1;
            }
        });
        $form->saved(function (Form $form) {
            $commonOrder = $form->model();
            DB::beginTransaction();
            try {
                if($form->model()->state == 1 && $form->store_id > 0){  
                    $offerService = new OfferServices;
                    $offerService->setOrderStore($form->store_id,$commonOrder->order);
                    //$offerService->pushOfferSuccessMsg($form->model()->store->openid,$form->model()->order);
                }
            } catch (\Throwable $th) {
                $commonOrder->state =0;
                $commonOrder->store_id = 0;
                $commonOrder->save();
                DB::rollback();
                return response()->json([
                    'status'  => false,
                    'message' => '订单分配失败',
                    'display'=>[]
                ]);
            }
            DB::commit();
        });
        
        return $form;
    }
}
