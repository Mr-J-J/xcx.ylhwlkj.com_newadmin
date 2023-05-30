<?php

namespace App\Models;

// use App\Support\MApi;
// use Overtrue\Pinyin\Pinyin;
// use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;
/**
 * 城市列表
 */
class Region extends Model
{
    
    protected $table = 'regions';
    
    protected $guarded  = [];

    protected $hidden = ['created_at','updated_at'];

    // static function getRegions(int $parent_id,$level = 1,$columns = ['*']){
    //     return self::select($columns)->where('parent_city_code',$parent_id)->where('city_level',(int)$level)->get();
    // }

    static function getSheng(){
        return self::select(['sheng_name as city_name','sheng_code as city_code'])->distinct()->get();
    }
    
    static function getShi(){
        return self::select(['sheng_name as city_name','sheng_code as city_code'])->distinct()->get();
    }
    static function getRegions($parent_code,$level = 1,$columns = ['*']){
        $list = collect(array());
        switch($level)
        {
            case 1: //省
                $list = self::getSheng();
                break;
            case 2: //市
                $list = self::select(['shi_name as city_name','shi_code as city_code'])->distinct('shi_code')->where('sheng_code',$parent_code)->get();
                break;
            case 3: //区
                $list = self::select(['qu_name as city_name','qu_code as city_code'])->distinct('qu_code')->where('shi_code',$parent_code)->get();
                break;
            case 4: //街道
                $list = self::select(['district_name as city_name','district_code as city_code'])->where('qu_code',$parent_code)->get();
                break;

        }
        
        return $list;
    }

    
}
