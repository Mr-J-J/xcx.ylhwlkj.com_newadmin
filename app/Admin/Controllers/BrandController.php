<?php

namespace App\Admin\Controllers;


use Encore\Admin\Form;
use Encore\Admin\Grid;

use Encore\Admin\Show;
use App\Models\store\StoreLevel;

use App\Admin\Selectable\SelectStore;
use App\ApiModels\Wangpiao\CinemasBrand;

use Encore\Admin\Controllers\AdminController;

class BrandController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '院线管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CinemasBrand());
        $grid->header(function($query){
            $str = <<<HTML
            
            <div>
            <p><strong>1. 影旅汇小程序价格设置：</strong></p>
            <p>系统设置中的【快速购票折扣】、【特惠购票折扣】用于统一设置所有院线的优惠折扣。</p>
            <p>院线管理中的【快速购票折扣】、【特惠购票折扣】用于单独设置某一个院线的优惠折扣。院线设置的折扣优先。</p>
            <p><strong>1. 影旅卡小程序价格设置：</strong></p>
            <p>系统设置中的【影旅卡优惠折扣】用于统一设置所有院线的优惠折扣。</p>
            <p>院线管理中的【影旅卡优惠折扣】用于单独设置某一个院线的优惠折扣。院线设置的折扣优先。</p>
            <p><strong>注：以上价格都在三方接口价的基础进行优惠</strong></p>
            </div>
            
HTML;
            return $str;
        });
        // $grid->model()->where('id',1);
        $grid->quickSearch('brand_name')->placeholder('搜索品牌名称');
        // $grid->disableFilter(fa);
        $grid->disableCreateButton();
        $grid->actions(function($action){
            $action->disableDelete();
            $action->disableView();
        });
        $grid->column('id', __('Id'));
        $grid->column('brand_name', __('品牌名称'));
        $grid->column('levels_id', __('商家类别'))->display(function($levels){
            $levels = StoreLevel::getLevelsByIds($levels);
            $levelsarr = array();
            foreach($levels as $item){
                $levelsarr[] = $item->title;
            }
            $levelsarr = array_map(function($level){
                return "<span class='label label-success'>{$level}</span>";
            },$levelsarr);
            return join('&nbsp;', $levelsarr);
        });        
        $grid->column('discount','影旅卡优惠折扣')->display(function($discount){
            if(intval($discount) == 0){
                return '<span style="color:#666">系统默认配置</span>';
            }
            return '<span style="color:#666">接口价基础上优惠</span> <b>'.intval($discount).'%</b>';
        });
        $grid->column('tehui_price_rate','特惠购票折扣')->display(function($tehui_price_rate){
            if(intval($tehui_price_rate) == 0){
                return '<span style="color:#666">系统默认配置</span>';
            }
            return '<span style="color:#666">接口价格</span> <b>'.round($tehui_price_rate,2).'折</b>';
        });;
        $grid->column('price_discount_rate','快速购票折扣')->display(function($price_discount_rate){
            if(intval($price_discount_rate) == 0){
                return '<span style="color:#666">系统默认配置</span>';
            }
            return '<span style="color:#666">接口价格</span> <b>'.round($price_discount_rate,2).'折</b>';
        });
        $grid->column('offer_price','票商最高出价比例')->display(function($offer_price){
            if(intval($offer_price) == 0){
                return '<span style="color:#666">系统默认配置</span>';
            }
            return '<span style="color:#666">订单支付金额的</span><b>'.intval($offer_price).'%</b>';
        });
        $grid->column('rs_order_commision','购票订单分销商分成比例')->display(function($rs_order_commision){
            if(intval($rs_order_commision) == 0){
                return '<span style="color:#666">系统默认配置</span>';
            }
            return '<span style="color:#666">订单支付金额的</span><b>'.intval($rs_order_commision).'%</b>';
        });
        $states4 = [
            'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
        ];
        $grid->column('redirect_out_ticket','网票网直接出票')->switch($states4)->help('关闭后当前院线购票订单不再通过网票网出票。');
        $grid->column('store.store_name','默认服务商');
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
        $show = new Show(CinemasBrand::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('brand_name', __('品牌名称'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CinemasBrand());
        
        $form->text('brand_name', __('院线名称'))->help('以下可以单独设置院线的票价折扣');
        $form->rate('discount','影旅卡优惠折扣')->setWidth(2)->help('0~100的数字，接口价优惠比例')->default(0.00);
       $form->text('price_discount_rate','快速购票价折扣')->setWidth(2)->append('折')->help('0~10的数字，基于三方接口的市场价折扣,为0则采用系统默认设置')->default(0.00);
        $form->text('tehui_price_rate','特惠购票折扣')->setWidth(2)->append('折')->help('0~10的数字，基于三方接口的市场价折扣,为0则采用系统默认设置')->default(0.00);
        $form->rate('offer_price','票商最高出价比例')->setWidth(2)->help('为0则采用系统默认设置')->default(0.00);
        $form->rate('rs_order_commision','购票订单分销商分成比例')->setWidth(2)->help('为0则采用系统默认设置')->default(0.00);
        $states4 = [
            'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
        ];
        $form->multipleSelect('levels_id', '指定接单商家类别')->options(StoreLevel::all()->pluck('title', 'id'))->help('当前院线订单指派给指定商家类别');
        $form->switch('redirect_out_ticket','网票网直接出票')->states($states4)->setWidth(3)->help('开启后院线下的购票订单采用网票网直接出票')->default(0);
        
        $form->belongsTo('store_id',SelectStore::class,'默认服务商家')->help('该院线下的订单无商家接单时分派给指定商家');  
        $form->saving(function($form){
            // dd($form);
            // $form->redirect_out_ticket = intval($form->redirect_out_ticket == 'on');
        });
        return $form;
    }

    
}
