<?php

namespace App\CardModels;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

/**
 * 影旅配置
 */
class CardSetting extends Model
{
    protected $table = 'rs_card_setting';

    protected $hidden = ['created_at','updated_at'];
    
    protected $casts = [
        'use_rules'=>'json'
    ];

    static function getSetting(int $id = 1){
        return self::where('id',$id)->first();
    }

    public function getUseRulesAttribute($value){
        return array_values(json_decode($value, true) ?: []);
    }
    public function setUseRulesAttribute($value){
        $this->attributes['use_rules'] = json_encode(array_values($value),256);
    }
    
}
