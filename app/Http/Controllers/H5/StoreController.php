<?php

namespace App\Http\Controllers\H5;

use App\Models\Store;
use App\Support\Code;
use App\Support\Helpers;
use App\Models\StoreInfo;
use App\Models\Suggestion;
use Illuminate\Http\Request;
use App\Models\store\StoreCheckOut;

class StoreController extends StoreBaseController
{
    /**
     * 商家信息
     *
     * @return void
     */
    public function info(){
        //待结算
        // $checkOutList = StoreCheckOut::where('state',0)->where('store_id',$this->store->id)->get();
        // foreach($checkOutList as $item){
        //     $item->doCheckOut();
        // }
        $nowtime = time();
        $yesterday = strtotime('-1 day',$nowtime);
        $info = $this->store;
        //累计
        $totalMoney = StoreCheckOut::where('store_id',$this->store->id)->sum('money');
        $totalMoney = round($totalMoney/100,2);
        $totalNumber = StoreCheckOut::where('store_id',$this->store->id)->count();

        //今日
        $todayMoney = StoreCheckOut::where('store_id',$this->store->id)->whereDate('created_at',date('Y-m-d'))->sum('money');
        $todayMoney = round($todayMoney/100,2);
        $todayNumber = StoreCheckOut::where('store_id',$this->store->id)->whereDate('created_at',date('Y-m-d'))->count();

        //昨日
        $yesterMoney = StoreCheckOut::where('store_id',$this->store->id)->whereDate('created_at',date('Y-m-d',$yesterday))->sum('money');
        $yesterMoney = round($yesterMoney/100,2);
        $yesterNumber = StoreCheckOut::where('store_id',$this->store->id)->whereDate('created_at',date('Y-m-d',$yesterday))->count();

        //本月
        $monthMoney = StoreCheckOut::where('store_id',$this->store->id)->whereMonth('created_at',date('m'))->sum('money');
        $monthMoney = round($monthMoney/100,2);
        $monthNumber = StoreCheckOut::where('store_id',$this->store->id)->whereMonth('created_at',date('m'))->count();

        $info->store_info = $info->storeInfo;
        if($info->storeInfo){
            $info->store_info->today_money = round($todayMoney / 100);
            $info->store_info->tongji = compact('totalMoney','totalNumber','todayMoney','todayNumber','yesterMoney','yesterNumber','monthMoney','monthNumber');
            $info->store_info->taking_mode = intval(!$info->store_info->taking_mode);
        }
        $info->teyue = Helpers::getSetting('teyuegongyingshang');
        $info->kefu =  Helpers::getSetting('offer_kefu');
        // $info->store_info->taking_auto = intval(!$info->store_info->taking_auto);
        return $this->success('成功',['store_info'=>$info]);
    }
    /**
     * 不在弹窗
     */
    public function nopop(Request $req){
        $info = $this->store;
        $info->no_pop=1;
        $info->save();
    }
    /**
     * 商家接单状态修改
     *
     * @return void
     */
    public function mode(Request $req){
        $storeInfo = $this->store->storeInfo;
        $message = '操作成功';
        if(empty($storeInfo)){
            return $this->error('商家信息不存在');
        }
        // dd(intval(!$storeInfo->taking_mode));
        if(!empty($req['taking_mode'])){
            $message = $storeInfo->taking_mode?'关闭接单':'开启接单';
            $storeInfo->taking_mode = intval(!$storeInfo->taking_mode);
        }

        if(!empty($req['taking_auto'])){
            $message = $storeInfo->taking_auto?'关闭自动接单':'开启自动接单';
            $storeInfo->taking_auto = intval(!$storeInfo->taking_auto);
        }
        if(!empty($req['taking_time'])){
            $storeInfo->taking_time = $req['taking_time'];
        }
        if(!empty($req['alipay_account'])){
            $storeInfo->alipay_account = trim($req['alipay_account']);
            $storeInfo->alipay_name = trim($req['alipay_name']);
        }
        $storeInfo->save();
        $info = $this->store;
        $info->store_info = $storeInfo;
        return $this->success($message,['store_info'=>$info]);
    }

    /**
     * 商家信息修改
     *
     * @param Request $request
     * @return void
     */
    public function editStoreInfo(Request $request ){
        $storeInfo = $this->store->storeInfo;

        if(empty($storeInfo)){
            return $this->error('商家信息不存在');
        }
        if(!empty($request['alipay_account'])){
            $storeInfo->alipay_account = trim($request['alipay_account']);
            $storeInfo->alipay_name = trim($request['alipay_name']);
        }
        $storeInfo->save();
        return $this->success('修改成功',$storeInfo);
    }

    /**
     * 商家入驻
     *
     * store_name,citys,phone,password,repassword
     * @return void
     */
    public function register(Request $req){
        try {
            Store::register($req->post(),$this->store);
        } catch (\Exception $e) {
            return $this->error('提交失败:'.$e->getMessage());
        }
        return $this->success('提交成功');
    }

    /**
     * 图片上传
     *
     * @param Request $req
     * @return void
     */
    public function upload(Request $req){
        try {
            $res = Helpers::uploadImage3('store');
        } catch (\Exception $e) {
            logger($e->getMessage());
            return $this->error('失败：'.$e->getMessage());
        }
        return $this->success('上传成功',$res);
    }

    /**
     * 意见反馈
     *
     * @return void
     */
    public function suggestion(Request $request){
        try {
            Suggestion::addSuggestion($request->post(),$this->store->id,1);
        } catch (\Exception $e) {
            return $this->error('提交失败:'.$e->getMessage());
        }
        return $this->success('提交成功');
    }
}
