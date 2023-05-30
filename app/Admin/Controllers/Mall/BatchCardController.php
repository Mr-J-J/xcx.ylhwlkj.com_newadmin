<?php

namespace App\Admin\Controllers\Mall;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Arr;
use App\MallModels\Category;
use \App\CardModels\OlCardBatch;
use App\Admin\Actions\BatchStartUse;
use App\CardModels\OlCard;
use Encore\Admin\Controllers\AdminController;

class BatchCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影城卡创建批次';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OlCardBatch());
        $grid->model()->where('type',1)->latest();
        $grid->actions(function($actions){
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->disableView();
            $count = OlCard::where('batch_id',$this->row['id'])->where('state','<',2)->count();
            if($count){
                $actions->add(new BatchStartUse());
            }
        });
        $grid->column('title', '批次')->link(function(){
            return '/admin/ol-cards-2?batch_id='.$this->id;
        },'');
        $grid->column('product.title','卡类型')->link(function(){
            return '/admin/ol-card-goods/'.$this->product_id.'/edit';
        },'');
        $grid->column('number', '生成数量');
        $grid->column('bind_number', '已激活数量')->display(function(){
            return OlCard::where('batch_id',$this->id)->where('state',10)->count();
        });
        $grid->column('created_at', '创建时间');

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
        $show = new Show(OlCardBatch::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('number', __('Number'));
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
        $form = new Form(new OlCardBatch());

        $form->ignore(['category_id']);
        $form->select('category_id','选择分类')->options(Arr::pluck(Category::getOlOptions(),'title','id'))->load('product_id','/admin/api/cardgoods');
        
        $form->select('product_id','卡类型')->rules('required',['required'=>'请选择卡片类型']);
        $form->text('title', '批次')->default('影城卡（线下）- '.date('Y-m-d'));
        $form->number('number', '生成卡数量')->min(1)->max(6000)->default(1)->help('单次最多生成6000张卡');
        $form->hidden('type')->value(1);//影城卡
        if($form->isCreating()){
            $form->saved(function($form){
                $form->model()->batchCreateCard();
            });
        }
        return $form;
    }
}
