<?php

namespace App\Admin\Controllers;

use App\MallModels\Activite;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Carousel;
use App\MallModels\Category;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Support\Arr;

class CarouselController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '轮播图';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Carousel());

        $category = $this->getCategory();
        // $category = [];
        $grid->filter(function($filter) use ($category){
            $filter->expand();
            $filter->disableIdFilter();
            $filter->equal('category_id','分类')->select($category);
        });
        $source = (int)request('source',0);
        $grid->model()->where('source',$source);
         $grid->tools(function($tool) use ($source){
            $tool->append("<a href='/admin/carousels' class='btn btn-sm btn-".($source == 0?'warning':'default')."'>影旅汇小程序</a>");
            $tool->append("<a href='/admin/carousels?source=1' class='btn btn-sm btn-".($source == 1?'warning':'default')."'>影旅汇享小程序</a>");

        });
        $grid->column('id', __('编号'));
        $grid->column('image', __('图片'))->image('',300,130);
        $grid->column('category_id','类别')->display(function($categoryId) use ($category){
            return $category[$categoryId] ?? '-';
        });
        $actlist = $this->getActivity();
        $grid->column('tag_id','专区')->display(function($tagId) use ($actlist){
            return $actlist[$tagId] ?? '-';
        });
        $grid->column('title', __('备注'));
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
        $show = new Show(Carousel::findOrFail($id));

        $show->field('image', __('图片'));
        $show->field('id', __('编号'));
        $show->field('title', __('备注'));
        $show->field('url', __('跳转地址'));
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

        $form = new Form(new Carousel());
        $form->select('category_id','所属分类')->options($this->getCategory())->help('分类代表banner图展示的位置')->default(-1);
        $form->select('tag_id','所属专区')->options($this->getActivity())->default(0);
        $form->text('title', __('备注'));
        $form->image('image', __('图片'))->move('banner')->uniqueName()->help('建议尺寸750 x 300像素，大小900K以内');
        $form->radio('source','发布到小程序')->options(['影旅汇','影旅汇享']);

        // $form->select('full_url', '指定跳转地址')->options($cardList)->default('');
//        ,<a href='/admin/cards'>点击获取影旅卡ID</a>
        $form->text('url','跳转位置')->default('')->help("<a href='/admin/films'>点击获取电影ID</a>")->placeholder("商品ID或者影旅卡ID");
        $form->number('sort', __('排序'))->default(1);

        $form->ignore(['goods_id','card_id']);
        $form->saving(function($form){
            $form->url = (int)trim($form->url);
            $form->tag_id = (int)$form->tag_id;


        });
        $form->saved(function($form){
           if($form->model()->source == 2) {
               return redirect('/admin/carousels?source=2');
           }
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
