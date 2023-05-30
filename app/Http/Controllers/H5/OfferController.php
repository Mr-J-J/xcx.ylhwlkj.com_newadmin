<?php

namespace App\Http\Controllers\H5;

use App\Models\OfferRules;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\store\StoreOfferOrder;
use App\Models\store\StoreOfferRecord;


class OfferController extends StoreBaseController
{
    public function __construct()
    {
        parent::__construct();

        if($this->store->store_state == 1){
            response($this->error('注册信息审核中'))->send();die;
        }
    }
    /**
     * 撤销报价
     *
     * @param Request $req
     * @return void
     */
    public function cancelOffer(Request $req){
        $offer_id = $req->input('offer_id',0);
        $offerRecord = StoreOfferRecord::where('id',$offer_id)
                        ->where('store_id',$this->store->id)
                        ->where('offer_status',0)
                        ->first();
        if(empty($offerRecord)){
            return $this->error('操作失败');
        }

        StoreOfferRecord::destroy($offerRecord->id);
        return $this->success('撤销成功');
    }
    /**
     * 商家报价
     *
     * @param Request $req
     * @return void
     */
    public function addOffer(Request $req){
        $order_no = $req->input('order_no','');
        $data['price'] = round($req->post('price'),2);
        $id = $req->post('offer_id'); //报价id
        if(!empty($id)){
            $data['id'] = intval($id);
        }
        if(empty($order_no)){
            return $this->error('参数错误');
        }
        if($data['price']  == 0 ){
            return $this->error('请填写报价');
        }
        $order = StoreOfferOrder::getOrderByOrderNo($order_no);
        if(empty($order)){
            return $this->error('订单不存在');
        }
        if($order->offer_status !=0){
            return $this->error('竞价已结束');
        }
        // $maxPrice = StoreOfferRecord::getMaxPrice($order->amount);
        // $maxPrice = round($maxPrice / $order->ticket_count,2);
        $minPrice = $order->getMinPrice_V2();
        $maxPrice = $order->getMaxPrice_V2();
//        logger($data['price']);
//        logger($minPrice);
//        logger($maxPrice);
        if($data['price'] < $minPrice){
            return $this->error('出价过低');
        }
        if($data['price'] > $maxPrice){
            return $this->error('出价过高');
        }
        try {
            StoreOfferRecord::saveOffer($data,$this->store->storeInfo,$order);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
        \App\Models\store\IgnoreOrder::addIgnoreOrder($order_no,$this->store->id);
        return $this->success('报价成功');
    }
    /**
     * 添加规则
     *
     * @param Request $req
     * @return void
     */
    public function addRules(Request $req){
        $data = $req->post();
        try {
            OfferRules::saveRules($data,$this->store);
        } catch (\Exception $e) {
            return $this->error('保存失败:'.$e->getMessage());
        }
        return $this->success('保存成功');
    }

    public function ruleList(Request $req){
        $rule_id = $req->input('rule_id');
        $list = OfferRules::ruleList($this->store,$rule_id);
        return $this->success('获取成功',$list);
    }
}
