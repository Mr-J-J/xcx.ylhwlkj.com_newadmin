<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\UserOrder;
use Encore\Admin\Layout\Row;
use Encore\Admin\Layout\Content;
use App\Admin\Actions\Order\Refund;
use App\Admin\Actions\Order\ViewOffer;
use App\Models\ApiOrders;
use Encore\Admin\Controllers\AdminController;

class ApiOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '接口出票记录';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ApiOrders());
        $grid->model()->orderBy('created_at','desc');
        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->like('order_no','订单号');
            $filter->like('mobile','手机号');
            $filter->between('created_at', '下单日期')->date();

        });
        $state = (int)request('state',0);
        if($state){
            switch($state){
                case 3:
                    $grid->model()->where('state',3);
                    break;
                default:
                    $grid->model()->where('state','!=',3);
                    break;
            }
        }
        $grid->actions(function ($actions) {
                    // 去掉查看            
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->disableView();
            // $actions->add(new ViewOffer());
            // $actions->add(new Refund());
        });
        $grid->tools(function($tool) use($state){                       
            $tool->append("<a href='/admin/user-api-orders' class='btn btn-sm btn-".($state == 0?'warning':'default')."'>全部订单</a>");
            $tool->append("<a href='/admin/user-api-orders?state=1' class='btn btn-sm btn-".($state == 1?'warning':'default')."'>未出票</a>");
            $tool->append("<a href='/admin/user-api-orders?state=3' class='btn btn-sm btn-".($state == 3?'warning':'default')."'>已出票</a>");          
        });
        $grid->column('order_no', __('订单号'))->link(function($orderno){
            $id = '';
            try {
                $order = UserOrder::getOrderByOrderNo($orderno);
                $id = $order->id;
            } catch (\Throwable $th) {
                //throw $th;
            }
            return '/admin/user-orders/'.$id;
        },'');
        $grid->column('sid','三方订单号');
        $grid->column('mobile', '客户手机');
        
        $grid->column('p_user_amount', '订单金额');
        // $grid->column('discount_price', __('优惠金额'))->display(function($price){
        //     return "<span class='text-danger'>-{$price}</span>";
        // });
        $grid->column('p_amount', '结算金额');
        $grid->column('seat_info', __('座位号'));

        $grid->column('film_name', __('影片名称'));
        $grid->column('cinema_name', __('影院'));        
        $grid->column('remark', '备注');   
        $grid->column('state', __('订单状态'))->using(ApiOrders::$state_enum)->label([
            2003=>'default',
            2002=>'warning',
            2001=>'info',
            2000=>'success'
        ]); 
        // $grid->column('state', __('订单状态'))->display(function($state){
        //     if($state == 3){
        //         return "<span class='label label-success'>已出票</span>";;
        //     }elseif($state == 1){
        //         return "<span class='label label-warning'>已下单</span>";;
        //     }
        //     return "<span class='label label-default'>未出票</span>";
        // });
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
        $show = new Show(ApiOrders::findOrFail($id));
        
        return $show;
    }

    

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ApiOrders());
        
        return $form;
    }
}
