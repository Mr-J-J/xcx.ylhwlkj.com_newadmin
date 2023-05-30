<?php

namespace App\MallModels;

use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    protected $table = 'mall_product_sku';
    protected $hidden = ['created_at','updated_at'];
    protected $fillable = [];
    protected $guarded = [];

    /**
     * 更新sku\product销量
     *
     * @param integer $productId
     * @param integer $skuId
     * @param integer $salenum
     * @return void
     */
    static function updateSaleNum(int $productId,int $skuId,int $salenum = 1){
        $product = Product::where('id',$productId)->first();
        ProductSku::where('id',$skuId)->increment('sale_num',$salenum);
        if($product->sku_id == $skuId){
            $product->increment('sale_num',$salenum);
        }
    }
    public function product(){
        return $this->belongsTo('App\MallModels\Product','product_id','id');
    }


}
