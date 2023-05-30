<?php

namespace App\Admin\Controllers\Card;

use App\CardModels\UserWallet;
use App\Models\TicketUser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UsersCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户影旅卡';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserWallet());
        $grid->model()->latest();
        
        
        $grid->filter(function($filter){
            $filter->like('user.nickname','昵称');
            $filter->equal('user.mobile','用户手机号');
        });
        $grid->column('id', __('ID'));
        $grid->column('nickname', __('昵称'));
        $grid->column('mobile', __('手机号'));
        $grid->column('card.short_title', '影旅卡类型');
        $grid->column('balance','影旅卡余额');
        
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
