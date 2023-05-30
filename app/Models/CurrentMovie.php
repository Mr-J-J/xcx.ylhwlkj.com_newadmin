<?php

namespace App\Models;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class CurrentMovie extends Model
{
    public $incrementing = false;


    protected $guarded  = [];

    protected $hidden = ['created_at','updated_at'];
    /**
     * 添加/更新影片 接口更新
     *
     * @param [type] $data
     * @return void
     */
    public static function saveFilms($data){  
        if(isset($data['open_time'])){
            $data['open_time'] = intval($data['open_time']);
        }
        $result = '';
        try {
           $result =  self::updateOrCreate(['id'=>$data['id']],$data);
        } catch (\Exception $e) {
            Helpers::exception($e->getMessage());
        }
        
    }

    /**
     * 影片搜索
     *
     * @param [type] $keywords
     * @return void
     */
    public static function searchFilm($keywords,$city_code){
        $list = self::select(['id','show_name','city_code','poster','remark','data_type','director','leading_role'])
                ->where('show_name','like',"%{$keywords}%")
                ->where('city_code',$city_code)
                ->paginate(10);

        $array = array(
            [
                'title'=> '即将上映',
                'list' => []
            ],
            [
                'title'=> '正在热映',
                'list'=> []
            ]
        );

        foreach($list as $item){            
            $array[$item->data_type-1]['list'][] = $item;
        }
        rsort($array);
        return $array;
    }


    public function scopeCity($query,$city_code){
        return $query->where('city_code',$city_code);
    }
}
