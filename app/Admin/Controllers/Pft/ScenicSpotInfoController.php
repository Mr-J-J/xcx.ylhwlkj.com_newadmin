<?php

namespace App\Admin\Controllers\Pft;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\UUModels\UUScenicSpot;

use App\UUModels\UUScenicSpotInfo;
use App\UUModels\UUScenicSpotTicket;
use Illuminate\Contracts\Support\Renderable;
use App\Admin\Actions\Api\DeleteTicketAction;
use Encore\Admin\Controllers\AdminController;
use App\Admin\Actions\Api\SyncScenicAddAction;
use App\Admin\Actions\Renderable\ShowTicketList;
use App\Admin\Actions\Api\SyncCurrentScenicAction;

class ScenicSpotInfoController extends AdminController
{
    // use HasResourceActions;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '已上架商品管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new UUScenicSpotInfo());
        $grid->model()->latest('updated_at');
        $grid->column('UUimgpath','图片')->image('',90);
        $grid->column('UUp_type','类型')->radio([
            'A'=>'景点',
            'B'=> '线路',
            'C'=> '酒店',
            'F' => '套票',
            'H'=> '演出'
        ]);
        $grid->column('UUid','景区id');
        $grid->column('UUtitle','产品名称');
        $grid->column('UUarea','所在地区');
        $grid->column('id','产品数量')->display(function(){
            return $this->ticketList()->count().'个产品';
        })->expand(ShowTicketList::class);
        $grid->column('updated_at','更新时间');
        $grid->column("operation",'操作')->display(function() use ($grid){
            $html = (new SyncCurrentScenicAction())->setGrid($grid)->setRow($this)->render();
            $html2 = '';
            return $html."<br />".$html2;
        });
        $grid->tools(function($tool){
            // $tool->append(new SyncScenicAddAction());
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
        $show = new Show(UUScenicSpotInfo::findOrFail($id));

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

        $form = new Form(new UUScenicSpotInfo());
        $form->radio('UUp_type','类型')->options([
            'A'=>'景点',
            'B'=> '线路',
            'C'=> '酒店',
            'F' => '套票',
            'H'=> '演出'
        ]);
        return $form;
    }
}
