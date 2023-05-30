<?php

namespace App\ApiModels\Wangpiao;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

class SubWay extends Model
{
    use ApiTrait;
    
    protected $table = 'wp_subway';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];



    public function scopeCity($query,$cityId){
        return $query->where('cid',$cityId);
    }
}
