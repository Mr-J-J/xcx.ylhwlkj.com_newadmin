<?php

namespace App\ApiModels\Wangpiao;

use App\Support\Helpers;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

class Schedules extends Model
{
    use ApiTrait;
    protected $table = 'wp_schedules';

    // protected $fillable = [];
    protected $guarded = [];

    protected $hidden = ['vprice','created_at','updated_at'];

    protected $appends =['local_price','show_time_txt','api_price'];//,

    static function getSchedulesInfo($paiqiId,$id=false){
        $info = null;
        // if($id){
        //     $info =  self::where('id',$paiqiId)->first();
        // }else{
        //     $info =  self::where('show_index',$paiqiId)->first();
        // }
        $info =  self::where('show_index',$paiqiId)->first();
        if(!empty($info)){
            $info->id = $info->show_index;
        }

        return $info;
    }

    /**
     * 获取分区价格
     *
     * @param string $sectionId
     * @return void
     */
    public function getScetionInfo($sectionId = '',$get_price = false){
        $section = json_decode($this->schedule_area,true);
        if(empty($section)){
            return array();
        }
        if($sectionId === ''){
            return $section;
        }
        $currentSection = array_values(array_filter($section,function($v)use ($sectionId){
            if($v['SectionID'] == $sectionId){
                return $v;
            }
        }));
        if($get_price){
            return round(!empty($currentSection[0])?$currentSection[0]['UPrice']:0,2);
        }
        return !empty($currentSection[0])?$currentSection[0]:array();
    }

    static function syncData($data,$film_id = 0){
        $nowtime = date('Y-m-d H:i:s');
        foreach($data as &$item){
            $item = self::formatField($item);
            $item['created_at'] = $item['updated_at'] = $nowtime;
        }
        try {
            logger($data);
            Schedules::upsert($data,['film_id','price','schedule_area','uprice','uwprice','vprice','updated_at']);
        } catch (\Throwable $th) {
            throw $th;
        }
        $result = array();
        foreach($data as &$item){
            if($film_id > 0 && $item['film_id'] != $film_id) continue;
            // $item['schedule_area'] = json_decode($item['schedule_area'],true);
            $item['api_price'] = $item['price'] / 100;
            $item['price'] = $price =  self::calcLocalPrice($item['price'] / 100,'price_discount_rate',$item['cinema_id']);
            $item['local_price'] = self::calcLocalPrice($item['api_price'],'tehui_price_rate',$item['cinema_id']);
            $item['show_time_txt'] = date('H:i',$item['show_time']);
            $result[] = (Object)$item;
        }
        return $result;
    }

    static function saveData($data){
        $apiResult = (array) \App\Support\WpApi::getFilmShowByDate($data,date('Y-m-d 03:00:00'));
        Schedules::syncData($apiResult);
        self::delaySync($data);
    }

    /**
     * 定时同步
     *
     * @param [type] $data
     * @return void
     */
    static function delaySync($data){
        //20分钟同步一次
        $lasttime = strtotime(date('Y-m-d 23:00:00'));
        if(time() > $lasttime){
            return false;
        }
        //logger('20分钟同步一次:'.$data);
        \App\Jobs\Wangpiao\SchedulesJob::dispatch($data)->delay(now()->addMinutes(\App\Support\WpApi::DELAY_TIME));
    }

    /**
     * 聚福宝
     *
     * @param [type] $data
     * @return void
     */
    static function formatField($data){
        $fields = array(
            'film_id'          => $data['film_id'],
            'cinema_id'        => $data['ciema_id'],
            'city_code'        => 0,
            'show_time'        => $data['show_time'],
            'show_date'        => date('Y-m-d',$data['show_time']),
            'close_time'        => $data['close_time'],
            'sale_end_time'       => 0,
            'price'            => $data['price'],
            'uprice'           => $data['price'],
            'vprice'           => $data['price'],
            'uwprice'          => $data['price'],
            'sptype'           => 0,
            'spprice'          => $data['price'],
            'isimax'           => 0,
            'language'         => '',
            'film_name'        => '',
            'show_index'       => $data['id'],
            'hall_id'          => 0,
            'seat_count'       => 0,
            'status'           => 0,
            'hall_name'        => $data['hall_name'],
            'show_version'     => $data['show_version'],
            'max_can_buy'      => $data['max_can_buy'],
            'schedule_area'    => $data['schedule_area'],
        );

        if(!empty($fields['schedule_area'])){
            $sectionarea = explode(';',$fields['schedule_area']);
            $list = array();
            $find = [':',','];
            $replace = ['=','&'];
            $sectionarea = str_replace($find,$replace,$sectionarea);
            foreach($sectionarea as $v){
                parse_str($v,$arr);
                $arr['SectionID'] = $arr['area']??0;
                $arr['UPrice'] = $arr['price']??0;
                $list[] = $arr;
            }
            $fields['schedule_area'] = json_encode($list);
        }

        return $fields;
    }

