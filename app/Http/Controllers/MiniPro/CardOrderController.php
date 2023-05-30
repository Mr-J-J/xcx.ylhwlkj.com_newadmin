<?php

namespace App\Http\Controllers\MiniPro;

use App\Support\Helpers;
use App\CardModels\Cards;
use App\Models\TicketUser;
use App\CardModels\CardSend;
use App\CardModels\RsOlCard;
use App\CardModels\RsStores;
use Illuminate\Http\Request;
use App\CardModels\CardOrder;
use App\CardModels\UserWallet;
use App\CardModels\CardSetting;
use App\CardModels\CardsGetLogs;
use App\CardModels\WalletDetail;

/**
 * 影旅卡
 */
class CardOrderController extends UserBaseController
{

    protected $comId = 0;
    public function __construct(){
        parent::__construct();
        // $this->comId = $this->user?$this->user->com_id:0;
        $this->comId = (int)request('com_id',0);
        if(empty($this->comId)){
            Helpers::exception('缺少必要参数');
        }

    }


    /**
     * 影旅卡赠送
     *
     * @param Request $request
     * @return void
     */
    public function sendCard(Request $request){
        // return $this->error('系统维护中...');
        $cardId = $request->input('card_id',0);
        $userWallet = UserWallet::getUserCardByCardId($this->user->id,$cardId);
        if(empty($userWallet)){
            return $this->error('没有可以赠送的影旅卡');
        }
        $cardInfo = $userWallet->canSendCard();
        if(!$cardInfo){
            return $this->error('没有可以赠送的影旅卡');
        }
        $send = new CardSend;

        $send->doSendCard($userWallet,$cardInfo,$send);
        if(!$send->exists){
            return $this->error('赠送失败');
        }
        return $this->success('影旅卡已送出',['id'=>$send->id]);
    }

    /**
     * 取消赠送
     *
     * @param Request $request
     * @return void
     */
    public function cancelSendCard(Request $request){
        // return $this->error('系统维护中...');
        $cardId = $request->input('card_id',0);
        $userWallet = UserWallet::getUserCardByCardId($this->user->id,$cardId);
        $send = new CardSend;
        $send->cancelSend($userWallet);
        return $this->success('已取消');
    }


    /**
     * 影旅卡激活
     *
     * @param Request $request
     * @return void
     */
    public function activeCard(Request $request){
        $cardNo = $request->input('card_no','');
        $cardKey = $request->input('card_key','');

        if(empty($cardNo)){
            return $this->error('请输入卡号');
        }
        if(empty($cardKey)){
            return $this->error('请输入卡密');
        }
        $cardModel = new RsOlCard;
        $cardInfo = $cardModel->getCardByNo($cardNo);
        if(!$cardInfo){
            return $this->error('卡号无效');
        }
        $cardInfo->checkOfflineCard($cardKey);

        if($cardInfo->activeCard($this->user)){
            return $this->success('影旅卡已激活');
        }
        return $this->error('影旅卡激活失败');
    }


    /**
     * 赠卡详情
     *
     * @param Request $request
     * @return void
     */
    public function sendDetail(Request $request){
        $detailId = (int)$request->input('id',0);
        $detail = CardSend::select(['id','number','from_user_id','card_id','card_money'])->where('id',$detailId)->first();
        if(empty($detail)){
            return $this->error('没有可以领取的影旅卡');
        }
        $formUser = TicketUser::select(['com_id','avatar','nickname'])->where('id',$detail->from_user_id)->first();
        $storeInfo = RsStores::getStoreInfo($formUser->com_id);
        $cardInfo = Cards::select(['image','short_title','title','card_money'])->where('id',$detail->card_id)->first();
        $detail->title = $cardInfo->title;
        $money = round($cardInfo->card_money);
        $detail->tips = "赠送您{$detail->number}张价值{$money}影旅卡";
        $detail->short_title = $cardInfo->short_title;
        $detail->image = $cardInfo->image;
        $detail->card_money = $cardInfo->card_money;
        $detail->store_logo = $storeInfo->store_logo;
        $detail->store_name = $storeInfo->store_name;
        $detail->avatar = $formUser->avatar;
        $detail->nickname = $formUser->nickname;
        return $this->success('',$detail);
    }

