<?php

namespace App\Admin\Controllers\Mall;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\CardModels\OlCard;
use Illuminate\Support\Arr;
use App\MallModels\Category;
use App\Admin\Actions\StartUse;
use App\Admin\Actions\BatchCard;
use App\CardModels\OlCardProduct;
use Illuminate\Support\Facades\Auth;
use App\Admin\Actions\BatchStartOlCard;
use Encore\Admin\Controllers\AdminController;

class OlCard2Controller extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影城卡管理（未激活）';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OlCard());
        $batchId = (int)request('batch_id',0);
        $grid->model()->when($batchId,function($query,$batchId){
            return $query->where('batch_id',$batchId);
        })->latest();
        $status = (int)request('status',0);
        if($status == 4){
            $grid->model()->where('state','<',2);
        }elseif($status == 3){
            $grid->model()->where('state','>=',2);
        }
        $grid->tools(function($tool) use ($batchId,$status){
            $tool->append("<a href='/admin/ol-cards-2' class='btn btn-sm btn-default'>显示全部卡片</a>");
            $tool->append("<a href='/admin/ol-card-batches' class='btn btn-sm btn-default'>批次管理</a>");
            $tool->append("<a href='/admin/ol-card-batches/create' class='btn btn-sm btn-success'>批量生成影城卡</a>");
            $tool->append("<a href='/admin/ol-cards-2?status=3' class='btn btn-sm btn-".($status == 3?'warning':'default')."'>已启用</a>");
            $tool->append("<a href='/admin/ol-cards-2?status=4' class='btn btn-sm btn-".($status == 4?'warning':'default')."'>未启用</a>");
        });
        $grid->batchActions(function($actions){
            $actions->add(new BatchStartOlCard());
        });
        $grid->disableExport(false);
        $grid->actions(function($action) use ($grid){
            $action->disableEdit();
            $action->disableView();
            $action->disableDelete();

            if($this->row->state < 2){
                $action->add(new StartUse());
            }
        });
        $grid->model()->typeOff()->where('user_id',0)->latest();
        $grid->column('qrcod','二维码')->display(function(){
            return '<image style="width:50px;height:50px;" src="https://api.qrserver.com/v1/create-qr-code/?size=50%C3%9750&data='.$this->card_no.','.$this->card_key.'">';
        });
        $grid->column('card_no', '卡号');
        $grid->column('card_key', '卡密')->display(function(){
            if(Auth::user()->isAdministrator()){
                return $this->card_key;
            }
            return str_replace(substr($this->card_key,2,3),'*****',$this->card_key);
        });
        $grid->column('product.title', '卡片标题')->link(function(){
            return '/admin/ol-card-goods/'.$this->product_id.'/edit';
        },'');
        $grid->column('user.nickname', '所属用户')->link(function(){
            if(!$this->user_id) return '';
            return '/admin/users?id='.$this->user_id;
        },'');
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
        $grid->column('state', '卡片状态')->using(OlCard::$status);
        $grid->column('created_at', '创建时间');
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('card_no','卡号');

        });
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
        // $cardPro = new OlCardProduct;
        // $list = $cardPro->getList(21);
        $form->hidden('card_no')->default('');
        $form->hidden('card_key')->default('');
        $form->ignore(['category_id']);
        $form->select('category_id','选择分类')->options(Arr::pluck(Category::getOlOptions(),'title','id'))->load('product_id','/admin/api/cardgoods');
        $form->select('product_id', '卡片类型')->rules('required',['required'=>'请选择卡片类型']);
        $form->hidden('type')->default(2);
        $form->hidden('open_time')->default(0);
        $form->hidden('start_time')->default(0);
        $form->hidden('expire_time')->default(0);
        $form->hidden('brand_ids')->default('');
        $form->hidden('cinema_ids')->default('');
        $form->hidden('user_id')->default(0);
        $form->text('number', '可用次数');
        $form->radio('state','卡片状态')->options([0=>'未启用',2=>'启用']);
        $form->saving(function($form){
            $model = new OlCard;
            $form->card_no = $model->createNo();
            $form->card_key = $model->createKey();

            if($form->product_id){
                $product = OlCardProduct::where('id',$form->product_id)->first();
                $form->expire_time = strtotime($product->check_end);
                $form->start_time = strtotime($product->check_start);
                $form->brand_ids = $product->rules->brand_ids;
                $form->cinema_ids = $product->rules->cinema_ids;
            }
        });
        $form->saved(function($form){
            return redirect('/admin/ol-cards-2');
        });
        return $form;
    }
}
