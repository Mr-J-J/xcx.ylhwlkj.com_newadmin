<?php

namespace App\CardModels;

use App\Models\TicketUser;
use App\Support\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * 影旅卡赠送记录
 */
class CardSend extends Model
{
    protected $table = 'rs_card_send';

    protected $hidden = ['created_at','updated_at'];
    // protected $appends = ['index_image','list_image'];
    // 1赠送中 2赠送成功 3取消赠送

    /**
     * 获得影旅卡赠送记录
     *
     * @param UserWallet $userWallet
     * @return Model
     */
    static function getCardSendRecord(UserWallet $userWallet){        
        $record = CardSend::where('wallet_id',$userWallet->id)->where('state',1)->orderBy('created_at','desc')->first();
        if(empty($record)){
            Helpers::exception('没有找到赠送记录');
        }
        return $record;
    }

    /**
     * 取消赠送
     *
     * @param UserWallet $userWallet
     * @return void
     */
    function cancelSend(UserWallet $userWallet){
        $sendRecord = self::getCardSendRecord($userWallet);
        if($sendRecord->state == 2){
            Helpers::exception('取消失败:影旅卡已被领取');
        }

        if($sendRecord->state == 3){
            Helpers::exception('已取消');
        }

        $user = TicketUser::where('id',$userWallet->user_id)->first();
        DB::beginTransaction();
        try {
            $sendRecord->state = 3;
            $sendRecord->save();
            $userWallet->balance = $userWallet->balance + $sendRecord->card_money;
            
            $userWallet->save();
            WalletDetail::createDetail($user->com_id,$userWallet->id,$userWallet->card_id,$user->id,0,'',$sendRecord->card_money,'取消赠送影旅卡');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            Helpers::exception('取消失败[error]');
        }
        DB::commit();        
    }
    
    
    /**
     * 系统免费赠送影旅卡
     *
     * @param TicketUser $user
     * @return void
     */
    function freeSendCard(TicketUser $user){
        $count = UserWallet::UserCardList($user->id)->toArray();
        if(!empty($count)){
            return false;
        }

        $cardInfo = Cards::where('free_num','>',0)->where('state',1)->where('is_default',1)->first();        
        if(empty($cardInfo)){
            $cardInfo = Cards::where('free_num','>',0)->where('state',1)->orderBy('card_money','asc')->first();
            if(empty($cardInfo)){
                return false;
            }
        }

        $uw = UserWallet::addUserWallet($user->id,$cardInfo->id,$cardInfo->card_money,1);
        WalletDetail::createDetail($user->com_id,$uw->id,$uw->card_id,$user->id,0,'',($cardInfo->card_money*1),'系统赠送影旅卡');
    }
    
    /**
     * 赠送卡
     *
     * @param UserWallet $userWallet
     * @param Cards $cardInfo
     * @return void
     */
    function doSendCard(UserWallet $userWallet,Cards $cardInfo,&$sendRecord){
        $user = TicketUser::where('id',$userWallet->user_id)->first();
        DB::beginTransaction();
        try {
            $balance = $userWallet->balance - $cardInfo->card_money;
            // $number = $userWallet->number - 1;
            if($balance < 0){
                throw new \Exception('没有可以赠送的影旅卡');
            }
            $userWallet->balance = $balance;
            $userWallet->save();
            $sendRecord = $this->addSendRecord($userWallet,$cardInfo);

            WalletDetail::createDetail($user->com_id,$userWallet->id,$userWallet->card_id,$user->id,0,'',($cardInfo->card_money*-1),'赠送影旅卡');
        } catch (\Throwable $th) {
            DB::rollback();
            Helpers::exception('赠送失败[error]');
        }

        DB::commit();
        
    }

    /**
     * 添加赠送记录
     *
     * @param UserWallet $userWallet
     * @param Cards $cardInfo
     * @return Model
     */
    private function addSendRecord(UserWallet $userWallet,Cards $cardInfo){
        $send = new CardSend;
        $send->from_user_id = $userWallet->user_id;
        $send->to_user_id = 0;
        $send->card_id = $userWallet->card_id;
        $send->wallet_id = $userWallet->id;
        $send->number = 1;
        $send->card_money = $cardInfo->card_money;
        $send->state = 1;
        $send->save();
        return $send;
    }


    
    
}