    /**
     * 领取赠卡
     *
     * @param Request $request
     * @return void
     */
    public function applyCard(Request $request){
        // return $this->error('系统维护中...');
        $detailId = (int)$request->input('id',0);
        $detail = CardSend::where('id',$detailId)->first();
        if(empty($detail)){
            return $this->error('没有可以领取的影旅卡');
        }
        $cardInfo = Cards::select(['image','short_title','title','card_money'])->where('id',$detail->card_id)->first();
        $money = round($cardInfo->card_money);
        $tips = '已超过领取有效期';
        $message = '领取失败';
        if(UserWallet::addMoneyFromSendCard($this->user,$detail)){
            $tips = "已领取{$detail->number}张价值{$money}影旅卡";
            $message = '领取成功';
            return $this->success($message,compact('tips'));
        }
        return $this->error($message,compact('tips'));

    }

    /**
     * 影旅卡消费记录
     *
     * @param Request $request
     * @return void
     */
    public function purchaseRecords(Request $request){
        $cardId = $request->input('card_id',0);
        $userWallet = UserWallet::getUserCardByCardId($this->user->id,$cardId);
        $list = array();
        if($userWallet){
            $list = WalletDetail::getDetail($this->user->id,$userWallet->id);
            foreach($list as $item){
                $item->makeHidden(['id','com_id','wallet_id','card_id','user_id','order_id']);
                $item->money = round($item->money,2);
            }
        }
        return $this->success('',$list);
    }

