<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Setting;
use Encore\Admin\Layout\Content;
use App\Admin\Forms\Setting as SettingForm;
use Encore\Admin\Controllers\AdminController;

class TeyueController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '特约供应商说明';

  
    
    protected function grid(){
        $grid = new Grid(new Setting());        
        return $grid;
    }
    
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Setting());
        $form->UEditor('content', '说明内容');
        $form->saved(function($form){
            Setting::getSettings(true);
            return redirect('/admin/teyue/teyuegongyingshang/edit');
        });
        return $form;
    }
}
