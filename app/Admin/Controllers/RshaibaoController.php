<?php

namespace App\Admin\Controllers;

use App\Models\Rshaibao;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RshaibaoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '海报管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Rshaibao());

        $grid->column('id', __('Id'));
        $grid->column('title', __('标题'));
//        $grid->column('url', __('链接'));
        $grid->column('img', __('图片'))->image();
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
        $show = new Show(Rshaibao::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
//        $show->field('url', __('Url'));
        $show->field('img', __('Img'));
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
        $form = new Form(new Rshaibao());

        $form->text('title', __('标题'));
//        $form->url('url', __('链接'));
        $form->image('img', __('轮播图'));

        return $form;
    }
}
