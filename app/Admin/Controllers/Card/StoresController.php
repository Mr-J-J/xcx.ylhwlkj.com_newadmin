<?php

namespace App\Admin\Controllers\Card;

use App\Admin\Actions\Store\RsStoreSettle;
use App\CardModels\CardPrice;
use Encore\Admin\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\CardModels\Cards;
use App\CardModels\RsStores;
use Illuminate\Support\Facades\Hash;
use Encore\Admin\Controllers\AdminController;

class StoresController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '分销商管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        Admin::js('vendor/retail/js/clipboard.min.js');
        $grid = new Grid(new RsStores());
        $grid->filter(function($filter){
            $filter->like('store_name','分销商名称');
            $filter->like('phone','联系方式');
        });
        $grid->tools(function($tool){
            $tool->append("<a href='".env('APP_URL').'/stores'."' class='btn btn-default btn-sm' target='_blank'>打开分销商管理后台</a>");
        });
        $grid->actions(function($action){
            $action->disableView();
            $action->disableDelete();

            $action->add(new RsStoreSettle());
        });
        $grid->model()->latest();
        $grid->column('id', '分销商ID');
        $grid->column('store_name', '名称');
        $grid->column('store_logo', 'Logo')->image('',45);
        $grid->column('type', '类型');
        $grid->column('phone', '手机号');
        $grid->column('viewprice','价格配置')->display(function(){
            return '查看影旅卡价格配置';
        })->expand(function(){
            $cardList = Cards::getList();
            $priceList = CardPrice::getCardPrice($this->id);
            return view('custom.card.price',compact('cardList','priceList'));
        });
        $grid->column('total_money', '累计分销收入');
        $grid->column('settle_money', '已结算')->link(function(){
            return '/admin/card-settle?com_id='.$this->id;
        },'');
        $grid->column('balance', '可提现金额');
        $grid->column('created_at', '开通时间');
        $states = [
            'on'  => ['value' => 1, 'text' => '打开', 'color' => 'primary'],
            'off' => ['value' => 2, 'text' => '关闭', 'color' => 'default'],
        ];
        $grid->column('shenfen','实名')->display(function ($shenfenid){
            if(!empty($this->shenfenid)||$this->shenfenid!=''){
                return '已实名';
            }else{
                return '未实名';
            }
        })->label('success');
        $grid->column('shenfenid','身份证号');
        $grid->column('name','姓名');
        $grid->column('parentid','上级分销商')->display(function ($parentid){
            if($parentid!=null&&$parentid!=''){
                $a = RsStores::where('id',$parentid)->first();
                return $a->store_name;
            }else{
                return  '';
            }

        });
        $grid->column('super','录入分销权限')->switch($states);
        $grid->column('viewconcat','对接说明')->display(function(){
            return '查看公众号对接说明';
        })->expand(function(){
            $store = $this;
            return view('custom.card.setting',compact('store'));
        });
        // $grid->column('updated_at', __('Updated at'));
        // $grid->column('deleted_at', __('Deleted at'));

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
        $show = new Show(RsStores::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('store_name', __('Store name'));
        $show->field('store_logo', __('Store logo'));
        $show->field('type', __('Type'));
        $show->field('phone', __('Phone'));
        $show->field('password', __('Password'));
        $show->field('balance', __('Balance'));
        $show->field('total_money', __('Total money'));
        $show->field('freeze_money', __('Freeze money'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RsStores());

        $form->text('store_name', '分销商名称');
        $form->image('store_logo', '分销商Logo')->uniqueName();
        $form->text('type', '分销商类型');
        $userTable = 'rs_stores';
        $connection = config('admin.database.connection');
        $states = [
            'on'  => ['value' => 1, 'text' => '打开', 'color' => 'primary'],
            'off' => ['value' => 2, 'text' => '关闭', 'color' => 'default'],
        ];
        $form->switch('super', '录入分销商权限')->states($states);
        $form->mobile('phone', '手机号')
                ->creationRules(['required', "unique:{$connection}.{$userTable}"])
                ->updateRules(['required', "unique:{$connection}.{$userTable},phone,{{id}}"])
                ->help('手机号作为分销商后台登录账号');
        $form->password('password', '登录密码')->rules('required|confirmed');
        $form->password('password_confirmation', '确认登录密码')->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });
            $form->ignore(['password_confirmation']);
        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        return $form;
    }
}
