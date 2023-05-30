<?php

namespace App\Http\Controllers\H5;

use DB;
use App\Models\City;
use App\Support\MApi;
use App\Models\Cinema;
use App\Models\Region;
use App\Support\WpApi;
use App\Models\JobList;
use App\Models\Setting;
use App\Models\Schedule;
use App\Support\Helpers;
use App\Support\SoapApi;
use App\MallModels\Group;
use App\MallModels\Order;
use App\Models\StoreInfo;
use App\Models\UserOrder;
use App\Models\OfferRules;
use App\Models\TicketUser;
use App\Models\CommonOrder;
use App\CardModels\RsStores;
use App\UUModels\UUAreaList;
use Illuminate\Http\Request;
use App\CardModels\CardOrder;
use App\Models\UserPayDetail;
use App\Models\user\Commision;
use App\UUModels\UUScenicSpot;
use App\UUModels\UUTicketOrder;
use App\UUModels\UUTicketSaleNum;
use App\UUModels\UUTicketStorage;
use App\UUModels\UUScenicSpotInfo;
use App\Models\store\OfferServices;
use App\Models\user\OrderCommision;
use App\ApiModels\Wangpiao\District;
use App\UUModels\UUScenicSpotTicket;
use EasyWeChat\Kernel\Messages\Card;
use App\ApiModels\Wangpiao\Schedules;
use App\Models\store\StoreOfferOrder;
use Illuminate\Support\Facades\Cache;
use App\CardModels\StoreBalanceDetail;
use App\Models\store\StoreOfferRecord;
use App\ApiModels\Wangpiao\ScheduleCinema;
use App\ApiModels\Wangpiao\City as WangpiaoCity;
use App\ApiModels\Wangpiao\Cinema as WangpiaoCinema;

class TestController extends StoreBaseController
{
    public function chooseStore(Request $req){
        $order_id = $req->input('id');
        $result = StoreOfferOrder::calcOfferToOrder($order_id);

        return $this->success('成功',$result);
    }

    private function test(){
        $app = $this->getApp(3);
                
        $payResult = $app->order->unify([
            'body' => '-电影票',
            'out_trade_no' => 'AB13465468797xcddd',
            'total_fee' => 1,
            // 'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            // 'notify_url' => 'https://pay.weixin.qq.com/wxpay/pay.action', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => 'oxjbE4m4Gv-J_clAwcYLbO17zi0s',
        ]);

//         "return_code" => "SUCCESS"
//   "return_msg" => "OK"
//   "result_code" => "FAIL"
//   "err_code_des" => "appid和openid不匹配"
//   "err_code" => "PARAM_ERROR"
//   "mch_id" => "1611939730"
//   "appid" => "wx9ea0e5b6f66b974a"
//   "nonce_str" => "eJT8QS1W4QSnlpDA"
//   "sign" => "C642D5EED496743C711C601152DEF9CE"

        // dd($payResult);
        $pay_param = $app->jssdk->bridgeConfig($payResult['prepay_id'], false);

        dd($pay_param);
    }

    private function step1($orderno){
        //创建新的竞价订单
        $order = UserOrder::getOrderByOrderNo($orderno);
        // StoreOfferOrder::createOfferOrder($order);
        //$res = $order->paySuccess($order,'12312321212156665');
        
        // \App\Jobs\UpdateOrder::dispatch($order);

        // dd($order);

        // $order = Order::getOrderByNo($orderno);
        // $order->paySuccess();
    }

    private function closeOrder($orderno){
        $order = StoreOfferOrder::getOrderByOrderNo($orderno);
        $order->closeOrder($order);
    }

    

