<?php
namespace App\Http\Controllers\MiniPro;
use Cache;

use App\Models\Setting;
use App\Support\Helpers;
use Illuminate\Http\Request;
use App\ApiModels\Wangpiao as Api;
use Illuminate\Support\Facades\DB;
/**
 * 网票网开放接口
 */
class PftController extends UserBaseController
{
    /**
     * 申请退票
     *
     * @param Request $request
     * @return void
     */
    public function pw_order_refund(Request $request){
        $orderNo = $request->input('order_no','');
        $refundNum = (int)$request->input('num',0);
        if(!$refundNum){
            return $this->error('请填写退票数量');
        }
        $order = \App\UUModels\UUTicketOrder::where('user_id',$this->user->id)
                ->whereRaw('order_no = ?',[$orderNo])
                ->first();
        if(!$order){
            return $this->error('订单信息不存在');
        }
        $refundAmount = 0;
        $refundFee = 0; //退票手续费
        $refundOrder = $order->refundOrder($refundNum,$refundAmount,$refundFee);
        if($refundOrder === false){
            return $this->error('退票申请失败');
        }        
        $refundResult = $refundOrder->requestRefundOrder($order);        
        if($refundResult === false){
            return $this->error('退票申请失败');
        }elseif($refundResult == 2){
            return $this->success('退票申请已提交');
        }        
        return $this->success('退票申请成功',$order);
    }

    /**
     * 订单详情
     *
     * @param Request $request
     * @return void
     */
    public function pw_order_info(Request $request){
        $orderNo = $request->input('order_no','');
        $field = ['order_no','UUltitle','UUttitle','UUtnum','UUorigin_num','UUordername','UUordertel','created_at','order_amount','UUMprice','UUlprice','UUcode'];
        $order = \App\UUModels\UUTicketOrder::select($field)->where('user_id',$this->user->id)
                ->whereRaw('order_no = ?',[$orderNo])
                ->first();
        if(!$order){
            return $this->error('订单信息不存在');
        }
        
        if($order->getOrderTnum() !== 0){
            $result = $order->PFT_OrderQuery();
            if($result !== false){
                $order = $result;
            }
        }
        if(!empty($order->UUcode)){
            $order->UUcode = json_decode($order->UUcode,true);
        }
        $order->UUlprice = round($order->UUlprice/100,2);
        $order->order_amount = round($order->order_amount/100,2);
        return $this->success('',$order);
    }
    
    /**
     * 订单列表
     *
     * @param Request $request
     * @return void
     */
    public function pw_order_list(Request $request){
        $status = (int)$request->input('status',0); // 0 全部 1待付款 2待使用 3已完成
        $limit = (int)$request->input('limit',10);
        $list = \App\UUModels\UUTicketOrder::where('user_id',$this->user->id)
                ->select(['order_no','user_id','order_amount','UUttitle','UUtnum','UUcode','UUlprice','created_at','order_status'])
                // ->when($whereLike,function($query,$whereLike) use ($whereLikeSql){
                //     return $query->whereRaw('('.implode(' or ',$whereLikeSql).')',$whereLike);
                // })
                // ->when($city,function($query,$city){
                //     return $query->whereRaw("UUarea like ?","%$city%");
                // })
                ->when($status,function($query,$status){
                    if($status == 2)
                    {
                        return $query->where('order_status',$status * 10)->where('UUcode','<>','');
                    }
                    return $query->where('order_status',$status * 10);
                })
                ->latest()
                ->simplePaginate($limit);

        $list->transform(function($item){
            $item->UUlprice = round($item->UUlprice / 100,2);
            $item->status_txt = $item->getStatusTxt();
            return $item;
        });

        return $this->success('',$list);
    }
    /**
     * 订单支付【合并支付】
     *
     * @param Request $request
     * @return void
     */
    public function pw_pay_order(Request $request){
        $payNo = trim($request->input('order_no',''));
        $user_id = $this->user->id;
        $payOrder = \App\UUModels\UUPayOrder::where('pay_no',$payNo)->where('user_id',$user_id)->first();
        if(!$payOrder){
            return $this->error('订单不存在');
        }
        
        $total_fee = $payOrder->pay_money;
        // if($user_id == 1){
        //     $total_fee = 0;
        // }
        if($total_fee > 0 ){
            $app = $this->getApp(3);
            $payResult = $app->order->unify([
                'body' => '影旅汇票务',
                'out_trade_no' => $payOrder->pay_no,                    
                'total_fee' => $payOrder->pay_money,                    
                'notify_url' => route('pwnotify'), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'trade_type' => 'JSAPI', //请对应换成你的支付方式对应的值类型
                'openid' => $this->user->openid,
            ]);
            if(empty($payResult['prepay_id'])){
                logger('订单创建失败 '.json_encode($payResult,256));
            }
            $pay_param = $app->jssdk->bridgeConfig($payResult['prepay_id'], false);
            return $this->success('创建成功',['pay_no'=>$payOrder->pay_no,'pay_param'=>$pay_param]);
       }
        $payOrder->paySuccess();
        return $this->success('支付成功',['pay_no'=>$payOrder->pay_no,'pay_param'=>array()]);
    }

    public function pw_get_payorder(Request $request){
        $payNo = trim($request->input('order_no',''));
        $user_id = $this->user->id;;
        $payOrder = \App\UUModels\UUPayOrder::select(['user_id','pay_no','pay_status','pay_money','expire_time'])->where('pay_no',$payNo)->where('user_id',$user_id)->first();
        if(!$payOrder){
            return $this->error('订单不存在');
        }
        return $this->success('',$payOrder);
    }

