<?php

namespace App\Admin\Controllers\Pft;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use App\UUModels\UUOrderRefund;
use App\Admin\Actions\Pft\AgreeRefund;
use App\Admin\Actions\Pft\RefuseRefund;
use Encore\Admin\Controllers\AdminController;

class PwRefundOrdersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '票付通退票订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UUOrderRefund());
        $grid->model()->latest();
        // $grid->column('id', __('Id'));
        $grid->column('refund_no', '退款单号');
        $grid->column('Order16U','票付通订单号')->display(function($pftno){
            return "票付通单号:".$pftno."<br />订单号:".$this->order_no;
        });
        $grid->column('refundNum','退票数量');
        $grid->column('RefundAmount','应退款金额')->display(function($amount){
            return round($amount/100,2);
        });
        $grid->column('RefundFee','退票手续费')->display(function($amount){
            return round($amount/100,2);
        });
        
        $grid->column('created_at', '申请时间');
        $grid->column('local_remark', '备注说明');
        $grid->column('Refundtype','审核结果')->using(['审核中',1=>'同意退票',2=>'拒绝退票'])->label(['warning','success','default']);;
        $grid->column('state', '状态')->using(['退款中','退款成功','退款失败'])->label(['warning','success','default']);
        $grid->filter(function($filter){
            $filter->equal('order_no','订单号');
            $filter->between('created_at','时间')->date();
        });
        $grid->disableCreateButton();
        $grid->actions(function($action){
            $action->disableEdit();
            $action->disableView();
            $action->disableDelete();
            if(!$action->row->state){
                $action->add(new AgreeRefund());
                $action->add(new RefuseRefund());
            }
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
        $show = new Show(UUOrderRefund::findOrFail($id));

        $show->field('id', __('Id'));
        

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UUOrderRefund());

        $form->text('order_no', __('Order no'));
        return $form;
    }
}
