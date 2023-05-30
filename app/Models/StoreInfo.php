<?php

namespace App\Models;

use App\Models\store\StoreOfferOrder;
use Illuminate\Database\Eloquent\Model;

class StoreInfo extends Model
{
    //       
    /**
     * 商户其他信息
     *
     * @param Store $store
     * @return void
     */
    public static function info(Store $store){
        return self::where('store_id',$store->id)->first();
    }

    public function getFreezeMoneyAttribute($value){
        return $value / 100;
    }

    public function setFreezeMoneyAttribute($value){
        $this->attributes['freeze_money'] = $value * 100;
    }

    public function getSettleMoneyAttribute($value){
        return $value / 100;
    }

    public function setSettleMoneyAttribute($value){
        $this->attributes['settle_money'] = $value *100;
    }

    public function getBalanceAttribute($value){
        return $value / 100;
    }

    public function setBalanceAttribute($value){
        $this->attributes['balance'] = $value *100;
    }

    public function getDrawRateAttribute($value){
        $rate = 0;
        if($this->attributes['order_count'] > 0){
            $rate = ($this->attributes['out_ticket_count'] / $this->attributes['order_count']) * 100;
            return sprintf('%.1f',$rate);
        }
        return $rate;
    }
    
}
