<?php

namespace App\CardModels;

use App\Support\Helpers;
use App\Models\UserOrder;
use App\Models\TicketUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户卡余额
 */
class UserWallet extends Model
{
    protected $table = 'rs_user_wallet';

    protected $hidden = ['created_at','updated_at','freeze_balance','freeze_number'];
    
    
    /**
     * 余额消费退回
     *
     * @param UserOrder $order
     * @return void
     */
    static function walletBackBalance(UserOrder $order,$remark = '订单退款'){
        $walletDetail = WalletDetail::getDetailByOrder($order->id)->map(function($detail){
            return $detail->only(['id','wallet_id','money']);
        });

        $waitBackDetail = $walletDetail->pluck('money','wallet_id')->toArray();   
        $waitDeleteDetail = $walletDetail->pluck('id','wallet_id')->toArray();        
        if(empty($walletDetail)) return false;
        $walletList = UserWallet::UserCardList($order->user_id);
        DB::beginTransaction();
        try {            
            foreach($walletList as $wallet){
                if(!empty($waitBackDetail[$wallet->id])){
                    if($waitBackDetail[$wallet->id]){
                        $backMoney = abs($waitBackDetail[$wallet->id]);
                        $wallet->balance = $wallet->balance + $backMoney;
                        $wallet->save();
                        // WalletDetail::where('id',$waitDeleteDetail[$wallet->id])->delete();
                        WalletDetail::createDetail($order->com_id,$wallet->id,$wallet->card_id,$order->user_id,$order->id,$order->getOrderNo(),$backMoney,$remark);
                    }
                }
            }
        } catch (\Throwable $th) {
            DB::rollback();
        }
        DB::commit();

    }
    /**
     * 余额扣除
     *
     * @param TicketUser $user
     * @param UserOrder $order
     * @return void
     */
    static function walletKouFee(TicketUser $user,UserOrder $order){
        $amount = $order->discount_price;
        $walletList = UserWallet::UserCardList($order->user_id);
        $userWalletBalance = $walletList->sum('balance');
        if($userWalletBalance < $order->discount_price){
            Helpers::exception('影旅卡余额不足');
        }
        $walletList = $walletList->sortBy('balance')->values()->all();
        
        DB::beginTransaction();
        try {
            $logs = array();
            foreach($walletList as $wallet){
                if($wallet->balance == 0) continue;
                $amount = $amount - $wallet->balance; // 5 - 2
                $koufee = $wallet->balance;
                
                if($amount<=0){
                    $koufee = $amount + $wallet->balance;
                }
                $wallet->koufee = $koufee;
                $logs[] = $wallet;
                if($amount<=0){
                    break;
                }
            }
            
            
            foreach($logs as $wallet){
                $balance = $wallet->balance;
                $koufee = $wallet->koufee = $wallet->koufee * -1; //扣费
                // WalletDetail::createDetail($user,$order,$wallet);
                WalletDetail::createDetail($order->com_id,$wallet->id,$wallet->card_id,$order->user_id,$order->id,$order->getOrderNo(),$koufee,'购买电影票');
                $balance = $balance + $koufee;
                unset($wallet->koufee);
                $wallet->balance = $balance;                
                $wallet->save();
            }

        } catch (\Throwable $th) {            
            DB::rollback();
            Helpers::exception('影旅卡余额支付失败');
        }

        DB::commit();

    }

    /**
     * 是否有可以赠送影旅卡
     *
     * @return boolean|Model
     */
    function canSendCard(){
        $userWallet = $this;
        $cardInfo = Cards::where('state',1)->where('id',$userWallet->card_id)->first();
        if(empty($cardInfo) || $userWallet->balance < $cardInfo->card_money){
            return false;
        }
        $sendNum = CardSend::where('from_user_id',$userWallet->user_id)->where('card_id',$userWallet->card_id)->where('state',1)->count();
        if($sendNum){
           // return false;
        }
        return $cardInfo;
    }
    

    /**
     * 用户影旅卡列表
     *
     * @param integer $user_id
     * @return collect
     */
    static function UserCardList(int $user_id){
        $list = UserWallet::where('user_id',$user_id)
                    ->get();        
        return $list;
    }

    
    /**
     * 根据卡id获取用户卡信息
     *
     * @param integer $user_id
     * @param integer $card_id
     * @return UserWallet
     */
    static function getUserCardByCardId(int $user_id,int $card_id){
        return UserWallet::where('user_id',$user_id)
                        ->where('card_id',$card_id)
                        ->first();
    }

    /**
     * 领取赠卡充值
     *
     * @param TicketUser $user
     * @param CardSend $detail
     * @return void
     */
    static function addMoneyFromSendCard(TicketUser $user,CardSend $detail){
        if($detail->state != 1){
            return false;
        }
        DB::beginTransaction();
        try {
            $uw = self::addUserWallet($user->id,$detail->card_id,$detail->card_money,$detail->number);
            // $fromUserWallet = UserWallet::where('id',$detail->wallet_id)->first();            
            // $fromUserWallet->freeze_number = $fromUserWallet->freeze_number - $detail->number;
            // $fromUserWallet->freeze_balance = $fromUserWallet->freeze_balance -$detail->card_money;
            // $fromUserWallet->save();
            $detail->state = 2;
            $detail->to_user_id = $user->id;
            $detail->save();
            WalletDetail::createDetail($user->com_id,$uw->id,$uw->card_id,$user->id,0,'',$detail->card_money,'领取赠卡');
        } catch (\Throwable $th) {
            DB::rollback();
            logger('赠卡领取失败'.$detail->id);
            return false;
        }

        DB::commit();
        return true;
    }
    /**
     * 购卡充值
     */
    static function addMoneyFromOrder(CardOrder $order){
        // if($order->card_money == 0){
        //     logger('UserWallet :'.$order->order_sn.'充值金额错误 '.$order->card_money);
        //     return false;
        // }
        $remark = $order->remark?:'购卡充值';
        $uw = self::addUserWallet($order->user_id,$order->card_id,$order->card_money,$order->number);
        WalletDetail::createDetail($order->com_id,$uw->id,$uw->card_id,$order->user_id,$order->id,$order->order_sn,$order->card_money,$remark);
    }
    /**
     * 用户影旅卡充值
     *
     * @param CardOrder $order
     * @param float $money
     * @return Model
     */
    static function addUserWallet(int $user_id,int $card_id,float $card_money,int $number = 1 ){        
        $uw = self::getUserCardByCardId($user_id,$card_id);        
        if(empty($uw)){
            $uw = new UserWallet;
            $uw->balance = 0;
            $uw->number = 0;
            $uw->user_id = $user_id;
            $uw->card_id = $card_id;
        }
        
        // $uw->number = $uw->number + $number;
        $uw->balance = $uw->balance + round($card_money * $number,2);
        $uw->save();

        // WalletDetail::createDetail($user,$order,$uw);
        
        return $uw;
    }
    
}

