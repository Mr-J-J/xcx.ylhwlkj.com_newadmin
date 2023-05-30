<?php

namespace App\Admin\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Store;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Support\Helpers;
use App\Models\store\StoreLevel;
use App\Admin\Actions\Store\Audit;
use App\Admin\Actions\Store\SetDefault;
use App\Models\store\StoreCheckOut;
use Encore\Admin\Controllers\AdminController;

class StoreCheckOutController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '票商结算';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StoreCheckOut());
        
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('store_id','商家ID');
            
            $filter->equal('state','结算状态')->radio(['待结算','已结算']);
            $filter->between('created_at','出票时间')->date();
        });
        $state = request('state',2);
       

        $grid->tools(function($tool) use($state){                       
            $tool->append("<a href='/admin/store-checkout' class='btn btn-sm btn-".($state == 2?'warning':'default')."'>全部订单</a>");
            $tool->append("<a href='/admin/store-checkout?state=0' class='btn btn-sm btn-".($state == 0?'warning':'default')."'>待结算</a>");
            $tool->append("<a href='/admin/store-checkout?state=1' class='btn btn-sm btn-".($state == 1?'warning':'default')."'>已结算</a>");         
        });
        $grid->header(function($query){
            $totalMoney = $query->sum('money');
            $totalMoney = round($totalMoney/100,2);
            $waitSettleMoney = $query->where('state',0)->sum('money');
            $waitSettleMoney = round($waitSettleMoney/100,2);
            $settleMoney = round($totalMoney- $waitSettleMoney,2);
            

            $str = "<span style=''><strong>票商应结金额: ￥</strong></span><span style='font-size:20px;font-weight:bold;margin-left:5px;'>{$totalMoney}</span>";
            $str = "<span style='margin-left:30px;'><strong>待结算: ￥</strong></span><span style='font-size:20px;font-weight:bold;margin-left:5px;'>{$waitSettleMoney}</span>";
            $str .= "<span style='margin-left:30px;color:#00A65A'><strong>已结算: ￥</strong></span><span style='color:#00A65A;font-size:20px;font-weight:bold;margin-left:5px;'>{$settleMoney}</span>";
            return $str;
        });
        $grid->model()->latest();
        $grid->column('order_no', '订单号');
        $grid->column('store.store_name', '店铺名称');
        
        $grid->column('money', '结算金额');
        $grid->column('state', '结算状态')->using([0=>'待结算' ,1=>'已结算'])->label([
            0=> 'default',
            1 => 'success'
        ]);
        $grid->column('created_at','订单出票时间');
        $grid->column('endtime','结算时间')->display(function($endtime){
            if($endtime && $this->state){
                return date('Y-m-d H:i:s',$endtime);
            }
            return '-';
        });
        

        $grid->disableBatchActions();
        $grid->disableActions();
        // $grid->actions(function($actions){
        //     $actions->disableDelete();
        //     $actions->disableView();           
        // });
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
        $show = new Show(Store::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('avatar', __('头像'));
        $show->field('nickname', __('昵称'));
        $show->field('sex', __('性别'));
        $show->field('province', __('Province'));
        $show->field('city', __('City'));
        $show->field('country', __('Country'));
        $show->field('openid', __('Openid'));
        $show->field('unionid', __('Unionid'));
        $show->field('store_name', __('Store name'));
        $show->field('store_pass', __('Store pass'));
        $show->field('store_level', __('Store level'));
        $show->field('store_province', __('Store province'));
        $show->field('store_city', __('Store city'));
        $show->field('store_country', __('Store country'));
        $show->field('store_pos_str', __('Store pos str'));
        $show->field('store_state', __('Store state'));
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
        $form = new Form(new Store());

        $form->display('avatar', __('头像'))->with(function($value){
            return "<img src=" . $value." />";
        });
        $form->display('nickname', __('昵称'));
        $form->display('sex', __('性别'))->with(function($value){
            return $value ? '男':'女';
        });
        
        $form->display('openid', __('Openid'));
        // $form->text('unionid', __('Unionid'));
        $form->display('store_name', __('店铺名称'));
        // $form->text('store_pass', __('Store pass'));
        $form->display('store_province', __('所在城市'))->with(function($value){
            return $value . $this->store_city . $this->store_country;
        });
        $form->select('store_level', __('商家等级'))->options(StoreLevel::all()->pluck('title','id'));
        // $form->text('store_city', __('Store city'));
        // $form->text('store_country', __('Store country'));
        // $form->text('store_pos_str', __('Store pos str'));
        $form->text('remark', '备注')->rules('max:200');
        $form->radio('store_state', __('商家注册状态'))->options(['未注册','待审核','审核通过'])->default(0);

        $form->saving(function($form){
            if($form->model()->id){
                if(!$form->model()->storeInfo && $form->model()->store_state != 0){                    
                    $error = new \Illuminate\Support\MessageBag([
                        'title'   => '保存失败',
                        'message' => '商家未注册，不能审核',
                    ]);
                    return back()->with(compact('error'));
                }
            }
        });
        return $form;
    }
}
