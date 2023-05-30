<?php

namespace App\MallModels;

use App\Models\TicketUser;
use Illuminate\Support\Facades\DB;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class OrderCheckCode extends Model
{
    protected $table = 'mall_orders_checkcode';
    protected $hidden = ['created_at','updated_at'];
    protected $fillable = ['code','order_id','product_id','sku_id','user_id','check_money','check_number','used_number','state','check_end_time'];

    /**
     * 创建核销码
     *
     * @param Order $order
     * @param ProductSku $sku
     * @return OrderCheckCode
     */
    static function createCheckCode(Order $order){
        $skuInfo = ProductSku::where('id',$order->sku_id)->first();
        $data = array();
        for($i=0;$i<$order->goods_count;$i++){
            $id = $order->id+$i;
            $id = ($id << 2) ^ 0x1020ACEF;
            $arr = array(
                'code'=> $id,
                'order_id'=> $order->id,
                'product_id'=> $order->product_id,
                'sku_id'=> $order->sku_id,
                'user_id'=> $order->user_id,
                'check_money'=> $skuInfo->check_price,
                'check_number'=> $skuInfo->check_number,
                'used_number'=> 0,
                'state'=> 0,
                'order_amount'=> $order->order_amount,
                'check_end_time'=> $order->check_end_time,
            );
            $data[] = OrderCheckCode::updateOrCreate(['code'=>$id,'order_id'=>$order->id],$arr);
        }
        return $data;
    }

    /**
     * 核销
     *
     * @param [type] $number
     * @return void
     */
    public function checkCode(int $number = 1,TicketUser $store){
        $info = $this;
        $order = Order::where('id',$info->order_id)->where('store_id',$store->id)->first();
        if(empty($order)){
            Helpers::exception('核销码无效');
        }
        $product = Product::where('id',$order->product_id)->first();
        if(strtotime($product->check_start) > time()){
            Helpers::exception('请在规定时间内使用');
        }
        if(time() > $order->check_end_time && $order->check_end_time > 0){
            Helpers::exception('核销码已过期');
        }
        if($info->state == 1 || !$order->canCheck()){
            Helpers::exception('核销码无效');
        }
        $limit_number = $info->check_number - $info->used_number;
        if($limit_number < 1){
            Helpers::exception('核销码已使用');
        }
        if($number > $limit_number){
            Helpers::exception('核销数量不能大于剩余数量');
        }
        DB::transaction(function () use ($info,$order,$number,$store){
            $info->used_number = $new_number = $info->used_number + $number;
            if($new_number == $info->check_number){
                $info->state = 1;
                $order->updateOrderToComplate();
            }
            $info->save();
            OrderCheckList::createRecord($order,$info,$store->id,$number);
            $storeInfo = Stores::where('user_id',$store->id)->first();
            if($storeInfo){
                $check_money = $info->check_money * $number;
                $storeInfo->increment('freeze_money',$check_money);
            }
        });
    }
    /***
     * 核销码是否已使用
     */
    static function codeIsUsed(Order $order){
        return self::where('order_id',$order->id)->where('used_number','>',0)->count();
    }
    static function getCheckEndCode(){
        return self::whereBetween('check_end_time',[1,time()])->where('state',0)->get();
    }

    /**
     * 作废核销码
     *
     * @return void
     */
    static function cancelCheckCode(int $order_id){
        OrderCheckCode::where('order_id',$order_id)->update(['state'=>1]);
    }

}