    /**
     * 网票网数据
     *
     * @param [type] $data
     * @return void
     */
    static function formatFieldV11($data){
        $fields = array(
            'film_id'          => $data['FilmID'],
            'cinema_id'        => $data['CinemaID'],
            'city_code'        => $data['CityID'],
            'show_time'        => strtotime($data['ShowTime']),
            'close_time'        => 0,
            'sale_end_time'       => strtotime($data['SaleEndTime']),
            'price'            => $data['UPrice'] * 100,
            'uprice'           => $data['UPrice'] * 100,
            'vprice'           => $data['VPrice'] * 100,
            'uwprice'          => $data['UWPrice'] * 100,
            'sptype'           => $data['SPType'],
            'spprice'          => $data['SPPrice'],
            'isimax'           => $data['IsImax'] ? 1: 0,
            'language'         => $data['LG'],
            'film_name'        => $data['FilmName'],
            'show_index'       => $data['ShowIndex'],
            'hall_id'          => $data['HallID'],
            'seat_count'       => $data['SeatCount'],
            'status'           => $data['Status'],
            'hall_name'        => $data['HallName'],
            'show_version'     => $data['Dimensional'],
            'max_can_buy'      => $data['SeatCount'],
            'schedule_area'    => json_encode($data['Sections']),
        );

        return $fields;
    }

    public function film(){
        return $this->hasOne('\App\ApiModels\Wangpiao\Film','id','film_id');
    }

    // public function scopeCity($query,$cityId){
    //     return $query->where('city_code',$cityId);
    // }

    // public function scopeCinema($query,$cinemaId){
    //     return $query->where('cinema_id',$cityId);
    // }
    public function cinema(){
        return $this->hasOne('\App\ApiModels\Wangpiao\Cinema','id','cinema_id');
    }

    //票价
    public static  function calcLocalPrice($price,$key='price_discount_rate',$cinemaId = 0){
        // $discount = round(Helpers::getSetting($key),2) / 100;
        $brandId = Cinema::where('id',$cinemaId)->value('brand_id');
        $brandInfo = CinemasBrand::where('id',$brandId)->first();
        $discount = round($brandInfo->$key,2) / 10;
        if(!$discount){
            $discount = round(Helpers::getSetting($key),2) / 10;
        }
        // logger($discount.'折扣');
        if(!$discount){
            return $price;
        }
        return round($price * $discount,2);
    }
    public function getLocalPriceAttribute(){
        // $price = $this->getPriceAttribute($this->attributes['price']);
        $cinemaId = $this->attributes['cinema_id'];
        $price = intval($this->attributes['price']) / 100;
        return self::calcLocalPrice($price,'tehui_price_rate',$cinemaId);
    }

    public function getPriceAttribute($value){
        $cinemaId = $this->attributes['cinema_id'];
        return self::calcLocalPrice($value / 100,'price_discount_rate',$cinemaId);
    }

    public function getApiPriceAttribute($value){
        return intval($this->attributes['price']) / 100;
    }

    // public function getShowDateAttribute(){
    //     return date('Y-m-d',$this->attributes['show_time']);
    // }
    public function getShowTimeTxtAttribute(){
        return date('H:i',$this->attributes['show_time']);
    }

    // public function getCloseTimeTxtAttribute(){
    //     // return date('H:i',$this->attributes['close_time']);
    //     return '';
    // }

    public function scopeSearchFilm($query,$cinemaId,$showtime,$film_id = ''){
        $end_time = strtotime(date('Y-m-d',$showtime) . ' 23:59:59');
        return $query->where('cinema_id',$cinemaId)
                ->whereBetween('show_time',[$showtime,$end_time])
                ->when($film_id,function($query,$film_id){
                    return $query->where('film_id',$film_id);
                });
    }
}
