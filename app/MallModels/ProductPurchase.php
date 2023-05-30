<?php

namespace App\MallModels;

use Illuminate\Database\Eloquent\Model;

class ProductPurchase extends Model
{
    protected $table = 'mall_sku_purchase';

    protected $guarded = [];
    protected $fillable = [];

    /**
     * 购买记录写入
     *
     * @param integer $user_id
     * @param integer $sku_id
     * @param integer $number
     * @return void
     */
    static function updatePurchase(int $user_id,int $sku_id,int $number = 1){
        $purchase = self::where('user_id',$user_id)->where('sku_id',$sku_id)->firstOr(function(){
            $model = new ProductPurchase;
            $model->buy_num = 0;
            return $model;
        });
        $purchase->user_id = $user_id;
        $purchase->sku_id = $sku_id;
        $purchase->buy_num += $number;
        $purchase->save();
    }

    /**
     * 判断限购数量
     *
     * @param integer $user_id
     * @param integer $sku_id
     * @param integer $limit_number
     * @param integer $buy_number
     * @return void
     */
    static function checkPurchaseNumber(int $user_id,int $sku_id,int $limit_number,int $buy_number){
        $purchase = self::where('user_id',$user_id)->where('sku_id',$sku_id)->first();
        if(empty($purchase)) return true;
        $limitbuyNumber = $purchase->buy_num + $buy_number;
        if($limitbuyNumber > $limit_number){
            return false;
        }
        return true;
    }
    
    
    /**
     * 撤销限购
     *
     * @param integer $user_id
     * @param integer $sku_id
     * @param integer $number
     * @return void
     */
    static function cancelPurchase(int $user_id,int $sku_id,int $number){
        $purchase = self::where('user_id',$user_id)->where('sku_id',$sku_id)->first();
        $limit_number = $purchase->buy_num - $number;
        if($limit_number <= 0){
            $purchase->delete();
            return true;
        }
        $purchase->buy_num = $limit_number;
        $purchase->save();
        return true;
    }
}
