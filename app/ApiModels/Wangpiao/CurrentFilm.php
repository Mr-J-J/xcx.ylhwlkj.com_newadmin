<?php

namespace App\ApiModels\Wangpiao;
use Illuminate\Support\Facades\DB;
use App\Support\Helpers;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 热映、即将上映
 */
class CurrentFilm extends Model
{
    use ApiTrait;
    protected $table = 'wp_current_movies';
    
    protected $primaryKey = 'infoid';
    // protected $fillable = [];
    protected $guarded = [];

    protected $appends = ['film_duration'];
    
    /**
     * 写入电影
     *
     * @param [type] $data
     * @param integer $showtype 1 即将上映 2 热映         
     * @return array
     */
    static function syncData(array $data,int $showtype = 1,int $cityId = 0){
        $nowtime = date('Y-m-d H:i:s');
        foreach($data as &$item){
            $item['ShowType'] = $showtype;
            $item['CityID'] = $cityId;
            $item = self::formatField($item);
            $item['created_at'] = $item['updated_at'] = $nowtime;
        }
        CurrentFilm::upsert($data,['show_name','updated_at','poster','lphoto']);        
        // Film::insert($data);
        return $data;
    }


    /**
     * 聚福宝数据
     *
     * @param [type] $data
     * @return void
     */
    static function formatField($data){        
        // $photos = [$data['SPhoto'],$data['Hphoto'],$data['Lphoto']];
        // $arr = array_filter($photos,function($v){
        //     if(!empty($v)) return $v;
        // });
        $poster = '';
        if(!empty($arr)){
            $poster = $arr[0];
        }
        $fields = array(
            'id'           => (int)$data['id'],
            // 'cinema_id'         => $data['Name'],
            'city_code'         => $data['city_code'],
            'show_name_en'      => $data['show_name_en'],
            'remark'            => $data['remark'],
            'highlight'         => $data['highlight'],
            'country'           => $data['country'],
            'show_name'         => $data['show_name'],
            'poster'            => $data['poster'],
            'hphoto'            => '',
            'lphoto'            => '',
            'sphoto'            => '',
            'duration'          => 0,
            'mprice'            => 0,
            'grade_num'         => $data['grade_num'] ?? 0,            
            // 'trailerList'       => $data['Name'],
            'type'              => $data['type'],
            'open_time'         => $data['open_time'],
            'show_version_list' => $data['show_version_list'],
            'description'       => $data['description'],
            'language'          => $data['language'],
            // 'show_mark'         => $data['Type'],
            'leading_role'      => $data['leading_role'],
            'director'          => $data['director'],
            'date_type'        => $data['ShowType'] ?? 1, // 1rightnow 即将上映 2 热映                         
        );

        return $fields;
    }

    /**
     * 网票网数据
     *
     * @param [type] $data
     * @return void
     */
    static function formatFieldV1($data){        
        $photos = [$data['SPhoto'],$data['Hphoto'],$data['Lphoto']];
        $arr = array_filter($photos,function($v){
            if(!empty($v)) return $v;
        });
        $poster = '';
        if(!empty($arr)){
            $poster = $arr[0];
        }
        $fields = array(
            'id'           => $data['ID'],
            // 'cinema_id'         => $data['Name'],
            'city_code'         => $data['CityID'] ?? 0,
            'show_name_en'      => '',
            'remark'            => $data['Grade'],
            'highlight'         => $data['Msg'],
            'country'           => $data['Area'],
            'show_name'         => $data['Name'],
            'poster'            => $poster,            
            'hphoto'            => $data['Hphoto'] ?: '',
            'lphoto'            => $data['Lphoto'] ?: '',
            'sphoto'            => $data['SPhoto'] ?: '',
            'duration'          => $data['Duration'],
            'mprice'            => $data['Mprice'],
            'grade_num'         => $data['GradeNum'] ?? 0,            
            // 'trailerList'       => $data['Name'],
            'type'              => $data['Sort'],
            'open_time'         => strtotime($data['ShowDate']),
            'show_version_list' => $data['Type'],
            'description'       => $data['Des'],
            'language'          => $data['Language'],
            // 'show_mark'         => $data['Type'],
            'leading_role'      => $data['MP'],
            'director'          => $data['Director'],
            'date_type'        => $data['ShowType'] ?? 1, // 1rightnow 即将上映 2 热映                         
        );

        return $fields;
    }

    /**
     * 影片信息
     *
     * @param [type] $film_id
     * @param integer $city_id
     * @return void
     */
    static function info($film_id,$city_id = 0){
        return self::where('id',$film_id)->when($city_id,function($query,$city_id){
            return $query->where('city_code',$city_id);
        })->first();
    }


    /**
     * 特别获取电影时长
     *
     * @param [type] $value
     * @return void
     */
    public function getFilmDurationAttribute($value){
        return $this->attributes['duration'] = (int) str_replace('分钟','',$this->attributes['duration']);
    }


    public function getOpenTimeAttribute($value){
        return date('Y-m-d H:i:s',$this->attributes['open_time']);
    }

    public function scopeSearch($query,$filmName){    
        return $query->where(DB::raw("CONCAT(`show_name`,`leading_role`)"),'like',"%$filmName%");
    }




    
}
