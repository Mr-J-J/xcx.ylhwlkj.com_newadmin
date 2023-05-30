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

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '订单列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserOrder());
        $grid->model()->orderBy('created_at','desc');
        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->like('order_no','订单号');
            $filter->like('buyer_phone','手机号');
            $filter->between('created_at', '下单日期')->date();

        });
        $state = (int)request('state',0);
        if($state){
            switch($state){
                case 1:
                    $grid->model()->where('order_status',10);
                    break;
                case 2:
                    $grid->model()->where('order_status',20);
                    break;
                case 3:
                    $grid->model()->where('order_status',30);
                    break;
                case 4:
                    $grid->model()->where('order_status',40);
                    break;
                case 5:
                    $grid->model()->where('order_status',0);
                    break;
            }
        }
        $grid->actions(function ($actions) {
                    // 去掉查看
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->add(new ViewOffer());
            $actions->add(new Refund());
        });
        $grid->tools(function($tool) use($state){
            $tool->append("<a href='/admin/user-orders' class='btn btn-sm btn-".($state == 0?'warning':'default')."'>全部订单</a>");
            $tool->append("<a href='/admin/user-orders?state=1' class='btn btn-sm btn-".($state == 1?'warning':'default')."'>待付款</a>");
            $tool->append("<a href='/admin/user-orders?state=2' class='btn btn-sm btn-".($state == 2?'warning':'default')."'>待出票</a>");
            $tool->append("<a href='/admin/user-orders?state=3' class='btn btn-sm btn-".($state == 3?'warning':'default')."'>已出票</a>");
            $tool->append("<a href='/admin/user-orders?state=4' class='btn btn-sm btn-".($state == 4?'warning':'default')."'>已退款</a>");
            $tool->append("<a href='/admin/user-orders?state=5' class='btn btn-sm btn-".($state == 5?'warning':'default')."'>已取消</a>");
        });
        $grid->column('order_no', __('订单号'))->link(function(){
            return '/admin/user-orders/'.$this->id;
        },'');
        $grid->column('buyer_phone', '客户手机')->display(function($buyer_phone){
            $str = '';
            if($this->buy_type == 1){
                $str ="<br /><span class='label label-primary'>实时订单</span>";
            }else{
                $str ="<br /><span class='label label-danger'>加急订单</span>";
            }
            $apiOrder = ApiOrders::where('order_no',$this->order_no)->first();
            if(!empty($apiOrder)){
                $str ="<br /><span class='label label-primary'>网票网出票</span>";
            }
            return $buyer_phone.$str;
        })->link(function(){
            return url('admin/users',['id'=>$this->user_id]);
        });
        $grid->column('market_price', '市场价');
        $grid->column('ordermoney', '订单金额')->display(function($price){
            return ($this->amount+$this->discount_price)."<span class='text-danger'>(-{$this->discount_price})</span>";
        });
        // $grid->column('discount_price', __('优惠金额'))->display(function($price){
        //     return "<span class='text-danger'>-{$price}</span>";
        // });
        $grid->column('amount', '实际支付');
        $grid->column('pay_name', '支付方式')->display(function($payname){
            if($payname == 'ol_card'){
                return '影城卡支付';
            }elseif($this->use_card == 1){
                return '微信支付+影旅卡抵扣';
            }else{
                return '微信支付';
            }
            return '';
        });

        $grid->column('seat_names', __('座位号'))->display(function($seatnames){
            $accepts = $this->accept_seats?'可换座':'不支持换座';
            if(!empty($this->old_seat_names)){
                $accepts = '已调座';
            }
            return "{$seatnames} <br /><span class='text-danger'>($accepts)</span>";
        });

        $grid->column('movie_name', __('影片名称'));
        $grid->column('cinemas', __('影院'))->display(function($cinemas){
            return "【{$this->citys}】{$cinemas}";
        });

        $grid->column('order_status', __('订单状态'))->display(function(){
            return UserOrder::statusTxt($this->order_status,$this->refund_status);
        })->label([
            0=>'default',
            10=>'warning',
            20=>'info',
            30=>'success',
            40=>'default'
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
        $show = new Show(UserOrder::findOrFail($id));
        $show->field('order_no', __('订单编号'));
        $show->field('com_id', __('订单来源'))->as(function(){
            return $this->com_id > 0 ? '影旅汇享小程序':'影旅汇小程序';
        });
        $show->field('buy_type', __('订单类型'))->using([1=>'特惠购票',2=>'快速购票']);
        $show->field('accept_seats', __('是否接受调座'))->using(['不接受调座','接受调座']);
        $show->field('buyer_phone', __('用户电话'));
        $show->field('market_price', __('市场价'));
        $show->field('ordermoney', __('订单金额'))->as(function(){
            return round($this->amount+$this->discount_price,2);
        });
        $show->field('discount_price', __('优惠价格'));
        $show->field('amount', __('实际支付金额'));
        $show->field('movie_name', __('影片名称'));
        $show->field('show_time', __('观影时间'))->as(function($showtime){
            $show = $showtime ? date('Y-m-d H:i:s',$showtime) : '';
            $close = $this->close_time?date('Y-m-d H:i:s',$this->close_time):'';
            return $show . ' - ' . $close;
        });
        $show->field('cinemas', __('影院'));
        $show->field('halls', __('影厅'));
        $show->field('seat_flag', __('座位类型'))->as(function($seat_flag){
            $flag = explode(',',$seat_flag);
            $result = (int)array_sum($flag);
            return $result?'情侣座':'普通座';
        });
        $show->field('seat_names', __('座位号'));
        $show->field('ticket_count', __('购票数量'));
        $show->field('order_status', __('订单状态'))->as(function(){
            return UserOrder::statusTxt($this->order_status,$this->refund_status);
        });
        $show->field('expire_time', __('过期时间'))->as(function($expire_time){
            return $expire_time?date('Y-m-d H:i:s',$expire_time):'';
        });
        $show->field('pay_time', __('付款时间'))->as(function($paytime){

            return $paytime?date('Y-m-d H:i:s',$paytime):'';
        });
        $show->field('transaction_id','交易单号');
        $show->field('refund_remark','备注');
        $show->field('created_at', __('创建时间'));

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
        $form = new Form(new UserOrder());

        $form->text('order_no', __('Order no'));
        $form->number('user_id', __('User id'));
        $form->switch('buy_type', __('Buy type'))->default(1);
        $form->switch('accept_seats', __('Accept seats'));
        $form->text('buyer_phone', __('Buyer phone'));
        $form->switch('agreements', __('Agreements'));
        $form->number('market_price', __('Market price'));
        $form->number('discount_price', __('Discount price'));
        $form->number('amount', __('Amount'));
        $form->number('ticket_count', __('Ticket count'));
        $form->text('movie_name', __('Movie name'));
        $form->text('movie_image', __('Movie image'));
        $form->number('close_time', __('Close time'));
        $form->number('show_time', __('Show time'));
        $form->text('citys', __('Citys'));
        $form->text('cinemas', __('Cinemas'));
        $form->text('halls', __('Halls'));
        $form->switch('seat_flag', __('Seat flag'));
        $form->text('api_order_id', __('Api order id'));
        $form->text('seat_areas', __('Seat areas'));
        $form->text('seat_ids', __('Seat ids'));
        $form->number('paiqi_id', __('Paiqi id'));
        $form->text('seat_names', __('Seat names'));
        $form->number('order_status', __('Order status'));
        $form->number('expire_time', __('Expire time'));
        $form->number('cancel_time', __('Cancel time'));
        $form->switch('pay_status', __('Pay status'));
        $form->text('pay_name', __('Pay name'));
        $form->number('pay_time', __('Pay time'));
        $form->text('out_trade_no', __('Out trade no'));

        return $form;
    }
}
