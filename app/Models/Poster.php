<?php

namespace App\Models;

use App\Support\MApi;
use Overtrue\Pinyin\Pinyin;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;
/**
 * 海报
 */
class Poster extends Model
{
    
    protected $guarded  = [];


    public function getPosterAttribute($value){
        return Helpers::formatPath($value,'admin');
    }
     public function getLphotoAttribute($value){
        return Helpers::formatPath($value,'admin');
    }
        
}
