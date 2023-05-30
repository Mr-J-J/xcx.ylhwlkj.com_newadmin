<?php

namespace App\Admin\Controllers\Mall;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Arr;

use Illuminate\Http\Request;

use App\MallModels\Category;
use Encore\Admin\Facades\Admin;

use App\CardModels\OlCardProduct;

use App\ApiModels\Wangpiao\Cinema;
use App\ApiModels\Wangpiao\CinemasBrand;
use Encore\Admin\Controllers\AdminController;

class OlCardGoodsController extends AdminController
{
    // use HasResourceActions;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影城卡管理';


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OlCardProduct());
        $grid->model()->where('type',3)->latest();
        $categoryId = request('category_id');
        if($categoryId){
            $childIds = Category::where('parent_id',$categoryId)->where('type',1)->pluck('id')->toArray();
            if(!empty($childIds)){
                $grid->model()->whereIn('category_id',$childIds);
            }
        }
        $storeId = (int)request('store_id','');
        if($storeId){
            $grid->model()->where('store_id',$storeId);
        }
        $grid->filter(function($filter){
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1/3,function($filter){
                $category = Arr::pluck(Category::getOlOptions(),'title','id');
                $filter->equal('category_id','商品分类')->ignore()->select($category);
                $filter->equal('state','上架状态')->radio(['已下架','已上架']);
            });
            $filter->column(1/3,function($filter){
                $filter->like('title','商品名称');

            });
        });
        $grid->actions(function($action){
            $action->disableView();
        });
        // $grid->column('id', '商品ID');
        $grid->column('image', '图片')->image('',60);
        $grid->column('title', '商品标题')->display(function($title){
            return "[ID:{$this->id}] {$title}";
        });
        $grid->column('category.title', '所属分类');
        $grid->column('sku_price', '商品价格(销售价)');
        $grid->column('sale_num', '销量');
        $grid->column('sort', '排序');
        $grid->column('state', '上架状态')->using(['已下架','已上架'])->label(['default','success']);

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
        $show = new Show(OlCardProduct::findOrFail($id));

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

        $form = new Form(new OlCardProduct());
        Admin::style('.empty-grid{padding:0 !important}');
        $category = Arr::pluck(Category::getOlOptions(),'title','id');

        $form->select('category_id','所属分类')->options($category)->rules('required',['required'=>'请选择商品分类']);
        $form->text('title','标题')->rules('required',['required'=>'请填写商品标题']);
        $form->text('sub_title','介绍');
        $list = CinemasBrand::brandsOptions()->toArray();
        $brandArr = array_combine(array_column($list,'id'),array_column($list,'brand_name'));
        $form->multipleSelect('rules.brand_ids','指定院线')->options($brandArr);
        $form->multipleSelect('rules.cinema_ids','指定影院')->options(function ($ids) {
            if(empty($ids)) return array();
            return Cinema::find($ids)->pluck('cinema_name', 'id');
        })->ajax('/admin/api/cinemas')->placeholder('输入影院名称搜索');
        // $form->select('tags_id','添加到专区')->options($activity)->default('');
        $form->text('rules.number','可兑换次数')->default(0)->setWidth(2);
        $form->text('sort','排序')->default(0)->setWidth(2);
        $form->hidden('type','商品类型')->default(OlCardProduct::OLCARD);
        $form->radio('state','是否上架')->options(['暂不上架','立即上架']);
        $form->image('image','缩略图')->move('goods_images')->setWidth(3)->uniqueName()->thumbnail('thumb280', 280,280);
        $form->multipleImage('images','商品图片')->move('goods_images')->uniqueName()->thumbnail('thumb750', 750,750)->pathColumn('url')->removable()->help('同时选中多张图片后上传');
        // $form->belongsTo('store_id', SelectMallStore::class, '所属商家')->rules('required',['required'=>'请选择商家'])->help('指定核销订单的商家');
        $form->hidden('store_id')->default(0);
        $form->hidden('city_id')->default(0);
        $form->text('virtual_sale_num','虚拟销量')->setWidth(3)->default(0);
        $nowdate = date('Y/m/d');

        $form->dateRange('check_start','check_end','兑换有效期')->rules('required',['required'=>'核销日期必填']);
        $form->hasMany('sku','价格信息', function ($form) {
            // $form->column(1/2,function($form){});
            // $form->text('title','规格名称')->rules('required')->withoutIcon();
            $form->hidden('title')->default('默认');
            $form->text('market_price','划线价')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(0);
            $form->text('price','销售价')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(0);
            $form->text('storage','库存数量')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(1);
            $form->text('limit_purchase','每人限购数量')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(0)->help('0为不限制购买数量');
            // $form->text('check_price','单次核销金额')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(0)->help('单次核销商家结账金额');
            // $form->number('check_number','核销次数')->default(1)->min(1)->rules('required');
            // $form->text('commision_money','佣金总额')->withoutIcon()->rules('required')->attribute('style','width:120px;')->default(0);
            $form->hidden('check_price')->default(0);
            $form->hidden('check_number')->default(0);
            $form->hidden('commision_money')->default(0);
            $form->hidden('sale_num')->default(0);
        });
        $form->UEditor('content.tips','购买须知');
        $form->UEditor('content.content', '商品详情');

        $form->saving(function($form){
            // $form->tags_id = $form->tags_id?:'';
            // if(empty($form->sku)){
            //    $form->state = 0;
            // }
            // if($form->store_id > 0){
            //     $storeInfo = Stores::getStore($form->store_id);
            //     $form->city_id = $storeInfo?$storeInfo->city:'';
            // }
        });
        $form->saved(function (Form $form) {
            $skulist = $form->model()->sku->toArray();
            if(!$form->model()->wasRecentlyCreated){
                $skulist = $form->sku;
            }
            if(!empty($skulist)){
                $sortPrice = array_column($skulist,'price');
                array_multisort($sortPrice,SORT_ASC,$skulist);
                $defaultSku = $skulist[0]??array();
                $form->model()->sku_id = $defaultSku['id'];
                $form->model()->sku_price = $defaultSku['price'];
                $form->model()->sku_market_price = $defaultSku['market_price'];
                $form->model()->sale_num = $defaultSku['sale_num'];
                $form->model()->save();
            }
        });
        return $form;
    }

    public function getList(Request $request){
        $cateId = $request->get('q',0);
        $model = new OlCardProduct;
        $list = $model->getList($cateId);
        $outList = array();
        foreach($list as $item){
            $outList[] = array(
                'text'=>$item->title,
                'id'=>$item->id
            );
        }
        return response()->json($outList);
    }
}
