<?php

namespace App\Admin\Controllers\Mall;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Tree;
use App\Support\Helpers;
use App\MallModels\Category;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Arr;
use Encore\Admin\Controllers\AdminController;

class OlCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影城卡类型';

    public function index(Content $content)
    {
        
        $tree = new Tree(new Category);
        $tree->query(function ($model) {
            return $model->where('type', 1);
        });
        $tree->branch(function ($branch) {
            $src = Helpers::formatPath($branch['image'],'admin');
            $logo = "<img src='$src' style='max-width:30px;max-height:30px' class='img'/>";
        
            return "{$branch['id']} - {$branch['title']} $logo";
        });
        return $content
            ->header('影城卡类型')
            ->body($tree);
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Category());

        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('parent_id', __('Parent id'));
        $grid->column('image', __('Image'));
        $grid->column('is_nav', __('Is nav'));
        $grid->column('sort', __('Sort'));
        $grid->column('link_url', __('Link url'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Category::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('parent_id', __('Parent id'));
        $show->field('image', __('Image'));
        $show->field('is_nav', __('Is nav'));
        $show->field('sort', __('Sort'));
        $show->field('link_url', __('Link url'));
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
        $form = new Form(new Category());
        $category = Category::getOlOptions();
        $category = Arr::prepend($category, ['id'=>0,'title'=>'默认分类']);
        $category = Arr::pluck($category,'title','id');        
        $form->select('parent_id', '所属分类')->options($category)->rules('required',['required'=>'请选择分类'])->default(0);
        $form->text('title', '分类名称');
        $form->image('image', '分类图片')->move('icon')->uniqueName()->help('建议上传200px * 200px ,png格式图片');
        $form->text('sort', '排序')->setWidth(2);
        $form->hidden('type')->default(1);
        $states = [
            'on'  => ['value' => 1, 'text' => '显示到首页', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '不显示', 'color' => 'default'],
        ];
        $form->switch('is_nav', '显示到首页菜单')->states($states);
        // $form->text('link_url', '链接地址');

        return $form;
    }
}
