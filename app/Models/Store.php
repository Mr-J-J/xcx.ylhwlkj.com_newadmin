<?php

namespace App\Models;


use App\Support\Helpers;
use App\ApiModels\Wangpiao\City;
use App\Models\store\StoreLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Store extends Authenticatable
{
    //
    protected $primaryKey = 'id';
    protected $hidden = ['store_pass','remember_token'];
    protected $appends = ['store_level_txt'];

    /**
     * 商户入驻
     *
     * @param [type] $data
     * @param Store $store
     * @return void
     */
    public static function register($data,Store $store){
        if($store->store_state == 1){
            Helpers::exception('信息审核中');
        }
        if(empty($data['store_name'])){
            Helpers::exception('请填写店铺名称');
        }
        
        // if(empty($data['citys'])){
        //     Helpers::exception('请选择所在城市');
        // }
        if(empty($data['city_code'])){
            Helpers::exception('请选择所在城市');
        }

        $city = City::where('code',$data['city_code'])->first();
        if(!empty($city)){
            $store->store_city_id = $city->id;
            $store->store_city = $city->name;
        }
        
        if(empty($data['phone'])){
            Helpers::exception('请填写手机号码');
        }
        
        if(empty($data['password'])){
            Helpers::exception('请设置密码');
        }
        $repassword = $data['repassword'] ?: '';
        if($data['password'] != $repassword){
            Helpers::exception('两次密码输入不一致');
        }
        
        
        DB::beginTransaction();
        try{
            $store->store_name = $data['store_name'];            
            $store->store_phone = $data['phone'];
            $store->store_state = 1;
            $store->store_level = 0;
            $store->store_pass = Hash::make($data['password']);
            $store->save();

            $storeInfo = StoreInfo::where('store_id',$store->id)->firstOr(function () {
                return new StoreInfo;
            });
            
            $storeInfo->store_id = $store->id;
            // $storeInfo->taking_mode = 1;
            $storeInfo->cinemas = $data['cinemas'];
            $storeInfo->save();

            DB::commit();
        }catch (\Exception $e) {
            DB::rollBack();
            throw $e;
            // Helpers::exception('ERROR');
        }

    }

    public function setRememberTokenAttribute($value){
        $this->attributes['remember_token'] = Helpers::generateToken($value);
    }

    public function getStoreLevelTxtAttribute(){
        $level = StoreLevel::where('id',$this->attributes['store_level'])->first(['title']);
        if(empty($level)) return '';
        return $level->title;
    }
    

    public function storeInfo(){
        return $this->hasOne('App\Models\StoreInfo','store_id');
    }


    public function level(){
        return $this->hasOne('App\Models\store\StoreLevel','store_level');
    }


    
    
    
}
