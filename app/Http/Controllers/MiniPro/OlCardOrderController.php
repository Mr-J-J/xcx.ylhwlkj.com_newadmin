<?php

namespace App\Http\Controllers\MiniPro;


use App\CardModels\OlCard;
use Illuminate\Http\Request;
use App\MallModels\ProductSku;
use App\CardModels\OlCardExChange;
use App\CardModels\OlCardOrder;
use App\CardModels\OlCardProduct;
use App\MallModels\ProductPurchase;
use Illuminate\Support\Facades\Hash;

/**
 * 影城卡
 */
class OlCardOrderController extends UserBaseController
{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 我的影城卡
     *
     * @param Request $request
     * @return void
     */
    public function myOlCard(Request $request){

        $type = $request->input('type',0);   //0未使用  1已使用 2 已过期
        $limit = $request->input('limit',10);
        $nouse = OlCard::where('user_id',$this->user->id)->where('state',10)->where('use_number',0)->count();
        $used = OlCard::where('user_id',$this->user->id)->where('state',10)->where('use_number','>',0)->count();
        $expire = OlCard::where('user_id',$this->user->id)->where('state',20)->count();
        $list = OlCard::where('user_id',$this->user->id)
                    ->when($type==0,function($query){  //未使用
                        return $query->where('state',10)->where('use_number',0);
                    })
                    ->when($type==1,function($query){   //已使用
                        return $query->where('state',10)->where('use_number','>',0);
                    })
                    ->when($type==2,function($query){   //已使用
                        return $query->where('state',20);
                    })
                    ->paginate((int)$limit);
        foreach($list as $item){
            $item->title = $item->product?$item->product->title:'';
            $item->image = $item->product?$item->product->image:'';
            $item->start_time = date('Y-m-d',$item->open_time);
            $item->open_time = date('Y-m-d H:i',$item->open_time);
            $item->expire_time = date('Y-m-d ',$item->expire_time);
            $item->limit_number = $item->number - $item->use_number;
            $item->log_list = OlCardExChange::getList($item->id)->map(function($query){
                return $query->only(['order_no','ex_no','ex_time','ex_number']);
            });
        }
        return $this->success('',compact('nouse','used','expire','list'));
    }

    /**
     * 使用须知
     *
     * @param Request $request
     * @return void
     */
    public function cardTips(Request $request){
        $cardNo = $request->input('card_no','');
        $tips = '影城卡使用须知';
        return $this->success('',compact('tips'));
    }

    /**
     * 影城卡线下卡激活
     *
     * @param Request $request
     * @return void
     */
    public function activeCard(Request $request){
        $cardNo = $request->input('card_no','');
        $cardKey = $request->input('card_key','');

        if(empty($cardNo)){
            return $this->error('请输入卡号');
        }
        if(empty($cardKey)){
            return $this->error('请输入卡密');
        }

        $cardModel = new OlCard;
        $cardInfo = $cardModel->getCardByNo($cardNo);
        if(!$cardInfo){
            return $this->error('卡号无效');
        }
        $time = time();
        if($cardInfo->expire_time<$time){
            return $this->error('该卡已过期');
        }
        $cardInfo->checkOfflineCard($cardKey);

        if($cardInfo->activeCard($this->user)){
            return $this->success('影城卡已激活');
        }
        return $this->error('影城卡激活失败');
    }

    /*
     * 查询卡
     */
    public function lookcard(Request $request){
        $cardNo = $request->input('card_no');
        $cardModel = new OlCard;
        $cardInfo = $cardModel->getCardByNo($cardNo);
        return $this->success('',$cardInfo);
    }

    public function orderList(Request $reqest){
        $field = [
            'order_sn','product_id','sku_id','product_title','product_info','check_start_time','check_end_time','order_status','order_amount','goods_count','created_at'
        ];
        $list = OlCardOrder::where('user_id',$this->user->id)->latest()->paginate(10,$field);
        foreach($list as $item){
            $item->check_start_time = date('Y.m.d',$item->check_start_time);
            $item->check_end_time = date('Y.m.d',$item->check_end_time);
            $item->status_txt = OlCardOrder::$status[$item->order_status];
            $item->product_info = json_decode($item->product_info);
        }
        return $this->success('',$list);
    }

    /**
     * 订单详情
     *
     * @param Request $request
     * @return void
     */
    public function orderInfo(Request $request){
        $orderNo = $request->input('order_no','');
        $order_info = OlCardOrder::getOrderByNo($orderNo);
        if(empty($order_info)){
            return $this->error('订单已删除或不存在');
        }

        $order_info->order_amount = $order_info->pay_money;
        $order_info = $order_info->makeHidden(['product_info','need_deliver','transaction_id','agreement','remark']);
        $order_info->status_txt = OlCardOrder::$status[$order_info->order_status];
        if($order_info->check_start_time){
            $order_info->check_start_time = date('Y.m.d',$order_info->check_start_time);
        }
        if($order_info->check_end_time){
            $order_info->check_end_time = date('Y.m.d',$order_info->check_end_time);
        }
        $order_info->olcard = OlCard::getCardByProduct($order_info->product_id,$order_info->goods_count,$orderNo);

        $sku = json_decode($order_info->product_info);
        if($sku){
            $sku->title = $order_info->product_title;
        }
        $content = $order_info->tips;
        return $this->success('',compact('order_info','sku','content'));
    }

}
