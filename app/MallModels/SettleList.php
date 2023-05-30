<?php

namespace App\MallModels;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class SettleList extends Model
{
    protected $table = 'mall_settle_list';
    /**
     * 结款记录列表
     *
     * @param integer $store_id
     * @return void
     */
    static function getSettleList(int $store_id){
        $limit = request('limit',10);
        return self::where('store_id',$store_id)->orderBy('created_at','desc')->paginate((int)$limit);
    }

    public function store(){
        return $this->hasOne('App\MallModels\Stores','user_id','store_id');
    }

    public function getImageAttribute($value){
        if(!empty($value)){
            return Helpers::formatPath($value,'admin');
        }
        return $value;
    }
}
