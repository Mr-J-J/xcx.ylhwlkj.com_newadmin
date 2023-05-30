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
use Encore\Admin\Controllers\AdminController;

class StoresController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商家管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Store());
        
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('store_name','店铺名称');
            $filter->like('remark','备注');
        });
        
        $grid->model()->orderBy(DB::raw('field(store_state,1,2,0)'))->orderBy('created_at','desc');
        $grid->column('avatar', __('头像'))->image('',45);        
        $grid->column('store_name', __('店铺名称'));
        $grid->column('store_province', __('所在城市'))->display(function(){
            return $this->store_province . $this->store_city . $this->store_country;
        });
        $grid->column('store_level', __('商家等级'))->display(function(){
            return $this->store_level_txt;
        });
        $grid->column('storeInfo.order_count','订单量')->link(function(){
            return '/admin/offer-orders?store_id='.$this->id;
        },'');
        $grid->column('storeInfo.freeze_money', __('待结算'))->link(function(){
            return '/admin/store-checkout?store_id='.$this->id.'&state=0';
        },'');
        $grid->column('storeInfo.balance', __('可提现'));
        $grid->column('storeInfo.points', __('积分'));
        $grid->column('storeInfo.draw_rate', __('出票率'));
        $grid->column('storeInfo.mean_time', __('平均出票时间'));
        $grid->column('remark', '备注');       
        $grid->column('storeInfo.taking_mode', __('接单状态'))->using(['接单关闭','接单开启']);
        $grid->column('store_state', __('商家是否审核'))->using([0=>'未注册' ,1=>'审核中' , 2=> '审核通过' ])->label([
            0=> 'default',
            1=> 'warning',
            2 => 'success'
        ]);

        $grid->actions(function($actions){
            $actions->disableDelete();
            $actions->disableView();
            if(!empty($this->row['store_name'])){
                $actions->add(new Audit());
                $setting = Helpers::getSetting('offer_defualt_store');
                if(empty($setting) || $setting['store_id'] != $this->row['id']){
                    $actions->add(new SetDefault());
                }
            }
        });
        $grid->disableCreateButton();
        $grid->column('created_at', __('注册时间'));

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
         $form->display('alipay_account', '支付宝账号');
         $form->display('alipay_name', '支付宝账号姓名');
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
                if(!$form->model()->storeInfo && $form->store_state != 0){                    
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
