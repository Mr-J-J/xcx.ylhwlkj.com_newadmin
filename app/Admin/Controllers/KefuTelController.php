<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Setting;
use Encore\Admin\Layout\Content;
use App\Admin\Forms\Setting as SettingForm;
use Encore\Admin\Controllers\AdminController;

class KefuTelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客服电话设置';

    // public function index(Content $content){
    //    // return redirect('/admin/kefu/offer_kefu/edit');
    // }
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
        $form->text('content', '客服电话');
        $form->saved(function($form){
            Setting::getSettings(true);
            return redirect('/admin/kefu_tel/kefu_tel/edit');
        });
        return $form;
    }
}
