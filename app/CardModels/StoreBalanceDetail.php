<?php

namespace App\CardModels;

use App\Support\Helpers;
use App\Models\UserOrder;
use App\CardModels\RsStores;
use Illuminate\Database\Eloquent\Model;

/**
 * 分销商
 */
class StoreBalanceDetail extends Model
{

    protected $table = 'rs_store_balance_detail';

    protected $hidden = ['updated_at'];
    protected $fillable = ['com_id','order_sn','order_id','money','after_balance','type','remark','endtime'];


    //影旅卡分成记录
    static function addDetail(CardOrder $order,float $commision = 0,$after_money = 0){
        if(!$commision) return false;
        $data = array(
            'com_id'=>$order->com_id,
            'order_sn'=>$order->order_sn,
            'order_id'=>$order->id,
            'card_id'=>$order->card_id,
            'type'=> 1,
            'remark'=> '影旅卡返佣',
            'state'=> 1,
            'money'=> round($commision,2),
            'after_balance'=>$after_money
        );
        $detail = new StoreBalanceDetail;
        $detail->fill($data)->save();
    }

    //购票分成记录
    static function addDetailByTicketOrder(UserOrder $order,float $commision = 0,$after_money = 0){
        if(!$commision) return false;
        $data = array(
            'com_id'=>$order->com_id,
            'order_sn'=>$order->order_no,
            'order_id'=>$order->id,
            'card_id'=>0,
            'type'=> 2,
            'remark'=> '用户购票返佣',
            'endtime'=> $order->close_time,
            'state'=> 0,
            'money'=> round($commision,2),
            'after_balance'=>$after_money
        );
        $detail = new StoreBalanceDetail;
        $detail->fill($data)->save();
    }

    //待结算的分成记录
    static function waitSettleList(){
        $list = StoreBalanceDetail::where('state',0)->where('endtime','<',time())->get();
        foreach($list as $detail){
            $store = RsStores::where('id',$detail->com_id)->first();
            if($store&&$store->ismoney==1){
                $store->balance = $store->balance + $detail->money;
                $store->total_money = $store->total_money + $detail->money;
                $store->save();

            }
            $detail->state = 1;
            $detail->save();
        }
    }




    public function store(){
        return $this->hasOne('App\CardModels\RsStores','id','com_id');
    }


}

