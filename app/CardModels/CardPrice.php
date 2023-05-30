<?php
namespace App\CardModels;
use Illuminate\Database\Eloquent\Model;
/**
 * 影旅卡价格
 */
class CardPrice extends Model
{
    protected $table = 'rs_card_price';
    protected $hidden = ['created_at','updated_at'];
    protected $fillable = ['com_id','card_id','price'];
    
    /**
     * 分销商影旅卡列表
     *
     * @param integer $comId
     * @param integer $cardId
     * @return void
     */
    static function getCardPrice(int $comId){
        $list = CardPrice::where('com_id',$comId)->get()->map(function($price){
                        return $price->only(['card_id','price']);
                    })->pluck('price','card_id')->toArray();                                        
        return $list;
    }
    /**
     * 添加修改商城价格
     *
     * @param integer $comId  分销商id
     * @param Cards $cardInfo
     * @param float $saleprice
     * @return void
     */
    static function editCardPrice(int $comId,Cards $cardInfo,float $saleprice){
        // if(!$saleprice) return false;        
        $cardPrice = CardPrice::updateOrCreate(['com_id'=>$comId,'card_id'=>$cardInfo->id],array(
            'com_id'=>$comId,
            'card_id'=>$cardInfo->id,
            'price'=> $saleprice
        ));
        return $cardPrice;
    }
    
    
    /**
     * 查询单张影旅卡价格
     *
     * @param integer $comId
     * @param integer $cardId
     * @param [type] $cardPrice
     * @return void
     */
    static function getCardPriceById(int $comId,int $cardId,&$cardPrice){        
        $list = self::getCardPrice($comId);
        if($cardId){
            $cardPrice = 0;
            if(!empty($list[$cardId])){
                $cardPrice = $list[$cardId];                
            }
        }
    }
}
