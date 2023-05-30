<?php

namespace App\Admin\Controllers\Mall;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Arr;
use App\MallModels\Activite;
use App\MallModels\Category;
use Encore\Admin\Controllers\AdminController;

class ActiveController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '特惠专区';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Activite());
        
        $category = $this->getCategory();
        // $category = [];
        $grid->filter(function($filter) use ($category){
            $filter->expand();
            $filter->disableIdFilter();
        });        
        $grid->actions(function($action){
            $action->disableView();
        });
        $grid->column('id', __('编号'));
        $grid->column('title', __('备注'));
        $grid->column('image', __('图片'))->image('',300,130);
        // $grid->column('category_id','类别')->display(function($categoryId) use ($category){
        //     return $category[$categoryId] ?? '-';
        // });
        
        // $grid->column('url', __('跳转地址'));
        $grid->column('sort', __('排序'));

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
        $show = new Show(Activite::findOrFail($id));

        $show->field('image', __('图片'));
        $show->field('id', __('编号'));
        $show->field('title', __('备注'));
        // $show->field('url', __('跳转地址'));
        $show->field('sort', __('排序'));
        $show->field('created_at', __('添加时间'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        
        $form = new Form(new Activite());        
        
        
        $form->text('title', __('备注'));
        $form->image('image', __('图片'))->move('banner')->uniqueName()->help('建议尺寸690*280像素，大小900K以内');
           
        // $form->select('full_url', '指定跳转地址')->options($cardList)->default('');
        // $form->text('url','跳转位置')->default('');//->help("<a href='/admin/products'>点击获取商品ID</a>,<a href='/admin/cards'>点击获取影旅卡ID</a>");//->placeholder("商品ID或者影旅卡ID");
        $form->number('sort', __('排序'))->default(1);            
        $form->saving(function($form){
            $form->url = (int)trim($form->url);        
            $form->tag_id = (int)$form->tag_id;                        
        });
        $form->saved(function($form){
            
        });
        return $form;
    }

    protected function getCategory(){
        $category = (array)Category::getFirstList();
        array_unshift($category,['title'=>'小程序首页展示','id'=>0]);
        array_unshift($category,['title'=>' 无 ','id'=>-1]);
        // $activity = Activite::getList()->map(function($d){
        //                 return $d->only(['id','title']);
        //             })->toArray();
        // array_push($category,...$activity);
        
        return Arr::pluck($category,'title','id');
    }
    
    protected function getActivity(){
        $activity = Activite::getList()->pluck('title','id');
        return $activity;
    }
}
