<?php

namespace App\MallModels;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

/**
 * 活动专区
 */
class Activite extends Model
{
    protected $table = 'mall_activities';

    protected $hidden = ['created_at','updated_at'];

    static function getList(int $limit = 0){
        if(!$limit){
            return self::orderBy('sort','desc')->get();
        }
        return self::orderBy('sort','desc')->take($limit)->get();
    }
    
     public function getImageAttribute($value){
        // return Helpers::formatPath($value,'admin');
        return Helpers::formatPath($value,'admin');
    }
}
