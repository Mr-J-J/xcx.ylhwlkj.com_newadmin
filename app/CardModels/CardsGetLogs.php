<?php

namespace App\CardModels;

use App\Models\TicketUser;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;


/**
 * 影旅卡免费领取记录
 */
class CardsGetLogs extends Model
{

    protected $table = 'rs_card_get_logs';

    protected $hidden = ['created_at','updated_at'];
    // protected $appends = ['index_image','list_image'];
    protected $guarded = [];

    /**
     * 创建领取记录
     *
     * @param integer $user_id
     * @return void
     */
    public function createLogs(int $comId,TicketUser $user,Cards $cardInfo){
        $data = array(
            'user_id'=>$user->id,
            'com_id'=> $comId,
            'card_id'=> $cardInfo->id,
            'title'=>$cardInfo->title,
            'number'=>1,
            'card_money'=>$cardInfo->card_money,
            'mobile'=>$user->mobile,
        );

        $this->create($data);
    }

    /**
     * 获取当天的领取次数
     *
     * @param integer $user_id
     * @param [type] $card_id
     * @return void
     */
    public function getTodayLogsCount(int $user_id,$card_id = 0){
//        return $this->where('user_id',$user_id)->where('card_id',$card_id)->whereDate('created_at',date('Y-m-d'))->count();
        return $this->where('user_id',$user_id)->where('card_id',$card_id)->count();
    }

    /**
     * 最近一次领取的时间
     *
     * @param integer $user_id
     * @return string
     */
    protected function getLastTime(int $user_id){
        $createdAt = $this->where('user_id',$user_id)->whereDate('created_at',date('Y-m-d'))->latest()->value('created_at');

        return $createdAt;
    }

    public function checkRate(int $user_id){

        $createdAt = $this->getLastTime($user_id);
        if(!$createdAt){
            return true;
        }
        $diff = time()-strtotime($createdAt);
        if($diff < 30){
            return false;
        }
        return true;
    }

}
