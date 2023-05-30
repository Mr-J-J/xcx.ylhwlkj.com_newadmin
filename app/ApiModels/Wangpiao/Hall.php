<?php

namespace App\ApiModels\Wangpiao;

use App\Support\Helpers;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    use ApiTrait;
    protected $table = 'wp_halls';
    // public $timestamps = false;
    // protected $fillable = [];
    protected $guarded = [];


    static function syncData(array $data,$cinema_id = 0){
        $nowtime = date('Y-m-d H:i:s');
        
        foreach($data as &$item){
            $item = self::formatField($item);
            $item['cinema_id'] = $cinema_id;
            $item['created_at'] = $item['updated_at'] = $nowtime;
        }
        Hall::upsert($data,['name','seatcount','cinema_id']);
        return $data;
    }

    static function formatField($data){
        $fields = array(
            'id'                    => $data['ID'],
            'name'                  => $data['Name'],
            'seatcount'             => $data['SeatCount'],
            'type'                  => $data['Type'],
            'is_valid'               =>  $data['IsValid'] ? 1 : 0,
            'sh'                    => $data['SH'],
            'sw'                    => $data['SW'],
            'sr'                    => $data['SR'],
        );

        return $fields;
    }
}
