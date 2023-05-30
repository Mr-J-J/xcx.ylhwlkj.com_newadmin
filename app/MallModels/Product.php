<?php

namespace App\MallModels;
use App\Support\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use \Encore\Admin\Traits\Resizable;

    protected $attributes = [
        'tags_id'=>''
    ];

    /**
     * 下架
     */
    const STATE_UNSALE = 0;
    /**
     * 上架
     */
    const STATE_SALE = 1;

    /**
     * 卡券
     */
    const CARD = 1;
    /**
     * 实物
     */
    const GOODS = 2;

    const OLCARD = 3; //影城卡

    static $type = [
        self::CARD=>"卡券商品",
        self::GOODS=>'实物商品(需要发货)',
        self::OLCARD=>'影城卡'
    ];
    protected $table = 'mall_product';

    public function category(){
        return $this->hasOne('App\MallModels\Category','id','category_id');
    }
    public function content(){
        return $this->hasOne('App\MallModels\ProductContent','product_id','id');
    }

    public function store(){
        return $this->hasOne('App\MallModels\Store','id','store_id');
    }

    public function images(){
        return $this->hasMany('App\MallModels\ProductImages','product_id','id');
    }

    public function sku(){
        return $this->hasMany('App\MallModels\ProductSku','product_id','id');
    }

    public function scopeSale($query){
        return $query->where('state',self::STATE_SALE);
    }

    public function scopeCity($query,$cityId){
        return $query->when($cityId,function($query,$cityId){
            return $query->where('city_id',$cityId);
        });
    }

    public function scopeCategory($query,int $cateId){
        return $query->where('category_id',$cateId);
    }

    public function scopeTitle($query,string $keyword = ''){
        if(empty($keyword)) return $query;
        return $query->where('title','like',"%{$keyword}%");
    }

    public function scopeTag($query,int $tagId){
        return $query->whereRaw(DB::raw("find_in_set({$tagId},tags_id)"));
    }

    public function getImageAttribute($value){
        // return Helpers::formatPath($value,'admin');
        return Helpers::formatPath($this->thumbnail('thumb280', 'image'),'admin');
    }

    protected static function boot(){
        parent::boot();
        self::deleting(function($product){

            $product->sku()->delete();
            $product->content()->delete();
            $product->images()->delete();
        });
    }
}
