<?php

namespace App\Models;

use App\Support\MApi;
use Overtrue\Pinyin\Pinyin;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;
/**
 * 城市列表维护
 */
class City extends Model
{
    
    protected $primaryKey = 'city_code';
    
    protected $guarded  = [];

    protected $hidden = ['created_at','updated_at'];

    /***
     * 更新城市列表
     */
    public static function saveCitys($data){        
        $result = '';

        $pinyin = new Pinyin();
        $pinyinstr = $pinyin->abbr($data['city_name']);;
        $data['pinyin'] = strtoupper(substr($pinyinstr, 0, 1 ));        
        try {
           $result =  self::updateOrCreate(['city_code'=>$data['city_code']],$data);
        } catch (\Exception $e) {
            Helpers::exception($e->getMessage());
        }
        return $result;
    }
}
