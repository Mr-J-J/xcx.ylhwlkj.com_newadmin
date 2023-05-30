<?php

namespace App\Models;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    public $incrementing = false;


    protected $guarded  = [];
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
        return $result;
    }

    
}
