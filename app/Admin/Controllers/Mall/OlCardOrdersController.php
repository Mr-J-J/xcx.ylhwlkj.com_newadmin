<?php

namespace App\Admin\Controllers\Mall;

use App\CardModels\OlCardOrder;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\MallModels\Group;
use App\Models\TicketUser;
use Encore\Admin\Layout\Content;
use App\MallModels\OrderCheckCode;
use App\MallModels\OrderCheckLogs;
use Encore\Admin\Controllers\AdminController;

class OlCardOrdersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影城卡订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OlCardOrder());



        $grid->model()->latest();



        $storeId = request('store_id',0);
        if($storeId){
            $grid->model()->where('store_id',(int)$storeId);
        }
        $userId = (int)request('user_id','');
        if($userId){
            $grid->model()->where('user_id',$userId);
        }
        $grid->disableCreateButton();
        $grid->tools(function($tool){
            $tool->append("<a class='btn btn-sm btn-primary' href='/admin/orders'>全部订单</a>");
        });
        $grid->filter(function($filter){
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('order_sn','订单号');
            $filter->equal('mobile','订单手机号');
            $filter->between('created_at', '下单日期')->date();
            $filter->equal('order_status','订单状态')->radio([OlCardOrder::CANCEL=>'已取消',OlCardOrder::NOPAY=>'待付款',OlCardOrder::SUCCESS=>'已付款']);
        });
        $grid->actions(function($action){
            $action->disableEdit();
            $action->disableDelete();
        });
        $grid->column('order_sn', '订单号');
        $grid->column('user_id','用户信息')->display(function($userId){
            $user = TicketUser::select(['nickname','mobile'])->where('id',$userId)->first();
            if(empty($user)) return $userId;
            return $user->nickname."({$user->mobile})";
        });
        $grid->column('product_title', '商品名称');
        $grid->column('goods_count', '购买数量');        
        $grid->column('order_amount', '订单金额');
        
        $grid->column('pay_money', '支付金额');        
        $grid->column('user_remark', '订单备注');
        $grid->column('order_status', '订单状态')->display(function($orderStatus){
            return OlCardOrder::$status[$orderStatus];
        });
        $grid->column('created_at', '下单时间');
        return $grid;
    }

    public function show($id, Content $content)
    {
        $model = OlCardOrder::findOrFail($id);
        
        return $content
            ->title($this->title())
            ->description($this->description['show'] ?? trans('admin.show'))
            ->row($this->detail($model));
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($model)
    {
        // $show = new Show(Order::findOrFail($id));  
        $userInfo = TicketUser::where('id',$model->user_id)->first();
        $userGroup = Group::where('id',$userInfo->group_id)->first();
        
        $groupTitle = $userGroup?$userGroup->title:'普通会员';
        // $storeInfo = Stores::getStore($model->store_id);
        $detail = [
            ['name'=>'订单编号','value'=>$model->order_sn],
            ['name'=>'订单状态','value'=>OlCardOrder::$status[$model->order_status]],
            ['name'=>'下单时间','value'=>$model->created_at],
            ['name'=>'客户信息','value'=>"[ID:{$model->user_id}] {$userInfo->nickname} ({$groupTitle})"],
            ['name'=>'收货人','value'=>"{$model->receive_name} {$model->mobile} {$model->address}"],
            ['name'=>'订单备注','value'=>$model->user_remark],
            ['name'=>'商品名称','value'=>$model->product_title],            
            ['name'=>'购买数量','value'=>$model->goods_count],
            ['name'=>'订单金额','value'=>'￥'.$model->order_amount],
            
            ['name'=>'影城卡有效期','value'=>date('Y.m.d',$model->check_start_time).' - '. date('Y.m.d',$model->check_end_time)],
            ['name'=>'实际支付金额','value'=>'￥'.$model->pay_money],
        ];

        // if($model->order_status >= Order::REFUND_OK){
        //     $detail[] = array(
        //         'name'=>'退款单号',
        //         'value'=> $model->refund_no
        //     );
        // }

        $codeList = array();
        // foreach($codeList as $item){
        //     $item->check_logs = OrderCheckLogs::where('check_code',$item->code)->where('order_id',$model->id)->get();
        // }
        return view('custom.mall.show-order',compact('detail','codeList'));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new OlCardOrder());

        
        return $form;
    }
}
