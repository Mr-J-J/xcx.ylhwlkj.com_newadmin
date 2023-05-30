<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\Suggestion;

class SuggestionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '意见反馈';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Suggestion());
        $grid->disableCreateButton();
        $grid->actions(function($action){
            $action->disableEdit();
            $action->disableView();
        });
        // $grid->column('id', __('Id'));
        $grid->column('user_id', '用户ID');
        $grid->column('phone', '用户手机');
        $grid->column('user_type', '用户类型')->using([1=>'商家',2=>'用户']);
        $grid->column('type', '反馈类型');
        $grid->column('content', '反馈内容');        
        $grid->column('detail','查看详情')->display(function(){
            return '查看详情';
        })->modal('反馈详情', function ($model) {
            $model->images = explode(',',$model->images);
            return view('custom.admin.suggestion-show',$model);
        });
        // $grid->column('state', __('State'));
        $grid->column('created_at', '反馈时间');
        // $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Suggestion::findOrFail($id));
        
        $show->field('phone', '用户联系方式');
        $show->field('user_type','用户类型')->using([1=>'商家',2=>'用户']);
        $show->field('type', '反馈类型');
        $show->field('content', '反馈内容');
        $show->field('images', '图片')->image('',200,400);
        
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Suggestion());

        $form->number('user_id', __('User id'));
        $form->switch('user_type', __('User type'))->default(1);
        $form->text('type', __('Type'));
        $form->text('content', __('Content'));
        $form->textarea('images', __('Images'));
        $form->mobile('phone', __('Phone'));
        $form->switch('state', __('State'));

        return $form;
    }
}
