<?php

namespace App\Admin\Controllers\Mall;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\MallModels\Group;
use App\Models\TicketUser;
use App\CardModels\OlCardOrder;
use Encore\Admin\Layout\Content;
use App\CardModels\OlCardExChange;
use Encore\Admin\Controllers\AdminController;

class OlCardExchangeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影城卡使用记录';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OlCardExChange());



        $grid->model()->latest();
        
        $userId = (int)request('user_id','');
        if($userId){
            $grid->model()->where('user_id',$userId);
        }
        $grid->disableCreateButton();
        $grid->tools(function($tool){
            $tool->append("<a class='btn btn-sm btn-primary' href='/admin/ol-exchange'>全部记录</a>");
        });
        $grid->filter(function($filter){
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('order_no','订单号');
            $filter->like('card.card_no','卡号');
            $filter->between('ex_time', '使用日期')->date();
        });
        $grid->disableActions();
        $grid->column('order_no', '订单号')->link(function(){
            return '/admin/user-orders?order_no='.$this->order_no;
        },'');
        $grid->column('user_id','用户信息')->display(function($userId){
            $user = TicketUser::select(['nickname','mobile'])->where('id',$userId)->first();
            if(empty($user)) return $userId;
            return $user->nickname."({$user->mobile})";
        });
        $grid->column('card.card_no', '影城卡号')->link(function(){
            $cardNo = $this->card?$this->card->card_no:'';
            if(!empty($cardNo)){
                return '';
            }
            return '/admin/ol-cards-1?card_no='.$cardNo;
        },'');
        $grid->column('ex_number', '兑换数量');
        $grid->column('ex_time', '兑换时间');
        return $grid;
    }
    
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($model)
    {
        // $show = new Show(Order::findOrFail($id));  
        $show = new Show(OlCardExChange::findOrFail($id));

        

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new OlCardExChange());

        
        return $form;
    }
}
