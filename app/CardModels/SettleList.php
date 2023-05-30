<?php

namespace App\CardModels;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class SettleList extends Model
{
    protected $table = 'rs_settle_list';

    /**
     * 添加结算
     *
     * @return void
     */
    static function addSettle(CardOrder $order){
        if(!$order->com_id) return false;
        $store = RsStores::where('id',$order->com_id)->first();
    }

    /**
     * 结款记录列表
     *
     * @param integer $store_id
     * @return void
     */
    static function getSettleList(int $comId){
        $limit = request('limit',10);
        return self::where('com_id',$comId)->orderBy('created_at','desc')->paginate((int)$limit);
    }

    public function store(){
        return $this->hasOne('App\CardModels\RsStores','id','com_id');
    }

    public function getImageAttribute($value){
        if(!empty($value)){
            return Helpers::formatPath($value,'admin');
        }
        return $value;
    }
}
