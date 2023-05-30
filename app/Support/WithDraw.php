<?php
namespace App\Support;
use Yansongda\Pay\Pay;
class WithDraw
{
    /**
     * 支付宝提现转账
     * {
	* "code": "10000",
	* "msg": "Success",
	* "order_id": "20220117110070000006000001226526",
	* "out_biz_no": "1642411632",
	* "status": "SUCCESS"
    * }
     *$transAmount 付款金额，$identity账号，$name姓名，
     * @return void
     */
    function alipayDraw($transAmount,$identity,$name,$orderTitle = '商家提现'){
        $transAmount = round($transAmount,2);
        $result = array('status'=>'SUCCESS','order_id'=>'','msg'=>'付款成功');
        if(!$transAmount){
            $result['status'] = 'ERROR';
            $result['msg'] = '付款金额为0';
            return $result;
        }
        if(empty($identity) || empty($name)){
            $result['status'] = 'ERROR';
            $result['msg'] = '收款方支付宝账号和姓名必填';
            return $result;
        }
        try {
            $result = Pay::alipay(config('pay.alipay'))->transfer([
                'out_biz_no' => ''.time(),
                'trans_amount' => $transAmount,
                'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                'biz_scene'=>'DIRECT_TRANSFER',
                'order_title'=>$orderTitle,
                'payee_info'=>array(
                    'identity'=>$identity,
                    'identity_type'=>'ALIPAY_LOGON_ID',
                    'name'=>$name
                ),
                'remark'=>'',
                'business_params'=>['payer_show_name'=>'影旅汇']
            ]);
        } catch (\Throwable $th) {
            $result['status'] = 'ERROR';
            $result['msg'] = $th->raw['alipay_fund_trans_uni_transfer_response']['sub_msg'];
        }
        return $result;
    }

    /**
     * 微信提现
     */

    function wechatDraw($comId,$transAmount,$openid,$tradeNo,$isStore = false){
        $result = array('status'=>'SUCCESS','order_id'=>'','msg'=>'付款成功');
        $transAmount = intval($transAmount * 100);
        logger('微信提现：'.$transAmount);
        if(!$transAmount){
            $result['status'] = 'ERROR';
            $result['msg'] = '付款金额为0';
            return $result;
        }
        if(empty($openid)){
            $result['status'] = 'ERROR';
            $result['msg'] = '用户openid必须';
            return $result;
        }
        if(empty($tradeNo)){
            $result['status'] = 'ERROR';
            $result['msg'] = '提现订单号不能为空';
            return $result;
        }
        $config = $comId ? config('wechat.payment.default'):config('wechat.payment.default1');
        if($isStore){
            $config = config('wechat.payment.default2');
        }
        try {
            $app =  \EasyWeChat\Factory::payment($config);
            $payResult = $app->transfer->toBalance([
                'partner_trade_no' => $tradeNo, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                'openid' => $openid,
                'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                're_user_name' => '', // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
                'amount' => $transAmount, // 企业付款金额，单位为分
                'desc' => '用户提现', // 企业付款操作说明信息。必填
            ]);
            if($payResult['return_code'] == 'SUCCESS'){
                if($payResult['result_code'] == 'SUCCESS'){
                    $result['order_id'] = $tradeNo;
                    return $result;
                }else{
                    $result['status'] = 'ERROR';
                    $result['msg'] = $payResult['err_code_des'];
                    return $result;
                }
            }else{
                $result['status'] = 'ERROR';
                $result['msg'] = $payResult['return_msg'];
                return $result;
            }
        } catch (\Throwable $th) {
            logger("wechatDraw: ".$th->getMessage());
            $result['status'] = 'ERROR';
            $result['msg'] = $th->getMessage();
            return $result;
        }

        return $result;
    }
}
