<?php

namespace App\Http\Controllers\MiniPro;

use Illuminate\Support\Facades\DB;
use App\MallModels\OrderCheckCode;
use App\MallModels\OrderCheckList;
use App\MallModels\OrderCheckLogs;
use App\MallModels\SettleList;
use Illuminate\Http\Request;
use App\MallModels\Stores;
use Hamcrest\Core\Set;

/**
 * 吃喝玩乐商家核销
 */
class MallStoreController extends UserBaseController
{

    /**
     * 商家结账统计
     *
     * @return void
     */
    public function storeAccount(){
        $store = Stores::where('user_id',$this->user->id)->first();
        if(empty($store)){
            return $this->error('商家信息不存在');
        }
        $info = array(
            'freeze_money'=>$store->freeze_money,
            'settle_money'=>$store->settle_money,
            'limit_money'=>sprintf('%.2f',$store->freeze_money - $store->settle_money),
        );
        return $this->success('',$info);
    }
    /**
     * 卡券核销
     *
     * @param Request $request
     * @return void
     */
    public function orderCheck(Request $request){
        $code = $request->input('code','');
        $number = (int)$request->input('number',1);
        $number = max(1,$number);

        if(empty($code)){
            return $this->error('请输入核销码');
        }

        $codeInfo = OrderCheckCode::where('code',$code)->first();
        if(empty($codeInfo)){
            return $this->error('核销码无效');
        }
        $codeInfo->checkCode($number,$this->user);
        return $this->success('核销成功');
    }

    /**
     * 核销商品列表
     *
     * @param Request $request
     * @return void
     */
    public function checkProductList(){
        $list = OrderCheckList::select(['product_id','product_title'])
                    ->where('store_id',$this->user->id)
                    ->groupBy(['product_id','product_title'])
                    ->get();
        return $this->success('',$list);
    }

    /**
     * 核销记录
     *
     * @param Request $request
     * @return void
     */
    public function checkList(Request $request){
        $limit = (int)$request->input('limit',10);
        $productId = $request->input('product_id',0);

        $list = OrderCheckList::when($productId,function($query,$productId){
                    return $query->where('product_id',$productId);
                })->where('store_id',$this->user->id)->orderBy('updated_at','desc')
                ->paginate($limit);
        foreach($list as $checkOrder){
            $codeInfo = OrderCheckCode::where('order_id',$checkOrder->order_id)->first();
            $checkOrder->limit_number = $codeInfo->check_number - $codeInfo->used_number;
            $checkOrder->check_number = $codeInfo->check_number;

            $checkOrder->check_logs = OrderCheckLogs::where('order_id',$checkOrder->order_id)
                                        ->orderBy('created_at','desc')
                                        ->get(['check_sn','username','check_number','created_at']);
        }
        return $this->success('',$list);
    }

    /**
     * 核销记录
     *
     * @param Request $request
     * @return void
     */
    public function checkStatistics(Request $request){
        $limit = (int)$request->input('limit',10);
        $productId = $request->input('product_id',0);
        $date = $request->input('date','');
        $month = $request->input('month','');
        $year = $request->input('year','');
        // if(empty($date) && empty($month) && empty($year)){
        //     $date = date('Y-m-d');
        // }
        $list = OrderCheckList::when($date,function($query,$date){
                    return $query->whereDate('created_at',$date);
                })->when($month,function($query,$month){
                    return $query->whereMonth('created_at',(int)$month);
                })->when($year,function($query,$year){
                    return $query->whereYear('created_at',(int)$year);
                })->when($productId,function($query,$productId){
                    return $query->where('product_id',$productId);
                })->where('store_id',$this->user->id)->orderBy('updated_at','desc')
                ->paginate($limit);
        foreach($list as $checkOrder){
            $codeInfo = OrderCheckCode::where('order_id',$checkOrder->order_id)->first();
            $checkOrder->limit_number = $codeInfo->check_number - $codeInfo->used_number;
            $checkOrder->check_number = $codeInfo->check_number;

            $checkOrder->check_logs = OrderCheckLogs::where('order_id',$checkOrder->order_id)
                                        ->orderBy('created_at','desc')
                                        ->get(['check_sn','username','check_number','created_at']);
        }
        $tongji = OrderCheckLogs::select(DB::raw('IFNULL(sum(check_money),0) as check_money,count(id) as check_count'))->when($date,function($query,$date){
            return $query->whereDate('created_at',$date);
        })->when($month,function($query,$month){
            return $query->whereMonth('created_at',(int)$month);
        })->when($year,function($query,$year){
            return $query->whereYear('created_at',(int)$year);
        })->where('store_id',$this->user->id)->first()->toArray();
        $list = collect($tongji)->merge($list);
        return $this->success('',$list);
    }


    /**
     * 结款统计
     *
     * @return void
     */
    public function settleList(){
        $list = SettleList::getSettleList($this->user->id);
        return $this->success('',$list);
    }
}
