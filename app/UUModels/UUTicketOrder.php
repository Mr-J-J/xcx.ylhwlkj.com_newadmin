<?php

namespace App\UUModels;


use App\Support\Helpers;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通数据类
 */
class UUTicketOrder extends Model
{
    use ApiTrait;
    protected $table = 'pw_ticket_order';
    // protected $guarded = [];
    protected $fillable = [
        'order_no',
        'user_id',
        'UUmember',
        'UUordernum',
        'UUlid',
        'UUtid',
        'UUpid',
        'UUbegintime',
        'UUendtime',
        'UUtnum',
        'UUtprice',
        'UUordername',
        'UUordertel',
        'UUpersonid',
        'UUstatus',
        'UUsalerid',
        'UUdtime',
        'UUtotalmoney',
        'UUpaymode',
        'UUordermode',
        'UUctime',
        'UUcode',
        'UUcontacttel',
        'UUaid',
        'UUifpack',
        'UUpack_order',
        'UUsmserror',
        'UUrefund_num',
        'UUverified_num',
        'UUorigin_num',
        'UUlprice',
        'UUplaytime',
        'UUpay_status',
        'UUconcat_id',
        'UUseries',
        'UUmemo',
        'UUltitle',
        'UUp_type',
        'UUttitle',
        'UUMprice',
        'UUdname',
        'UUtnum_cancel',
        'UUtnum_used',
        'UUifprint',
        'UUgetaddr',
        'buy_price',
        'ticket_id',
        'order_amount',
        'transaction_id',
        'order_status',
        'refund_remark',
    ];


    /**
     * 预下单
     *
     * @return model
     */
    static function preOrder(UUScenicSpotInfo $spotInfo, UUScenicSpotTicket $ticket, $user_id, $startDate, $contacttel, $ordertel, $ordername,$personId='', $remark = '')
    {
        $orderNo = Helpers::makeOrderNo('P');
        $checked = false;
        while (!$checked) {
            $count = UUTicketOrder::where('order_no', $orderNo)->count();
            if ($count) {
                $orderNo = Helpers::makeOrderNo('P');
            } else {
                $checked = true;
            }
        }



        $order = UUTicketOrder::create(array(
            'order_no' => $orderNo,
            'user_id' => $user_id,
            'UUlid' => $ticket->UUlid, //产品id
            'UUtid' => $ticket->UUid, //门票id
            'UUpid' => $ticket->UUpid, //价格id
            'UUaid' => $ticket->UUaid, //供应商
            'UUplaytime' => $startDate,
            'UUltitle' => $spotInfo->UUtitle,
            'UUttitle' => $ticket->UUtitle,
            'UUp_type' => $spotInfo->UUp_type,
            'buy_price' => $ticket->buy_price,
            'UUorigin_num' => $ticket->buy_number,
            'UUtnum' => $ticket->buy_number,
            'UUlprice' => $ticket->retail_price * 100,
            'UUMprice' => $ticket->UUtprice,
            'UUpersonid'=>$personId,
            'UUmemo' => $remark,
            'UUtotalmoney' => $ticket->retail_price * $ticket->buy_number * 100,
            'UUcontacttel' => $contacttel,
            'UUordername' => $ordername,
            'UUordertel' => $ordertel,
            'order_status'=> 10,
            'order_amount'=>$ticket->retail_price * $ticket->buy_number * 100,
        ));
        return $order;
    }


    /**
     * 支付成功
     *
     * @param UserOrder $order
     * @param string $transaction_id
     * @return model
     */
    // -1无效订单 10待付款 20已付款 30交易完成 40退款 41退款中 42退款失败
    function paySuccess($transaction_id = '')
    {
        $order = $this;
        $order->order_status = 20; //支付成功待出票
        $order->transaction_id = $transaction_id;
        // $order->expire_time = 0;
        $order->save();
        UUTicketSaleNum::addSaleNum($order->UUtid,$order->UUlid,$order->UUtnum);
        
        return $order;
    }

