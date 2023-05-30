<?php

namespace App\Models;

use App\Models\user\TicketCode;
use App\Support\MApi;
use App\Support\NApi;
use App\Support\WpApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Workerman\Protocols\Ws;

/**
 * 网票网出票订单
 */
class ApiOrders extends Model
{

    protected $table = 'api_orders';

    protected $guarded  = [];
    // protected $fillable = ['*'];

    protected $hidden = ['updated_at'];

    protected $total_rtimes = 5;

    static $state_enum = ['2002'=>'购票失败','2000'=>'已出票','2001'=>'出票中','2003'=>'已取消'];


    function getStateTxt(){
        return self::$state_enum[$this->state]??'';
    }
    /**
     * 创建出票订单
     *
     * @return void
     */
    public function createOrder(string $sid,string $order_no,$mobile,$uprice,$order_amount,$pay_no){
        // $mobile = WpApi::ssl_encrypt($mobile);
        $data = array(
            'sid'=>$sid,
            'order_no'=> $order_no,
            'mobile'=> $mobile,
            'msgtype'=>1,
            'p_amount'=>$uprice,
            'p_user_amount'=>$order_amount,
            'pay_no'=>'',
            'plat_form_payno'=>$pay_no,
            'pay_flag'=>0,
            'remark'=>'',
            'state'=>2001
        );
       return ApiOrders::updateOrcreate(['sid'=>$sid],$data);
    }
    public function apiOutTicketnew()
    {
        $api_order = $this;
        $order_id = $api_order->sid;
        // "channel_order_id": "",
        // "order_id": "506614354417282241",
        // "phone_num": "15303122197",
        // "count": 2,
        // "price": 9600,
        // "placed_time": 1658893474,
        // "state": 2003, //订单状态（失败订单：2002， 出票中：2001，已出票：2000，已取消：2003）
        // "film_id": "1308",
        // "paiqi_id": "F3642573495",
        // "seat_names": "",
        // "ticket_code": ""
        $buyOrder = UserOrder::getOrderByOrderNo($api_order->order_no);
        if(empty($buyOrder)){
            return false;
        }
        $info = Store::where('id',$buyOrder->com_id)->first();
//        $resultOrder = \App\Support\MovieApiFactory::api('jufubao')->getOrder($order_id);
        $resultOrder = NApi::getorderstatus($buyOrder['cinema_id'],$buyOrder['api_lock_sid']);
        if($resultOrder['result']['orderStatus']=='3'){
            logger($resultOrder);
            logger($info->email);
            logger('退款订单');
            Mail::raw( $resultOrder['result']['orderNo'].'被退款', function ($message) use ($info) {
                $message->to($info->email)->subject('订单退款');
            });
            return false;
        }
        if(empty($resultOrder) || $resultOrder['result']['orderStatus']!='4'){
            \App\Jobs\Wangpiao\OutTicketJob::dispatch($api_order)->delay(4);
            return false;
        }
        $resultOrder=$resultOrder['result'];
        $state = (int)$resultOrder['orderStatus'];
        $t=['0','2001','2003','2002','2000'];
        if($state){
            $api_order->state = $t[$state];
        }
        $api_order->seat_info = $buyOrder['seat_names'];
        $api_order->amount = round((int)$buyOrder['rprice'] ,2);
        $api_order->s_time = $buyOrder['updated_at'];
        $code='';
        foreach ($resultOrder['ticketInfo'] as $item){
            if($item['name']=='qrcode'){
                $code=$item['value'];
            }
        }
        $api_order->ticket_info = $code;
        $api_order->save();
        TicketCode::where('order_id',$buyOrder->id)->where('user_id',$buyOrder->user_id)->delete();
        //出票
        try {
            TicketCode::addCode(array(
                'user_id'=>$buyOrder->user_id,
                'store_id'=>0,
                'order_id'=>$buyOrder->id,
                'order_no'=>$api_order->order_no,
                'ticket_code'=> $code,
                'valid_code'=>$code,
                'remark'=>''
            ));
        } catch (\Throwable $th) {
            logger('order_id:'.$order_id.','.$th->getMessage());
            return false;
        }
//        \App\Models\Tongji::addRecord($buyOrder->com_id,2,1,'接口出票');
        if($buyOrder->order_status == UserOrder::PAY_SUCCESS ){
            UserOrder::outTicket($api_order->order_no);
            try {
                $showtime = date('Y/m/d H:i',$buyOrder->show_time);
                $codeArr = TicketCode::where("order_id",$buyOrder->id)->pluck('ticket_code')->toArray();
                $cinemaName = $buyOrder->cinemas;
                $movie_name = "《{$buyOrder->movie_name}》";
                $cloudsms = new \App\Support\CloudSms;
                $message = $cloudsms->ticket_count_templet($showtime.$cinemaName.$movie_name,implode(',',$codeArr));
                $cloudsms->send_sms($buyOrder->buyer_phone,$message);
            } catch (\Throwable $th) {
                logger("短信发送失败：{$api_order->order_no},".$th->getMessage());
            }
        }

        return $api_order;
    }

