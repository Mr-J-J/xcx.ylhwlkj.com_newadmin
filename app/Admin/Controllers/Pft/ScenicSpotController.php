<?php

namespace App\Admin\Controllers\Pft;


use App\Admin\Actions\Api\SyncCurrentScenicallAction;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\UUModels\UUScenicSpot;

use App\UUModels\UUScenicSpotTicket;
use Illuminate\Contracts\Support\Renderable;
use App\Admin\Actions\Api\DeleteTicketAction;
use App\Admin\Actions\Api\SyncCurrent;
use Encore\Admin\Controllers\AdminController;
use App\Admin\Actions\Api\SyncScenicAddAction;
use App\Admin\Actions\Renderable\ShowTicketList;
use App\Admin\Actions\Api\SyncCurrentScenicAction;

class ScenicSpotController extends AdminController
{
    // use HasResourceActions;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '票付通产品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new UUScenicSpot());
        $grid->model()->latest('updated_at');
        $grid->column('UUimgpath','图片')->image('',90);
        $grid->column('UUp_type','类型')->using(UUScenicSpot::$type);
        $grid->column('UUid','景区id');
        $grid->column('UUtitle','产品名称');
        $grid->column('UUarea','所在地区');
        $grid->column('id','是否上架')->display(function(){
            if($this->detail){
                return "<span class='label label-success'>已上架</span>";
            }
            return "<span class='label label-default'>未上架</span>";
        });//->expand(ShowTicketList::class);
        $grid->column('updated_at','更新时间');
        $grid->column("operation",'操作')->display(function() use ($grid){
            $html = (new SyncCurrentScenicAction('产品上架'))->setGrid($grid)->setRow($this)->render();
            $html2 = '';
            return $html."<br />".$html2;
        });
        $grid->header(function($header){
            $total = \App\UUModels\UUScenicSpot::count();
            return <<<HTML
        <div>当前已下载<b>{$total}</b>条产品。</div>
        <div>接口每页100条，可输入指定页码进行同步。</div>
        <div>当前展示已下载的票付通产品，挑选产品上架即可展示到小程序。</div>
HTML;
        });
        $grid->tools(function($tool){
            $tool->append(new SyncScenicAddAction());
//            $tool->append(new SyncCurrentScenicallAction());
            // $tool->append(new SyncScenicAddAction('从第一页开始同步',true));
        });
        $grid->batchActions(function ($batch) {
            $batch->add(new SyncCurrent());
        });
        $grid->actions(function($actions){
            $actions->disableView();
            $actions->disableEdit();
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('UUid','景区id');
            $filter->like('UUtitle','景区名称');
            $filter->like('UUarea','所在地区');
        });
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
        $show = new Show(UUScenicSpot::findOrFail($id));

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

        $form = new Form(new UUScenicSpot());

        return $form;
    }
}
