<?php

namespace App\Models;

use App\Support\Helpers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
/**
 * 系统配置
 */
class Setting extends Model
{
    public $incrementing = false;
    protected $table = "sys_setting";
    protected $primaryKey = "names";

    protected $hidden = ['created_at','updated_at'];


    /**
     * 获取配置缓存
     *
     * @return void
     */
    public static function getSettings($force = false){
        $caches = Cache::get('sys_setting',false);
        if($caches && !$force){
            return $caches;
        }
        $list = self::all()->toArray();
        $kes = array_column($list,'names');
        $list = array_combine($kes,$list);
        Cache::forever('sys_setting', $list);
        return $list;
    }

    /**
     * 更新配置
     *
     * @param [type] $data
     * @return void
     */
    public static function updateSetting($data){
        if(empty($data)){
            Helpers::exception('更新失败');
        }

        foreach($data as $key=>$item){
            self::where('names',$key)->update(['content'=>$item]);
        }
        self::getSettings(true);
    }

    public function setContentAttribute($value){
        if(is_array($this->attributes['content'])){
            $this->attributes['content'] = json_encode($value);
        }else{
            $this->attributes['content'] = $value;
        }
    }

    public function getContentAttribute($value){
        if(json_decode($value,true)){
            return json_decode($value,true);
        }
        return $value;
    }




}
