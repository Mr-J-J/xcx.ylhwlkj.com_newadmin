<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Setting;
use Encore\Admin\Layout\Content;
use App\Admin\Forms\Setting as SettingForm;
use Encore\Admin\Controllers\AdminController;

class SettingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '系统设置';


    public function index(Content $content)
    {
        return $content
        ->title($this->title())
        ->description($this->description['index'] ?? trans('admin.edit'))
        ->body(new SettingForm());
    }
        
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Setting::findOrFail($id));

        $show->field('names', __('Names'));
        $show->field('tag', __('Tag'));
        $show->field('desc', __('Desc'));
        $show->field('content', __('Content'));
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
        $form = new Form(new Setting());
        $form->text('tag', __('Tag'));
        $form->text('desc', __('Desc'));
        $form->text('content', __('Content'));

        return $form;
    }
}
