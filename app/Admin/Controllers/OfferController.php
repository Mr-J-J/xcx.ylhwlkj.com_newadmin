<?php

namespace App\Admin\Controllers;

use App\Models\Store;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\OrderRules;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use App\Admin\Actions\Order\Offer;
use App\Models\store\StoreOfferDetail;
use \App\Models\store\StoreOfferOrder;
use App\Models\store\StoreOfferRecord;
use Encore\Admin\Controllers\AdminController;

class OfferController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '竞价列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StoreOfferOrder());
        $grid->model()->orderBy('created_at','desc');
        $grid->disableExport();

        $storeId = (int)request('store_id','');
        if($storeId){
            $grid->model()->where('store_id',$storeId);
        }
        $grid->disableCreateButton();
        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->like('order_no','订单号');
            $filter->like('buyer_phone','手机号');
            $filter->between('created_at', '下单日期')->date();

        });
        $state = (int)request('state','');
        if($state){
            $state2 = $state - 1;
            $grid->model()->where('offer_status',$state2);
        }
        $grid->tools(function($tool) use($state){
            //报价状态 0竞价中 1待出票 2已出票 3已关闭/竞价失败 4退回
            $tool->append("<a href='/admin/offer-orders' class='btn btn-sm btn-".($state == 0?'warning':'default')."'>全部订单</a>");
            $tool->append("<a href='/admin/offer-orders?state=1' class='btn btn-sm btn-".($state == 1?'warning':'default')."'>竞价中</a>");
            $tool->append("<a href='/admin/offer-orders?state=2' class='btn btn-sm btn-".($state == 2?'warning':'default')."'>待出票</a>");
            $tool->append("<a href='/admin/offer-orders?state=3' class='btn btn-sm btn-".($state == 3?'warning':'default')."'>已出票</a>");
            $tool->append("<a href='/admin/offer-orders?state=4' class='btn btn-sm btn-".($state == 4?'warning':'default')."'>已关闭</a>");
            $tool->append("<a href='/admin/offer-orders?state=5' class='btn btn-sm btn-".($state == 5?'warning':'default')."'>已退回</a>");
        });
        $grid->actions(function($action){
            $action->disableDelete();
            $action->disableEdit();
            // $action->add(new Offer());

        });

        $grid->column('order_no', __('订单编号(点击查看报价)'))->modal('查看报价',function($model){
            $offerlist = $model->offerlist()->get()->map(function($offer){
                // dump($offer);
                if($offer->store){
                    $offer->store_name = $offer->store->store_name;
                    return $offer->only(['store_name','offer_amount','updated_at']);
                }
                return [];
            });
            return new Table(['商家','报价','报价时间'],$offerlist->toArray());
        });

        $grid->column('buyer_phone', '用户手机');
