<?php

namespace App\Http\Controllers\MiniPro;

use App\CardModels\UserWallet;
use App\Models\UserOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\store\OfferServices;

/**
 * 支付回调
 */
class NotifyController extends Controller
{
    public function index(Request $req,int $comId = 0){

        //用户订单支付成功
        $app =  (Object) $this->getApp(3,$comId);
        $response = $app->handlePaidNotify(function($message, $fail){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = UserOrder::getOrderByOrderNo($message['out_trade_no']);
        
            if (empty($order) || $order->pay_status ==2) { // 如果订单不存在 或者 订单已经支付过了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
        
            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////
        
            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if ($message['result_code'] === 'SUCCESS') {                    
                    $order->paySuccess($order,$message['transaction_id']);
                // 用户支付失败
                } elseif ($message['result_code'] === 'FAIL') {
                    // $order->status = 'paid_fail';
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
        
            // $order->save(); // 保存订单
        
            return true; // 返回处理完成
        });
        
        return $response;
    }


    public function refundBackNotify(Request $request,int $comId = 0){          
        $app =  (Object) $this->getApp(3,$comId);
        $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) {
            // 其中 $message['req_info'] 获取到的是加密信息
            // $reqInfo 为 message['req_info'] 解密后的信息
            // 你的业务逻辑...
            logger(\json_encode($reqInfo,256).json_encode($message,256));
            
            $order = UserOrder::where('order_no',$reqInfo['out_trade_no'])->first();
 
            if (!$order || $order->refund_status == 2) { // 如果订单不存在 或者 订单已经退过款了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            if($message['return_code']=='SUCCESS'){
                $update_data = [];
                if($reqInfo['refund_status']=='SUCCESS'){
                   $order->refund_status = 2;
                   $order->refund_no = $reqInfo['out_refund_no'];
                   $order->save();
                   $offerService = new OfferServices;
                   $offerService->closeOffer($order);
                   UserWallet::walletBackBalance($order);
                   return true;
                }else{
                    $order->refund_status = 3;
                }                 
            }
            $order->save();
            logger($order->order_no.'退款失败'.json_encode($reqInfo,256));
            $fail('退款失败');            
        });
        
        $response->send(); // Laravel 
    }

