<?php
namespace App\CardModels;


use Illuminate\Database\Eloquent\Model;

/**
 * 影城卡兑换条件设置
 */
class OlCardRules extends Model
{
        
    protected $table = 'ol_card_rules';
    protected $hidden = ['updated_at'];

    public function setBrandIdsAttribute($value){
        $this->attributes['brand_ids'] = implode(',', $value);
    }

    public function getBrandIdsAttribute($value){
        return explode(',', $value);
    }

    public function setCinemaIdsAttribute($value){
        $this->attributes['cinema_ids'] = implode(',', $value);
    }

    public function getCinemaIdsAttribute($value){
        return explode(',', $value);
    }
    
}
