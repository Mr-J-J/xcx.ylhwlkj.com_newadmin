<?php

namespace App\Http\Controllers\MiniPro;

use App\CardModels\OlCardOrder;
use App\MallModels\Order;
use App\MallModels\Product;
use App\Models\UserOrder;
use Illuminate\Http\Request;
use App\MallModels\ProductSku;
use App\MallModels\UserAddress;
use App\MallModels\OrderCheckLogs;
use App\MallModels\ProductComment;
use App\MallModels\ProductPurchase;
use Illuminate\Support\Facades\DB;

/**
 * 吃喝玩乐订单
 */
class MallOrderController extends UserBaseController
{
    /**
     * 订单数量统计
     *
     * @return void
     */
    public function orderStatistics(){
        $mallOrderCount = Order::select([DB::raw('count(*) as total'),'order_status'])
                            ->where('user_id',$this->user->id)
                            ->whereIn('order_status',[Order::NOUSE,Order::NOPAY])
                            ->groupBy('order_status')
                            ->pluck('total','order_status')
                            ->toArray();
        $ticketOrderCount = UserOrder::select([DB::raw('count(*) as total'),'order_status'])
                                ->where('user_id',$this->user->id)
                                ->whereIn('order_status',[10,20])
                                ->groupBy('order_status')
                                ->pluck('total','order_status')
                                ->toArray();

        $statistics = array(
            // 状态值 => 数量
            'ticket'=>array(
                'nopay'=> $ticketOrderCount[10]??0,
                'nouse'=> $ticketOrderCount[20]??0,
            ),
            'mall'=>array(
                'nopay'=>$mallOrderCount[Order::NOPAY] ?? 0,
                'nouse'=>$mallOrderCount[Order::NOUSE] ?? 0,
            ),
        );
        return $this->success('',$statistics);
    }
    /**
     * 订单列表
     *
     * @param Request $requst
     * @return void
     */
    public function orderList(Request $request){
        // 0全部 1待付款 2待使用 3 已完成  4待评价 5退款订单
        $status = (int)$request->input('status',0);
        $limit = (int)$request->input('limit',10);

        $statusArr = array();
        $comment = 0;
        switch($status){
            case 1:
                $statusArr[] = Order::NOPAY;
                break;
            case 2:
                $statusArr[] = Order::NOUSE;
                break;
            case 3:
                $statusArr = [Order::SUCCESS,Order::EXPIRE];
                break;
            case 4:
                $comment = 1;
                $statusArr[] = Order::SUCCESS;
                break;
            case 5:
                $statusArr = [Order::REFUNDING,Order::REFUND_OK,Order::REFUND_FAIL];
                break;
        }

        $list = Order::when($statusArr,function($query,$statusArr){
                    return $query->whereIn('order_status',$statusArr);
                })
                ->when($comment,function($query,$comment){
                    return $query->where('is_comment',0);
                })
                ->where('user_id',$this->user->id)
                ->latest()
                ->paginate($limit,['order_sn','order_status','product_title','product_info','expire_time','created_at','pay_money']);
        foreach($list as $order){
            $order->status_txt = Order::$status[$order->order_status];
            if($status == 4){
                $order->status_txt = '待评价';
            }
            $order->product_info = json_decode($order->product_info);
        }
        return $this->success('',$list);
    }