    /**
     * 商城退款回调
     *
     * @param Request $request
     * @return void
     */
    public function mallRefundNotify(Request $request,int $comId = 0){          
        $app =  (Object) $this->getApp(3,$comId);
        $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) {
            // 其中 $message['req_info'] 获取到的是加密信息
            // $reqInfo 为 message['req_info'] 解密后的信息
            // 你的业务逻辑...
            logger(\json_encode($reqInfo,256).json_encode($message,256));
            
            $order = \App\MallModels\Order::getOrderByNo($reqInfo['out_trade_no']);
 
            if (!$order || $order->order_status != \App\MallModels\Order::REFUNDING) { // 如果订单不存在 或者 订单已经退过款了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            if($message['return_code']=='SUCCESS'){                
                if($reqInfo['refund_status']=='SUCCESS'){
                   $order->refundSuccesss($reqInfo);
                   return true;
                }else{
                    $order->order_status = \App\MallModels\Order::REFUND_FAIL;
                    $order->save();
                }                 
            }
            logger($order->getOrderNo().'退款失败'.json_encode($reqInfo,256));
            $fail('退款失败');            
        });
        
        $response->send(); // Laravel 
    }

    public function mallNotify(Request $request,int $comId = 0){
        //用户订单支付成功   
        $app =  (Object) $this->getApp(3,$comId);
        $response = $app->handlePaidNotify(function($message, $fail) use($comId){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            if($comId == 6){ //影城卡回调
                $order = \App\CardModels\OlCardOrder::getOrderByNo($message['out_trade_no']);        
                if (empty($order) || $order->order_status != \App\CardModels\OlCardOrder::NOPAY) { // 如果订单不存在 或者 订单已经支付过了
                    return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
                }
            }else{
                $order = \App\MallModels\Order::getOrderByNo($message['out_trade_no']);        
                if (empty($order) || $order->order_status != \App\MallModels\Order::NOPAY) { // 如果订单不存在 或者 订单已经支付过了
                    return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
                }
            }
            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////        
            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if ($message['result_code'] === 'SUCCESS') {                    
                    $order->paySuccess($message);
                // 用户支付失败
                } elseif ($message['result_code'] === 'FAIL') {
                    // $order->status = 'paid_fail';
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
        
            // $order->save(); // 保存订单
        
            return true; // 返回处理完成
        });
        
        return $response;
    }
    

    public function cardNotify(Request $request,int $comId = 0){
        //用户订单支付成功   
        $app =  (Object) $this->getApp(3,$comId);
        $response = $app->handlePaidNotify(function($message, $fail){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = \App\CardModels\CardOrder::getOrderByNo($message['out_trade_no']);        
            if (empty($order) || $order->order_status != \App\CardModels\CardOrder::NOPAY) { // 如果订单不存在 或者 订单已经支付过了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }        
            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////        
            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if ($message['result_code'] === 'SUCCESS') {                    
                    $order->paySuccess($message);
                // 用户支付失败
                } elseif ($message['result_code'] === 'FAIL') {
                    // $order->status = 'paid_fail';
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
        
            // $order->save(); // 保存订单
        
            return true; // 返回处理完成
        });
        
        return $response;
    }
    
    
    public function pwNotify(Request $request){
        //用户订单支付成功   
        $app =  (Object) $this->getApp(3);
        $response = $app->handlePaidNotify(function($message, $fail){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            logger('票务支付成功：'.json_encode($message,256));
            $order = \App\UUModels\UUPayOrder::where('pay_no',$message['out_trade_no'])->first();
            // $order = \App\UUModels\UUTicketOrder::where('order_no',$message['out_trade_no'])->first();
            if (empty($order) || $order->pay_status ==2) { // 如果订单不存在 或者 订单已经支付过了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////        
            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if ($message['result_code'] === 'SUCCESS') {                    
                    $order->paySuccess($message['transaction_id']);
                // 用户支付失败
                } elseif ($message['result_code'] === 'FAIL') {
                    // $order->status = 'paid_fail';
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
        
            // $order->save(); // 保存订单
        
            return true; // 返回处理完成
        });
        
        return $response;
    }
    
    
    //票付通产品变更回调
    public function pwProductNotify(Request $request){
        if($request->isMethod('get')){
            echo 'success';die;
        }
        $apiModel = \App\Support\SoapApi::getInstance();
        $localVerifyCode = $apiModel->getVerifyCode();
        $success = "200";
        $fail = "-1";
        extract($request->all());
        if(empty($VerifyCode) || $VerifyCode != $localVerifyCode){            
            logger('pwProductNotify'."$VerifyCode != $localVerifyCode");
            echo -1;die;
        }
        $product = \App\UUModels\UUScenicSpotInfo::getDetail($UUlid);
        if(empty($product)){
            echo $success;die;
        }
        // 2-产品上架 3-产品下架 5-产品信息变动/价格变动 
        try {
            switch((int)$OrderState){
                case 2:
                    $ticketList = $api->Get_Ticket_List((int)$UUlid);
                    \App\UUModels\UUScenicSpotTicket::saveData($ticketList);
                    break;
                case 3:
                    $ticketId = $UUtids['tid']??0;
                    $priceId = $UUtids['pid']??0;
                    foreach($product->ticketList as $ticket){
                        if($ticket->UUid == $ticketId){
                            $ticket->setTicketOff();
                        }
                    }
                    break;
                case 5:
                    file_put_contents(storage_path('logs/pft-product-'.date('Y-m-d').'.log'),json_encode($request->all(),256)."\n",FILE_APPEND);
                    $product->updateTicketByNotify((int)$UUlid,$UUtids);
                    break;
                default:
                    echo $fail;die;
                    break;
            }
        } catch (\Throwable $th) {
            logger('pwProductNotify:'.$th->getMessage().','.json_encode($request->all(),256));
            echo $fail;die;
        }
        echo $success;die;
    }
    
    //票付通订单回调状态
    public function pwOrderNotify(Request $request){
        if($request->isMethod('get')){
            echo 'success';die;
        }
        $apiModel = \App\Support\SoapApi::getInstance();
        $localVerifyCode = $apiModel->getVerifyCode();
        $success = "200";
        $fail = -1;
        //7ec38b1685b92ae2dda13ae864752896
        $params = $request->all();
        if(is_string($params))
        {
            $params = json_decode($params,true);
        }
        
        
        extract($request->all());
        if(empty($VerifyCode)){
             logger('pwOrderNotify：接口验证失败'."$VerifyCode != $localVerifyCode");
            echo -1;die;
        }
        if($VerifyCode != $localVerifyCode){
            logger('pwOrderNotify：接口验证失败'."$VerifyCode != $localVerifyCode");
            echo -1;die;
        }
        if($OrderState == 2){ //异步返码
            $OrderCall = $remoteOrder;
        }
        
        $order = \App\UUModels\UUTicketOrder::getOrderByOrderNo($OrderCall);
        if(empty($order)){
            logger('订单为空');
            echo $fail;die;
        }        
        switch((int)$OrderState){
            case 2:
                //异步返码
                try { 
                
                    $order->UUordernum = $pftOrder;
                    $returnData = $qrcodeUrlList[0];
                    $order->UUcode = json_encode(['code'=>$code,'qrcodeURL'=>$returnData['qrcodeUrl'],'qrcodeIMG'=>$returnData['qrcodeUrl']]);
                    $order->save();
                } catch (\Throwable $th) {
                    logger('异步返码异常'.$th->getMessage());
                    echo $fail;die;
                }
                break;          
            case 1: 
            case 7:               
                $order->memberCheckSuccess($OrderState,$ActionTime,$Tnumber,$AllCheckNum,$Source,$Action);       
                break;
            case 8:
                //订单退票通知
                $refundOrder = \App\UUModels\UUOrderRefund::getOrderByRefundNo($RemoteSn);
                if(empty($refundOrder)){
                    $refundNum = $order->getOrderTnum() - $Tnumber;
                    $refundOrder = $order->refundOrder($refundNum,$RefundAmount,$RefundFee,$RemoteSn);
                    if(empty($refundOrder)){
                        echo $fail;die;
                    }
                }
                
                if($refundOrder->Refundtype > 0){
                    echo $success;die;
                }
                // if($refundOrder->state > 0){
                //     echo $success;die;
                // }
                $data = compact(
                    'Order16U',
                    'ActionTime',
                    'Tnumber',
                    'Refundtype',
                    'AllCheckNum',
                    'RefundAmount',
                    'RefundFee',
                    'Source'
                );
                $remark = $Explain??'';
                if($Refundtype == 1){
                    $remark = '同意退单';
                    // $data['state'] = 1;
                }elseif($Refundtype == 2){
                    $order->refuseRefundOrder($refundOrder);
                    $data['state'] = 2;
                }
                $data['remark'] = $remark;
                logger('退单回调'.json_encode($data,256));
                $refundOrder->update($data);
                $order->agreeRefundOrder($refundOrder);
                break;
            default:
                logger('未知状态');
                echo $fail;die;
                break;
        }
        echo $success;die;                   
    }
    
    
    public function pwRefundNotify(Request $request){
        try {
            $app =  (Object) $this->getApp(3);
            $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) {
                // 其中 $message['req_info'] 获取到的是加密信息
                // $reqInfo 为 message['req_info'] 解密后的信息
                // 你的业务逻辑...
                logger('退款回调：'.\json_encode($reqInfo,256));
                
                // $order = \App\UUModels\UUTicketOrder::where('order_no',$reqInfo['out_trade_no'])->first();
                $refundOrder = \App\UUModels\UUOrderRefund::getOrderByRefundNo($reqInfo['out_refund_no']);
    
                if (!$refundOrder || $refundOrder->state > 0) { // 如果订单不存在 或者 订单已经退过款了
                    return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
                }
                $order = \App\UUModels\UUTicketOrder::getOrderByOrderNo($refundOrder->order_no);
               
                if($message['return_code']=='SUCCESS'){
                    if($reqInfo['refund_status']=='SUCCESS'){
                        $order->refundOrderSuccess($refundOrder,$reqInfo);
                    }else{
                        $order->refuseRefundOrder($refundOrder,'退款失败');
                    }
                }
                logger($refundOrder->order_no.'退款失败'.json_encode($reqInfo,256));
                $fail('退款失败');            
            });
        
            $response->send(); // Laravel
        } catch (\Throwable $th) {
            //throw $th;
            echo 'success';die;
        } 
    }


    /**
     * 聚福宝出票通知
     *
     * @param Request $request
     * @return void
     */
    public function jufubao_order(Request $request){
        logger($request->all());
        $type = $request->input('type','');
        $order_id = $request->input('order_id',0);
        $channel_order_id = $request->input('channel_order_id',0);
        $state = (int)$request->input('state',0);
        $sign = $request->input('sign','');
        $localSign = \App\Support\MApi::verifyNotifySign(compact('type','order_id','channel_order_id','state'));        
        if(empty($order_id) || empty($channel_order_id) || empty($state) || empty($sign) || ($sign != $localSign)){
            echo 'fail';die;
        }
        
        $apiOrder = \App\Models\ApiOrders::getOrder($order_id);
        if(empty($apiOrder)){
            logger(json_encode($request->all()).'订单未找到');
            echo 'fail';die;
        }
        $apiOrder->apiOutTicket();                
        echo 'success';die;
    }


}