    /**
     * 票付通下单
     *
     * @return void
     */
    function PFT_Order_Submit()
    {
        $order = $this;
        $api = \App\Support\SoapApi::getInstance();
        // $ticket = UUScenicSpotTicket::getDetail($order->ticket_id);
        $api->UUlid = $order->UUlid; //产品id,对应 Get_Ticket_List.UUlid
        $api->UUid = $order->UUtid; //门票id
        $api->UUaid = $order->UUaid; //供应商id
        $api->orderNo = $order->order_no; //贵方订单号,请确保唯一
        $api->tprice = $order->buy_price; //供应商配置的结算单价，单位：分
        $api->tnum = $order->UUtnum; //购买数量
        $api->playtime = $order->UUplaytime; //游玩日期
        $api->ordername = $order->UUordername; //客户姓名,多个用英文逗号隔开，不支持特殊符号
        $api->ordertel = $order->UUcontacttel; //取票人手机号
        $api->contactTEL = $order->UUcontacttel; //多个用英文逗号隔开，不支持特殊符
        $api->personID = $order->UUpersonid; //身份证,
        $api->smsSend = 0; //0 -票付通发送短信 1-票付通不发短信（前提是票属性上有勾选下单成功发短信给游客）
        $api->paymode = 0; //扣款方式（0使用账户余额2使用供应商处余额4现场支付
        $api->ordermode = 0; //下单方式（0正常下单1手机用户下单）
        $api->assembly = ''; //集合地点 线路时需要，参数必传，值可传输空
        $api->series = ''; //团号 线路，演出时需要，参数必传，值可传输空； 演出需要时传输格式：json_encode(array(int)场馆id,(int)场次id,(string)分区id));        
        $api->concatID = 0; //联票ID
        $api->pCode = 0; //套票ID
        $api->orderRemark = $order->remark; //备注
        $api->OrderCallbackUrl = ''; //核销/退票回调地址

        $apiResult =  $api->PFT_Order_Submit();

        if (!$apiResult['status']) {
            return false;
        }
        if (!empty($apiResult['data'])) {
            $returnData = $apiResult['data'];
            $order->UUordernum = $returnData['UUordernum'];
            $order->UUcode = json_encode(['code' => $returnData['UUcode'], 'qrcodeURL' => $returnData['UUqrcodeURL'], 'qrcodeIMG' => $returnData['UUqrcodeIMG']]);
            $order->order_status = 20;
            $order->save();
        }
        return $order;
    }

    /**
     * 发送消费码
     *
     * @param [type] $returnData
     * @return void
     */
    function outTicket($returnData)
    {
        // $returnData = $apiResult['data'];
        $order = $this;
        $order->UUordernum = $returnData['UUordernum'];
        $order->UUcode = json_encode(['code' => $returnData['UUcode'], 'qrcodeURL' => $returnData['UUqrcodeURL'], 'qrcodeIMG' => $returnData['UUqrcodeIMG']]);
        $order->order_status = 20;
        $order->save();
    }



    /**
     * 查询并更新订单信息
     *
     * @return void
     */
    function PFT_OrderQuery()
    {
        $order = $this;
        $api = \App\Support\SoapApi::getInstance();
        $api->remoteOrdernum = $order->order_no;
        $api->pftOrdernum = $order->UUordernum;
        $apiResult = $api->OrderQuery();
        if ($apiResult === false) {
            return false;
        }
        try {
            unset($apiResult['@attributes']);
            unset($apiResult['UUremotenum'], $apiResult['UUordertime']);
            // $apiResult['UUpaymode'] = $apiResult['UUpmode'];
            // unset($apiResult['UUpmode']);

            if (empty($order->UUcode)) {
                $apiResult['UUcode'] = json_encode(['code' => $apiResult['UUcode'], 'qrcodeURL' => '', 'qrcodeIMG' => '']);
            }else{
                unset($apiResult['UUcode']);
            }
            
            // if(strtotime($apiResult['UUctime']) < 0){
            //     unset($apiResult['UUctime']);
            // }
            if(empty($apiResult['UUctime'])){
                $apiResult['UUctime'] = '';
            }
            if(empty($apiResult['UUdtime'])){
                $apiResult['UUdtime'] = '';
            }
            if (strtotime($apiResult['UUctime']) < 0) {
                unset($apiResult['UUctime']);
            }
            if (strtotime($apiResult['UUdtime']) < 0) {
                unset($apiResult['UUdtime']);
            }
            $apiResult['order_status'] = 20;
            $order->update($apiResult);
        } catch (\Throwable $th) {
            logger('订单查询错误 PFT_OrderQuery：' . $th->getMessage());
            return false;
        }
        return $order;
    }

