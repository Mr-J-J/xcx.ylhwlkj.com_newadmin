<?php

namespace App\Admin\Controllers\Pft;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\UUModels\UUTicketOrder;
use App\UUModels\UUOrderException;
use App\Admin\Actions\Pft\OrderRefund;
use App\Admin\Actions\Pft\ExceptionAction;
use Encore\Admin\Controllers\AdminController;

class PwExceptionOrdersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '票付通异常订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UUOrderException());
        $state = (int)request('state',0);
        $grid->model()->where('state',$state)->latest();
        // $grid->column('id', __('Id'));
        $grid->column('order_no', '订单号');
        $grid->column('request_id','票付通request_id');
        $grid->column('errorinfo','异常信息')->display(function($info){
            return $this->exception_no.':'.$info;
        });
        $grid->column('state', '状态')->using(['未处理','已处理'])->label(['warning','success']);
        $grid->column('created_at', '时间');
        $grid->filter(function($filter){
            $filter->equal('order_no','订单号');
            $filter->equal('state','状态')->radio(['未处理','已处理']);
            $filter->between('created_at','购买时间')->date();
        });
        $grid->tools(function($tool) use($state){                       
            $tool->append("<a href='/admin/pw-exception-order' class='btn btn-sm btn-".($state == 0?'warning':'default')."'>未处理</a>");
            $tool->append("<a href='/admin/pw-exception-order?state=1' class='btn btn-sm btn-".($state == 1?'warning':'default')."'>已处理</a>");            
        });
        $grid->disableCreateButton();        
        $grid->actions(function($action){
            $action->disableEdit();
            $action->disableView();
            $action->disableDelete();
            $action->add(new ExceptionAction());
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
        $show = new Show(UUOrderException::findOrFail($id));

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
        $form = new Form(new UUOrderException());

        $form->text('order_no', __('Order no'));
        return $form;
    }
}
