<?php

namespace App\MallModels;

use App\Support\Helpers;
use App\Models\TicketUser;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = 'mall_user_address';


    /**
     * 创建用户收货地址
     *
     * @param TicketUser $user
     * @param array $address
     * @return UserAddress
     */
    static function createAddress(TicketUser $user, array $address = []){
        if(empty($address['receive_name'])){
            Helpers::exception('请填写联系人姓名');
        }
        if(empty($address['phone'])){
            Helpers::exception('请填写手机号码');
        }
        if(!preg_match('/^1[3456789]\d{9}$/',$address['phone'])){
            Helpers::exception('手机号码不正确');
        }
               
        $addressModel = UserAddress::where('user_id',$user->id)->firstOr(function(){ return (new self);});
        $addressModel->user_id = $user->id;
        $addressModel->receive_name = $address['receive_name']??'';
        $addressModel->phone = $address['phone']??'';
        $addressModel->is_default = 1; //直接就是默认
        $addressModel->address = $address['address']??'';
        $addressModel->province = '';
        $addressModel->city = '';
        $addressModel->area = '';
        $addressModel->save();
        return $addressModel;
    }

    /**
     * 获取用户地址
     *
     * @param integer $userId
     * @return UserAddress
     */
    static function getUserAddress(int $userId){
        return self::where('user_id',$userId)->first();
    }
}
