<?php

namespace App\ApiModels\Wangpiao;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

class TradingArea extends Model
{
    use ApiTrait;
    
    protected $table = 'wp_trading_area';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];


    public function scopeCity($query,$cityId){
        return $query->where('cid',$cityId);
    }
}
