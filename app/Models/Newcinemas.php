<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Newcinemas extends Model
{
    protected $table = 'newcinemas';
    public static function getyuan($shiid='x',$brandid='x'){
        
        if($shiid!='x'){
            
            $qu = Region::where('qu_code',$shiid)->first();
            $list = self::select(['cinemaName as text','cinemaCode as id'])->where('county',$qu->qu_name)->distinct()->get();
            $l=[];
            foreach ($list as $item){
                $l[]=['text'=>$item->text,'id'=>$item->id];
            }
            return $l;
        }else{
            $list = self::select(['cinemaName as text','cinemaCode as id'])->distinct()->get();
        }

        $l=[];
        foreach ($list as $item){
            $l[$item->id]=$item->text;
        }
        return $l;
    }
    public static function syncData($data){
        Newcinemas::truncate();
        // 转换为集合
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
                $list=$cinema;
            }
//            logger($list);
            Newcinemas::insert($list);
        }
        return $chunks;
    }
}
