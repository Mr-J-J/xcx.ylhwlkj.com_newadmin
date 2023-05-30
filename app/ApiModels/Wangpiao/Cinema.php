<?php

namespace App\ApiModels\Wangpiao;
use Illuminate\Support\Facades\DB;
use App\Support\Helpers;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

class Cinema extends Model
{
    use ApiTrait;
    protected $table = 'wp_cinemas';
    // public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];

    protected $hidden = ['created_at','updated_at'];

    static function syncData($data,$cityId = 0){
        $nowtime = date('Y-m-d H:i:s');
        $chunk = array_chunk($data,1000);
        foreach($chunk as $newlist){
            $list = array();
            foreach($newlist as $cinema){
                $cinema['city_code'] = $cityId;
                $cinema = self::formatField($cinema);
                $cinema['updated_at'] = $cinema['created_at'] = $nowtime;
                $list[] = $cinema;
            }
            Cinema::upsert($list,['grade','cinema_name','updated_at']);
        }
    }

    /**
     * 聚福宝
     *
     * @param [type] $data
     * @return void
     */
    static function formatField($data){
        // $coord = explode(',',$data['coord']);
        $fields = array(
            'id'                    => $data['id'],
            'type'                  => 1,
            'brand_id'              => 0,
            'hall_count'            => $data['hall_count']??0,
            'longitude'             => $data['longitude'],
            'latitude'              => $data['latitude'],
            'cinema_name'           => $data['cinema_name'],
            'phone'                 => $data['phone'],
            'city_code'             => $data['city_code'],
            'trade_area'            => '',
            'subway'                => '',
            'dist_id'               => 0,
            'address'               => $data['address'],
            'lowest_price'          => $data['lowest_price'] ?: 0,
            'schedule_close_time'       => (int)$data['schedule_close_time'] ?:0,
            // 'std_cinema_code'       => $data['StdCinemaCode'] ?? '',
            // 'show_time'             => $data['ID'],
            // 'created_at'            => $data['ID'],
            // 'updated_at'            => $data['ID'],
        );

        $fields['lowest_price'] = $fields['lowest_price'] * 100;

        return $fields;
    }
    /**
     * 网票网
     *
     * @param [type] $data
     * @return void
     */
    // static function formatField($data){
    //     $coord = explode(',',$data['coord']);
    //     $fields = array(
    //         'id'                    => $data['ID'],
    //         'type'                  => $data['Type'],
    //         'brand_id'              => $data['CinemaLine'],
    //         'hall_count'            => $data['HallCount'],
    //         'longitude'             => $coord[0] ?? 0,
    //         'latitude'              => $coord[1] ?? 0,
    //         'cinema_name'           => $data['Name'],
    //         'phone'                 => $data['Tel'],
    //         'city_code'             => $data['CityID'],
    //         'trade_area'            => $data['TradeArea'],
    //         'subway'                => $data['SubWay'],
    //         'dist_id'               => $data['DistID'],
    //         'address'               => $data['Address'],
    //         'lowest_price'          => $data['LowestPrice'] ?? 0,
    //         'std_cinema_code'       => $data['StdCinemaCode'] ?? '',
    //         // 'show_time'             => $data['ID'],
    //         // 'created_at'            => $data['ID'],
    //         // 'updated_at'            => $data['ID'],
    //     );

    //     $fields['lowest_price'] = $fields['lowest_price'] * 100;

    //     return $fields;
    // }

    public function brand(){
        return $this->hasOne('\App\ApiModels\Wangpiao\CinemasBrand','id','brand_id');
    }

    /**
     * 影院搜索
     *
     * @param [type] $query
     * @param string $cityId  城市
     * @param string $distId  区域
     * @param string $brandId 品牌/院线
     * @param string $subway  地铁
     * @param string $trade_area 商圈
     * @return void
     */
    public function scopeSearch($query,$cityId = '',$distId = '',$brandId = '',$subway = '',$trade_area = '',$regionname = ''){
        $a=$regionname;
        return $query->when($regionname=='',function($query) use ($cityId){
            return $query->where('city_code',$cityId);
        })->when($regionname!=='',function($query) use ($a){
            return $query->where('address','like',"%".$a."%");
        })->when($distId,function($query,$distId){
            return $query->where('dist_id',$distId);
        })->when($brandId !=='',function($query) use ($brandId){
            return $query->where('brand_id',$brandId);
        })->when($subway,function($query,$subway){
            // return $query->whereRaw(DB::raw(" find_in_set(subway,'{$subway}')"));
            return $query->whereRaw("find_in_set(subway,'?')",[intval($subway)]);
        })->when($trade_area,function($query,$trade_area){
            // return $query->whereRaw(DB::raw(" find_in_set(trade_area,'{$trade_area}')"));
            return $query->whereRaw("find_in_set(trade_area,'?')",[intval($trade_area)]);
        });
    }


    public function getLowestPriceAttribute($value){
        return $value / 100;
    }

}