    /**
     * 用户影旅卡列表
     *
     * @param Request $request
     * @return void
     */
    public function myCardList(Request $request){
        $list = UserWallet::UserCardList($this->user->id);
        $cardList = Cards::getList()->toArray();
        $cardList = array_combine(array_column($cardList,'id'),array_values($cardList));
        // return $this->success('',$cardList);
        $card_seting = CardSetting::getSetting();
        $storeInfo = RsStores::getStoreInfo($this->comId);
        $tips = $card_seting->tips;
        $rules = $card_seting->use_rules;
        foreach($list as $item){
            $item->title = $cardList[$item->card_id]['title'];
            $item->short_title = $cardList[$item->card_id]['short_title'];
            $item->image = $cardList[$item->card_id]['image'];
            $item->card_money = $cardList[$item->card_id]['card_money'];
            $item->store_logo = $storeInfo->store_logo;
            $item->store_name = $storeInfo->store_name;
            $item->cancel_send = false; //是否有转赠记录
            try {
                $sendRecord = CardSend::getCardSendRecord($item);
            } catch (\Throwable $th) {
                //throw $th;
            }
            $item->id = $item->card_id;
            if(!empty($sendRecord)){
                $item->cancel_send = ($sendRecord->state == 1);
            }
            $allowSend = $item->canSendCard();
            $item->allow_send = ($allowSend !== false);
            if($item->cancel_send){
                //$item->allow_send = false;
            }
        }
        return $this->success('',compact('list','tips','rules'));
    }
    /*
        * 获取卡列表
        */
    public function myCardListt(Request $request)
    {
//        $list = UserWallet::UserCardList($this->user->id);
        $list = RsOlCard::where('user_id',$this->user->id)->get();
        return $list;
    }
    /**
     * 订单列表
     *
     * @param Request $requst
     * @return void
     */
    public function orderList(Request $request){
        // 0全部 1待付款 2已付款 3取消
        $status = (int)$request->input('status',0);
        $limit = (int)$request->input('limit',10);

        $statusArr = array();
        switch($status){
            case 1:
                $statusArr[] = CardOrder::NOPAY;
                break;
            case 2:
                $statusArr[] = CardOrder::SUCCESS;
                break;
            case 3:
                $statusArr[] = CardOrder::CANCEL;
                break;
        }

        $list = CardOrder::when($statusArr,function($query,$statusArr){
                    return $query->whereIn('order_status',$statusArr);
                })
                ->where('user_id',$this->user->id)
                ->latest()
                ->paginate($limit,['order_sn','order_status','card_id','card_info','expire_time','created_at','order_amount']);
        foreach($list as $order){
            $order->status_txt = CardOrder::$status[$order->order_status];
            $order->card_info = json_decode($order->card_info);
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
        $order_info = CardOrder::getOrderByNo($orderNo);
        if(empty($order_info)){
            return $this->error('订单已删除或不存在');
        }

        $order_info = $order_info->makeHidden(['card_money','transaction_id','agreement','remark']);
        $order_info->status_txt = CardOrder::$status[$order_info->order_status];
        $order_info->card_info = json_decode($order_info->card_info);

        return $this->success('',compact('order_info'));
    }

    /**
     * 影旅卡免费领取
     *
     * @param Request $request
     * @return void
     */
    public function freeget(Request $request){
        $cardId = (int)$request->input('card_id',0);
        $cardInfo = Cards::where('state',1)->where('id',$cardId)->first();
        if(empty($cardInfo)){
            return $this->error('影旅卡已下架');
        }
        $getLogsModel = new CardsGetLogs;

        if(!$cardInfo->free_num){
            return $this->error('免费领取活动已结束');
        }
        $UserFreeCount = $getLogsModel->getTodayLogsCount($this->user->id,$cardInfo->id);
        if($UserFreeCount >= $cardInfo->free_num){
            return $this->error('您已经领取过了');
        }

        $checkRes = $getLogsModel->checkRate($this->user->id);
        if(!$checkRes){
            return $this->error('操作太频繁!');
        }

        $cardInfoData = (object)array(
            'id'=>$cardInfo->id,
            'title'=>$cardInfo->title,
            'image'=>$cardInfo->list_image,
            'number'=>1,
            'price'=>0,
            'card_money'=>$cardInfo->card_money
        );

        CardOrder::createOrderV2($this->user,$this->comId,$cardInfoData,'免费领取');
        $getLogsModel->createLogs($this->comId,$this->user,$cardInfo);
        return $this->success('领取成功');
    }

    /**
     * 创建订单
     *
     * @param Request $request
     * @return void
     */
    public function createOrder(Request $request){
        // com_id
        $cardId = (int)$request->input('card_id',0);

        $cardInfo = Cards::where('state',1)->where('id',$cardId)->first();

        if(empty($cardInfo)){
            return $this->error('影旅卡已下架');
        }

        $newOrder = CardOrder::createOrder($this->user,$this->comId,$cardInfo);

        if(empty($newOrder)){
            return $this->error('订单创建失败');
        }
        $pay_param = array();

        if($newOrder->order_amount > 0){
            try {
                $pay_param = $this->getPayInfo($newOrder);
                if(empty($pay_param)){
                    throw new \Exception('支付参数错误');
                }
            } catch (\Throwable $th) {
                logger($th->getMessage());
                return $this->error('订单支付失败'.$th->getMessage(),['order_no'=>$newOrder->order_sn]);
            }
        }
        $pay_param['order_no']= $newOrder->order_sn;

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

        $order = CardOrder::getOrderByNo($orderNo);
        if(empty($order)){
            return $this->error('订单不存在');
        }
        if($order->order_status != CardOrder::NOPAY){
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

    protected function getPayInfo($orderInfo){
        if($orderInfo->order_amount == 0) return false;
        $app = (Object) $this->getApp(3,$this->comId);
        $payResult = $app->order->unify([
            'body' => '购买影旅卡',
            'out_trade_no' => $orderInfo->order_sn,
            'total_fee' => round($orderInfo->order_amount * 100),
            'notify_url' => route('cardnotify',['comId'=>$this->comId]), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
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

        $order = CardOrder::getOrderByNo($orderNo);
        if(empty($order)){
            return $this->error('订单不存在');
        }
        if(!$order->canCancel()){
            return $this->error('订单取消失败');
        }
        $order->cancelOrder();
        return $this->success('订单已取消');
    }



}
