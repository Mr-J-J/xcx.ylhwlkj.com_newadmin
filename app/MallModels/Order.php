<?php

namespace App\MallModels;

use Illuminate\Support\Facades\DB;
use App\Models\TicketUser;
use App\Models\UserPayDetail;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'mall_orders';   
    
    const CANCEL = 0;
    const NOPAY = 10;
    const NOUSE = 20;
    const SUCCESS = 30;
    const EXPIRE = 31;
    const REFUND_OK = 50;
    const REFUNDING = 51;
    const REFUND_FAIL = 52;

    static $status = array(
        self::CANCEL=>'已取消',
        self::NOPAY=>'待付款',
        self::NOUSE=>'待使用',        
        self::SUCCESS=>'已完成',
        self::EXPIRE=>'已过期',
        self::REFUND_OK=>'已退款',
        self::REFUNDING=>'退款中',
        self::REFUND_FAIL=> '退款失败'
    );
    /**
     * 创建订单
     *
     * @param TicketUser $user
     * @return App\MallModels\Order
     */
    static function createOrder(TicketUser $user,ProductSku $sku,UserAddress $address,int $number,int $agreement ,$remark = ''){
        
        $order = new self;
        DB::beginTransaction();
        try {
            $order->order_sn = Helpers::makeOrderNo('KQ');
            $order->user_id = $user->id;
            $order->product_id = $sku->product_id;
            $order->store_id = $sku->product->store_id;
            $order->sku_id = $sku->id;
            $order->product_title = $sku->product->title;

            $total = $sku->price * $number;
            $discount = self::getDiscountMoney($sku,$user,$number);
            $order->order_amount = $total; //总价
            $order->discount_money = $discount; //优惠金额 
            $order->pay_money = $total - $discount;; //支付金额 
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
            $order->need_deliver = (int)($product->type == 2);                        
            $order->check_start_time = strtotime($product->check_start); //核销截止日期
            $order->check_end_time = strtotime($product->check_end); //核销截止日期
            $order->area = $address->province.' '. $address->city.' '.$address->area;
            $order->address = $address->address;
            $order->mobile = $address->phone;
            $order->receive_name = $address->receive_name;
            $order->agreement = $agreement;
            $order->is_comment = 0;
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
            //订单商品
            OrderGoods::createOrderGoods($order,$sku);
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
            $order->order_status = self::NOUSE; //支付成功、待使用
            $order->transaction_id = $params['transaction_id']??'';
            if($order->need_deliver){ //需要发货
                $oe = OrderExpress::createExpress($order);
                $order->express_id = $oe->id;                
            }else{  //不用发货核销
                OrderCheckCode::createCheckCode($order);
            }
            $order->save();
            //计算订单佣金
            \App\Models\user\Commision::clacCommision($order);
            $user = TicketUser::where('id',$order->user_id)->first();
            $user->calcCashMoney($order->pay_money);
            UserPayDetail::addDetail($user,$order);
            //更新销量
            ProductSku::updateSaleNum($order->product_id,$order->sku_id,$order->goods_count);

            //更新商家销售额
            $store = Stores::getStore($order->store_id);
            if(!empty($store)){
                $store->setSaleMoney($order->order_amount);
            }

        } catch (\Throwable $th) {
            logger('PaySuccess Error:'.$th->getMessage().':'.json_encode($order->toArray()));
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 订单退款
     *
     * @return void
     */
    public function refundOrder(){
        $order =$this;
        // if($order->order_status != self::NOUSE){
        //     Helpers::exception('当前订单不能退款');
        // }
        $hasUsed = (int)OrderCheckCode::codeIsUsed($order);
        if($hasUsed){
            Helpers::exception('当前订单不能退款');
        }
        if(empty($order->transaction_id)){
            Helpers::exception('微信支付订单号不存在');
        }
        
        $refundMoney = round($order->pay_money * 100);
        if(!$refundMoney){
            Helpers::exception('可退款金额为0');
        }
        $config = config('wechat.payment.default');
        $app = \EasyWeChat\Factory::payment($config);
        $refundNo = 'REFUND-' . $order->getOrderNo();  
        $result = $app->refund->byTransactionId($order->transaction_id,$refundNo, $refundMoney, $refundMoney, [
            'notify_url'=> route('mallrefundnotify'),
            'refund_desc' => '商城订单退款',
        ]);  
        if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'FAIL'){                    
            $order->remark = $result['err_code_des'];   
            $order->save();
            Helpers::exception('退款申请失败');
        }
        $order->order_status = self::REFUNDING; //退款中
        $order->save();
        return true;        
    }

    /**
     * 退款成功回调
     *
     * @param [type] $notifyParam
     * @return void
     */
    public function refundSuccesss($notifyParam){
        $order = $this;
        DB::beginTransaction();
        try {                        
            $order->order_status = self::REFUND_OK; //退款成功
            $order->refund_no = $notifyParam['out_refund_no']??'';
            $order->save();                  
            //更新商家退款金额
            $store = Stores::getStore($order->store_id);
            if(!empty($store)){
                $store->setRefundMoney($order->pay_money);
            }
            OrderCheckCode::cancelCheckCode($order->id);
        } catch (\Throwable $th) {
            logger('refundOrder Error:'.$th->getMessage().':'.json_encode($order->toArray()));
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 根据会员等级获取优惠金额
     *
     * @param ProductSku $sku
     * @param TicketUser $user
     * @param integer $number
     * @return float
     */
    static function getDiscountMoney(ProductSku $sku,TicketUser $user,int $number){
        $discountRate = Group::getGroupDiscount($user->group_id);
        $amount = $sku->price * $number;
        
        if($discountRate > 0){
            return round($amount - $amount * $discountRate,2);
        }
        return 0;
    }
    
    /**
     * 获得超时订单
     *
     * @return collect
     */
    static function getExpireOrder(){
        return self::where('expire_time','<',time())->where('order_status',self::NOPAY)->get();
    }
    
    /**
     * 根据订单号获取订单信息
     *
     * @param [type] $orderNo
     * @return object
     */
    static function getOrderByNo($orderNo){
        return self::where('order_sn',$orderNo)->first();
    }


    /**
     * 是否可以核销
     *
     * @return boolean
     */
    public function canCheck(){
        $order = $this;        
        return in_array($order->order_status,[self::NOUSE,self::SUCCESS]);
    }

    /**
     * 更改订单状态为已评价
     *
     * @return void
     */
    public function commentComplate(){
        $order = $this;
        $order->order_status = self::SUCCESS; 
        $order->is_comment = 1;//已评价
        $order->save();
    }

    /**
     * 更改订单状态为已完成
     *
     * @return void
     */
    public function updateOrderToComplate(){
        $order = $this;
        $order->order_status = self::SUCCESS; //已完成
        $order->save();
        TicketUser::where('id',$order->user_id)->increment('cash_money',$order->pay_money);
    }
    
    /**
     * 是否可以取消
     *
     * @return bool
     */
    public function canCancel(){
        return $this->order_status == self::NOPAY;
    }

    /**
     * 订单取消
     *
     * @return void
     */
    public function cancelOrder(){
        $order = $this;
        if($order->order_status != Order::NOPAY) return;
        DB::transaction(function () use ($order) {
            $order->order_status = Order::CANCEL;
            $order->save();
            $sku = ProductSku::where('id',$order->sku_id)->first();
            $sku->storage += $order->goods_count;
            $sku->save();
            if($sku->is_default){
                
            }
            //撤回限购的数量
            if($sku->limit_purchase){
                ProductPurchase::cancelPurchase($order->user_id,$order->sku_id,$order->goods_count);
            }
        });
    }

    /**
     * 是否可以评价
     *
     * @return bool
     */
    public function canComment(){
        return ($this->order_status == self::SUCCESS && !$this->is_comment);
    }

    /**
     * 是否可以计算佣金
     *
     * @return boolean
     */
    public function canCommision(){
        return $this->order_status == self::NOUSE;
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

    /**
     * 订单号
     *
     * @return void
     */
    public function getOrderNo(){
        return $this->order_sn;
    }

    
    /**
     * 获取订单分佣总额
     *
     * @return float
     */
    public function getTotalCommisionMoney(){        
        $skuInfo = ProductSku::where('id',$this->sku_id)->first();
        if(empty($skuInfo)) return 0;
        return $skuInfo->commision_money;
    }

    public function getOrderAmount(){
        return $this->order_amount;
    }

    /**
     * 佣金结算时间
     *
     * @return void
     */
    public function getCommisionTime(){
        return 0;
    }

    public function goods(){
        return $this->hasOne('App\MallModels\OrderGoods','order_id','id');
    }

    public function checkCode(){
        return $this->hasMany('App\MallModels\OrderCheckCode','order_id','id');
    }
    
}
