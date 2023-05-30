<?php

namespace App\Models;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    public $incrementing = false;


    protected $guarded  = [];
    
    protected $hidden = ['created_at','updated_at'];

    protected $appends =['show_date','show_time_txt','close_time_txt','local_price'];
    /**
     * 添加/更新影片 接口更新
     *
     * @param [type] $data
     * @return void
     */
    public static function saveSchedule($data){
        // if(isset($data['id'])){
        //     $data['film_id'] = intval($data['film_id']);
        // }
        if(isset($data['film_id'])){
            $data['film_id'] = intval($data['film_id']);
        }
        if(isset($data['cinema_id'])){
            $data['cinema_id'] = intval($data['cinema_id']);
        }
        //接口返回的参数名是错的
        if(isset($data['ciema_id'])){
            $data['cinema_id'] = intval($data['ciema_id']);
            unset($data['ciema_id']);
        }
        if(isset($data['show_time'])){
            $data['show_time'] = intval($data['show_time']);
        }
        if(isset($data['close_time'])){
            $data['close_time'] = intval($data['close_time']);
        }
        if(isset($data['max_can_buy'])){
            $data['max_can_buy'] = intval($data['max_can_buy']);
        }
        $result = '';
        try {
           $result =  self::updateOrCreate(['id'=>$data['id']],$data);
        } catch (\Exception $e) {
            Helpers::exception($e->getMessage());
        }
        return $result;
    }


    public function getPriceAttribute($value){
        return $value / 100;
    }

    public function getShowDateAttribute(){
        return date('Y-m-d',$this->attributes['show_time']);
    }
    public function getShowTimeTxtAttribute(){
        return date('H:i',$this->attributes['show_time']);
    }
    public function getCloseTimeTxtAttribute(){
        return date('H:i',$this->attributes['close_time']);
    }

    public function getLocalPriceAttribute(){
        $discount = Helpers::getSetting('price_discount_rate') / 100;
        return round($this->attributes['price'] / 100 * $discount,2);
    }

    public function cinema(){
        return $this->hasOne('\App\Models\Cinema','id','cinema_id');
    }

    public function film(){
        return $this->hasOne('\App\Models\Movie','id','film_id');
    }
}
