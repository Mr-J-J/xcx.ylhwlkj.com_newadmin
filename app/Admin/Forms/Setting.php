<?php

namespace App\Admin\Forms;

use App\ApiModels\Wangpiao\CinemasBrand;
use Illuminate\Http\Request;
use Encore\Admin\Widgets\Form;
use Illuminate\Support\MessageBag;
use App\Models\Setting as SettingModel;

class Setting extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = ' ';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $post = $request->all();
        if(($post['retail_setting_level1'] + $post['retail_setting_level2']) > 100){
            $error = new MessageBag([
                'title'   => '一级、二级佣金比例合计不能超过100%',
                // 'message' => '配置已更新',
            ]);

            return back()->with(compact('error'));
        }

        // $post['offer_rules_out_ticket_rate'] = \json_encode($post['offer_rules_out_ticket_rate']);

        $post['retail_setting'] = array(
            'total_rate'=>$post['retail_setting_total'],
            'level1_rate'=>$post['retail_setting_level1'],
            'level2_rate'=>$post['retail_setting_level2']
        );
        $post['redirect_out_ticket'] = intval($post['redirect_out_ticket'] == 'on');
        if(!$post['redirect_out_ticket']){
            CinemasBrand::where('redirect_out_ticket',1)->update(['redirect_out_ticket'=>0]);
        }
        $post['jiekoufang'] = intval($post['jiekoufang'] == 'on');
        $post['kusu_show'] = intval($post['kusu_show'] == 'on');
        $post['kusu_offer_show'] = intval($post['kusu_offer_show'] == 'on');
        $post['draw_audit'] = intval($post['draw_audit'] == 'on');
        $post['draw_audit_money'] = max(1,(int)$post['draw_audit_money']);
        unset($post['retail_setting_total'],$post['retail_setting_level1'],$post['retail_setting_level2']);
        SettingModel::updateSetting($post);

        extract($post);

        $success = new MessageBag([
            'title'   => '配置已更新',
            // 'message' => '配置已更新',
        ]);

        return back()->with(compact('success'));
        // return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $setting = SettingModel::getSettings(true);
        $this->divider('票商规则设置');
        $this->display('offer_defualt_store',$setting['offer_defualt_store']['desc'])->with(function($value){
            return "<a href='/admin/stores/".$value['store_id']."/edit'>".$value['store_name']."</a>";
        })->help('可在商家管理中设置服务商');
        // $this->rate('offer_rules_out_ticket_rate',)

