<?php

namespace App\Http\Controllers\H5;


use App\Models\OfferRules;
use Illuminate\Http\Request;
use App\Models\store\WithDraw;

class WithdrawController extends StoreBaseController
{
    public function __construct()
    {
        parent::__construct();

        if($this->store && $this->store->store_state == 1){
            response($this->error('注册信息审核中'))->send();die;
        }
    }
    /**
     * 提现记录
     *
     * @return void
     */
    public function lists(){
       
        $lists = WithDraw::getDrawList($this->store->id);
        $total_money = WithDraw::where('store_id',$this->store->id)->where('state',1)->sum('money');
        return $this->success('成功',compact('lists','total_money'));
    }

    /**
     * 申请提现
     *
     * @param Request $request
     * @return void
     */
    public function applyWithdraw(Request $request){
        $money = $request->input('money',0);
        $type = (int)$request->input('type',1); //1微信  2支付宝
        $storeInfo = $this->store->storeInfo;
        $money = round($money,2);
        
        $hasDrawNumber = WithDraw::where('store_id',$this->store->id)->where('state',1)->whereDate('created_at',date('Y-m-d'))->count();
        if($hasDrawNumber >= 1){
            return $this->error('您今天已经提现过了，明天再来!');
        }

        if($money < 1){
            return $this->error('提现金额须大于1元');
        }
        
        if($type == 2){
            if(empty($storeInfo->alipay_account) || empty($storeInfo->alipay_name)){
                return $this->error('申请失败：请选择设置提现账号');
            }
        }else{
            $drawTotal = WithDraw::where('store_id',$this->store->id)->whereDate('created_at',date('Y-m-d'))->sum('money');
            //微信提现单用户最高200
            if($drawTotal > 200 || $money > 200){
                return $this->error('微信单日提现金额不能超过200元');
            }
        }
        
        if(!$storeInfo->balance){
            return $this->error('申请失败：可提现金额不足');
        }
        $drawMoney = $storeInfo->balance;
        if($money > 0){
            if($storeInfo->balance < $money){
                return $this->error('申请失败：可提现金额不足');
            }
            $drawMoney = $money;
        }
        if(!$drawMoney){
            return $this->error('请输入提现金额');
        }
        $startAllowDraw = 1;
        if($storeInfo->balance < $startAllowDraw){
            return $this->error("余额大于{$startAllowDraw}才可以提现");
        }
        

        WithDraw::addDraw($storeInfo,$type,$drawMoney);
        return $this->success('提现申请已提交');
    }


    
}
