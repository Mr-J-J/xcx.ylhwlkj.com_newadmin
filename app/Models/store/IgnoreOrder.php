<?php

namespace App\Models\store;

use Cache;
use Illuminate\Database\Eloquent\Model;

class IgnoreOrder extends Model
{
    protected $table = 'store_ignore_order';
    

    /**
     * 忽略报价订单
     *
     * @param [type] $orderno
     * @param [type] $store_id
     * @return void
     */
    static function addIgnoreOrder($orderno,$store_id){
        $model = new self;
        $info = $model->where('store_id',$store_id)->where('orderno',$orderno)->first();
        if(!empty($info)) return true;
        $model->store_id = $store_id;
        $model->orderno = $orderno;
        $model->save();
        // Cache::add('ignore_order_'.$store_id,$orderno,7200);
    }

    /**
     * 获取忽略的订单
     *
     * @param [type] $store_id
     * @return void
     */
    static function getIgnoreOrder($store_id){
        // $cacheKey = 'ignore_order_'.$store_id;
        // $cachelist = Cache::get($cacheKey,false);
        // if(!$cachelist){
            $list = self::where('store_id',$store_id)->get()->pluck('orderno')->toArray();
            // if(empty($list)){
                // return $list;
            // }
            // Cache::put($cacheKey,$list,7200);
            return $list;
        // }

        // return $cachelist;
    }
}
