<?php

namespace App\ApiModels\Wangpiao;

use Overtrue\Pinyin\Pinyin;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'wp_cities';

    public $timestamps = false;

    protected $fillable = [];
    protected $guarded = [];

    static function syncData($data){
        $result = '';
        $data = self::formatField($data);
        $pinyin = new Pinyin();
        $pinyinstr = $pinyin->abbr($data['name']);;
        $data['pinyin'] = strtoupper(substr($pinyinstr, 0, 1 ));        
        try {
           $result =  self::updateOrCreate(['id'=>$data['id']],$data);
        } catch (\Exception $e) {
            Helpers::exception($e->getMessage());
        }
        return $result;
    }

    static function saveData($data){
        $result = '';
        $data = self::formatField($data);
        $pinyin = new Pinyin();
        $pinyinstr = $pinyin->abbr($data['name']);;
        $data['pinyin'] = strtoupper(substr($pinyinstr, 0, 1 ));        
        try {
           $result =  self::updateOrCreate(['id'=>$data['id']],$data);
        } catch (\Exception $e) {
            Helpers::exception($e->getMessage());
        }
        return $result;
    }

    /**
     * 聚福宝
     *
     * @param [type] $data
     * @return void
     */
    static function formatField($data){
        $fields = array(
            'id'        =>  $data['city_code'],
            'code'      =>  $data['city_code'],
            'name'      =>  $data['city_name'],
            'sname'     =>  '',
            'pname'     =>  '',
            'pinyin'    =>  '',
            'type'      =>  0,
            'hot'       =>  0,
        );

        return $fields;
    }
    // 网票网
    // static function formatField($data){
    //     $fields = array(
    //         'id'        =>  $data['ID'],
    //         'code'      =>  $data['Code'],
    //         'name'      =>  $data['Name'],
    //         'sname'     =>  $data['SName'],
    //         'pname'     =>  $data['PName'],
    //         'pinyin'    =>  '',
    //         'type'      =>  $data['Type'] ?? 0,
    //         'hot'       =>  $data['Hot'] ?? 0,
    //     );

    //     return $fields;
    // }
}
