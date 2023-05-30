<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use App\Models\JfbOrder;
use Encore\Admin\Controllers\AdminController;

class JfbOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '上游接口订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new JfbOrder());
        $grid->model()->orderBy('created_at','desc');
        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->like('order_id','订单号');
            $filter->like('channel_order_id','手机号');
            $filter->between('created_at', '下单日期')->date();

        });
        
        $grid->actions(function ($actions) {
                    // 去掉查看            
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->disableView();
            // $actions->add(new ViewOffer());
            // $actions->add(new Refund());
        });
        // $grid->tools(function($tool) use($state){                       
        //     $tool->append("<a href='/admin/user-orders' class='btn btn-sm btn-".($state == 0?'warning':'default')."'>全部订单</a>");
        //     $tool->append("<a href='/admin/user-orders?state=1' class='btn btn-sm btn-".($state == 1?'warning':'default')."'>待付款</a>");
        //     $tool->append("<a href='/admin/user-orders?state=2' class='btn btn-sm btn-".($state == 2?'warning':'default')."'>待出票</a>");
        //     $tool->append("<a href='/admin/user-orders?state=3' class='btn btn-sm btn-".($state == 3?'warning':'default')."'>已出票</a>");
        //     $tool->append("<a href='/admin/user-orders?state=4' class='btn btn-sm btn-".($state == 4?'warning':'default')."'>已退款</a>");
        //     $tool->append("<a href='/admin/user-orders?state=5' class='btn btn-sm btn-".($state == 5?'warning':'default')."'>已取消</a>");            
        // });
        $grid->column('channel_order_id', __('订单号'))->link(function(){
            $order = \App\Models\UserOrder::where('order_no',$this->channel_order_id)->first();
            if(empty($order)){
                return '';
            }
            return '/admin/user-orders/'.$order->id;
        },'');
        
        
        $grid->column('price', '订单金额');
        
        
        $grid->column('state', __('订单状态'))->using(JfbOrder::$state_enum)->label([
            2003=>'default',
            2002=>'warning',
            2001=>'info',
            2000=>'success'
        ]);        
        $grid->column('created_at', __('下单时间'));
        $grid->disableCreateButton();

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
        $show = new Show(JfbOrder::findOrFail($id));
        $show->field('order_id', __('订单编号'));
        
        return $show;
    }

    

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new JfbOrder());

        

        return $form;
    }
}
