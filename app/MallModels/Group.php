<?php

namespace App\MallModels;

use App\Models\TicketUser;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'mall_groups';
    protected $hidden = ['created_at','updated_at'];

    static $default_group_id = 1; //普通会员
    static function getList(){
        $list = self::orderBy('id','asc')->get();
        foreach($list as $item){
            $item->cash_money = round($item->cash_money);
            $item->level1_rate = round($item->level1_rate);
            $item->level2_rate = round($item->level2_rate);
            $item->discount = round($item->discount,1);
        }
        foreach($list as $k=>$item){
            $item->next_group = '';
            
            if(!empty($list[$k+1])){
                $nextGroup = clone($list[$k+1]);
                
                $item->next_group = $nextGroup;
            }
        }
        return $list;
    }
    
    /**
     * 检查用户等级
     *
     * @param TicketUser $user 
     * @return void
     */
    static function checkGroup(TicketUser $user){
        $group = self::where('cash_money','<=',$user->cash_money)->orderBy('cash_money','desc')->first();
        if(empty($group)){
            return false;
        }
        if($user->group_id != $group->id){
            $user->group_id = $group->id;
            $user->save();
        }
    }

    /**
     * 获取会员折扣
     *
     * @param [type] $groupId
     * @return float
     */
    static function getGroupDiscount($groupId){
        $discountRate = (float)self::where('id',$groupId)->value('discount');
        return $discountRate / 10;
    }


    /**
     * 检查用户等级
     *
     * @param TicketUser $user
     * @return void
     */
    static function checkUserGroup(TicketUser $user){
        $groupList = self::getList();
        if($groupList->isEmpty()) return false;
        self::doGroupUp($groupList,$user);
    }

    static function doGroupUp($groupList,TicketUser $user){
        $userGroupId = $user->group_id;
        $group = new Group;
        $groupList->map(function($v) use (&$group,$userGroupId){
            if($v->id == $userGroupId) $group = $v;
        });
        if(!$group->exists) return false;
        if(empty($group->next_group)) return false;
        if($user->cash_money < $group->next_group->cash_money) return false;
        $user->group_id = $group->next_group->id;
        $user->save();
        GroupLogs::create(['user_id'=>$user->id,'group_id'=>$user->group_id]);
        self::doGroupUp($groupList,$user);
    }

    public function delete(){
        if($this->id == self::$default_group_id){
            throw new \Exception('默认会员等级不能删除');
        }
        return parent::delete();
    }

    public function getImageAttribute($value){
        if(!empty($value)){
            return Helpers::formatPath($value,'admin');
        }
        return $value;
    }
    
      
    
}