    /**
     * 订单核销通知客人线下消费后触发的通知，
     *
     * @param [type] $OrderState
     * @param [type] $ActionTime
     * @param [type] $Tnumber 本次消费数量
     * @param [type] $AllCheckNum 总计消费数量	
     * @param [type] $Source
     * @param [type] $Action
     * @return void
     */
    function memberCheckSuccess($orderState, $ActionTime, $Tnumber, $AllCheckNum, $Source, $Action)
    {
        $order = $this;
        if($AllCheckNum == $order->UUtnum){
            $orderState = 1;
        }
        $order->UUverified_num = $AllCheckNum;
        $order->UUstatus = $orderState;
        // if($orderState == 8){
        //     $order->order_status = 30;
        // }
        $order->save();
    }

    /**
     * 订单退款
     *
     * @param [type] $Refundtype 1-同意退票 2-拒绝退票
     * @return model
     */
    function refundOrder($num,$refundAmount,$refundFee,$refundNo = '',$localRemark = '')
    {
        $order = $this;
        $refundNo = $refundNo?:Helpers::makeOrderNo('R');
        $canUseNum = $this->getOrderTnum();
        if($canUseNum < $num){
            return false;
        }
        $limitNum = max(0,$canUseNum - $num);
        $array = [
            'user_id'=>$order->user_id,
            'refund_no'=>$refundNo,
            'Order16U'=>$order->UUordernum,
            'order_no'=>$order->order_no,
            'order_id'=>$order->id,
            'ActionTime'=>'',
            'refundNum'=>$num,
            'Tnumber'=>$limitNum,
            'Refundtype'=>0,
            'remark'=>'',
            'AllCheckNum'=>$order->UUverified_num,
            'RefundAmount'=>$refundAmount,
            'refund_money'=>$order->order_amount,
            'RefundFee'=>$refundFee,
            'Source'=>0,
            'local_remark'=>$localRemark,
            'state'=>0,
        ];
        $refundOrder =  UUOrderRefund::create($array);
        $order->UUrefund_num = $order->UUrefund_num+$num;
        // $order->UUtnum = $limitNum;
        $order->refund_status = 1;
        $order->save();
        
        return $refundOrder;
    }
    
    /**
     * 拒绝退单
     *
     * @return int
     */
    function refuseRefundOrder($refundOrder){
        $order =  $this;
        $order->UUrefund_num = max(0, $order->UUrefund_num - $refundOrder->refundNum);
        $order->order_status = 42;
        $order->save();
    }

