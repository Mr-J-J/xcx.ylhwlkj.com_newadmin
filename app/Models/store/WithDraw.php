<?php

namespace App\Models\store;

use App\Admin\Actions\Store\StoreWithdrawOK;
use App\Models\Store;
use App\Models\StoreInfo;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class WithDraw extends Model
{
    protected $table ="store_withdraw";

    public static function getDrawList($store_id,$pageNum = 10){
        $list = self::select('id','store_id','title','money','created_at','state')
                ->where('store_id',$store_id)
                ->orderBy('created_at','desc')
                ->paginate($pageNum);
        return $list;
    }

    /**
     * 提现订单写入
     *
     * @param StoreInfo $storeInfo
     * @param integer $money  
     * type 1微信  2支付宝
     * @return void
     */
    static function addDraw(StoreInfo $storeInfo,$type,$money = 0){
        
       
        $store = Store::where('id',$storeInfo->store_id)->first();
        $draw = new WithDraw;
        $draw->store_id = $storeInfo->store_id;
        $draw->title = ($type == 1)?'微信提现':'支付宝提现';
        $draw->money = $money;
        $draw->before_money = $storeInfo->balance;
        $balance =  $storeInfo->balance - $money;
        $draw->after_money = $balance>0?$balance:0;
        $draw->draw_account = ($type == 1)?$store->openid:$storeInfo->alipay_account;
        $draw->account_name = ($type == 2)?$storeInfo->alipay_name:'';
        $draw->trade_name = ($type == 1)?'wechat':'alipay';
        $draw->trade_no = 'S'.date('YmdHis').intval(microtime(true) * 1000);
        $draw->state = 0;
        $draw->save();
        
        $storeInfo->balance = $balance>0?$balance:0;
        $storeInfo->settle_money =$storeInfo->settle_money + $money;
        $storeInfo->save();
        
        
        
        //自动提现
        $draw_audit = (int)Helpers::getSetting('draw_audit');
        $draw_audit_money = (int)Helpers::getSetting('draw_audit_money');
        
        if(!$draw_audit && $money <= $draw_audit_money) {
            $actions = new StoreWithdrawOK;
            $actions->handle($draw,request());
        }
         
    }

    // public function getMoneyAttribute($value){
    //     return $value / 100;
    // }
    // public function setMoneyAttribute($value){
    //     $this->attributes['money'] = $value * 100;
    // }

    // public function getAfterMoneyAttribute($value){
    //     return $value / 100;
    // }

    // public function setAfterMoneyAttribute($value){
    //     $this->attributes['after_money'] = $value * 100;
    // }

    // public function setBeforeMoneyAttribute($value){
    //     $this->attributes['before_money'] = $value * 100;
    // }

    // public function getBeforeMoneyAttribute($value){
    //     return $value / 100;
    // }

    public function store(){
        return $this->hasOne('App\Models\Store','id','store_id');
    }


}
