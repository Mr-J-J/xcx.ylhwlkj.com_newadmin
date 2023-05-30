<?php

namespace App\Admin\Controllers\Card;

use App\Admin\Actions\Store\RsStoreWithdrawErr;
use App\Admin\Actions\Store\StoreWithdrawOK;
use App\CardModels\RsStores;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;


use App\CardModels\StoreBalanceDetail;
use Encore\Admin\Controllers\AdminController;


class RsStoreBalanceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '分销商分成明细';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StoreBalanceDetail());
        $grid->model()->latest();
        $grid->disableCreateButton();
        
        
        
        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('order_sn','订单号');
            $filter->like('store.store_name','分销商名称');
            $filter->like('store.phone','分销商手机号');
            $filter->between('created_at','分成时间')->date();
        });
        $grid->column('store_id',' ');
        $grid->column('remark','分成类型');
        $grid->column('money', '分成金额');
        $grid->column('com_id', __('商家名称'))->display(function(){
            if($this->store){
                return $this->store->store_name;
            }
            return '';
        })->link(function(){
            return url("admin/rs-stores/{$this->com_id}/edit");
        },'');
        
        $grid->column('state', __('状态'))->using(['待结算','已结算'])->label(['default','success']);
        $grid->column('created_at', '分成时间');
        $grid->column('endtime', '结算时间')->display(function($endtime){
            if(!$endtime){
                return (string)$this->created_at;
            }
            return date('Y-m-d H:i:s',$endtime);
        });
        $grid->column('order_sn', '订单号');

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
        $show = new Show(StoreBalanceDetail::findOrFail($id));

        
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StoreBalanceDetail());

       
        

        return $form;
    }
}