    /**
     * 订单详情
     *
     * @param Request $request
     * @return void
     */
    public function orderInfo(Request $request){
        $orderNo = $request->input('order_no','');
        $order_info = Order::getOrderByNo($orderNo);
        if(empty($order_info)){
            return $this->error('订单已删除或不存在');
        }
        // if($order_info->user_id != $this->user->id){
        //     return $this->error('订单已删除或不存在');
        // }
        $order_info->order_amount = $order_info->pay_money;
        $order_info = $order_info->makeHidden(['checkCode','store_id','goods','product_info','need_deliver','express_id','transaction_id','agreement','remark']);
        $order_info->status_txt = Order::$status[$order_info->order_status];
        if($order_info->check_start_time){
            $order_info->check_start_time = date('Y.m.d',$order_info->check_start_time);
        }
        if($order_info->check_end_time){
            $order_info->check_end_time = date('Y.m.d',$order_info->check_end_time);
        }
        $sku = json_decode($order_info->product_info);
        if($sku){
            $sku->title = $order_info->product_title;
        }
        $content = $order_info->goods->content;
        $check_code = $order_info->checkCode;
        require_once app_path('Support/phpQrCode.php');
        if($check_code){
            foreach($check_code as $item){
                $item->makeHidden(['id','order_id','user_id','product_id','sku_id','check_money','order_amount']);
                $item->check_start_time = $order_info->check_start_time;
                $item->check_end_time = $order_info->check_end_time;
                $item->status_txt = Order::$status[$order_info->order_status];
                ob_start();//开启缓冲区
                \QRcode::png($item->code,false,1,6,1);
                $img = ob_get_contents();//获取缓冲区内容
                ob_end_clean();//清除缓冲区内容
                header("Content-type:text/html;");
                $item->qrcode = "data:image/png;base64,".base64_encode($img);//转base64
                $item->check_logs = OrderCheckLogs::getCheckLogs($item->code,$item->order_id);
            }
        }


        return $this->success('',compact('order_info','sku','content','check_code'));
    }


    /**
     * 订单信息确认
     *
     * @param Request $request
     * @return void
     */
    public function confirmOrder(Request $request){
        $skuId = $request->input('sku_id',0);
        $number = $request->input('number',1);
        $number = max(1,(int)$number);
        $sku = ProductSku::where('id',$skuId)->first();
        $product = $sku->product;
        if(empty($sku) || empty($product)){
            return $this->error('商品已下架');
        }

        if($sku->storage < $number){
            return $this->error('库存不足');
        }

        if($sku->limit_purchase){ //是否限购
            $checkReturn = ProductPurchase::checkPurchaseNumber($this->user->id,$sku->id,$sku->limit_purchase,$number);
            if(!$checkReturn){
                return $this->error("每人最多购买{$sku->limit_purchase}个");
            }
        }

        $address = UserAddress::select(['receive_name','phone','address'])->where('user_id',$this->user->id)->first();
        $peisong = Product::select(['peisong','tuihuo','baoyou'])->where('id',$product->id)->first();
        $discountMoney = Order::getDiscountMoney($sku,$this->user,$number);
        $outInfo = array(
            'sku_id'=>$sku->id,
            'product_id'=>$sku->product_id,
            'title'=>$product->title,
            'sku_title'=>$sku->title,
            'number'=>$number,
            'type'=>$product->type,
            'receive_name'=> $address?$address->receive_name:'',
            'phone'=> $address?$address->phone:'',
            'address'=> $address?$address->address:'',
            'need_address'=> $product->type == 2,
            'image'=>$product->image,
            'content'=>$product->content, //使用须知
            'price'=> $sku->price * $number,
            'order_amount'=> $discountMoney,
            'peisong'=>$peisong->peisong,
            'tuihuo'=>$peisong->tuihuo,
            'baoyou'=>$peisong->baoyou
        );
        return $this->success('',$outInfo);
    }
    /**
     * 创建订单
     *
     * @param Request $request
     * @return void
     */
    public function createOrder(Request $request){
        $skuId = $request->input('sku_id',0);
        $number = $request->input('number',1);
        $number = max(1,(int)$number);
        $agreement = (bool)$request->input('agreement',0);
        $remark = $request->input('remark','');
        $receive_name = $request->input('receive_name','');
        $phone = $request->input('phone','');
        $address = $request->input('address','');
        $sku = ProductSku::where('id',$skuId)->first();
        $product = $sku->product;
        if(empty($sku) || empty($product)){
            return $this->error('商品已下架');
        }
        if($sku->storage < $number){
            return $this->error('库存不足');
        }

        if($sku->limit_purchase){ //是否限购
            $checkReturn = ProductPurchase::checkPurchaseNumber($this->user->id,$sku->id,$sku->limit_purchase,$number);
            if(!$checkReturn){
                return $this->error("每人最多购买{$sku->limit_purchase}个");
            }
        }

        if(!$agreement){
            if($product->type == 3){
                return $this->error('请先同意勾选《影城卡购买协议》');
            }
            return $this->error('请先同意勾选《商品退款协议》');
        }


        if($product->type == 2 && empty($address)){ //实物商品
            return $this->error('请填写收货地址');
        }

        if($product->type == 3){
            $newOrder = OlCardOrder::createOrder($this->user,$sku,$number,$phone,$agreement,trim($remark));
        }else{
            $userAddress = UserAddress::createAddress($this->user,['receive_name'=>$receive_name,'phone'=>$phone,'address'=>$address]);
            $newOrder = Order::createOrder($this->user,$sku,$userAddress,$number,$agreement,trim($remark));
        }
        if(empty($newOrder)){
            return $this->error('订单创建失败');
        }
        $pay_param = array();
        try {
            $pay_param = $this->getPayInfo($newOrder,$product->type == 3);
            if(empty($pay_param)){
                throw new \Exception('支付参数错误');
            }
        } catch (\Throwable $th) {
            //throw $th;
            logger($th->getMessage());
            return $this->error('订单支付失败'.$th->getMessage(),['order_no'=>$newOrder->order_sn,'type'=>$product->type]);
        }
        $pay_param['order_no']= $newOrder->order_sn;
        $pay_param['type']= $product->type;
        return $this->success('创建成功',$pay_param);
    }

