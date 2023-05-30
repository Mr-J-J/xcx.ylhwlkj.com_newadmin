<?php

namespace App\CardModels;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 影旅卡
 */
class Cards extends Model
{
    use SoftDeletes;
    protected $table = 'rs_film_card';

    protected $hidden = ['created_at','updated_at'];
    // protected $appends = ['index_image','list_image'];

    static function getList(){
        $list = Cards::where('state',1)->get();
        return $list;
    }

    public function getImageAttribute($value){
        if(!empty($value)){
            return Helpers::formatPath($value,'admin');
        }
        return $value;
    }

    public function getIndexImageAttribute($value){
        return  Helpers::formatPath($value,'admin');
    }

    public function getListImageAttribute($value){
        return  Helpers::formatPath($value,'admin');
    }
}
