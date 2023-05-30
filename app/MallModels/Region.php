<?php

namespace App\MallModels;

use App\Models\Region as ModelsRegion;
// use Overtrue\Pinyin\Pinyin;

use Illuminate\Database\Eloquent\Model;
/**
 * 城市列表
 */
class Region extends ModelsRegion
{
     protected $table = 'mall_cities';
//    protected $table = 'regions';
    // protected $primaryKey = 'city_code';

}