    public function apiOutTicket()
    {
        $api_order = $this;
        $order_id = $api_order->sid;
        // "channel_order_id": "",
        // "order_id": "506614354417282241",
        // "phone_num": "15303122197",
        // "count": 2,
        // "price": 9600,
        // "placed_time": 1658893474,
        // "state": 2003, //订单状态（失败订单：2002， 出票中：2001，已出票：2000，已取消：2003）
        // "film_id": "1308",
        // "paiqi_id": "F3642573495",
        // "seat_names": "",
        // "ticket_code": ""
        $buyOrder = UserOrder::getOrderByOrderNo($api_order->order_no);
        if(empty($buyOrder)){
            return false;
        }
//        $resultOrder = \App\Support\Api\ApiHouse::getOrder($order_id);
        $resultOrder = MApi::getOrder($order_id);
        logger($resultOrder);
        if(empty($resultOrder)){
            return false;
        }

        $state = $api_order->state = (int)$resultOrder['state'];
        $api_order->seat_info = $resultOrder['seat_names'];
        $api_order->amount = round((int)$resultOrder['price'] / 100 ,2);
        $api_order->s_time = date('Y-m-d H:i:s',(int)$resultOrder['placed_time']);
        $ticketInfo = $api_order->ticket_info = $resultOrder['ticket_code'];
        $api_order->save();

        if($state == 2000 && !empty($ticketInfo))
        {
            $ticketInfo = \App\Support\MApi::decrypt_code($ticketInfo);
            // dd($ticketInfo);
            TicketCode::where('order_id',$buyOrder->id)->where('user_id',$buyOrder->user_id)->delete();
            //出票
            try {
                TicketCode::addCode(array(
                    'user_id'=>$buyOrder->user_id,
                    'store_id'=>0,
                    'order_id'=>$buyOrder->id,
                    'order_no'=>$api_order->order_no,
                    'ticket_code'=> $ticketInfo['ticketcode'],
                    'valid_code'=>$ticketInfo['validcode'],
                    'remark'=>''
                ));
            } catch (\Throwable $th) {
                logger('order_id:'.$order_id.','.$th->getMessage());
                return false;
            }
        }

        if($buyOrder->order_status == UserOrder::PAY_SUCCESS ){
            UserOrder::outTicket($api_order->order_no);
            try {
                $showtime = date('Y/m/d H:i',$buyOrder->show_time);
                $codeArr = TicketCode::where("order_id",$buyOrder->id)->pluck('ticket_code')->toArray();
                $cinemaName = $buyOrder->cinemas;
                $movie_name = "《{$buyOrder->movie_name}》";
                $cloudsms = new \App\Support\CloudSms;
                $message = $cloudsms->ticket_count_templet($showtime.$cinemaName.$movie_name,implode(',',$codeArr));
                $cloudsms->send_sms($buyOrder->buyer_phone,$message);
            } catch (\Throwable $th) {
                logger("短信发送失败：{$api_order->order_no},".$th->getMessage());
            }
        }

        return $api_order;
    }


    /**
     * 申请购票
     *
     * @return void
     */
    public function buyTicket(){
        $api_order = $this;
        if($api_order->state == 1){
            return true;
        }
        //申请下单
        $getOrder = WpApi::applyTicket($api_order->sid,$api_order->mobile,$api_order->p_amount,$api_order->p_user_amount);
        if(!$getOrder['status']){ //下单失败
            $api_order->remark = $getOrder['msg'];
            $api_order->save();
            return false;
        }
        $result = $getOrder['data'];
        $payNo = $result[0]['PayNo'];
        if($payNo != ''){
            $buyTicket = WpApi::buyTicket($api_order->sid,$payNo,$api_order->plat_form_payno);
            if(!$buyTicket['status']){
                $api_order->remark = $buyTicket['msg'];
                $api_order->save();
                return false;
            }
            $api_order->rtimes = $api_order->rtimes + 1;
            $api_order->pay_no = $payNo;
            $api_order->state = 1;
            $api_order->save();
        }
        return true;
    }

