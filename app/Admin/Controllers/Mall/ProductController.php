<?php

namespace App\Admin\Controllers\Mall;

use Exception;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\MallModels\Stores;
use App\MallModels\Product;
use Illuminate\Support\Arr;
use App\MallModels\Activite;
use App\MallModels\Category;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Facades\Admin;
use App\Admin\Forms\Product\Sku;
use Encore\Admin\Layout\Content;
use App\Admin\Forms\Product\Tips;
use App\Admin\Forms\Product\Basic;
use Illuminate\Support\MessageBag;
use App\Admin\Forms\Product\Detail;
use App\Admin\Actions\Goods\BatchSaleOnGoods;
use App\Admin\Actions\Goods\BatchSaleOffGoods;
use App\Http\Controllers\Controller;
use App\Admin\Selectable\SelectMallStore;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\HasResourceActions;

class ProductController extends AdminController
{
    // use HasResourceActions;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品管理';


    /**
     * 制作一个网格生成器。
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());
        $grid->model()->latest();
        $grid->batchActions(function($actions){
            $actions->add(new BatchSaleOnGoods());
            $actions->add(new BatchSaleOffGoods());
        });
        $categoryId = request('category_id');
        if($categoryId){
            $childIds = Category::where('parent_id',$categoryId)->pluck('id')->toArray();
            if(!empty($childIds)){
                $grid->model()->whereIn('category_id',$childIds);
            }
        }
        $storeId = (int)request('store_id','');
        if($storeId){
            $grid->model()->where('store_id',$storeId);
        }
        $grid->model()->where('type','!=',3);
        $grid->filter(function($filter){
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1/3,function($filter){
                $category = Arr::pluck(Category::getOptions(),'title','id');
                $activity = Activite::getList()->pluck('title','id');
                $filter->equal('category_id','商品分类')->ignore()->select($category);
                $filter->equal('tags_id','活动专区')->select($activity);
                $filter->equal('state','上架状态')->radio(['已下架','已上架']);
            });
            $filter->column(1/3,function($filter){
                $filter->like('title','商品名称');
                $provinces = \App\MallModels\Region1::getRegions(0,1)->pluck('city_name','city_code');
                $filter->equal('province','城市')->ignore()->select($provinces)->load('city_id','/admin/selectCity/2');
                $citylist = array();
                $province = request('province',0);
                if($province){
                    $citylist = \App\MallModels\Region1::getRegions($province,2)->pluck('city_name','city_code');
                }
                $filter->equal('city_id',' ')->select($citylist);
            });
        });
        $grid->actions(function($action){
            $action->disableView();
        });
        // $grid->column('id', '商品ID');
        $grid->column('image', '图片')->image('',60);
        $grid->column('id', '商品ID');
        $grid->column('title', '商品标题');
        $grid->column('category.title', '所属分类');
        $grid->column('tags_id', '活动专区')->display(function($tagId){
            return Activite::where('id',$tagId)->value('title');
        });
        $grid->column('sku_price', '商品价格(销售价)');
        $grid->column('sale_num', '销量');
        $grid->column('sort', '排序');
        $grid->column('state', '上架状态')->using(['已下架','已上架'])->label(['default','success']);
        $grid->column('peisong', '配送方式')->display(function($peisong){
            $s='';
            for ($i=0; $i < strlen($peisong); $i++) {
                if($peisong[$i]=='0'){
                    $s.='自提';
                }else if($peisong[$i]=='1'){
                    $s.='快递';
                }else if($peisong[$i]=='2'){
                    $s.='二维码';
                }else if($peisong[$i]=='3'){
                    $s.='短信';
                }else if($peisong[$i]=='4'){
                    $s.='同城';
                }else if($peisong[$i]=='5'){
                    $s.='电子票';
                }else if($peisong[$i]=='6'){
                    $s.='五元自配';
                }
            }
            return $s;
        })->label('success');
        $grid->column('baoyou', '是否包邮')->display(function($baoyou){
            $s='';
            for ($i=0; $i < strlen($baoyou); $i++) {
                if($baoyou[$i]=='0'){
                    $s.='包邮';
                }else if($baoyou[$i]=='1'){
                    $s.='运费到付';
                }else if($baoyou[$i]=='2'){
                    $s.='指定存货点';
                }
            }
            return $s;
        })->label('success');
        $grid->column('tuihuo', '退货')->display(function($tuihuo){
            $s='';
            for ($i=0; $i < strlen($tuihuo); $i++) {
                if($tuihuo[$i]=='0'){
                    $s.='不可退';
                }else if($tuihuo[$i]=='1'){
                    $s.='发货前可退';
                }else if($tuihuo[$i]=='2'){
                    $s.='取货前可退';
                }
            }
            return $s;
        })->label('success');
        $grid->column('storeshow', '商家信息')->display(function($storeshow){
            $s='';
            for ($i=0; $i < strlen($storeshow); $i++) {
                if($storeshow[$i]=='0'){
                    $s.='显示';
                }else if($storeshow[$i]=='1'){
                    $s.='不显示';
                }
            }
            return $s;
        })->label('success');
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
        $show = new Show(Product::findOrFail($id));

        $show->field('id', 'Id');
        $show->field('title', 'Title');
        $show->field('sub_title', 'Sub title');
        $show->field('category_id', 'Category id');
        $show->field('tags_id', 'Tags id');
        $show->field('city_id', 'City id');
        $show->field('store_id', 'Store id');
        $show->field('sku_id', 'Sku id');
        $show->field('sku_price', 'Sku price');
        $show->field('sku_market_price', 'Sku market price');
        $show->field('image', 'Image');
        $show->field('sale_num', 'Sale num');
        $show->field('sort', 'Sort');
        $show->field('state', 'State');
        $show->field('peisong', 'peisong');
        $show->field('category_path', 'Category path');
        $show->field('created_at', 'Created at');
        $show->field('updated_at', 'Updated at');

        return $show;
    }




    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new Product());
        Admin::style('.empty-grid{padding:0 !important}');
        $category = Arr::pluck(Category::getOptions(),'title','id');
            $activity = Activite::getList()->pluck('title','id');

            $form->select('category_id','所属分类')->options($category)->rules('required',['required'=>'请选择商品分类']);
            $form->text('title','标题')->rules('required',['required'=>'请填写商品标题']);
            $form->text('sub_title','介绍');
            $form->select('tags_id','添加到专区')->options($activity)->default('');
            $form->text('sort','排序')->default(0)->setWidth(2);
            // $form->radio('type','商品类型')->options(Product::$type)->default(Product::CARD);
            $form->hidden('type','商品类型')->default(Product::CARD);
            $form->radio('state','是否上架')->options(['暂不上架','立即上架']);
            $form->image('image','缩略图')->move('goods_images')->setWidth(3)->uniqueName()->thumbnail('thumb280', 280,280);
            $form->radio('peisong','配送方式')->options(["0" => "到店自取","1" => "快递配送","2" => "二维码","3" => "短信","4" => "同城配送","5" => "电子票","6" => "五元自配送","7" => "无"]);
            $form->radio('baoyou','是否包邮')->options(["0" => "包邮","1" => "运费到付","2" => "指定存货点"]);
            $form->radio('tuihuo','退货')->options(["0" => "不可退","1" => "发货前可退","2" => "取货前可退"]);
            $form->radio('storeshow','是否显示商家信息')->options(["0" => "显示","1" => "不显示"]);
            $form->multipleImage('images','商品图片')->move('goods_images')->uniqueName()->thumbnail('thumb750', 750,750)->pathColumn('url')->removable()->help('同时选中多张图片后上传');
            $form->belongsTo('store_id', SelectMallStore::class, '所属商家')->rules('required',['required'=>'请选择商家'])->help('指定核销订单的商家');
            $form->hidden('city_id')->default('');
        $form->text('virtual_sale_num','虚拟销量')->setWidth(3)->default(0);
        $nowdate = date('Y-m-d H:i:s');
            $form->dateRange('check_start','check_end','核销起止日期')->rules('required',['required'=>'核销日期必填']);
            $form->hasMany('sku','规格列表', function ($form) {
                // $form->column(1/2,function($form){});
                $form->text('title','规格名称')->rules('required')->withoutIcon();
                $form->text('market_price','划线价')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(0);
                $form->text('price','销售价')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(0);
                $form->text('storage','库存数量')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(1);
                $form->text('limit_purchase','每人限购数量')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(0)->help('0为不限制购买数量');
                $form->text('check_price','单次核销金额')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(0)->help('单次核销商家结账金额');
                $form->number('check_number','核销次数')->default(1)->min(1)->rules('required');
                $form->text('commision_money','佣金总额')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(0);
                $form->hidden('sale_num')->default(0);
            });
        $form->UEditor('content.tips','购买须知');
        $form->UEditor('content.content', '商品详情');

        $form->saving(function($form){
            $form->tags_id = $form->tags_id?:'';
            if(empty($form->sku)){
               $form->state = 0;
            }
            if($form->store_id > 0){
                $storeInfo = Stores::getStore($form->store_id);
                $form->city_id = $storeInfo?$storeInfo->city:'';
            }
        });
        $form->saved(function (Form $form) {
            $skulist = $form->model()->sku->toArray();
            if(!$form->model()->wasRecentlyCreated){
                $skulist = $form->sku;
            }
            if(!empty($skulist)){
                $sortPrice = array_column($skulist,'price');
                array_multisort($sortPrice,SORT_ASC,$skulist);
                $defaultSku = $skulist[0];
                $form->model()->sku_id = $defaultSku['id'];
                $form->model()->sku_price = $defaultSku['price'];
                $form->model()->sku_market_price = $defaultSku['market_price'];
                $form->model()->sale_num = $defaultSku['sale_num'];
                $form->model()->save();
            }
        });
        return $form;
    }
}
