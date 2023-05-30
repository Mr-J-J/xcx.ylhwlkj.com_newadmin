<?php

namespace App\Models;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TicketUser extends Authenticatable
{
    //
    protected $primaryKey = 'id';
    protected $hidden = ['remember_token'];

    public function setRememberTokenAttribute($value){
        $this->attributes['remember_token'] = Helpers::generateToken($value);
    }
    
    /**
     * 更新用户累计消费金额
     *
     * @param [type] $money
     * @return void
     */
    public function calcCashMoney(float $money){
        if(!$money) return;
        $this->cash_money = $this->cash_money + $money;
        $this->save();        
    }


    public function inviter1(){
        return $this->hasOne('App\Models\TicketUser','id','inviter_id');
    }
    
    // public function getAuthIdentifier()
    // {
    //     return $this->id;
    // }
    public function group(){
        return $this->hasOne('App\MallModels\Group','id','group_id');
    }

    public function getBalanceAttribute($value){
        return $value / 100;
    }

    public function setBalanceAttribute($value){
        $this->attributes['balance'] = $value * 100;
    }

    public function getTotalBalanceAttribute($value){
        return round($value / 100,2);
    }

    public function setTotalBalanceAttribute($value){
        $this->attributes['total_balance'] = $value * 100;
    }


    

}
