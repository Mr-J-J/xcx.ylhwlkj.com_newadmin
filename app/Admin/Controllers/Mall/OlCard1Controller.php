<?php

namespace App\Admin\Controllers\Mall;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\CardModels\OlCard;

class OlCard1Controller extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影城卡管理（已激活）';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OlCard());
        $grid->disableCreateButton();
        $grid->actions(function($action){
            $action->disableEdit();
            $action->disableView();
            $action->disableDelete();
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();            
            $filter->like('card_no','卡号');
            $filter->like('user.mobile','用户手机号');
            $filter->equal('type','卡片类型')->radio([1=>'线上卡',2=>'线下卡']);
        });
        $type = (int)request('type',0);
        $status = (int)request('status',0);
        if($status == 4){
            $grid->model()->where('use_number','>',0);
        }elseif($status == 3){
            $grid->model()->where('use_number',0);
        }
        $grid->tools(function($tool) use ($type,$status){
            $tool->append("<a href='/admin/ol-cards-1' class='btn btn-sm btn-".(($type == 0 && $status ==0)?'warning':'default')."'>显示全部卡片</a>");
            $tool->append("<a href='/admin/ol-cards-1?type=1' class='btn btn-sm btn-".($type == 1?'warning':'default')."'>线上影城卡</a>");
            $tool->append("<a href='/admin/ol-cards-1?type=2' class='btn btn-sm btn-".($type == 2?'warning':'default')."'>线下影城卡</a>");
            $tool->append("<a href='/admin/ol-cards-1?status=3' class='btn btn-sm btn-".($status == 3?'warning':'default')."'>已激活</a>");
            $tool->append("<a href='/admin/ol-cards-1?status=4' class='btn btn-sm btn-".($status == 4?'warning':'default')."'>已使用</a>");
            
        });
        $grid->model()->where('user_id','>',0)->latest();
        $grid->column('card_no', '卡号');  
        $grid->column('user.nickname', '所属用户')->link(function(){
            if(!$this->user_id) return '';
            return '/admin/users?id='.$this->user_id;
        },'');      
        
        $grid->column('type', '卡片类型')->using(['','线上卡','线下卡']);
        $grid->column('open_time', '激活时间')->display(function($time){
            if($time == 0) return '-';
            return date('Y-m-d H:i:s',$time);
        });
        
       
        $grid->column('expire_time', '过期时间')->display(function($time){
            if($time == 0) return '-';
            return date('Y-m-d H:i:s',$time);
        });
        $grid->column('number', '已使用/总次数')->display(function(){
            return $this->use_number .' / '. $this->number;
        });
        $grid->column('product.title', '卡片标题')->link(function(){
            return '/admin/ol-card-goods/'.$this->product_id.'/edit';
        },'');  
        $grid->column('state', '卡片状态')->display(function($state){
            if($this->use_number > 0){
                return "<span class='label label-info'>已使用</span>";
            }
            return OlCard::$status[$state];
        });
        $grid->column('created_at', '添加时间');

        return $grid;

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
        $show = new Show(OlCard::findOrFail($id));

        
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new OlCard());
        

        return $form;
    }
}
