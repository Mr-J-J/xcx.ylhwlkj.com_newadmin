<?php

namespace App\Admin\Controllers;

use App\UUModels\UUTicketOrder;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PwOrdersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '票付通订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UUTicketOrder());

        $grid->column('id', __('Id'));
        $grid->column('order_no', __('Order no'));
        $grid->column('user_id', __('User id'));
        $grid->column('UUmember', __('UUmember'));
        $grid->column('UUordernum', __('UUordernum'));
        $grid->column('UUlid', __('UUlid'));
        $grid->column('UUtid', __('UUtid'));
        $grid->column('UUpid', __('UUpid'));
        $grid->column('UUbegintime', __('UUbegintime'));
        // $grid->column('UUendtime', __('UUendtime'));
        // $grid->column('UUtnum', __('UUtnum'));
        // $grid->column('UUtprice', __('UUtprice'));
        // $grid->column('UUordername', __('UUordername'));
        // $grid->column('UUordertel', __('UUordertel'));
        // $grid->column('UUpersonid', __('UUpersonid'));
        // $grid->column('UUstatus', __('UUstatus'));
        // $grid->column('UUsalerid', __('UUsalerid'));
        // $grid->column('UUdtime', __('UUdtime'));
        // $grid->column('UUtotalmoney', __('UUtotalmoney'));
        // $grid->column('UUpaymode', __('UUpaymode'));
        // $grid->column('UUordermode', __('UUordermode'));
        // $grid->column('UUctime', __('UUctime'));
        // $grid->column('UUcode', __('UUcode'));
        // $grid->column('UUcontacttel', __('UUcontacttel'));
        // $grid->column('UUaid', __('UUaid'));
        // $grid->column('UUifpack', __('UUifpack'));
        // $grid->column('UUpack_order', __('UUpack order'));
        // $grid->column('UUsmserror', __('UUsmserror'));
        // $grid->column('UUrefund_num', __('UUrefund num'));
        // $grid->column('UUverified_num', __('UUverified num'));
        // $grid->column('UUorigin_num', __('UUorigin num'));
        // $grid->column('UUlprice', __('UUlprice'));
        // $grid->column('UUplaytime', __('UUplaytime'));
        // $grid->column('UUpay_status', __('UUpay status'));
        // $grid->column('UUconcat_id', __('UUconcat id'));
        // $grid->column('UUseries', __('UUseries'));
        // $grid->column('UUmemo', __('UUmemo'));
        // $grid->column('UUltitle', __('UUltitle'));
        // $grid->column('UUp_type', __('UUp type'));
        // $grid->column('UUttitle', __('UUttitle'));
        // $grid->column('UUMprice', __('UUMprice'));
        // $grid->column('UUdname', __('UUdname'));
        // $grid->column('UUtnum_cancel', __('UUtnum cancel'));
        // $grid->column('UUtnum_used', __('UUtnum used'));
        // $grid->column('UUifprint', __('UUifprint'));
        // $grid->column('UUgetaddr', __('UUgetaddr'));
        // $grid->column('buy_price', __('Buy price'));
        // $grid->column('ticket_id', __('Ticket id'));
        // $grid->column('order_amount', __('Order amount'));
        // $grid->column('transaction_id', __('Transaction id'));
        // $grid->column('order_status', __('Order status'));
        // $grid->column('refund_no', __('Refund no'));
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
        $show = new Show(UUTicketOrder::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_no', __('订单号'));
        $show->field('transaction_id', __('微信支付交易号'));
        $show->field('order_status', __('本地订单状态'))->as(function($status){
            return $status.'-'.$this->getStatusTxt();
        });
        $show->field('UUtotalmoney', __('订单总金额'))->as(function($money){
            return '￥'.round($money/100,2);
        });
        $show->field('created_at', __('创建时间'));
        $show->field('refund_remark', __('退款备注'));
        // $show->field('user_id', __('User id'));
        // $show->field('UUmember', __('UUmember'));
        $show->divider('票付通订单信息');
        $show->field('UUordernum', __('票付通订单号'));
        $show->field('UUlid', __('门票ID'))->as(function(){
            return "供应商ID：{$this->UUaid} | 景区ID：{$this->UUlid} | 门票ID：{$this->UUtid}";
        });
        $show->field('UUpid', __('价格id'));
         $show->field('UUltitle', __('产品名称'));
        $show->field('UUp_type', __('产品类型'))->using(\App\UUModels\UUScenicSpotTicket::$type);
        $show->field('UUttitle', __('门票名称'));
        $show->field('UUMprice', __('门市价'));
        $show->field('UUdname', __('分销商名称'));
        
        $show->field('UUbegintime', __('订单有效开始日期'));
        $show->field('UUendtime', __('订单有效结束日期'));
        $show->field('UUtnum', __('订单数量'));
        $show->field('UUtprice', __('订单结算价'));
        $show->field('UUordername', __('游客姓名'));
        $show->field('UUordertel', __('游客手机号'));
        $show->field('UUpersonid', __('游客身份证'));
        $show->field('UUstatus', __('票付通订单状态 '))->as(function($status){
            $arr = [0=>'未使用',1=>'已使用',2=>'已过期',3=>'被取消',4=>'待确认(酒店)，待收货(特产)',5=>'被终端修改',6=>'被终端撤销',7=>'部分使用',8=>'订单完结',9=>'被删除'];
            return $arr[$status];
        });
        // $show->field('UUsalerid', __('资源id'));
        $show->field('UUdtime', __('订单验证时间'));
        
        $show->field('UUpaymode', __('支付方式'));
        $show->field('UUordermode', __('下单方式'));
        $show->field('UUctime', __('订单取消时间'));
        $show->field('UUcode', __('凭证码/消费码'));
        $show->field('UUcontacttel', __('联系人手机号'));
        $show->field('UUifpack', __('是否套票'))->using(['不是','是']);
        $show->field('UUpack_order', __('套票订单号'));
        $show->field('UUsmserror', __('短信是否发送成功'))->using(['成功','失败']);
        $show->field('UUorigin_num', __('订单原始数量'));
        $show->field('UUrefund_num', __('已退数量'));
        $show->field('UUverified_num', __('已验证数量'));
        $show->field('UUlprice', __('零售单价'))->as(function($price){
            return  '￥'.round($price/100,2);
        });
        $show->field('UUplaytime', __('游玩日期'));
        $show->field('UUpay_status', __('支付状态'))->using(['0-景区到付','1-已支付','2-未支付']);
        $show->field('UUconcat_id', __('联票关联订单号'));
        $show->field('UUseries', __('团号或者演出座位信息'));
        $show->field('UUmemo', __('订单备注'));
       
        $show->field('UUtnum_cancel', __('已取消数量'));
        $show->field('UUtnum_used', __('已验证数量'));
        $show->field('UUifprint', __('是否打印取票'))->using(['0-未打印','1-已打印']);
        $show->field('UUgetaddr', __('取票信息'));
        $show->field('buy_price', __('结算价'));
        // $show->field('ticket_id', __('local门票id'));
        // $show->field('order_amount', __('Order amount'));
        
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
        $form = new Form(new UUTicketOrder());

        $form->text('order_no', __('Order no'));
        $form->number('user_id', __('User id'));
        $form->number('UUmember', __('UUmember'));
        $form->text('UUordernum', __('UUordernum'));
        $form->number('UUlid', __('UUlid'));
        $form->number('UUtid', __('UUtid'));
        $form->number('UUpid', __('UUpid'));
        $form->datetime('UUbegintime', __('UUbegintime'))->default(date('Y-m-d H:i:s'));
        $form->datetime('UUendtime', __('UUendtime'))->default(date('Y-m-d H:i:s'));
        $form->number('UUtnum', __('UUtnum'));
        $form->number('UUtprice', __('UUtprice'));
        $form->text('UUordername', __('UUordername'));
        $form->text('UUordertel', __('UUordertel'));
        $form->text('UUpersonid', __('UUpersonid'));
        $form->number('UUstatus', __('UUstatus'));
        $form->text('UUsalerid', __('UUsalerid'));
        $form->datetime('UUdtime', __('UUdtime'))->default(date('Y-m-d H:i:s'));
        $form->number('UUtotalmoney', __('UUtotalmoney'));
        $form->number('UUpaymode', __('UUpaymode'));
        $form->number('UUordermode', __('UUordermode'));
        $form->datetime('UUctime', __('UUctime'))->default(date('Y-m-d H:i:s'));
        $form->text('UUcode', __('UUcode'));
        $form->text('UUcontacttel', __('UUcontacttel'));
        $form->number('UUaid', __('UUaid'));
        $form->switch('UUifpack', __('UUifpack'));
        $form->text('UUpack_order', __('UUpack order'));
        $form->switch('UUsmserror', __('UUsmserror'));
        $form->number('UUrefund_num', __('UUrefund num'));
        $form->number('UUverified_num', __('UUverified num'));
        $form->number('UUorigin_num', __('UUorigin num'));
        $form->number('UUlprice', __('UUlprice'));
        $form->text('UUplaytime', __('UUplaytime'));
        $form->switch('UUpay_status', __('UUpay status'));
        $form->text('UUconcat_id', __('UUconcat id'));
        $form->text('UUseries', __('UUseries'));
        $form->text('UUmemo', __('UUmemo'));
        $form->text('UUltitle', __('UUltitle'));
        $form->text('UUp_type', __('UUp type'));
        $form->text('UUttitle', __('UUttitle'));
        $form->decimal('UUMprice', __('UUMprice'))->default(0.00);
        $form->text('UUdname', __('UUdname'));
        $form->number('UUtnum_cancel', __('UUtnum cancel'));
        $form->number('UUtnum_used', __('UUtnum used'));
        $form->switch('UUifprint', __('UUifprint'));
        $form->text('UUgetaddr', __('UUgetaddr'));
        $form->number('buy_price', __('Buy price'));
        $form->number('ticket_id', __('Ticket id'));
        $form->number('order_amount', __('Order amount'));
        $form->text('transaction_id', __('Transaction id'));
        $form->switch('order_status', __('Order status'));
        $form->text('refund_remark', __('Refund no'));

        return $form;
    }
}
