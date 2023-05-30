<?php

namespace App\Models;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class Cinema extends Model
{
    public $incrementing = false;

    protected $guarded  = [];

    protected $hidden = ['created_at','updated_at'];
    /**
     * 添加/更新影院 接口更新
     *
     * @param [type] $data
     * @return void
     */
    public static function saveCinema($data){               
        if(isset($data['schedule_close_time'])){
            $data['schedule_close_time'] = intval($data['schedule_close_time']);
        }
        if(isset($data['lowest_price'])){
            $data['lowest_price'] = intval($data['lowest_price']);
        }
        if(isset($data['show_time'])){
            $data['show_time'] = intval($data['show_time']);
        }
        $result = '';
        Helpers::debugLog(json_encode($data));
        try {
           $result =  self::updateOrCreate(['id'=>$data['id']],$data);
        } catch (\Exception $e) {
            Helpers::exception($e->getMessage());
        }

        return $result;
    }    


    public function brand(){
        return $this->belongsTo('\App\Models\CinemasBrand','brand_id');
    }


}