//        $grid->column('ju','票商票据')->display(function ($ju){
//           //通过订单编号查询user_ticket_img的数据
//            $user_ticket_img = \App\Models\user\TicketImg::where('order_no',$this->order_no)->first();
//            if($user_ticket_img){
//                $user_ticket_img = $user_ticket_img->images;
//                return "<img src='".$user_ticket_img."' style='width:100px;height:100px;'>";
//            }else{
//                return "无票据";
//            }
//
//        });
        $grid->column('amount', '支付金额');
        // $grid->column('ticket_count', '购票数量');
        $grid->column('movie_name', '影片名称');
        $grid->column('show_time', '放映时间')->display(function($showtime){
            return date('Y-m-d H:i',$showtime);
        });
        $grid->column('cinemas', '影院名称')->display(function($cinema){
            return $cinema."<br />".$this->halls;
        });
        // $grid->column('halls', '影厅');
        $grid->column('seat_names', '座位');

        // $grid->column('seat_flag', '座位类型');
        $grid->column('offer_times', '竞价次数');
        $grid->column('offer_status', '竞价状态')->using([0=>'竞价中',1=>'待出票',2=>'已出票',3=>'已关闭',4=>'退回'])->label(['primary','warning','success','danger','default']);
        $grid->column('store_id', '中标商家')->display(function($storeId){
            return Store::where('id',$storeId)->value('store_name');
        });
        $grid->column('success_money', '中标价格');
        $grid->column('order_id',' ')->display(function(){return '订单详情';})->link(function($row){
            return "/admin/user-orders/{$row->order_id}";
        },'');
        return $grid;
    }

    public function show($id, Content $content)
    {
        $model = StoreOfferOrder::findOrFail($id);

        return $content
            ->title($this->title())
            ->description($this->description['show'] ?? trans('admin.show'))
            ->row($this->detail($model));
    }

    protected function detail($model){
        $statusTxt = [0=>'竞价中',1=>'待出票',2=>'已出票',3=>'已关闭',4=>'退回'];
        $model->show_time = date('Y-m-d H:i:s',$model->show_time);
        $model->offer_status = $statusTxt[$model->offer_status];
        $model->expire_time = date('Y-m-d H:i:s',$model->expire_time);
        $rulesInfo = OrderRules::where('order_no',$model->order_no)->first();
        $storeInfo = Store::where('id',$model->store_id)->first();
        //通过订单编号查询user_ticket_img的数据
        $user_ticket_img = \App\Models\user\TicketImg::where('order_no',$model->order_no)->first();
        if($user_ticket_img){
            $user_ticket_img = $user_ticket_img->images;
            //$user_ticket_img通过,或,分割
            $user_ticket_img = explode(',',$user_ticket_img);
            //循环输出图片
            $img='';
            foreach($user_ticket_img as $k=>$v){
                $img = $img."<img src='".$v."' style='width:100px;'>";
            }
        }else{
            $img = "无票据";
        }
        $detail = [
            ['name'=>'订单编号','value'=>$model->order_no],
            ['name'=>'用户手机','value'=>$model->buyer_phone],
            ['name'=>'放映时间','value'=>$model->show_time],
            ['name'=>'电影名称','value'=>$model->movie_name],

            ['name'=>'座位号','value'=>$model->seat_names],
            ['name'=>'是否情侣座','value'=>$model->seat_flag],
            ['name'=>'影院名称','value'=>$model->cinemas . "({$model->halls})"],
            ['name'=>'是否接受调座','value'=>$model->accept_seats?'接受调座':'不接受调座'],
            ['name'=>'支付金额','value'=>sprintf('￥%.2f',$model->amount)],
            ['name'=>'购票数量','value'=>$model->ticket_count],

            ['name'=>'竞价次数','value'=>$model->offer_times],
            ['name'=>'开始时间','value'=>$model->created_at],
            ['name'=>'到期时间','value'=>$model->expire_time],
            ['name'=>'竞价状态','value'=>$model->offer_status],
            ['name'=>'中标商家','value'=>($storeInfo?$storeInfo->store_name:'')."(".($storeInfo?$storeInfo->store_phone:'').")"],
            ['name'=>'中标金额','value'=>sprintf('￥%.2f',$model->success_money)],
            ['name'=>'商家票据','value'=>$img],
        ];
        $list = StoreOfferRecord::where('order_id',$model->id)->get();
        $liucheng = StoreOfferDetail::where('offer_order_id',$model->id)->get();
        return view('custom.offer.show-order',compact('detail','list','liucheng'));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StoreOfferOrder());

        $form->number('order_id', __('Order id'));
        $form->number('user_id', __('User id'));
        $form->text('order_no', __('Order no'));
        $form->switch('buy_type', __('Buy type'))->default(1);
        $form->switch('accept_seats', __('Accept seats'));
        $form->text('buyer_phone', __('Buyer phone'));
        $form->number('market_price', __('Market price'));
        $form->number('discount_price', __('Discount price'));
        $form->number('amount', __('Amount'));
        $form->number('ticket_count', __('Ticket count'));
        $form->text('movie_name', __('Movie name'));
        $form->text('movie_image', __('Movie image'));
        $form->text('show_version', __('Show version'));
        $form->number('close_time', __('Close time'));
        $form->number('show_time', __('Show time'));
        $form->text('citys', __('Citys'));
        $form->number('cinema_id', __('Cinema id'));
        $form->text('cinemas', __('Cinemas'));
        $form->text('halls', __('Halls'));
        $form->text('seat_areas', __('Seat areas'));
        $form->text('paiqi_id', __('Paiqi id'));
        $form->text('api_order_id', __('Api order id'));
        $form->textarea('seat_ids', __('Seat ids'));
        $form->number('brand_id', __('Brand id'));
        $form->text('seat_names', __('Seat names'));
        $form->text('seat_flag', __('Seat flag'));
        $form->number('offer_times', __('Offer times'))->default(1);
        $form->number('offer_status', __('Offer status'));
        $form->number('expire_time', __('Expire time'));
        $form->number('store_id', __('Store id'));
        $form->number('success_money', __('Success money'));

        return $form;
    }
}
