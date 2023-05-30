<?php

namespace App\Models;

use App\ApiModels\Wangpiao\Cinema;
use App\Models\store\StoreOfferOrder;
use Illuminate\Database\Eloquent\Model;
use App\ApiModels\Wangpiao\CinemasBrand;

/**
 * 竞价订单展示规则
 */
class OrderRules extends Model
{
    protected $table = 'order_rules';

    
    /**
     * 创建订单展示规则
     *
     * @param StoreOfferOrder $offerOrder
     * @return void
     */
    static function createOrderRules(StoreOfferOrder $offerOrder){
        $orderRules = new OrderRules();

        $orderRules->order_no = $offerOrder->order_no;

        $orderRules->store_id = 0;
        $orderRules->brand_id = $offerOrder->brand_id;
        $orderRules->level_id = 0; //商家等级id
        $brand = CinemasBrand::where('id',$offerOrder->brand_id)->first();
        if(!empty($brand)){
            $orderRules->level_id = implode(',',$brand->levels_id);
        }
        $cinema = Cinema::where('id',$offerOrder->cinema_id)->first();
        $orderRules->cinema_id    = $cinema->id;
        $orderRules->city_id      = $cinema->city_code;
        $orderRules->cinema_name  = $offerOrder->cinemas;
        $orderRules->hall_name    = $offerOrder->halls;
        $orderRules->film_name    = $offerOrder->movie_name;
        $orderRules->love_flag    = $offerOrder->seat_flag;
        $orderRules->accept_seat  = $offerOrder->accept_seats;
        $orderRules->seat_count   = $offerOrder->ticket_count;
        $orderRules->market_price = $offerOrder->market_price;
        $orderRules->save();
    }



    /**
     * 已竞价过的删除规则不再展示
     *
     * @param StoreOfferOrder $offerOrder
     * @return void
     */
    static function deleteByOrderNo(StoreOfferOrder $offerOrder){
        if($offerOrder->offer_status == 0){
            return false;
        }
        self::where('order_no',$offerOrder->order_no)->delete();
    }


    public function setMarketPriceAttribute($value){
        $this->attributes['market_price'] = $value * 100;
    }

    public function getMarketPriceAttribute($value){
        return $value / 100;
    }

    public function scopeSearch($query){
        return $query;
    }
}
