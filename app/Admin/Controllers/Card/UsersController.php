<?php

namespace App\Admin\Controllers\Card;

use App\Models\TicketUser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UsersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影旅卡用户';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TicketUser());
        $grid->model()->where('com_id','>',0)->latest();
        $inviterId = (int)request('inviter_id','');
        if($inviterId){
            $grid->model()->where('inviter_id',$inviterId);
        }
        $grid->tools(function($tool) use ($inviterId){
            if($inviterId){
                $tool->append("<a class='btn btn-sm btn-primary' href='/admin/users'>查看所有用户</a>");
            }
        });

        $grid->filter(function($filter){
            $filter->like('nickname','昵称');
            $filter->equal('mobile','用户手机号');
        });
        $grid->column('id', __('ID'));
        $grid->column('avatar', __('头像'))->image('',45);
        $grid->column('nickname', __('昵称'));
        $grid->column('mobile', __('手机号'));
        $grid->column('group.title','会员等级');
        $grid->column('inviter1.nickname', __('推荐人'));
        $grid->column('fans','我的粉丝')->display(function(){
            $count = TicketUser::where('inviter_id',$this->id)->count();
            return '粉丝数('.$count.')';
        })->link(function(){
            return '/admin/users?inviter_id='.$this->id;
        },'');
        // $grid->column('is_retail', __('是否分销'));
        $grid->column('cash_money', '累计消费金额')->link(function(){
            return '/admin/orders?user_id='.$this->id;
        },'');;
        $grid->column('total_balance', __('累计佣金'));
        $grid->column('balance', __('可提现佣金'));
        $grid->column('created_at', __('注册时间'));


        $grid->actions(function($actions){
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->disableCreateButton();
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
        $show = new Show(TicketUser::findOrFail($id));

        $show->field('avatar', __(' '))->image('',120);
        // $show->field('id', __('Id'));
        $show->field('nickname', __('微信昵称'));
        $show->field('sex', __('性别'))->using([0=>'女',1=>'男']);
        // $show->field('province', __('Province'));
        // $show->field('city', __('City'));
        // $show->field('country', __('Country'));
        $show->field('openid', __('Openid'));
        // $show->field('unionid', __('Unionid'));
        $show->field('mobile', __('手机号码'));
        // $show->field('inviter_id', __('Inviter id'));
        // $show->field('is_retail', __('Is retail'));
        $show->field('total_balance', __('累计佣金'));
        $show->field('balance', __('可提现佣金'));
        $show->field('created_at', __('注册时间'));
        // $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TicketUser());

        $form->image('avatar', __('Avatar'));
        $form->text('nickname', __('Nickname'));
        $form->switch('sex', __('Sex'));
        $form->text('province', __('Province'));
        $form->text('city', __('City'));
        $form->text('country', __('Country'));
        $form->text('openid', __('Openid'));
        $form->text('unionid', __('Unionid'));
        $form->mobile('mobile', __('Mobile'));
        $form->number('inviter_id', __('Inviter id'));
        $form->switch('is_retail', __('Is retail'));
        $form->number('total_balance', __('Total balance'));
        $form->number('balance', __('Balance'));

        return $form;
    }
}
