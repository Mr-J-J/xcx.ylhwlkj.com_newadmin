<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Newmove extends Model
{
    protected $table = 'newmoves';

    public static function syncData($data,$state=0){
//        Newmove::truncate();
        // 转换为集合
        foreach ($data as &$item){
            $item['stills']=json_encode($item['stills']);
        }
        $collection = collect($data);

        // 定义每批的数量，例如1000
        $batchSize = 1;

        // 使用chunk方法将集合分割为多个小集合
        $chunks = $collection->chunk($batchSize);
        $nowtime = date('Y-m-d H:i:s');
        // 遍历每个小集合，使用insert方法插入到数据库中
        foreach ($chunks as $chunk) {
            $list=array();
            foreach($chunk as $cinema){
                $cinema['updated_at'] = $cinema['created_at'] = $nowtime;
                $cinema['state'] = $state;
                $list=$cinema;
            }
//            logger($list);
            Newmove::insert($list);
        }
        return $chunks;
    }
}
