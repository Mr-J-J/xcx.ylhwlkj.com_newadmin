<?php

namespace App\MallModels;

use Illuminate\Database\Eloquent\Model;

class OrderGoods extends Model
{
    protected $table = 'mall_orders_goods';


    /**
     * 订单商品
     *
     * @param Order $order
     * @param ProductSku $sku
     * @return void
     */
    static function createOrderGoods(Order $order,ProductSku $sku){
        $og = new self;
        $og->order_id = $order->id;
        $og->user_id = $order->user_id;
        $og->product_id = $sku->product_id;
        $og->sku_id = $sku->id;

        $product = $sku->product;

        // $content = array(
        //     'sku_content' => $product->content->sku_content, //sku套餐内容
        //     'tips' => $product->content->tips, //温馨提示
        //     'use_tips'=> $product->content->use_tips, //使用须知
        // );
        $og->content = $product->content->tips;
        $og->save();
    }
}