    /**
     * 预下单【需要登录了】
     *
     * @param Request $order
     * @return void
     */
    public function pw_pre_order(Request $request){
        $spotId = (int)$request->input('id','');
        $ticket_ids = $request->input('ticket_ids',''); //门票id
        $startDate = $request->input('date','');
        $personId = $request->input('personId','');
        $personId = ($personId != 'null')?$personId:'';
        $ordername = $request->input('ordername',''); //游客姓名
        $ordername = ($ordername != '') ?$ordername:'';
        $ordertel = $request->input('ordertel',''); 
        $contacttel =  $request->input('contacttel','');//取票人手机号
        $numbers = $request->input('number','');
        $remark = $request->input('remark','');
        $remark = ($remark != '')?$remark:'';
        $remark = substr($remark,0,190);
        
        $product = \App\UUModels\UUScenicSpotInfo::getDetail($spotId);
        if(!$product){
            return $this->error('请选择景区');
        }
        
        $ticket_ids_arr = array_filter(explode(',',$ticket_ids),function($v){return !empty($v);});
        $ticketList = \App\UUModels\UUScenicSpotTicket::whereIn('id',$ticket_ids_arr)->where('UUstatus',1)->orderByRaw("field(id,".implode(',',$ticket_ids_arr).")")->get();
        if(empty($ticketList)){
            return $this->error('门票已下架或删除');
        }
        
        if(count($ticket_ids_arr) != count($ticketList)){
            return $this->error('门票信息错误');
        }
        
        $persion_ids_arr = array_filter(explode(',',$personId),function($v){return !empty($v);});
        $ordername_arr = array_filter(explode(',',$ordername),function($v){return !empty($v);});
        $ordertel_arr = array_filter(explode(',',$ordertel),function($v){return !empty($v);});
        $number_arr = array_filter(explode(',',$numbers),function($v){return !empty($v);});
        if(empty($number_arr)){
            return $this->error('请输入购买门票数量');
        }
        if(!preg_match('/^1[3456789]\d{9}$/',$contacttel)){
            return $this->error('取票人手机号码不正确');
        }

        foreach($ticketList as $ticket){
            if($ticket->UUtourist_info == 1 && empty($personId)){
                return $this->error('请填写取票人身份证号');
            }elseif($ticket->UUtourist_info > 1){
                if(array_sum($number_arr) != count($persion_ids_arr)){
                    return $this->error('请填写取票人身份证号');
                }
            }
        }

        if(empty($ordername) || empty($ordertel) ){
            return $this->error('请填写游客信息');
        }
        
        if(empty($numbers)){
            return $this->error('请填写购票数量');
        }
        $datetime = strtotime($startDate);
        if(!$datetime){
            return $this->error('请选择游玩日期1');
        }
        if($datetime < strtotime(date('Y-m-d'))){
            return $this->error('请选择游玩日期2');
        }
               
        $startDate = date('Y-m-d',$datetime);
        
        $allTicket = array();
        foreach($ticketList as $key=>$ticket){
            $storage = $ticket->getPriceList($startDate,$startDate);
            $storage = $storage[0]??array();
            if(empty($storage)){
                return $this->error("{$ticket->UUtitle}暂时无法购买");
            }
            $ticket->buy_price = $storage['buy_price']??0;
            $ticket->retail_price = $storage['retail_price']??0;
            $ticket->storage = $storage['storage']??0;

            if($ticket->storage == 0){
                return $this->error("{$ticket->UUtitle}暂无库存");
            }else if($ticket->storage == -2){
                return $this->error("{$ticket->UUtitle}暂停售票");
            }elseif($ticket->storage> 0 && $ticket->storage < 99999999){
                if($number_arr[$key] > $ticket->storage){
                    return $this->error("{$ticket->UUtitle}库存不足");
                }
            }
            $ticket->buy_number = (int)$number_arr[$key]; 
            $allTicket[$ticket->id] = $ticket;
        }        
        
        if(empty($allTicket)){
            return $this->error('请选择要购买的门票');
        }
             
        foreach($allTicket as $key=>$item){
            $result = $item->OrderPreCheck($item->buy_number,$startDate,$contacttel,$ordername,$personId,$item->buy_price);
            
            if($result !== true){
                $msg = '服务异常，请稍候重新尝试';
                if(!empty($result['msg'])){
                    $msg = $result['msg'].':'.$result['code'];
                }
                
                return $this->error($msg);
                break;
            }
        }        
       
        $user_id= $this->user->id;
        DB::beginTransaction();
        try {
            $orderIds = array();
            $totalMoney = 0;
            foreach($allTicket as $ticket){
                $order = \App\UUModels\UUTicketOrder::preOrder($product,$ticket,$user_id,$startDate,$contacttel,$ordertel,$ordername,$personId,$remark);
                if($order){
                    $orderIds[] = $order->id;
                    $totalMoney+= $order->order_amount;
                }
            }
            $orderIds = implode(',',$orderIds);
            if(empty($orderIds) && $totalMoney == 0){
                throw new \Exception('订单创建失败_');
            }
            $payOrder = \App\UUModels\UUPayOrder::createPayOrder($orderIds, $totalMoney, $user_id,$totalMoney == 0);
            
        } catch (\Throwable $th) {
            logger('pw_pre_order订单创建失败:'.json_encode($request->all(),256).','.$th->getMessage());
            DB::rollBack();
            return $this->error('订单创建失败');
        }
        DB::commit();
        return $this->success('验证成功',['pay_order_no'=>$payOrder->pay_no,'pay_status'=>$payOrder->pay_status]);
    }
}
