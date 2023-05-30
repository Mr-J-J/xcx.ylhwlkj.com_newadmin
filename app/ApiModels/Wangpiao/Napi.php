<?php

namespace App\ApiModels\Wangpiao;

use App\Models\Setting;
use App\Support\Api\ApiHouse;
use Illuminate\Http\Request;

class Napi
{

    /**
     * 热映、即将上映的电影
     *
     * @param Request $req
     * @return void
     */
    public static function currentFilmList(Request $req){
        $type = (int)$req->input('type',1);//1即将上映 2热映
        $limit = (int) $req->input('limit',100); //0全部   0指定数量
        $city_code = $req->input('city_code','');
        $list = \App\Support\NApi::get('https://dyp.ylhwlkj.com/api/current_film?type=2&limit=5&city_code=110100&token=be72e2515448a21a2ef7646c28849ffc');

        return $list;
    }
}
