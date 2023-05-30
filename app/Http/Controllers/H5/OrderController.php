<?php

namespace App\Http\Controllers\H5;


use App\Support\WpApi;
use App\Support\Helpers;
use App\Models\UserOrder;
use App\Models\OfferRules;
use Illuminate\Http\Request;
use App\Models\user\TicketCode;
use App\ApiModels\Wangpiao\Cinema;
use App\Models\store\OfferServices;
use App\Models\store\StoreOfferOrder;
use App\Models\store\StoreOfferRecord;

class OrderController extends StoreBaseController
{
    public function __construct()
    {
        parent::__construct();

        if($this->store && $this->store->store_state == 1){
            response($this->error('注册信息审核中'))->send();die;
        }
    }
    /**
     * 新订单
     *
     * @return void
     */
    public function index(Request $req){

        $type = $req->input('type',1); //1新订单 2已报价 3待出票 4已出票  5已关闭  6已退回
        $list = array();
        if($this->store->storeInfo && $this->store->storeInfo->taking_mode == 0){
            return $this->success('成功',compact('list'));
        }
        $offerService = new OfferServices;
        $list = $offerService->OfferOrderList($this->store,(int)$type);

        $list->transform(function ($item) {
            $item = (object)$item->toArray();
            $item->back_times = $item->offer_times - 1;
            $item->show_date = date('Y-m-d',$item->show_time);
            $item->show_time_txt = date('m-d',$item->show_time).' '.date('H:i',$item->show_time).'~'.date('H:i',$item->close_time);
            if($item->win_store_id != $this->store->id){
                // $item->seat_names = str_repeat('*排*座',$item->ticket_count);
                $item->seat_names = '';
            }
            $item->market_price = round($item->market_price / $item->ticket_count,2)." x ".$item->ticket_count;
            return $item;
        });
        return $this->success('成功',compact('list'));
    }

    /**
     * 忽略订单
     *
     * @param Request $req
     * @return void
     */
    public function ignoreOrder(Request $req){
        $order_no = $req->input('order_no','');
        $offerOrder = StoreOfferOrder::getOrderByOrderNo($order_no);
        if(empty($offerOrder)){
            return $this->error('订单不存在');
        }

        \App\Models\store\IgnoreOrder::addIgnoreOrder($order_no,$this->store->id);
        return $this->success('已忽略');
    }

