<?php
namespace App\ApiModels\Wangpiao;

use App\Traits\ApiTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
/**
 * 有放映计划的影院
 */
class ScheduleCinema extends Model
{
    use ApiTrait;
    protected $table = 'wp_schedule_cinema';
    protected $fillable = [];
    protected $guarded = [];
    
    /**
     * 放映计划的影院数据
     *
     * @param [type] $data  影院数据
     * @param [type] $film_id  电影id
     * @param [type] $date  查询时间戳
     * @return array
     */    
    static function syncData(array $data,int $film_id,string $date){
        $showdate = strtotime(date('Y-m-d',$date));
        $nowtime = date('Y-m-d H:i:s');
        foreach($data as &$item){
            $item['FilmID'] = $film_id;
            $item['ShowTime'] = $date;
            $item['ShowDate'] = $showdate;
            $item = self::formatField($item);
            $item['created_at'] = $item['updated_at'] = $nowtime;
        }
        try {
            ScheduleCinema::upsert($data,['cinema_name','updated_at']);
        } catch (\Throwable $th) {
            return [];
        }
        foreach($data as &$item){
            $item['lowest_price'] =  $item['lowest_price'] / 100;
            $item['show_time'] = date('Y-m-d H:i:s', $item['show_time']);
            // $item['id'] = $item['cinema_id'];
            $item = (Object)$item;
        }
        return $data;
    }

    /**
     * 队列同步
     *
     * @param [type] $data
     * @return void
     */
    static function saveData($data){
        $show_time = $data['show_time'];        
        $apiResult = (array) \App\Support\WpApi::getCinemaQueryList($data['cityId'],date('Y-m-d H:i:s',$show_time),$data['film_id']);
        ScheduleCinema::syncData($apiResult,$data['film_id'],$data['show_time']);
        self::delaySync($data);
    }

    /**
     * 定时同步
     *
     * @param [type] $data
     * @return void
     */
    static function delaySync($data){
        //20分钟同步一次
        // $show_time = $data['show_time'];
        $show_time = time();
        $lasttime = strtotime(date('Y-m-d 23:00:00'));
        if($show_time > $lasttime){
            return false;
        }
        \App\Jobs\Wangpiao\ScheduleCinemaJob::dispatch($data)->delay(now()->addMinutes(\App\Support\WpApi::DELAY_TIME));
    }

    static function formatField($data){
        $coord = explode(',',$data['coord']);
        $fields = array(
            'city_id'         => $data['CityID'],
            'film_id'         => $data['FilmID'] ?? 0,
            'brand_id'        => $data['CinemaLine'] ?? 0,
            'longitude'       => $coord[0] ?? 0,
            'latitude'        => $coord[1] ?? 0,
            'show_time'       => $data['ShowTime'],
            'show_date'       => $data['ShowDate'],
            'dist_id'         => $data['DistID'],
            'cinema_name'     => $data['Name'],
            'trade_area'      => $data['TradeArea'],
            'subway'          => $data['SubWay'],
            'address'         => $data['Address'],
            'cinema_id'       => $data['ID'],
            'lowest_price'    => $data['LowestPrice'] * 100,
        );
        return $fields;
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
    public function scopeSearch($query,$cityId = '',$distId = '',$brandId = '',$subway = '',$trade_area = ''){
        return $query->when($cityId,function($query,$cityId){
            return $query->where('city_id',$cityId);
        })
        ->when($distId,function($query,$distId){
            return $query->where('dist_id',$distId);
        })->when($brandId !=='',function($query) use ($brandId){
            return $query->where('brand_id',$brandId);
        })->when($subway,function($query,$subway){
            return $query->whereRaw(DB::raw(" find_in_set(subway,'{$subway}')"));
        })->when($trade_area,function($query,$trade_area){
            return $query->whereRaw(DB::raw(" find_in_set(trade_area,'{$trade_area}')"));
        });
    }
    public function scopeFilm($query,$film_id){
        return $query->where('film_id',$film_id);
    }
    public function scopeShowTime($query,$showtime){
        return $query->where('show_time',$showtime);
    }
    public function getLowestPriceAttribute($value){
        return $value / 100;
    }
    public function getShowTimeAttribute($value){
        return date('Y-m-d H:i:s',$value);
    }
}