    /**
     * 订单付款
     *
     * @param Reuest $request
     * @return void
     */
    public function payOrder(Request $request){
        $orderNo = $request->input('order_no','');

        $order = Order::getOrderByNo($orderNo);
        if(empty($order)){
            return $this->error('订单不存在');
        }
        if($order->order_status != 10){
            return $this->error('支付失败');
        }
        $pay_param = array();
        try {
            $pay_param = $this->getPayInfo($order);
            if(empty($pay_param)){
                throw new \Exception('支付参数错误');
            }
        } catch (\Throwable $th) {
            //throw $th;
            return $this->error('订单支付失败'.$th->getMessage(),['order_no'=>$order->order_sn]);
        }
        $pay_param['order_no']= $order->order_sn;
        return $this->success('',$pay_param);
    }

    protected function getPayInfo($orderInfo,int $notifyType = 0){
        if($orderInfo->pay_money == 0) return false;
        $app = (Object) $this->getApp(3,$this->user->com_id);
        $comId = $notifyType?6:$this->user->com_id;
        $payResult = $app->order->unify([
            'body' => $orderInfo->product_title,
            'out_trade_no' => $orderInfo->order_sn,
            'total_fee' => round($orderInfo->pay_money * 100),
            'notify_url' => route('mallnotify',['comId'=>$comId]), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', //请对应换成你的支付方式对应的值类型
            'openid' => $this->user->openid,
        ]);
        if(empty($payResult['prepay_id'])){
            logger(json_encode($payResult));
            return false;
        }
        $pay_param = $app->jssdk->bridgeConfig($payResult['prepay_id'], false);
        return $pay_param;
    }


    /**
     * 订单取消
     *
     * @param Request $request
     * @return void
     */
    public function cancelOrder(Request $request){
        $orderNo = $request->input('order_no','');

        $order = Order::getOrderByNo($orderNo);
        if(empty($order)){
            return $this->error('订单不存在');
        }
        if(!$order->canCancel()){
            return $this->error('订单取消失败');
        }
        $order->cancelOrder();
        return $this->success('订单已取消');
    }


    /**
     * 订单退款
     *
     * @param Request $request
     * @return void
     */
    public function refundOrder(Request $request){
        $orderNo = $request->input('order_no','');
        $order = Order::getOrderByNo($orderNo);
        if(empty($order)){
            return $this->error('订单不存在');
        }
        $order->refundOrder();
        return $this->success('退款申请已提交');
    }

    /**
     * 订单评价
     *
     * @param Request $request
     * @return void
     */
    public function orderComment(Request $request){
        $orderNo = $request->input('order_no','');

        $order = Order::getOrderByNo($orderNo);
        if(empty($order)) {
            return $this->error('订单不存在');
        }
        if(!$order->canComment()){
            return $this->error(Order::$status[$order->order_status].'订单不能评价');
        }

        $data = $request->only(['rate','rate_txt','content','images']);

        $comment = ProductComment::creatComment($this->user,$order,$data);
        $id = $comment->id;
        return $this->success('感谢您的评价!',compact('id'));
    }
}