    /**
     * 重发验票码
     *
     * @return void
     */
    public function resendMsg(){
        $api_order = $this;
        if($api_order->state != 3){
            return false;
        }
        WpApi::reSendMsg($api_order->sid);
    }



    /**
     * 订单查询并更新
     *
     * @return void
     */
    public function searchOrder(){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']!=1){
            return $this->apiOutTicket();
        }else{
            return $this->apiOutTicketnew();
        }

        $api_order = $this;
        if($api_order->state == 3){
            return true;
        }
        $buyOrder = UserOrder::getOrderByOrderNo($api_order->order_no) ;
        if($api_order->rtimes >= $this->total_rtimes){
            if($buyOrder->order_status != UserOrder::PAY_SUCCESS && $buyOrder->order_status != UserOrder::ORDER_SUCCESS){ //停止写票
                WpApi::stopBuyTicket($api_order->sid);
            }
            return false;
        }
        $result = WpApi::searchOrderInfoBySID($api_order->sid);
        if(!$result['status'] || empty($result['data'])){
            return false;
        }
        $result = $result['data'];

        $api_order->rtimes = $api_order->rtimes + 1;
        $api_order->save();
        $ticket_info = $result["QRCodeText"]?$result["QRCodeText"]:$result["TicketID"];
        if(empty($result) || empty($ticket_info) ){
            \App\Jobs\Wangpiao\OutTicketJob::dispatch($api_order)->delay($api_order->rtimes*5);
            return false;
        }
        $res = array(
            'pay_flag'=> $result["PayFlag"],
            'cinema_id'=> $result["CinemaID"],
            'film_id'=> $result["FilmID"],
            'stype'=> $result["Stype"],
            's_time'=> $result["Stime"],
            // ''=> $result["Mobile"] => "15303122197",
            'cinema_name'=> $result["CinemaName"],
            'hall_name'=> $result["HallName"],
            'film_time'=> $result["FilmTime"],
            'amount'=> $result["Amount"],
            'film_name'=> $result["FilmName"],
            'effective_time'=> $result["EffectiveTime"],// => "2022-03-08 17:41:00",
            'ticket_id'=> $result["QRCodeText"]?$result["QRCodeText"]:$result["TicketID"],
            'pwd'=> $result["Pwd"],
            'seat_info'=> $result["SeatInfo"],// => "3:2",
            'payment_no'=> $result["PaymentNo"] ,//=> "1231321231547987943541541351",
            'pay_type'=> $result["PayType"],
            // ''=> $result["FilmPhoto"]?:'',
            // ''=> $result["PayNo"] => "",
            // ''=> $result["QRCodeText"] => "",
            'ticket_info'=> $result["TicketInfo"],// => "凭序号999999验票码999999至影院柜台或影城自动出票机取票。",
        );
        // -1表示用户取消订单，0 未支付 1，4正在处理中 2支付失败 3购票成功 ,5准备退款，6退款成功
        // if(!in_array($res['pay_flag'],[3,5,6])){
        //     return false;
        // }

        $res['state'] = 3;
        $res['remark'] = '';
        $api_order->fill($res)->save();

        TicketCode::where('order_id',$buyOrder->id)->where('user_id',$buyOrder->user_id)->delete();
        //出票
        TicketCode::addCode(array(
            'user_id'=>$buyOrder->user_id,
            'store_id'=>0,
            'order_id'=>$buyOrder->id,
            'order_no'=>$api_order->order_no,
            'ticket_code'=> $res['ticket_id'],
            'valid_code'=>$res['pwd'],
            'remark'=>$res['ticket_info']
        ));
        if($buyOrder->order_status == UserOrder::PAY_SUCCESS ){
            UserOrder::outTicket($api_order->order_no);
            try {
                $showtime = date('Y/m/d H:i',$buyOrder->show_time);
                $codeArr = TicketCode::where("order_id",$buyOrder->id)->pluck('ticket_code')->toArray();
                $cinemaName = $buyOrder->cinemas;
                $movie_name = "《{$buyOrder->movie_name}》";
                $cloudsms = new \App\Support\CloudSms;
                $message = $cloudsms->ticket_count_templet($showtime.$cinemaName.$movie_name,implode(',',$codeArr));
                $cloudsms->send_sms($buyOrder->buyer_phone,$message);
            } catch (\Throwable $th) {
                logger("短信发送失败：{$api_order->order_no},".$th->getMessage());
            }
        }

        return $api_order;
    }

    /**
     * 获取订单信息
     *
     * @param string $sid
     * @return ApiOrders
     */
    static function getOrder(string $sid){
        return self::where('sid',$sid)->first();
    }

}