    /**
     * 确认出票
     *
     * @param Request $req
     * @return void
     */
    public function confirmOutTicket(Request $req){
        $order_no = $req->input('order_no','');
        $offerOrder = StoreOfferOrder::where('order_no',$order_no)->where('store_id',$this->store->id)->first();
        if(empty($offerOrder)){
            return $this->error('您未中标此订单');
        }
        $orderInfo = UserOrder::getOrderByOrderNo($order_no);
        if(!$orderInfo->canOutTicket()){
            return $this->error('该订单已出票或取消');
        }
        $codeCount = TicketCode::where("order_id",$orderInfo->id)->count();
        if($codeCount < $orderInfo->ticket_count){
            return $this->error('请录入取票码');
        }
        try {
            $offerService = new OfferServices;
            $offerService->outTicket($this->store->storeInfo,$offerOrder);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        try {
            $showtime = date('Y/m/d H:i',$orderInfo->show_time);
            $codeArr = TicketCode::where("order_id",$orderInfo->id)->pluck('ticket_code')->toArray();
            $cinemaName = $orderInfo->cinemas;
            $movie_name = "《{$orderInfo->movie_name}》";
            $cloudsms = new \App\Support\CloudSms;
            $message = $cloudsms->ticket_count_templet($showtime.$cinemaName.$movie_name,implode(',',$codeArr));
            $cloudsms->send_sms($orderInfo->buyer_phone,$message);
        } catch (\Throwable $th) {
            logger("短信发送失败：{$order_no},".$th->getMessage());
        }
        return $this->success('出票成功');

    }


    /**
     * 确认出票一步式
     */
    public function confirmOutTicketOne(Request $request){
        $order_no = $request->input('order_no','');
        $offerOrder = StoreOfferOrder::where('order_no',$order_no)->where('store_id',$this->store->id)->first();
        if(empty($offerOrder)){
            return $this->error('您未中标此订单');
        }
        $orderInfo = UserOrder::getOrderByOrderNo($order_no);
        if(!$orderInfo->canOutTicket()){
            //return $this->error('该订单已出票或取消');
        }

        $ticketCodeList = json_decode($request->input('code',''));

        if(!$ticketCodeList || !count($ticketCodeList)){
            return $this->error('请录入取票码');
        }
        // $codeCount = TicketCode::where("order_id",$orderInfo->id)->count();
        // if($codeCount >= count($ticketCodeList)){
        //     return $this->error('取票码已录入');
        // }
        $new_seat_names = $request->input('new_seat_names','');
        if(!empty($new_seat_names)){
            $orderInfo->old_seat_names = $orderInfo->seat_names;
            $orderInfo->seat_names = $new_seat_names;
            $orderInfo->save();
        }
        TicketCode::where('order_id',$orderInfo->id)->where('user_id',$orderInfo->user_id)->delete();
        foreach($ticketCodeList as $k => $item){
            $data = array();
            $data['store_id'] = $this->store->id;
            $data['user_id'] = $orderInfo->user_id;
            $data['order_id'] = $orderInfo->id;
            $data['order_no'] = $orderInfo->order_no;
            $data['ticket_code'] = $item->ticket_code??'';
            $data['valid_code'] = $item->valid_code??'';
            TicketCode::addCode($data,$orderInfo->ticket_count);
        }

        $images = $request->input('images','');
        if(!empty($images)){
            $data = array(
                'order_id' => $orderInfo->id,
                'order_no' => $orderInfo->order_no,
                'user_id' => $orderInfo->user_id,
                'store_id' => $this->store->id,
                'images' => $images,
            );
            \App\Models\user\TicketImg::updateOrCreate(['order_id'=>$orderInfo->id,'user_id'=>$orderInfo->user_id],$data);
        }
        if($orderInfo->order_status != 30){
            try {
                $offerService = new OfferServices;
                $offerService->outTicket($this->store->storeInfo,$offerOrder);
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }

            try {
                $showtime = date('Y/m/d H:i',$orderInfo->show_time);
                $codeArr = TicketCode::where("order_id",$orderInfo->id)->pluck('ticket_code')->toArray();
                $cinemaName = $orderInfo->cinemas;
                $movie_name = "《{$orderInfo->movie_name}》";
                $cloudsms = new \App\Support\CloudSms;
                $message = $cloudsms->ticket_count_templet($showtime.$cinemaName.$movie_name,implode(',',$codeArr));
                $cloudsms->send_sms($orderInfo->buyer_phone,$message);
            } catch (\Throwable $th) {
                logger("短信发送失败：{$order_no},".$th->getMessage());
            }
        }else{
            return $this->success('出票信息已修改');
        }

        return $this->success('出票成功');
    }




    /**
     * 订单转单
     *
     * @param Request $request
     * @return void
     */
    public function backOutOrder(Request $request){
        $order_no = $request->input('order_no','');
        $offerOrder = StoreOfferOrder::getOrderByOrderNo($order_no);
        if(empty($offerOrder)){
            return $this->error('订单信息不存在');
        }

        if($offerOrder->offer_status != 1){
            return $this->error('转单操作失败');
        }

        if($offerOrder->store_id != $this->store->id){
            return $this->error('转单失败：您未中标此订单！');
        }
        \App\Models\CommonOrder::addOrder($offerOrder,2);
        $offerOrder->store_id = 0;
        $offerOrder->offer_status = 4;//关闭后由客服人工分配
        $offerOrder->save();
        return $this->success('转单成功');
    }

    /**
     * 释放座位
     *
     * @param Request $request
     * @return void
     */
    public function releaseSeat(Request $request){
        $order_no = $request->input('order_no','');
        $order = UserOrder::getOrderByOrderNo($order_no);
        $message = '座位已释放';
        $status = 0; //未释放
        if(!empty($order) && !empty($order->api_lock_sid)){
            $result = \App\Support\Api\ApiHouse::unLockSeat($order->api_lock_sid);
            logger(json_encode($result).'释放座位');
            if(!empty($result[0]['Result'])||$result=='ok'){
                if($result=='ok'){
                    $message='座位释放成功';
                    $status = 1;
                }else{
                    $message = $result[0]['Result']?'座位释放成功':'座位失败';
                    $status = $result[0]['Result'] ? 1 : 0;
                }
                $order->api_release_status = $status;
                $order->save();
            }
        }
        return $this->success($message,compact('status'));
    }

    /**
     * 取票码录入
     *
     * @param Request $req
     * @return void
     */
    public function insertTicket(Request $req){
        $order_no = $req->input('order_no','');

        $offerOrder = StoreOfferOrder::where('order_no',$order_no)->where('store_id',$this->store->id)->first();
        if(empty($offerOrder)){
            return $this->error('您未中标此订单');
        }
        try {
            $data = array();
            $data['store_id'] = $this->store->id;
            $orderInfo = UserOrder::getOrderByOrderNo($order_no);
            if(!$orderInfo->canOutTicket()){
                return $this->error('该订单已出票或取消');
            }
            $data['user_id'] = $orderInfo->user_id;
            $data['order_id'] = $orderInfo->id;
            $data['order_no'] = $orderInfo->order_no;
            $data['id'] = (int)$req->input('code_id','');
            $data['images'] = $req->input('images','');
            $data['ticket_code'] = $req->input('code','');
            $data['valid_code'] = $req->input('valid_code','');
            TicketCode::addCode($data,$orderInfo->ticket_count);
            // if($orderInfo->code()->count() == $orderInfo->ticket_count){
            //     try {
            //         $offerService = new OfferServices;
            //         $offerService->outTicket($this->store->storeInfo,$offerOrder);
            //     } catch (\Exception $e) {
            //         return $this->error($e->getMessage());
            //     }
            // }
        } catch (\Exception $e) {
            logger('H5/OrderController:105'.$e->getMessage());
            return $this->error($e->getMessage());
        }
        return $this->success('录入成功');
    }

    /**
     * 订单详情（用于报价时展示）
     *
     * @param Request $Req
     * @return void
     */
    public function info(Request $req){
        $order_no = $req->input('order_no','');

        $field = array(
            'id',
            'order_no',
            'ticket_count',
            'movie_name',
            'show_time',
            'close_time',
            'cinema_id',
            'cinemas',
            'amount',
            'buyer_phone',
            'accept_seats',//可调座
            'seat_flag', //0普通座  1情侣左 2情侣右
            'market_price',
            'halls',
            'seat_names',
            'expire_time',
            'offer_status',
            'show_version',
            'store_id',
            'movie_image'
        );
        $info = StoreOfferOrder::select($field)->where('order_no',$order_no)->first();
        if(empty($info)){
            return $this->error('订单未找到');
        }

        //影院地址
        $cinemaInfo = Cinema::where('id',$info->cinema_id)->first();
        if($cinemaInfo){
            $info->address = $cinemaInfo->address;
        }

        //报价
        $offerRecord = StoreOfferRecord::where('order_id',$info->id)->where('store_id',$this->store->id)->orderBy('created_at','desc')->first();
        if(!empty($offerRecord)){
            $info->offer_amount = $offerRecord->offer_amount;
            $info->offer_id = $offerRecord->id;
        }
        $info->show_date = date('Y-m-d',$info->show_time);
        $info->show_time = date('H:i',$info->show_time);
        $info->close_time = date('H:i',$info->close_time);
        $priceRatemin = (int) Helpers::getSetting('offer_price_min');
        $minPrice = round($info->amount * ($priceRatemin / 100),2);
        $info->min_price = round($minPrice / $info->ticket_count,2);
        $priceRate = (int) Helpers::getSetting('offer_price');
        $maxPrice = round($info->amount * ($priceRate / 100),2);
        $info->max_price = round($maxPrice / $info->ticket_count,2);
        if($info->offer_status == 1 && $info->store_id != $this->store->id){
            $info->offer_status =3;
        }
        if($info->offer_status == 1 || $info->offer_status == 2){
            $ticketCode = TicketCode::where('order_no',$info->order_no)->get();
            $info->ticket_code = $ticketCode;
        }

        $images = \App\Models\user\TicketImg::where('order_no',$info->order_no)->first();

        if($images){
            if(!empty($images->images)){
                $info->images = explode(',',$images->images);
            }
        }

        $seatNames = $info->seat_names;
        if($info->offer_status  != 2 && $info->store_id != $this->store->id){
            // $info->seat_names = str_repeat('*排*座',$info->ticket_count);
            $info->seat_names = '';
        }
        $info->buyer_phone = '';
        // if($info->offer_status == 1 && $info->win_store_id == $this->store->id){
        //     $info->seat_names = $seatNames;
        // }
        $userOrder = UserOrder::getOrderByOrderNo($info->order_no);
        $info->market_price = round($info->market_price/$info->ticket_count,2);
        $info->seat_status = $userOrder->api_release_status;
        $info->cinemas = $userOrder->citys.' · '.$info->cinemas;
        $info->ticket_timeout = intval(StoreOfferOrder::getDelayTime('offer_out_ttl') / 60);
        $info->offer_timeout = intval(StoreOfferOrder::getDelayTime('offer_ttl') / 60);
        $info = $info->toArray();
        unset($info['id']);
        return $this->success('',$info);
    }
}
