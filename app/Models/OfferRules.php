<?php

namespace App\Models;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class OfferRules extends Model
{
    protected $table = 'store_offer_rules';

    public static function ruleList(Store $store,$rule_id = ''){
        $list = [];
        if($rule_id > 0){
            return self::where('store_id',$store->id)->where('id',$rule_id)->first();
        }
        $list = self::where('store_id',$store->id)->get();
        return $list;
    }
    /**
     * 添加报价规则
     *
     * @param [type] $data
     * @param Store $store
     * @return void
     */
    public static function saveRules($data,Store $store){
        if(empty($data['offer_value'])){
            Helpers::exception('请设置报价模式');
        }
        $data['offer_type'] = intval($data['offer_type'])?:1;
        if($data['offer_type'] == 1 ){ //比例的报价
            $data['offer_value'] = round($data['offer_value'],2);
        }else{
            //
            $data['offer_value'] = round($data['offer_value'],2) ;
        }

        $mode = new OfferRules;
        if(!empty($data['id'])){
            $mode = OfferRules::find($data['id']);
        }
        $mode->store_id = $store->id;
        $mode->market_left  = round($data['market_left'],2);
        $mode->market_right = round($data['market_right'],2);
        $mode->contain_cinema   = !empty($data['contain_cinema'])?$data['contain_cinema']:'';
        $mode->un_contain_cinema    = !empty($data['un_contain_cinema'])?$data['un_contain_cinema']:'';
        $mode->contain_movie    = !empty($data['contain_movie'])?$data['contain_movie']:'';
        $mode->un_contain_movie = !empty($data['un_contain_movie'])?$data['un_contain_movie']:'';
        $mode->contain_hall = !empty($data['contain_hall'])?$data['contain_hall']:'';
        $mode->un_contain_hall  = !empty($data['un_contain_hall'])?$data['un_contain_hall']:'';
        $mode->lovers_seat  = (int)$data['lovers_seat'];
        $mode->accept_seats = (int)$data['accept_seats'];
        $mode->seats_number = (int)$data['seats_number'];
        $mode->offer_type   = (int)$data['offer_type'];
        $mode->offer_value  = round($data['offer_value'],2);
        $mode->state    = (bool)intval($data['state']);
        
        try {
            $mode->save();
        } catch (\Exception $e) {
            throw $e;
            // Helpers::exception('添加报价模式');
        }
    }


    public function setMarketLeftAttribute($value){
        $this->attributes['market_left'] = round(floatval($value),2) * 100; //分
    }

    public function setMarketRightAttribute($value){
        $this->attributes['market_right'] = round(floatval($value),2) * 100; //分
    }

    public function getMarketLeftAttribute($value){
        if(!$value){
            return '不限';
        }
        return $value / 100;
    }

    public function getMarketRightAttribute($value){
        if(!$value){
            return '不限';
        }
        return $value / 100;
    }

    public function setLoversSeatAttribute($value){
        $this->attributes['lovers_seat'] = (int)boolval($value);
    }
    
    public function setAcceptSeatsAttribute($value){
        $this->attributes['accept_seats'] = (int)boolval($value);
    }

    public function setSeatsNumberAttribute($value){
        $this->attributes['seats_number'] = min(2,intval($value));
    }
    //1 比例 2市场价- 3最高价- 4固定金额
    public function setOfferTypeAttribute($value){        
        $this->attributes['offer_type'] = min(4,intval($value));
    }

    // contain_cinema
    // un_contain_cinema
    // contain_movie
    // un_contain_movie
    // contain_hall
    // un_contain_hall

}
