<?php
namespace App\CardModels;

use App\Support\Helpers;
use App\Models\TicketUser;
use App\MallModels\ProductSku;
use Illuminate\Support\Facades\DB;
use App\MallModels\ProductPurchase;
use Illuminate\Database\Eloquent\Model;

/**
 * 影城卡兑换条件设置
 */
class OlCardOrder extends Model
{
        
    protected $table = 'ol_card_orders';
    
    const CANCEL = 0;
    const NOPAY = 10;
    const SUCCESS = 20;
    static $status = [
        self::CANCEL=>'已取消',
        self::NOPAY=>'待付款',
        self::SUCCESS=>'已付款'
    ];
    
    protected $fillable = ['order_sn','user_id','com_id','card_id','number','tips','order_amount','mobile','order_status','expire_time','transaction_id','card_info','card_money'];
    
    /**
     * 创建订单
     *
     * @param TicketUser $user
     * @return App\MallModels\Order
     */
    static function createOrder(TicketUser $user,ProductSku $sku,int $number,string $phone,int $agreement ,$remark = ''){
        
        $order = new self;
        DB::beginTransaction();
        try {
            $order->order_sn = Helpers::makeOrderNo('YC');
            $order->user_id = $user->id;
            $order->product_id = $sku->product_id;
            $order->store_id = $sku->product->store_id;
            $order->sku_id = $sku->id;
            $order->product_title = $sku->product->title;

            $total = $sku->price * $number;
            // $discount = self::getDiscountMoney($sku,$user,$number);
            $order->order_amount = $total; //总价
            $order->discount_money = 0; //优惠金额 
            $order->pay_money = $total ; //支付金额 
            $order->goods_count = $number;
            $product = $sku->product;
            $skuInfo = array(
                'sku_title'=> $sku->title,
                'number'=> $number,
                'market_price' => $sku->market_price,
                'price'=> $sku->price,
                'image'=>$sku->product->image,
                'order_amount'=>$order->pay_money,
            );
            $order->product_info = json_encode($skuInfo);            
            $order->need_deliver = 0;                        
            $order->check_start_time = strtotime($product->check_start); //核销截止日期
            $order->check_end_time = strtotime($product->check_end); //核销截止日期                        
            $order->mobile = $phone;
            $order->agreement = $agreement;
            $order->tips = $product->content->tips;
            $delay = (int)self::getDelayTime('order_pay_ttl');
            $order->expire_time = time() + $delay;            
            $order->user_remark = $remark;            
            $order->order_status = self::NOPAY;            
            $order->save();

            //减库存
            $newStorage = $sku->storage - $number;
            if($newStorage < 0){
                throw new \Exception('库存不足');
            }
            $sku->storage = $newStorage;
            $sku->save();
            //加入限购
            if($sku->limit_purchase){
                ProductPurchase::updatePurchase($user->id,$sku->id,$number);
            }
            
        } catch (\Throwable $th) {
            Helpers::exception('订单创建失败:'.$th->getMessage());
        }
        DB::commit();       

        return $order;
    }

    /**
     * 支付成功
     *
     * @param array $params
     * @return Boolean
     */
    public function paySuccess(array $params= []){
        $order =$this;
        if($order->order_status != self::NOPAY){  //不在待付款状态
            return false;
        }
        DB::beginTransaction();
        try {
            $order->order_status = self::SUCCESS; //支付成功、待使用
            $order->transaction_id = $params['transaction_id']??'';
            
            $order->save();
            //计算订单佣金
            // \App\Models\user\Commision::clacCommision($order);
            $user = TicketUser::where('id',$order->user_id)->first();
            $user->calcCashMoney($order->pay_money);                        
            //更新销量
            ProductSku::updateSaleNum($order->product_id,$order->sku_id,$order->goods_count);
            $model = new OlCard;
            $model->card_no = $model->createNo();
            $model->card_key = $model->createKey();
            
            $product = OlCardProduct::where('id',$order->product_id)->first();
            $model->product_id = $product->id;
            $model->expire_time = strtotime($product->check_end);
            $model->start_time = strtotime($product->check_start);
            $model->brand_ids = $product->rules->brand_ids;
            $model->cinema_ids = $product->rules->cinema_ids;
            $model->user_id = $order->user_id;
            $model->open_time = time();
            $model->type = 1;
            $model->state = 10;
            $model->number = $product->rules->number;
            $model->save();

        } catch (\Throwable $th) {
            logger('PaySuccess Error:'.$th->getMessage().':'.json_encode($order->toArray()));
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }
    /**
     * 订单取消
     *
     * @return void
     */
    public function cancelOrder(){
        $this->order_status = CardOrder::CANCEL;
        $this->save();
    }
    /**
     * 是否可以取消
     *
     * @return boolean
     */
    public function canCancel(){
        return $this->order_status == CardOrder::NOPAY;
    }
    
    static function getOrderByNo($orderNo){
        return self::where('order_sn',$orderNo)->first();
    }
    /**
     * 获取订单超时时间
     *
     * @param [type] $key
     * @return void
     */
    static function getDelayTime($key){
        $ttl = (int)Helpers::getSetting($key);//分钟
        return $ttl * 60;
    }
        
}
