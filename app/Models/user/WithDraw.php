<?php

namespace App\Models\user;

use App\Admin\Actions\User\UserWithdrawOK;
use App\Support\Helpers;
use App\Models\TicketUser;
use Illuminate\Database\Eloquent\Model;

class WithDraw extends Model
{
    protected $table = 'retail_withdraw';

    /**
     * 提现订单写入
     *
     * @param StoreInfo $storeInfo
     * @param integer $money
     * @return void
     */
    static function addDraw(TicketUser $user,$money = 0){
        
        $draw = new WithDraw;
        $draw->user_id = $user->id;
        // $draw->title = '提现';
        $draw->com_id = $user->com_id;
        $draw->money = $money;
        $draw->before_money = $user->balance;
        $balance = $user->balance - $money;
        $draw->after_money = $balance>0?$balance:0;
        $draw->draw_account = $user->openid;
        $draw->trade_name = '微信支付';
        $draw->trade_no = 'U'.date('YmdHis').intval(microtime(true) * 1000);
        $draw->state = 0;
        $draw->save();
        $user->balance = $balance>0?$balance:0;
        $user->save();
        
        //自动提现
        $draw_audit = (int)Helpers::getSetting('draw_audit');
        $draw_audit_money = (int)Helpers::getSetting('draw_audit_money');
        
        if(!$draw_audit && $money <= $draw_audit_money) {
            $actions = new UserWithdrawOK;
            $actions->handle($draw,request());
        }
        
    }

    public function user(){
        return $this->hasOne('App\Models\TicketUser','id','user_id');
    }


    public function getMoneyAttribute($value){
        return $value / 100;
    }
    public function setMoneyAttribute($value){
        $this->attributes['money'] = $value * 100;
    }

    public function getAfterMoneyAttribute($value){
        return $value / 100;
    }

    public function setAfterMoneyAttribute($value){
        $this->attributes['after_money'] = $value * 100;
    }

    public function setBeforeMoneyAttribute($value){
        $this->attributes['before_money'] = $value * 100;
    }

    public function getBeforeMoneyAttribute($value){
        return $value / 100;
    }
}
