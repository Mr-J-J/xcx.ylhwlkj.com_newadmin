<?php

namespace App\ApiModels\Wangpiao;

use App\Support\Helpers;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

class CinemasBrand extends Model
{
    use ApiTrait;
    protected $table = 'wp_cinemas_brands';
    protected $hidden = ['created_at','updated_at'];

    protected $fillable  = [];
    protected $guarded = [];
    protected $appends = [];

    static function syncData($data){
        $nowtime = date('Y-m-d H:i:s');
        foreach($data as &$item){
            $item = self::formatField($item);
            $item['created_at'] = $item['updated_at'] = $nowtime;
        }
        CinemasBrand::upsert($data,['brand_name','updated_at']);
        return $data;
    }
    
    static function formatField($data){        
        $fields = array(
            'id'          => $data['ID'],
            'brand_name'  => $data['Name'],
        );
        return $fields;
    }
    /**
     * 品牌下拉选项
     *
     * @return void
     */
    public static function brandsOptions(){
        $list = self::select('id','brand_name')->get();
        return $list;
    }
    // public function getLevelsAttribute(){
    //     $brand_id = $this->attributes['levels'];
    //     $levelArr = BrandBindsLevel::where('brand_id',$brand_id)->get('level_id');
    //     return array_column($levelArr->toArray(),'level_id');
    // }

    // public function setLevelsAttribute($value){
    //     unset($this->attributes['levels']);
    // }
    public function calcDiscountMoney($price){
        $discount = (int)$this->discount;
        if(!$discount){
            $discount = intval(Helpers::getSetting('card_discount'));
        }
        return  round($price * $discount / 100,2);
    }
    public function cinemas(){
        return $this->hasMany('\App\ApiModels\Wangpiao\Cinema','brand_id');
    }

    public function setLevelsIdAttribute($value){
        $this->attributes['levels_id'] = implode(',',$value);
    }
    
    public function setDiscountAttribute($value){
        $value = round($value,2);
        $value = $value > 100?100:$value;
        $this->attributes['discount'] = $value;
    }
    
    public function setTehuiPriceRateAttribute($value){
        $value = round($value,2);
        $value = $value > 10?10:$value;
        $this->attributes['tehui_price_rate'] = $value;
    }

    public function setPriceDiscountRateAttribute($value){
        $value = round($value,2);
        $value = $value > 10?10:$value;
        $this->attributes['price_discount_rate'] = $value;
    }

    public function setOfferPriceAttribute($value){
        $value = intval($value);
        $value = $value > 100?100:$value;
        $this->attributes['offer_price'] = $value;
    }

    public function getLevelsIdAttribute($value){
        return explode(',',$value);
    }
    public function levels(){
        return $this->hasMany('\App\Models\store\StoreLevel','id','levels_id');
    }


    public function store(){
        return $this->hasOne('\App\Models\Store','id','store_id');
    }

    
}
