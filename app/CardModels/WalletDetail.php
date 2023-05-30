<?php

namespace App\CardModels;

use App\Models\TicketUser;
use App\Models\UserOrder;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户消费记录
 */
class WalletDetail extends Model
{
    protected $table = 'rs_balance_detail';

    protected $hidden = ['updated_at'];
    protected $fillable = ['com_id','wallet_id','card_id','user_id','order_id','order_no','money','remark'];

    static function getStoreWalletDetail(int $com_id,int $user_id){
        $limit = (int)request('limit',10);
        $list = WalletDetail::where('user_id',$user_id)
                            ->where('com_id',$com_id)
                            ->latest()
                            ->paginate($limit);
        return $list;
    }

    static function getDetail(int $user_id, int $wallet_id){
        $limit = (int)request('limit',10);
        $list = WalletDetail::where('user_id',$user_id)
                            ->where('wallet_id',$wallet_id)
                            ->latest()
                            ->paginate($limit);
        return $list;
    }

    static function getDetailByOrder(int $order_id){
        $list = WalletDetail::where('order_id',$order_id)->get();
        return $list;
    }
    /**
     * 创建消费记录
     *
     * @param TicketUser $user
     * @param \App\Models\UserOrder $order
     * @param UserWallet $userWallet
     * @return WalletDetail
     */
    
    static function createDetail($com_id,$userWalletId,$cardId,$user_id,$order_id,$order_no,$money,$remark=''){
        $data = array(
            'com_id'        => $com_id,
            'wallet_id'     => $userWalletId,
            'card_id'       => $cardId,
            'user_id'       => $user_id,
            'order_id'      => $order_id,
            'order_no'      => $order_no,
            'money'         => $money,
            'remark'        => $remark
        );
        $wd = new WalletDetail;
        $wd->fill($data)->save();
        return $wd;
}


    
    
}