    public function index(Request $req){
        // dd(11);
        //票付通订单超时
        $pwList = \App\UUModels\UUPayOrder::where('pay_status','<',2)->where('expire_time','<',time())->get();
     
        foreach($pwList as $item){
            $item->cancelOrder();
        }
        dd(111);
        // return $this->pkcs5_unpad($des_data);
        // $bytes = array();

        // for ($i = 0; $i < strlen($data); $i++) {

        //     $bytes[] = ord($data[$i]);

        // }

        // $str = '';

        // foreach ($bytes as $ch) {

        //     $str .= chr($ch);

        // }
        // $data = base64_encode($str);
        // $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('des-ede3'));
        
        
        $pad = ord($text{strlen($text)-1});
 
        // if ($pad > strlen($text)) {
        //     echo 66;
        // };
        // if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        // $decData = substr($text, 0, -1 * $pad);
        
        // $res = \App\Support\Api\ApiHouse::lockSeat($param); //506631226713648472
        // $res = \App\Support\Api\ApiHouse::payOrder('506631226713648472','NSD506631226713648472'); //506631226713648472
        // $res = \App\Support\Api\ApiHouse::unLockSeat('506614354417282241');
        dd($data,$decData);
        $res = \App\Support\Api\ApiHouse::getOrder('506631226713648472');
        
        
                
        # 获取景区列表  
        // $res = $api->Get_ScenicSpot_List();
        // UUScenicSpot::saveData($res);
        
        //获取景区详情
        
        // $res = $api->Get_ScenicSpot_Info((int)$city_id);
        // UUScenicSpotInfo::saveData($res);

        //获取门票列表
        // $res = $api->Get_Ticket_List((int)$city_id);
        // UUScenicSpotTicket::saveData($res);

        //获取门票价格
        // $ticket = UUScenicSpotTicket::find((int)$city_id);
        // $res = $ticket->getPriceList('2022-4-10','2022-4-20');
        //身份证检验
        // $res = $api->Check_PersonID('130627198910023630');

        //预判下单
        // $ticket = UUScenicSpotTicket::find(45);
        $time = '2022-4-18 13:00';
        // $storage = UUTicketStorage::where('storage_id',$ticket->getStorageId())->whereDate('date','2022-4-15')->first();
        // $res =  $ticket->OrderPreCheck(1,'2022-4-15 11:00','15303122197','宁宁','130627198910023630',$storage->buy_price);

        // $api->UUlid = $ticket->UUlid;//产品id,对应 Get_Ticket_List.UUlid
        // $api->UUid = $ticket->UUid;//门票id
        // $api->UUaid = $ticket->UUaid;//供应商id
        // $api->orderNo = date('YmdHis').mt_rand(1000,9999);//贵方订单号,请确保唯一
        // $api->tprice = $storage->buy_price;//供应商配置的结算单价，单位：分
        // $api->tnum = 1;//购买数量
        // $api->playtime = $time;//游玩日期
        // $api->ordername = '宁宁';//客户姓名,多个用英文逗号隔开，不支持特殊符号
        // $api->ordertel = '15303122197';//取票人手机号
        // $api->contactTEL = '15303122197';//多个用英文逗号隔开，不支持特殊符
        // $api->personID = '130627198910023630';//身份证,
        // $api->smsSend = 0;//0 -票付通发送短信 1-票付通不发短信（前提是票属性上有勾选下单成功发短信给游客）
        // $api->paymode = 0;//扣款方式（0使用账户余额2使用供应商处余额4现场支付
        // $api->ordermode = 0;//下单方式（0正常下单1手机用户下单）
        // $api->assembly = '';//集合地点 线路时需要，参数必传，值可传输空
        // $api->series = '';//团号 线路，演出时需要，参数必传，值可传输空； 演出需要时传输格式：json_encode(array(int)场馆id,(int)场次id,(string)分区id));        
        // $api->concatID = 0;//联票ID
        // $api->pCode = 0;//套票ID
        // $api->orderRemark = '';//备注
        // $api->OrderCallbackUrl = '';//核销/退票回调地址
        // $res =  $api->PFT_Order_Submit();
        
//         景区id：lid = 60638 门票id：tid = 143342 供应商id: m=113以及特定的账号密码测试（账号 ：100019
// 密钥 ： a36c415c112c749aba38efd7c5abe755），
        // $api->UUlid = '60638';//产品id,对应 Get_Ticket_List.UUlid
        // $api->UUid = '143342';//门票id
        // $api->UUaid = '113';//供应商id
        // $api->orderNo = date('YmdHis').mt_rand(1000,9999);//贵方订单号,请确保唯一
        // $api->tprice = 0;//供应商配置的结算单价，单位：分
        // $api->tnum = 1;//购买数量
        // $api->playtime = $time;//游玩日期
        // $api->ordername = '宁宁';//客户姓名,多个用英文逗号隔开，不支持特殊符号
        // $api->ordertel = '15303122197';//取票人手机号
        // $api->contactTEL = '15303122197';//多个用英文逗号隔开，不支持特殊符
        // $api->personID = '';//身份证,
        // $api->smsSend = 0;//0 -票付通发送短信 1-票付通不发短信（前提是票属性上有勾选下单成功发短信给游客）
        // $api->paymode = 0;//扣款方式（0使用账户余额2使用供应商处余额4现场支付
        // $api->ordermode = 0;//下单方式（0正常下单1手机用户下单）
        // $api->assembly = '';//集合地点 线路时需要，参数必传，值可传输空
        // $api->series = '';//团号 线路，演出时需要，参数必传，值可传输空； 演出需要时传输格式：json_encode(array(int)场馆id,(int)场次id,(string)分区id));        
        // $api->concatID = 0;//联票ID
        // $api->pCode = 0;//套票ID
        // $api->orderRemark = '异步返码订单';//备注
        // $api->OrderCallbackUrl = route('pwordernotify');//核销/退票回调地址
        // $res =  $api->PFT_Order_Submit();

        // $order = UUTicketOrder::getOrderByOrderNo($orderno);
        // $res = $order->PFT_Order_Submit();
        
        //重推订单
        // $res = $order->PFT_Order_Submit();

        //订单查询 
        // $api->remoteOrdernum = '';
        // $api->pftOrdernum = '65008980216067';
        
        // // $res = $api->Order_Change_Pro('64964693114460',0,'15300000000');
        // $res = $api->OrderQuery();
        // UUAreaList::saveData($res);
        // UUScenicSpotTicket::saveData($res);
        // dd($res);
        return response()->json($res);
        // $order = UserOrder::getOrderByOrderNo($orderno);
        // $order->redirectOutTicket();
        // $info = Schedules::getSchedulesInfo('612673251');

        // $res = $info->getScetionInfo(0);
        // dd($res ,$info);
        // $order = UserOrder::getOrderByOrderNo($orderno);

        // $order->redirectOutTicket();
        // $apiorder = \App\Models\ApiOrders::getOrder($order->api_lock_sid);
        // $result = $apiorder->searchOrder();
        // $result = WpApi::searchOrderInfoBySID('0043349686');
        // dd($result);
        // $param = [
        //     'user_id'=> 5,
        //     'paiqi_id'=> 612923591,
        //     'cinema_id'=> 1,
        //     'seat_ids' => '79344044'
        // ];
        $result = 'ok';
        // $result = WpApi::lockSeat($param); //影院
        dd($result);
        die;
        // $list = StoreCheckOut::where('state',0)->get();
        // \Illuminate\Support\Facades\Schema::create('users222', function (\Illuminate\Database\Schema\Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('name');
        //     $table->string('email')->unique();
        //     $table->timestamp('email_verified_at')->nullable();
        //     $table->string('password');
        //     $table->rememberToken();
        //     $table->timestamps();
        // });
        
        // foreach($list as $item){
        //     $item->doCheckOut();
        // }        
        // $list = file_get_contents(base_path('1.json'));
        // $list = json_decode($list,true);

        // $arr[] = $list[0];
        // \App\ApiModels\Wangpiao\Cinema::insertData($list);
        // $list2= file_get_contents('city.json');
        // $list= file_get_contents('1.json');
        // $list = file_get_contents('district.json');
        // $list3 = file_get_contents('billcity.json');
        // $list2 = json_decode($list2,true);
        // $list = json_decode($list,true);
       
        
        
        // $sql = WangpiaoCinema::syncData($list);
        // $order = Order::getOrderByNo('KQ20211008145420995310');
        // $sql = Commision::clacCommision($order);
   
   
        // $orderno = $req->input('order','');
        // $order = StoreOfferOrder::getOrderByOrderNo($orderno);
        // $order->closeOrder($order);
        
        // dd($order);
        // $store = \App\Models\Store::where('id',9)->first();
        
        // $order = StoreOfferOrder::OfferOrderList($store);
        // dd($store);
        // $brand = \App\ApiModels\Wangpiao\CinemasBrand::whereRaw(DB::raw("find_in_set(levels_id,'{$store->store_level}')"))->orWhere('levels_id','')->get()->map(function($brand){
        //     return $brand->only(['id']);
        // });
        // $brandIds = array_column($brand->toArray(),'id');
        // // dd($brandIds);
        
        // $list = \App\Models\OrderRules::where('city_id',$store->store_city_id)
        //             ->when($brandIds,function($query,$brandIds){
        //                 return $query->whereIn('brand_id',$brandIds);
        //             })
        //             ->get(); 
        // dd($list->toArray());
        // \App\ApiModels\Wangpiao\Schedules::truncate();
        // die;
        // $app = $this->getApp(2);
        // $miniapp = $this->getApp();
        // $app->template_message->send([
        //     'touser' => 'oDjTw0lYLO1Q7Wwy9nZMDHA8cE-o',
        //     'template_id' => 'mdXG4bbsC5_HGLztgvC_3a9R1NRMbZUhOf_3YtKj4f0',
        //     'url' => 'https://easywechat.org',
        //     // 'miniprogram' => [
        //     //     'appid' => $miniapp['config']['app_id'],
        //     //     'pagepath' => 'pages/index',
        //     // ],
        //     'data' => [
        //         'first' => '测试2',
        //         'keyword1' => '测试1111111',
        //         'keyword2'=>'测试22222',
        //         'remark'=>'测试22222',
        //     ],
        // ]);
        
        // try {
        //    // $result = MApi::getToken(true); //ok
        //    $result = MApi::getCityList(); //城市列表
        // //    $result = MApi::hotFilmList(330100); //热映
        // //    $result = MApi::rightNowFilmList(330100); //热映
        //    $result = WpApi::commonRequest('Base_Cinema'); //影院
        // $result =(string) \Illuminate\Support\Str::uuid();
        // $apiResult = WpApi::getCinemaHall(1);

        // dd($apiResult);
        //    $result = WpApi::getPlanFilm(); //影院
        // $date = date('Y-m-d H:i:s');
            // $apiResult = WpApi::getCurrentFilm($city_id,$date,$cinema_id); //影院
            // $apiResult = WpApi::getFilmShowByDate($cinema_id,'');
            // $apiResult = WpApi::getCityList(); //影院
            // $apiResult = WpApi::getTradingArea();
            // $apiResult = WpApi::getCityDistrict();
        $apiResult = WpApi::getSellSeatInfo($showindex,$cinema_id); //已售座位信息
        
        // $apiResult = WpApi::getSeatByShowIndex($showindex,$cinema_id);
        // $apiResult = WpApi::FilmShowCheck($cinema_id,$date,$showindex);
        // $apiResult = WpApi::getFilmViewList(58,$cinema_id);
        
        
        // $apiResult = WpApi::getSeatByHallId($hallId,$cinema_id);
        
        // $apiResult = WpApi::getCinemaQueryList($city_id,$date);
        // $apiResult = WpApi::getPreFilmShow($cinema_id,$date,$film_id);
        // cinema_id=6020&date=2021-8-31&film_id=28650
        // $apiResult = WpApi::getFilmShowByDate($cinema_id,$date,$film_id);
        // $apiResult = WpApi::getPreFilmShow($cinema_id,$date);
        // $apiResult = WpApi::commonRequest('Base_Cinema',['CityID'=>$city_id]);
        dd($apiResult);
        return response()->json($apiResult);
        dd($apiResult);
        $param = [
            'user_id'=> 2,
            'paiqi_id'=> 524349205,
            'cinema_id'=> 1691,
            'seat_ids' => '74768790'
        ];

        // $result = WpApi::lockSeat($param); //影院
        // $result =WpApi::getSeatByShowIndex($info->show_index,$info->cinema_id);
        
        // dump($rowStep);
        // dump($colStep);
        // $result = WpApi::getSeatByShowIndex(521764874,1);
        // $letter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        // $result = strpos($letter,'Z');
        // $result = WpApi::getSeatByHallId(530145,6253);
        // //    $result = MApi::filmList(60464); //影院下的电影
        // //    $result = MApi::filmPaiqiList(60464); //排期
        // $result = WpApi::getCinemaQueryList(1,'2021-8-10');
        //    $result = MApi::seatList('564a4a3b98e7e727a6a5f902b55a25f5c'); //影院下的电影
        // $order = UserOrder::getOrderByOrderNo('CK20210717113150541025');
        echo json_encode($result,256);die;
        dd($result);

        // $result = MApi::seatLock($order->user_id,$order->seat_names,$order->paiqi_id,$order->seat_ids,$order->buyer_phone,'');
        // dump($result2);
            // $result = Schedule::where('show_time','>',10000000000000000)->paginate(3);
            // dump($result->exists());
            // dd($result);
        
        // } catch (\Exception $e) {
        //     // return $this->error('失败:'.$e->getMessage());
        //     throw $e;
        // }
        // return $this->success('成功',$result);

        // $result = StoreOfferOrder::calcOfferToOrder(22);

        
        // $list = \App\Models\City::all()->toArray();
        
        // Cache::store('redis')->forever('city_list',$list);
        // echo '已存储';
        // $list = MApi::filmList(60464);
        // $list[0]['trailerlist'] = (array)$list['trailerlist'];
        // $result = $list[0];

        // $result['trailerlist'] = \json_decode($result['trailerlist'],true);
        // dd($result);
        // $list =  MApi::filmPaiqiList(52009);
        // dd($list);
        // $data['id'] = 2;
        // $data['brand_id'] = 111111;
        // $data['latitude'] = 2;
        // $data['longitude'] = 2;
        // $data['cinema_name'] = 222222222;
        // $data['schedule_close_time'] = 222222;
        // $data['phone'] = 2222;
        // $data['region_name'] = 666;
        // $data['address'] = 666;
        // $data['lowest_price']= 666;
        // $data['show_time'] = 666;     
        // $result = Cinema::saveCinema($data);
        // dd($result);
        // \App\Jobs\SyncCinemas::dispatch()->delay(10);
        // \App\Jobs\SyncSchedules::dispatch()->delay(10);
        // \App\Jobs\SyncCities::dispatch()->delay(10);
        // \App\Jobs\SyncCurrentMovies::dispatch(1)->delay(10);  //即将上映
        // \App\Jobs\SyncCurrentMovies::dispatch(2)->delay(10); //热映
        // $paiqidInfo = Schedule::where('id',123123)->first();

        // $retail = array(
        //     'total_rate'=> 10,
        //     'level1_rate'=>50,
        //     'level2_rate'=>50
        // );

        // echo json_encode($retail);
        // $data = array(
        //     'ticket_out_ttl'=>10
        // );
        // $result = Setting::getSettings(true);
        // $result = Helpers::storeBrandList(1);
        // dd( $result);

        // echo date('Y-m-d H:i:s');
        
        // try {
            // $order = UserOrder::where('id',37)->first();
            // $order2 = UserOrder::where('id',38)->first();
            // \App\Jobs\UpdateOrder::dispatch($order)->delay(10);
            // \App\Jobs\UpdateOrder::dispatch($order2)->delay(2);
        // } catch (\Exception $e) {
        //     echo $e->getMessage();
        // }
        echo '111';
        // $jobSetting = JobList::all()->toArray();
        // $keys = array_column($jobSetting,'names');
        // $jobSetting = array_combine($keys,$jobSetting);
        // extract($jobSetting);
        // var_dump($sync_cinemas);
        // $rate = 0;
        // $jobSetting = date('Y-m-d H:i:s',strtotime("+ {$rate} day",time()));
        // dd($jobSetting);
        echo '队列演示';
    }



}
