<?php

namespace App\ApiModels\Wangpiao;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'wp_district';

    public $timestamps = false;

    protected $fillable = [];
    protected $guarded = [];


    static function saveData($data){
        $result = '';
        $data = self::formatField($data);      
        try {
           $result =  self::updateOrCreate(['id'=>$data['id']],$data);
        } catch (\Exception $e) {
            Helpers::exception($e->getMessage());
        }
        return $result;
    }

    static function formatField($data){
        $keys = array_keys($data);
        $keys = array_map(function($value){
            return strtolower($value);
        },$keys);
        $fields = array_combine($keys,array_values($data));
        // $fields = array(
        //     'id'        =>  $data['ID'],
        //     'name'      =>  $data['Name'],
        //     'cid'     =>  $data['SName'],
        //     'pname'     =>  $data['PName'],
        //     'pinyin'    =>  '',
        //     'type'      =>  $data['Type'] ?? 0,
        //     'hot'       =>  $data['Hot'] ?? 0,
        // );

        return $fields;
    }

    public function scopeCity($query,$cityId){
        return $query->where('cid',$cityId);
    }
}
