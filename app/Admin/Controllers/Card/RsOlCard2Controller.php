<?php

namespace App\Admin\Controllers\Card;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\CardModels\Cards;
use \App\CardModels\OlCard;
use Illuminate\Support\Arr;
use App\CardModels\RsOlCard;
use App\MallModels\Category;
use App\CardModels\CardPrice;
use App\Admin\Actions\StartUse;
use App\Admin\Actions\BatchCard;
use App\CardModels\OlCardProduct;
use Illuminate\Support\Facades\Auth;
use App\Admin\Actions\BatchStartOlCard;
use App\Admin\Selectable\SelectCardStore;
use Encore\Admin\Controllers\AdminController;

class RsOlCard2Controller extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影旅卡卡密管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RsOlCard());
        $batchId = (int)request('batch_id',0);
        $grid->model()->when($batchId,function($query,$batchId){
            return $query->where('batch_id',$batchId);
        })->latest();
        $status = (int)request('status',0);
        if($status == 4){
            $grid->model()->where('state','<=',2);
        }elseif($status == 3){
            $grid->model()->where('state',10);
        }
        $grid->tools(function($tool) use ($batchId,$status){            
            
            $tool->append("<a href='/admin/rs-card-batches/create' class='btn btn-sm btn-primary'>批量生成影旅卡卡</a>");
            $tool->append("<a href='/admin/rs-card-batches' class='btn btn-sm btn-default'>批次管理</a>");
            $tool->append("<a href='/admin/rs-cards-list' class='btn btn-sm btn-".($status == 0?'warning':'default')."'>全部卡片</a>");
            $tool->append("<a href='/admin/rs-cards-list?status=4' class='btn btn-sm btn-".($status == 4?'warning':'default')."'>未激活</a>");
            $tool->append("<a href='/admin/rs-cards-list?status=3' class='btn btn-sm btn-".($status == 3?'warning':'default')."'>已激活</a>");
            
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
        $grid->model()->latest();
        $grid->column('card_no', '卡号');
        $grid->column('card_key', '卡密')->display(function(){
            if(Auth::user()->isAdministrator()){
                return $this->card_key;
            }
            return str_replace(substr($this->card_key,2,3),'*****',$this->card_key);
        });
        $grid->column('title', '卡片标题');     
        $grid->column('store.store_name','分销商');
        $grid->column('user.nickname', '所属用户')->link(function(){
            if(!$this->user_id) return '';
            return '/admin/users?id='.$this->user_id;
        },'');
        $grid->column('open_time', '激活时间')->display(function($time){
            if($time == 0) return '-';
            return date('Y-m-d H:i:s',$time);
        });
        // $grid->column('expire_time', '过期时间')->display(function($time){
        //     if($time == 0) return '-';
        //     return date('Y-m-d H:i:s',$time);
        // });
        // $grid->column('number', '已使用/总次数')->display(function(){
        //     return $this->use_number .' / '. $this->number;
        // });
        $grid->column('state', '卡片状态')->using(RsOlCard::$status);
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
        $form = new Form(new RsOlCard());
        // $cardPro = new OlCardProduct;
        // $list = $cardPro->getList(21);
        $form->hidden('card_no')->default('');
        $form->hidden('card_key')->default('');
        $cardList = Cards::getList()->pluck('title','id');
        $form->belongsTo('store_id', SelectCardStore::class, '选择分销商');
        $form->select('rs_card_id','卡类型')->options($cardList)->rules('required',['required'=>'请选择卡片类型']);
        $form->hidden('type')->default(2);
        $form->hidden('open_time')->default(0);
        $form->hidden('start_time')->default(0);
        $form->hidden('card_money')->default(0);
        $form->hidden('title')->default('');
        $form->hidden('expire_time')->default(0);
        $form->hidden('brand_ids')->value('');
        $form->hidden('cinema_ids')->value('');
        $form->hidden('card_info')->value('');
        $form->hidden('user_id')->default(0);
        $form->hidden('number', '可用次数')->value(1);
        $form->radio('state','卡片状态')->options([0=>'未启用',2=>'启用']);
        $form->saving(function($form){
            $model = new RsOlCard;
            $form->card_no = $model->createNo();
            $form->card_key = $model->createKey();
            if($form->rs_card_id){
                $cardId = $form->rs_card_id;
                $card = Cards::where('id',$cardId)->first();                            
                $cardPrice = $card->price;
                CardPrice::getCardPriceById($form->store_id,$card->id,$cardPrice);
                $cardInfo = array(
                    'id'=>$card->id,
                    'title'=>$card->title,
                    'image'=>$card->list_image,
                    'number'=>1,
                    'price'=>$cardPrice,
                    'card_money'=>$card->card_money
                );
                $form->brand_ids = '';
                $form->cinema_ids = '';
                $form->title = $cardInfo['title'];
                $form->card_money = $cardInfo['card_money'];
                $form->card_info = json_encode($cardInfo,256);
            }
        });
        $form->saved(function($form){
            return redirect('/admin/rs-cards-list');
        });
        return $form;
    }
}
