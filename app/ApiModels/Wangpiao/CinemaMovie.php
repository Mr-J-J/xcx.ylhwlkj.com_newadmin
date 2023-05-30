<?php

namespace App\ApiModels\Wangpiao;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

class CinemaMovie extends Model
{
    use ApiTrait;
    
    protected $table = 'wp_cinemas_movie';
    protected $fillable = [];
    protected $guarded = [];

    static function syncData($data,$cinemaId){
        $nowtime = date('Y-m-d H:i:s');
        foreach($data as &$item){
            $item['CinemaID'] = $cinemaId;
            $item = self::formatField($item);
            $item['created_at'] = $item['updated_at'] = $nowtime;
        }
        try {
            CinemaMovie::upsert($data,['updated_at']);
        } catch (\Throwable $th) {
            return [];
        }
        $result = array();
        foreach($data as $item){
            $result[] = (Object)$item;
        }
        return $result;
    }

    static function formatField($data){
        $fields = array(
            'cinema_id'    => $data['CinemaID'],
            'film_id'  => $data['id'],
        );
        return $fields;
    }

    public function film(){
        return $this->hasOne('\App\ApiModels\Wangpiao\Film','id','film_id');
    }
    
    public function cinema(){
        return $this->hasOne('\App\ApiModels\Wangpiao\Cinema','id','cinema_id');
    }
    
    

    public function scopeSearchFilm($query,$cinemaId,$showtime,$film_id = ''){       
        $end_time = strtotime(date('Y-m-d',$showtime) . ' 23:59:59');
        return $query->where('cinema_id',$cinemaId)
                // ->whereBetween('show_time',[$showtime,$end_time])
                ->when($film_id,function($query,$film_id){
                    return $query->where('film_id',$film_id);
                });
    }

    public function scopeCity($query,$cityId){
        return $query->where('cid',$cityId);
    }
}