// <div class=\"input-group\" style=\"margin-top:15px\">
//         <span class=\"input-group-addon\">退票率高于</span>
//     <input type=\"text\" class=\"form-control\" name=\"offer_rules_refund_ticket\" value='".$setting['offer_rules_refund_ticket']['content']."' placeholder=\"最高出票率\" required style=\"\">
//     <span class=\"input-group-addon\">%的商户不再分派订单</span>
// </div>
        // <input type=\"text\" class=\"form-control\" name=\"offer_rules_out_ticket_rate[min]\" placeholder=\"最低金额\" required style=\"\">
        // <span class=\"input-group-addon\" style=\"border:none; border-radius:0;\">-</span>
        $this->html("<div class=\"input-group\">
        <span class=\"input-group-addon\">价格相差少于</span>

    <input type=\"text\" class=\"form-control\" name=\"offer_rules_out_ticket_rate\" value='".$setting['offer_rules_out_ticket_rate']['content']."' placeholder=\"最高金额\" required>
    <span class=\"input-group-addon\">元时，出票率高的商户优先派单;</span>
</div>

",'派单规则')->help($setting['offer_rules_out_ticket_rate']['remark'])->setWidth(5);;
        $this->divider('订单竞价设置');
        $this->text('offer_out_ttl',$setting['offer_out_ttl']['desc'])->append('分钟')->setWidth(2)->rules('required');
        $this->text('offer_price',$setting['offer_price']['desc'])->rules('required')->append('%')->setWidth(2)->help($setting['offer_price']['remark']);
        $this->text('offer_price_min',$setting['offer_price_min']['desc'])->rules('required')->append('%')->setWidth(2)->help($setting['offer_price_min']['remark']);
        $this->text('offer_times',$setting['offer_times']['desc'])->rules('required')->append('次')->setWidth(2);
        $this->text('offer_ttl',$setting['offer_ttl']['desc'])->rules('required')->append('分钟')->setWidth(4);;
        $this->divider('交易订单设置');
        $this->text('order_cancel_ttl',__('订单出票超时取消(分钟)'))->rules('required')->append('分钟')->setWidth(4);;
        $this->text('order_pay_ttl',__('订单支付超时取消(分钟)'))->rules('required')->append('分钟')->setWidth(4)->help('购票订单、卡券订单未支付时生效');
        $this->text('stop_order',__('放映开始前停止售票(分钟)'))->rules('required')->append('分钟')->setWidth(4);;
        $this->divider('票价折扣设置');
        $this->text('price_discount_rate',__('品牌院线快速购票折扣(市场价)'))->rules('required|max:10')->help('1~10的数字，基于三方接口的市场价折扣')->append('折')->setWidth(3);
        $this->text('tehui_price_rate',__('品牌院线特惠购票折扣'))->rules('required|max:10')->help('1~10的数字，基于三方接口的市场价折扣')->append('折')->setWidth(3);
        $this->text('card_discount',__('影旅卡抵扣比例'))->rules('required|max:10')->help('统一设置影旅卡购票的优惠折扣，0~100的数字，基于三方接口的折扣')->append('%')->setWidth(3);
        $states = [
            'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'default'],
        ];
        $this->switch('kusu_show',__('是否显示快速购票'))->states($states);
        $states2 = [
            'on'  => ['value' => 1, 'text' => '参与', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '不参与', 'color' => 'default'],
        ];
        $this->switch('kusu_offer_show',__('快速购票参与竞价'))->states($states2);
        $states4 = [
            'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
        ];
        $this->switch('redirect_out_ticket','网票网直接出票')->states($states4)->help('选择关闭后则所有购票订单不再通过网票网出票，同时【院线配置】的开关也会关闭');
        //
        $this->divider('分佣设置');//->style('padding-top:5px;text-align:left;padding-left:20px;');
        $states3 = [
            'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
        ];
        $this->switch('draw_audit',__('提现审核'))->states($states3);
        $this->text('draw_audit_money','自动提现最高金额')->help('审核关闭时生效。提现超过指定金额则开启审核，保障资金安全。系统默认1元')->append('元')->setWidth(4);
        $states4 = [
            'on'  => ['value' => 1, 'text' => '嘿影', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '聚福宝', 'color' => 'default'],
        ];
        $this->switch('jiekoufang',__('接口方'))->states($states4);
        $this->rate('rs_order_commision',__('影旅卡分销商购票订单分成比例(%)'))->rules('max:100')->setWidth(3)->help('例,支付金额100元 x 分成比例15% = 分销商分成15元');

        $this->rate('retail_setting_total',__('用户分销佣金总比例(%)'))->rules('required|max:100')->setWidth(3)->help('例,支付金额100元 x 佣金总比例10% = 用户分销总佣金10元');;
        $this->rate('retail_setting_level1',__('一级用户佣金比例(%)'))->rules('required|max:100')->setWidth(3)->help('例,总佣金10元 x 一级佣金比例60% = 一级佣金6元');;;
        $this->rate('retail_setting_level2',__('二级用户佣金比例(%)'))->rules('required|max:100')->setWidth(3)->help('例,总佣金10元 x 二级佣金比例40% = 二级佣金4元');;;

    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        $setting = (array) SettingModel::getSettings(true);
        $contents = array_column($setting,'content');
        $settings = array_combine(array_keys($setting),$contents);
        if(!empty($settings['retail_setting'])){
            $settings['retail_setting_total'] = $settings['retail_setting']['total_rate'];
            $settings['retail_setting_level1'] = $settings['retail_setting']['level1_rate'];
            $settings['retail_setting_level2'] = $settings['retail_setting']['level2_rate'];
        }
        return $settings;
    }
}
