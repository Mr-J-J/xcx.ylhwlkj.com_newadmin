<?php

namespace App\Admin\Controllers\Card;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\CardModels\Cards;
use App\CardModels\CardOrder;
use App\CardModels\RsStores;
use EasyWeChat\Kernel\Messages\Card;
use Encore\Admin\Controllers\AdminController;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影旅卡订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CardOrder());
        $grid->model()->latest();
        $grid->tools(function($tool){
            $tool->append("<a class='btn btn-sm btn-primary' href='/admin/card-orders'>全部订单</a>");
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('order_sn','订单号');
            $filter->like('mobile','用户手机号');
            $list = Cards::getList()->map(function($list){
                return $list->only(['id','title']);
            })->pluck('title','id');
            $filter->equal('card_id','影旅卡')->select($list);
            $filter->between('created_at','下单时间')->date();
            $filter->equal('order_status','订单状态')->radio(CardOrder::$status);
        });
        $grid->disableCreateButton();
        $grid->actions(function($action){
            $action->disableDelete();
            $action->disableEdit();
        });
        $grid->column('order_sn', '订单号');
        $grid->column('mobile', '客户手机号');
        $grid->column('com_id','分销商')->display(function($comId){
            $storeInfo = RsStores::getStoreInfo($comId);
            if(empty($storeInfo)) return $comId;
            return $storeInfo->store_name;
        });
        $grid->column('card_id', '影旅卡')->display(function($cardId){
            $cardInfo = json_decode($this->card_info);
            return $cardInfo->title;
        });
        $statuKeys = array_keys(CardOrder::$status);
        $labelArray = array_combine($statuKeys,['default','default','success']);
        $grid->column('order_amount', '支付金额');
        $grid->column('order_status', '订单状态')->display(function($status){
            return CardOrder::$status[$status];
        })->label($labelArray);
        $grid->column('created_at', '下单时间');

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
        $show = new Show(CardOrder::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_sn', __('Order sn'));
        $show->field('user_id', __('User id'));
        $show->field('com_id', __('Com id'));
        $show->field('card_id', __('Card id'));
        $show->field('card_info', __('Card info'));
        $show->field('number', __('Number'));
        $show->field('card_money', __('Card money'));
        $show->field('order_amount', __('Order amount'));
        $show->field('mobile', __('Mobile'));
        $show->field('order_status', __('Order status'));
        $show->field('expire_time', __('Expire time'));
        $show->field('transaction_id', __('Transaction id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CardOrder());

        $form->text('order_sn', __('Order sn'));
        $form->number('user_id', __('User id'));
        $form->number('com_id', __('Com id'));
        $form->number('card_id', __('Card id'));
        $form->text('card_info', __('Card info'));
        $form->number('number', __('Number'));
        $form->decimal('card_money', __('Card money'))->default(0.00);
        $form->decimal('order_amount', __('Order amount'));
        $form->mobile('mobile', __('Mobile'));
        $form->switch('order_status', __('Order status'));
        $form->number('expire_time', __('Expire time'));
        $form->text('transaction_id', __('Transaction id'));

        return $form;
    }
}
