<?php

namespace App\CardModels;

use App\ApiModels\Wangpiao\CinemasBrand;
use App\Support\Helpers;

use App\Models\UserOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * 分销商
 */
class RsStores extends Authenticatable
{
    use SoftDeletes;
    protected $table = 'rs_stores';

    protected $hidden = ['updated_at'];
    

    /**
     * 影旅卡订单分账
     *
     * @param CardOrder $order
     * @return void
     */
    static function settleOrder(CardOrder $order){
        $cardInfo = Cards::where('id',$order->card_id)->first();        
        $commision = round($order->order_amount - $cardInfo->price,2);
        
        if($commision <= 0) {
            return false;
        }
        $isSettle = (int)StoreBalanceDetail::where('order_sn',$order->order_sn)->count();
        if($isSettle){
            return false; //已经分账
        }
        $store = RsStores::where('id',$order->com_id)->first();
        DB::beginTransaction();
        try {
            $store->balance = $store->balance + $commision;
            $store->total_money = $store->total_money + $commision;
            $store->save();
            StoreBalanceDetail::addDetail($order,$commision,$store->balance);
        } catch (\Throwable $th) {
            DB::rollBack();
        }
        DB::commit();
    }

    /**
     * 购票订单分成
     *
     * @param UserOrder $order
     * @return void
     */
    static function settleTicketOrder(UserOrder $order){                
        $amount = $order->getOrderAmount();
        
        if($order->com_id == 0 || $amount  == 0 || $order->use_card != 1 || $order->order_status != 30){            
            return false;
        }
        $isSettle = (int)StoreBalanceDetail::where('order_sn',$order->order_no)->count();
        
        if($isSettle){
            return false; //已经分账
        }
        //院线分成比例
        $rate = CinemasBrand::where('id',$order->brand_id)->value('rs_order_commision');
        $rate = round($rate / 100,2);
        if(!$rate){
            $setting = round((float)Helpers::getSetting('rs_order_commision'),2);
            $rate = round($setting / 100,2);
            if(!$rate){
                return false;
            }
        }
        $commision = round($amount * $rate ,2);
        $store = RsStores::where('id',$order->com_id)->first();
        DB::beginTransaction();
        try {
            // $store->balance = $store->balance + $commision;
            // $store->total_money = $store->total_money + $commision;
            // $store->save();
            $balance = $store->balance + $commision;
            // $total_money = $store->total_money + $commision;
            StoreBalanceDetail::addDetailByTicketOrder($order,$commision,$balance);
        } catch (\Throwable $th) {
            DB::rollBack();
        }
        DB::commit();        
    }
    
    
    public function delete(){
        if($this->id == 10001){
            throw new \Exception('系统默认分销商，不能删除');
        }
        parent::delete();
    }

    /**
     * 影旅卡订单结账
     *
     * @param float $money
     * @return void
     */
    public function settleMoney(float $money){
        $account = $this;
        if($money <= 0){
            throw new \Exception('请输入结算金额');
        }
        $limit_money = $account->balance;
        if($limit_money < $money){
            throw new \Exception('剩余应结金额不足');
        }
        $account->balance = $account->balance - $money;
        $account->settle_money = $account->settle_money + $money;
        $account->save();
    }
    static function getStoreInfo(int $comId){
        return self::where('id',$comId)->first();
    }

    public function getStoreLogoAttribute($value){
        return Helpers::formatPath($value,'admin');
    }

    public function getAuthPassword(){
        return $this->attributes['password'];
    }

}

