<?php

namespace App\MallModels;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Stores extends Model
{
    use SoftDeletes;
    protected $table = 'mall_stores';


    static function getStore(int $user_id){
        return self::where('user_id',$user_id)->first();
    }

    static function getStoreList(string $cityCode,$lat,$lng){
        return Stores::select([DB::raw("(st_distance(point(longitude,latitude),point({$lng},{$lat}))*111195) as distance"),'user_id as id'])->where('city',$cityCode)->orderBy('distance','desc')->get();
    }
    //        从数据表mall_store中获取商家信息
    static function getStoreInfo(int $user_id){
        return self::where('id',$user_id)->first();
    }
    /**
     * 距离
     *
     * @param float $distance
     * @return void
     */
    static function formatDistance($distance){
        if(!$distance) return '';
        if($distance>10000){
            $distance = '>10km';
        }else if($distance>1000){
            $distance = round($distance/1000,1) . 'km';
        }else{
            $distance = round($distance).'米';
        }
        return $distance;
    }
    /**
     * 更新销售额
     *
     * @param float $money
     * @return void
     */
    public function setSaleMoney(float $money){
        $account = $this;
        if($money <= 0) return false;
        $account->sale_money = $account->sale_money + $money;
        $account->save();
    }

    /**
     * 更新退款总额
     *
     * @param float $money
     * @return void
     */
    public function setRefundMoney(float $money){
        $account = $this;
        if($money <= 0) return false;
        $account->refund_money = $account->refund_money + $money;
        $account->save();
    }

    public function settleMoney(float $money){
        $account = $this;
        if($money <= 0){
            throw new \Exception('请输入结算金额');
        }
        $limit_money = $account->freeze_money - $account->settle_money;
        if($limit_money < $money){
            throw new \Exception('剩余应结金额不足');
        }
        $account->settle_money = $account->settle_money + $money;
        $account->save();
    }

    public function getLatitudeAttribute($value){
        if(empty($value)) return 39.905785;
        return $value;
    }
    public function getLongitudeAttribute($value){
        if(empty($value)) return 116.398859;
        return $value;
    }
    public function user(){

        return $this->hasOne('\App\Models\TicketUser','id','user_id');
    }

    public function category(){
        return $this->hasOne('App\MallModels\Category','id','category_id');
    }
}