    /**
     * 同意退款
     *
     * @param [type] $refundOrder
     * @return void
     */
    function agreeRefundOrder($refundOrder){
        $order = $this;
        
        if($refundOrder->state > 0){
            return false;
        }        
        //TODO 退款
        
        if(empty($order->transaction_id)){
            $refundOrder->local_remark = '无需退款';
            $refundOrder->state = 1;
            $refundOrder->save();
            if(!$this->getOrderTnum()){
                $order->order_status = 30;
                $order->save();
            }
            return false;
        }
        $orderFee = $refundOrder->refund_money;
        if(!$orderFee){
            $refundOrder->local_remark = $refundOrder->local_remark . '可退款金额为0';
            $refundOrder->save();
            return false;
        }
        logger('agreeRefundOrder:同意退款'.$refundOrder->order_no);
        $config = config('wechat.payment.default');
        $app = \EasyWeChat\Factory::payment($config);
        $refundNo = 'REFUND-' . $order->order_no;        
        // $orderFee = 1;
        try {
            $result = $app->refund->byTransactionId($order->transaction_id,$refundNo, $orderFee, $orderFee, [
                // 可在此处传入其他参数，详细参数见微信支付文档
                'notify_url'=> route('pwrefundnotify'),
                'refund_desc' => '票务订单退款:'.$order->order_no,
            ]);
            if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'FAIL'){                    
                $order->refund_remark = $result['err_code_des'];            
                $refundOrder->state = 2; //退款失败
                $refundOrder->local_remark = $refundOrder->local_remark . $result['err_code_des'];
                $refundOrder->save();
                return true;
            }
        } catch (\Throwable $th) {
            return false;
        }
        $refundOrder->state = 1; //退款成功
        $refundOrder->save();
        if(!$this->getOrderTnum()){
            $order->order_status = 30;
            $order->save();
        }
        return true;
    }
   
    /**
     * 获取可用数量
     *
     * @return int
     */
    function getOrderTnum(){
        $order = $this;
        $num = $order->UUorigin_num - $order->UUrefund_num - $order->UUverified_num;
        return max(0,$num);
    }
    /**
     * 订单状态
     *
     * @return void
     */
    function getStatusTxt()
    {
        $status = $this->order_status;
        $useStatus = $this->UUstatus;

        if ($status <= 0) {
            return '已取消';
        } elseif ($status == 10) {
            return '待付款';
        } elseif ($status == 20 && empty($this->UUcode)) {
            return '待出票';
        } elseif ($status == 21) {
            return '待出票';
        }elseif ($status == 20 && $useStatus == 0) {
            return '待使用';
        } elseif ($status == 20 && $useStatus == 1) {
            return '已使用';
        } elseif ($status == 20 && $useStatus == 7) {
            return '部分使用';
        } elseif ($status == 30) {
            return '已完成';
        }elseif ($status == 40) {
            return '退款完成';
        }elseif ($status == 41) {
            return '退款中';
        }elseif ($status == 42) {
            return '退款失败';
        }
        return '';
    }
    
    function getStatusTxtWithLabel()
    {
        $status = $this->order_status;
        $useStatus = $this->UUstatus;

        if ($status <= 0) {
            return "<span class='label label-default'>已取消</span>";
        } elseif ($status == 10) {
            return "<span class='label label-default'>待付款</span>";;
        } elseif ($status == 20 && empty($this->UUcode)) {
            return "<span class='label label-warning'>待出票</span>";
        } elseif ($status == 21) {
            return "<span class='label label-warning'>待出票</span>";
        } elseif ($status == 20 && $useStatus == 0) {
            return "<span class='label label-warning'>待使用</span>";
        } elseif ($status == 20 && $useStatus == 1) {
            return "<span class='label label-success'>已使用</span>";
        } elseif ($status == 20 && $useStatus == 7) {
            return "<span class='label label-success'>部分使用</span>";
        } elseif ($status == 30) {
            return "<span class='label label-success'>已完成</span>";
        }elseif ($status == 40) {
            return "<span class='label label-success'>已退款</span>";
        }elseif ($status == 41) {
            return "<span class='label label-success'>退款中</span>";
        }elseif ($status == 42) {
            return "<span class='label label-success'>退款失败</span>";
        }
        return '';
    }
    /**
     * 订单状态筛选
     *
     * @param [type] $query
     * @param integer $status
     * @return void
     */
    public function scopeOrderStatus($query, int $status)
    {
        $queryState = $query;
        switch ($status) {
            case 1: //待付款
                $queryState = $query->where('order_status', 10);
                break;
            case 2: //未使用
                $queryState = $query->where('order_status', 30)->where('UUstatus', 0);
                break;
            case 3:
                $queryState = $query->where('order_status', 30)->where('UUstatus', 8);
        }
        return $queryState;
    }

    static function getOrderByOrderNo($orderNo)
    {
        return self::where('order_no', $orderNo)->first();
    }
    
    public function user(){
        return $this->belongsTo('\App\Models\TicketUser','user_id');
    }
}
