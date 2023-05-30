<?php
namespace App\Traits;

use App\Support\Helpers;

/**
 * 同步接口数据时公共写入更新方法
 */
trait ApiTrait
{
    // static function saveData($data){
    //     $result = '';
    //     $data = self::formatField($data);      
    //     try {
    //        $result =  self::updateOrCreate(['id'=>$data['id']],$data);
    //     } catch (\Exception $e) {
    //         Helpers::exception($e->getMessage());
    //     }
    //     return $result;
    // }

    // static function formatField($data){
    //     $keys = array_keys($data);
    //     $keys = array_map(function($value){
    //         return strtolower($value);
    //     },$keys);
    //     $fields = array_combine($keys,array_values($data));
    //     return $fields;
    // }

    static function upsert(array $insert,array $update = []){
        $table = (new self)->getTable();
        if(empty($insert)){
            return array();
        }
        $fieldColumnStr = join(',',array_keys($insert[0]));
        $valuesArr = [];
        foreach($insert as $item){
            $arr = array_values($item);
            foreach($arr as &$v){
                $v = addslashes($v);
            }
            $valuesArr[] = "('".join('\',\'',$arr)."')";
        }
        $valueColumnStr = join(',',$valuesArr);
        $sql = "INSERT INTO {$table} ({$fieldColumnStr}) VALUES $valueColumnStr";

        if(!empty($update)){
            foreach($update as &$item){
                $item = "{$item}=VALUES({$item})";
            }
            $sql .= " ON DUPLICATE KEY UPDATE " . join(',',$update);
        }
        $res = (new self)->getConnection()->statement($sql);
        return $res;
    }
}