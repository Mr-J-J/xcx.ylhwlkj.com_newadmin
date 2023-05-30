<?php
namespace App\Models\store;

use App\Models\Store;
use App\Support\Helpers;
use App\Models\StoreInfo;
use App\Models\UserOrder;
use App\Models\OfferRules;
use App\Models\OrderRules;
use App\Models\TicketUser;
use App\Models\CommonOrder;
use App\Models\store\OutTicketLog;
use Illuminate\Support\Facades\DB;
use App\Models\store\StoreOfferRecord;
use App\ApiModels\Wangpiao\CinemasBrand;

/**
 * 竞价服务类
 */
class OfferServices
{

    /**
     * 按商家规则查找新订单
     *
     * @param Store $store
     * @param integer $type
     * @return array
     */
    function getOfferOrderList(Store $store){
        $brandIds = array();

        $brandList = CinemasBrand::whereRaw(DB::raw("find_in_set('{$store->store_level}',levels_id)"))
                        ->orWhere('levels_id','')
                        ->get()->map(function($brand){
                            return $brand->only(['id']);
                        });
        //商家指定的品牌
        $brandIds = array_column($brandList->toArray(),'id');

        $ignoreOrder = IgnoreOrder::getIgnoreOrder($store->id);

        //where('city_id',$store->store_city_id)
        $list = OrderRules::when($brandIds,function($query,$brandIds){
                        return $query->whereIn('brand_id',$brandIds);
                    })
                    ->get()
                    ->pluck('order_no')
                    ->toArray();
        $list = array_unique($list);
        $list = array_diff($list,$ignoreOrder);

        return $list;
    }

    /**
     * 根据订单找到可以抢单的商家
     *
     * @param StoreOfferOrder $order
     * @return void
     */
    function getOfferStoresList(StoreOfferOrder $order){
        $userInfo = TicketUser::select(['nickname','mobile'])->where('id',$order->user_id)->first();
        $brandInfo = CinemasBrand::where('id',$order->brand_id)->first();
        $levelIds = array_filter($brandInfo->levels_id,function($v){
            return !empty($v);
        });
        $storeList = Store::select(['id','openid','store_level'])->where('store_state',2)->when($levelIds,function($query,$levelIds){
            return $query->whereIn('store_level',$levelIds);
        })->get();
        logger('2'.$order);
        $storeInfoList = array();
        foreach($storeList as $store){
            if($store->storeInfo && $store->storeInfo->taking_mode){
                $storeInfoList[] = $store;
                \App\Jobs\AutoOfferOrderJob::dispatch($order,$store->storeInfo);
                \App\Jobs\PushStoreMsgJob::dispatch($order,$store->openid,$userInfo->nickname,$userInfo->mobile);
            }
        }
        return $storeInfoList;
    }

    /**
     * 自动报价
     *
     * @param StoreOfferOrder $order
     * @return void
     */
    function AutoOfferOrder(StoreOfferOrder $order,$storeInfo){

        if(!$storeInfo->taking_auto){ //是否开启报价
            return false;
        }

        if($order->offer_status > 0){

            return false;
        }

        $mactchRules = 0;
        $notOfferRules = 0;
        $mactchRulesList = array();

        $rulesList = OfferRules::where('store_id',$storeInfo->store_id)->where('state',1)->get();

        foreach($rulesList as $rule){
            $weight = 0;
            //市场价符合
            if($order->market_price >= $rule->market_left && $order->market_price <= $rule->market_right){
                $mactchRules = 1;
                $weight++;
            }

            //包含影院
            if(!empty($rule->cinemas)){
                if(stripos($order->cinemas,$rule->contain_cinema) !== false){
                    $mactchRules = 1;
                    $weight++;
                }
            }

            //不包含影院
            if(!empty($rule->un_contain_cinema)){
                if(stripos($order->cinemas,$rule->un_contain_cinema) !== false){
                    $notOfferRules = 1;
                }
            }

            //包含影厅
            if(!empty($rule->contain_hall)){
                if(stripos($order->halls,$rule->contain_hall) !== false){
                    $mactchRules = 1;
                    $weight++;
                }
            }

            //不包含影厅
            if(!empty($rule->un_contain_hall)){
                if(stripos($order->halls,$rule->un_contain_hall) !== false){
                    $notOfferRules = 1;
                    $weight++;
                }
            }

            //包含影片
            if(!empty($rule->contain_movie)){
                if(stripos($order->movie_name,$rule->contain_movie) !== false){
                    $mactchRules = 1;
                    $weight++;
                }
            }

            //不包含影片
            if(!empty($rule->un_contain_movie)){
                if(stripos($order->movie_name,$rule->un_contain_movie) !== false){
                    $notOfferRules = 1;
                    $weight++;
                }
            }

            //lovers_seat  1可含情侣座 0不含情侣座
            if(!$rule->lovers_seat){
                $loverFalg = explode(',',$order->seat_falg);
                if(array_sum($loverFalg)){
                    $notOfferRules = 1;
                }
            }
            //accept_seats 0不可调座 1调座   (1含不可调座 0只含调座)
            if(!$rule->accept_seats){
                if($order->accept_seats){
                    $mactchRules = 1;
                    $weight++;
                }else{
                    $notOfferRules = 1;
                }
            }
            //seats_number 0 不限 1单数 2双数
            if($rule->seats_number){
                if($rule->seats_number == 1 && ($order->ticket_count % 2 > 0)){
                    $mactchRules =1;
                    $weight++;
                }elseif($rule->seats_number == 2 && ($order->ticket_count % 2 == 0)){
                    $mactchRules = 1;
                    $weight++;
                }else{
                    $notOfferRules = 1;
                }
            }

            $mactchRulesList[$weight] = $rule;
        }
        $mactchRulesList = array_values($mactchRulesList);
        try {
            if(!empty($storeInfo->taking_time)){
                $timeArr = explode('-',$storeInfo->taking_time);
                $timeArr = array_filter($timeArr,function($v){
                    return !empty($v);
                });
                $timeArr = array_map(function($v){
                    return strtotime($v);
                },$timeArr);
                if(!empty($timeArr)){
                    sort($timeArr);
                    $now = time();
                    if($now >= $timeArr[0] && $now <=$timeArr[1]){
                        $mactchRules = 1;
                    }else{
                        $notOfferRules = 1;
                    }

                }
            }
        } catch (\Throwable $th) {
            logger('时间段报价：'.$th->getMessage());
        }
        if(!$mactchRules || $notOfferRules || empty($mactchRulesList[0])){

            return false;
        }



        $offerRules = $mactchRulesList[0];
        // $maxPrice = StoreOfferRecord::getMaxPrice($order->amount);
        // $maxPrice = round($maxPrice / $order->ticket_count,2);
        $maxPrice = $order->getMaxPrice_V2();
        $data['price'] = 0;
        $offerValue = round($offerRules->offer_value,2);
        // 1 比例报价 2市场价报价- 3最高价减法报价- 4固定金额报价
        if($offerRules->offer_type == 1){
            $data['price'] = round($maxPrice * $offerValue / 100,2);
        }elseif($offerRules->offer_type == 2){
            // $data['price'] = round($maxPrice * $offerValue / 100,2);
        }elseif($offerRules->offer_type == 3){
            $data['price'] = round($maxPrice - $offerValue,2);
        }elseif($offerRules->offer_type == 4){
            $data['price'] = round($offerValue,2);
        }
        $data['status'] = 0;
        $data['remark'] = '自动报价';

        if($data['price'] == 0){
            return false;
        }
        try {
            StoreOfferRecord::saveOffer($data,$storeInfo,$order);
        } catch (\Throwable $th) {
            logger($th->getMessage());
        }
    }

    /**
     * 订单列表
     *
     * @param [type] $store_id
     * @param integer $type 1新订单 2已报价 3待出票 4已出票  5已关闭  6已退回
     * @return collect  0竞价中 1待出票 2已出票 3已关闭/竞价失败 4退回
     */
    function OfferOrderList(Store $store,$type = 1){
        $limit = request('limit',20);
        $keywords = request()->input('keywords','');

        $store_id = $store->id;

        $field = array(
            'store_offer_orders.id',
            'order_no',
            'store_offer_orders.updated_at',
            'citys',
            'store_offer_orders.ticket_count',
            'movie_name',
            'seat_names',
            'accept_seats',
            'show_time',
            'close_time',
            'expire_time',
            'cinemas',
            'halls',
            'market_price',
            'store_offer_orders.amount',
            'store_offer_orders.market_price',
            'store_offer_orders.offer_status',
            'store_offer_orders.offer_times',
            'store_offer_orders.brand_id',
            'store_offer_orders.store_id as win_store_id'
        );


        if($type == 1){ //新订单

            $storeInfo = $store->storeInfo;
            if($storeInfo && !$storeInfo->taking_mode){
                return array();
            }
            $orderNoList = $this->getOfferOrderList($store);
            $list = StoreOfferOrder::select($field)->where('offer_status',0)->whereIn('order_no',$orderNoList)->orderBy('created_at','desc')->paginate($limit);
//            $priceRate = (int) Helpers::getSetting('offer_price');
//            $priceRatemin = (int) Helpers::getSetting('offer_price_min');
            foreach($list as $item){
//                logger('订单');
//                logger($item);
                $priceRate = (int)CinemasBrand::where('id',$item->brand_id)->value('offer_price');
//                logger('院线报价');
//                logger($priceRate);
                if(!$priceRate){
                    $priceRate = (int) Helpers::getSetting('offer_price');
//                    logger('默认价格限制');
//                    logger($priceRate);
                }
                $priceRatemin = (int)CinemasBrand::where('id',$item->brand_id)->value('offer_price_min');
                if(!$priceRatemin){
                    $priceRatemin = (int) Helpers::getSetting('offer_price_min');
                }
                $maxPrice = round($item->market_price * ($priceRate / 100),2);
                $item->max_price = round($maxPrice / $item->ticket_count,2);
                $minPrice = round($item->market_price * ($priceRatemin / 100),2);
                $item->min_price = round($minPrice / $item->ticket_count,2);
            }
            return $list;
            //查询'offer_status'==0或者==2的数据
//            return StoreOfferOrder::select($field)->where(function($query){
//                $query->where('offer_status',0)->orWhere('offer_status',2);
//            })->whereIn('order_no',$orderNoList)->orderBy('created_at','desc')->paginate($limit);
        }

        //2已报价 3待出票 4已出票  5已关闭  6已退回
        $field[] = 'store_offer_record.id as offer_record_id';
        $field[] = 'store_offer_record.offer_amount';
        $field[] = 'store_offer_record.store_id';
        $recordOfferStatus = ($type == 5) ? 3:0;
        // $recordOfferStatus = 0;
        $map = '';
        $statusArr = [0,0,0,1,2,3,4];
        if(!empty($keywords)){
            $map = "concat(`order_no`,`movie_name`,`cinemas`,`citys`) like '%{$keywords}%'";
        }
        if($type == 3 || $type == 4){ //待出票  已出票
            $recordOfferStatus = 1;
        }

        if($type == 2){
             $list = StoreOfferOrder::select($field)->leftJoin('store_offer_record','store_offer_record.order_id','=','store_offer_orders.id')
                ->when($type == 2,function($query) use ($store_id){ //已报价
                    return $query->where('store_offer_record.store_id',$store_id)->whereIn('store_offer_orders.offer_status',[0,1]);
//                    return $query->where('store_offer_orders.store_id',$store_id);
                })
                 ->orwhere(function($query) use ($store_id){ //已报价
                     return $query->where('store_offer_orders.store_id',$store_id)->whereIn('store_offer_orders.offer_status',[2,3]);
//
                 })
                ->whereIn('store_offer_orders.offer_status',[0,1,2,3])
                ->when($recordOfferStatus,function($query,$recordOfferStatus){
                    return $query->where('store_offer_record.offer_status',$recordOfferStatus);
                })
                ->when($map,function($query,$map){
                    return $query->whereRaw($map);
                })
                ->orderBy('store_offer_orders.created_at','desc')
                ->paginate($limit);
        }else{
            $list = StoreOfferOrder::select($field)->leftJoin('store_offer_record','store_offer_record.order_id','=','store_offer_orders.id')
                ->when(in_array($type,[5,6]),function($query) use ($store_id){ //已报价
                    return $query->where('store_offer_record.store_id',$store_id);
                })
                ->when($type == 2,function($query) use ($store_id){ //已报价
                    return $query->where('store_offer_record.store_id',$store_id);
                })
                ->when(in_array($type,[3,4]),function($query) use ($store_id){ //已报价
                    return $query->where('store_offer_orders.store_id',$store_id);
                })
                ->where('store_offer_orders.offer_status',$statusArr[$type])
                ->when($recordOfferStatus,function($query,$recordOfferStatus){
                    return $query->where('store_offer_record.offer_status',$recordOfferStatus);
                })
                ->when($map,function($query,$map){
                    return $query->whereRaw($map);
                })
                ->orderBy('store_offer_orders.created_at','desc')
                ->paginate($limit);
        }
        $priceRate = (int) Helpers::getSetting('offer_price');
        $priceRatemin = (int) Helpers::getSetting('offer_price_min');
        foreach($list as $item){
            $maxPrice = round($item->amount * ($priceRate / 100),2);
            $item->max_price = round($maxPrice / $item->ticket_count,2);
            $minPrice = round($item->amount * ($priceRatemin / 100),2);
            $item->min_price = round($minPrice / $item->ticket_count,2);
            $item->offer_amount = $item->offer_amount / 100;
            $item->status_txt = '竞价中';
            if($item->offer_status == 1){
                if($type == 2){
                    $item->status_txt = '竞价失败';
                }
                if($item->win_store_id == $store->id){
                    $item->status_txt = '竞价成功';
                }
            }elseif($item->offer_status == 2){
                $item->status_txt = '已出票';
            }elseif($item->offer_status == 3){
                $item->status_txt = '已关闭';
            }elseif($item->offer_status == 4){
                $item->status_txt = '已退回';
            }
        }
        logger($list);
        return $list;
    }

    /**
     * 竞价开始
     *
     * @param StoreOfferOrder $order
     * @return void
     */
    function startOffer(StoreOfferOrder $offerOrder,int $delay = 0){
        if($offerOrder->offer_times == 1){
            OrderRules::createOrderRules($offerOrder); //添加到订单展示规则
        }
        StoreOfferDetail::createDetail($offerOrder,'第'.$offerOrder->offer_times.'轮竞价开始');
        if($delay){
            $this->dispatchQueue($offerOrder,$delay);
        }

        $this->getOfferStoresList($offerOrder);
    }

    /**
     * 用户退款关闭竞价
     *
     * @param UserOrder $userorder
     * @return void
     */
    function closeOffer(UserOrder $userorder){
        //订单非待出票状态 直接关闭
        $offerOrder = StoreOfferOrder::getOrderByOrderNo($userorder->getOrderNo());
        if(empty($offerOrder) || $offerOrder->offer_status == 3){
            return;
        }
        $offerOrder->offer_status = 3;
        $offerOrder->save();
        StoreOfferRecord::udpateOfferStatus($offerOrder->id); //报价作废
        CommonOrder::deleteOrder($offerOrder);
        StoreOfferDetail::createDetail($offerOrder,"用户订单退款取消，竞价关闭");
    }
    /**
     * 商家出票
     *
     * @param [type] $data
     * @param StoreOfferOrder $order
     * @return void
     */
    function outTicket(StoreInfo $storeInfo,StoreOfferOrder $order){
        DB::beginTransaction();
        try {
            // 出票
            $order->offer_status = 2;
            $order->save();
            UserOrder::outTicket($order->order_no);
            OutTicketLog::editLogs($storeInfo,$order); //计算平均出票时间
            StoreCheckOut::addCheckOrder($storeInfo,$order); //商家结算单
            StoreOfferDetail::createDetail($order,'商家出票,竞价结束');
            CommonOrder::deleteOrder($order);
        } catch (\Exception $e) {
            DB::rollback();
            Helpers::exception($e->getMessage());
        }
        DB::commit();
    }

    /**
     * 指定商家出票
     *
     * @param Store $store
     * @return void
     */
    function setOrderStore(int $store_id,StoreOfferOrder $offerOrder){
        $storeInfo = StoreInfo::where('store_id',$store_id)->first();
        StoreOfferRecord::addStoreOffer($offerOrder,$storeInfo,'人工派单');
    }
    /***
     * 默认出票商家
     */
    function getDefaultStore($brandId){
        $brandStore = CinemasBrand::where('id',$brandId)->first();
        //系统默认商家
        $defaultStore = Helpers::getSetting('offer_defualt_store');
        if($brandStore->store_id > 0){
            return (int)$brandStore->store_id;
        }
        return empty($defaultStore['store_id']) ? 0: (int)$defaultStore['store_id'];
    }
    /**
     * 竞价订单退回公共订单池
     *
     * @param StoreOfferOrder $order
     * @return void
     */
    function backOrderToCommon(StoreOfferOrder $order){
        $storeInfo = StoreInfo::where('store_id',$order->store_id)->first();
        !empty($storeInfo) && $storeInfo->increment('refund_ticket_count');
        CommonOrder::addOrder($order,3);
        StoreOfferRecord::udpateOfferStatus($order->id); //报价作废
        StoreOfferDetail::createDetail($order,"商家ID({$order->store_id})出票超时进入后台人工派单");
    }
    /**
     * 报价中标规则
     *
     * @return void
     */
    function offerPriceRules(array $offerArray){
        //排除掉退票率高的商家
        $refundTicketRate = Helpers::getSetting('offer_rules_refund_ticket');

        // $offerArray = array_filter($offerArray,function($v) use ($refundTicketRate){
        //     return $v['draw_rate'] >= $refundTicketRate;
        // });
        // 判断前两名的差价，小于设定范围，出票率高的得到订单
        $drawRateDiffPrice = Helpers::getSetting('offer_rules_out_ticket_rate');
        usort($offerArray,function($a,$b) use ($drawRateDiffPrice){
            $a1 = $a['offer_amount'];
            $b1 = $b['offer_amount'];
            $d1 = $a['draw_rate'];
            $d2 = $b['draw_rate'];
            $diff = abs($a1 - $b1);
            if($diff <= $drawRateDiffPrice){
                return ($d1 > $d2) ? -1 : 1;
            }
            return ($a1 < $b1) ? -1 : 1;
        });
        return array_shift($offerArray);
    }


    /**
     * 加入延迟队列
     *
     * @param StoreOfferOrder $offerOrder
     * @param mixed $delay
     * @return void
     */
    static function dispatchQueue(StoreOfferOrder $offerOrder,$delay){
        try {
            \App\Jobs\UpdateOfferOrder::dispatch($offerOrder)->delay($delay);
        } catch (\Throwable $th) {
            logger($th->getMessage());
            throw $th;
        }
    }

    // /**
    //  * 竞价成功出票提醒
    //  *
    //  * @param [type] $storeOpenId
    //  * @param StoreOfferOrder $order
    //  * @return void
    //  */
    // function pushOfferSuccessMsg($storeOpenId,StoreOfferOrder $order){
    //     $app = \EasyWeChat\Factory::officialAccount(config('wechat.official_account.default'));
    //     $app->template_message->send([
    //         'touser' => $storeOpenId,//oHb-H5qPi7TbVeHHslJ9baAPBfkI
    //         'template_id' => 'v8aMAH_EW1YFYh0TUacxbgslkJPYIVX_ZejdTzu2-tQ',
    //         'url' => env('APP_URL').'/#/user/details?id='.$order->order_no,
    //         'miniprogram' => [],
    //         'data' => [
    //             'first' => "恭喜抢单成功 \n{$order->cinemas} \n {$order->halls}",
    //             'keyword1' => "【{$order->movie_name}】 x{$order->ticket_count}张",//电影名称
    //             'keyword2' => "{$order->seat_names}",//座位信息
    //             'keyword3' => date('Y-m-d',$order->show_time) .' ' . date('H:i',$order->show_time).' - '. date('H:i',$order->close_time),//放映时间
    //             'remark' => '点击出票',//备注
    //         ],
    //     ]);
    // }

    /**
     * 竞价成功，接单成功提醒
     *
     * @param [type] $storeOpenId
     * @param StoreOfferOrder $order
     * @return void
     */
    function pushOfferSuccessMsg($storeOpenId,StoreOfferOrder $order,$nickname = '',$mobile = ''){
        if($order->offer_status != 1){
            return false;
        }
        $offer_out_ttl = (int)Helpers::getSetting('offer_out_ttl');
        $app = \EasyWeChat\Factory::officialAccount(config('wechat.official_account.default'));
        $difftime = $order->show_time - time();
        $hours = intval($difftime / 3600);
        $minute = intval(intval($difftime%3600) / 60);
        $time = $hours.'时'.$minute.'分';
        $app->template_message->send([
            'touser' => $storeOpenId,//oHb-H5qPi7TbVeHHslJ9baAPBfkI
            'template_id' => 'BCE6w8sqGNgChe_1mvsZQgarDoVIJDSRq-n4m4jj_dE',
            'url' => env('APP_URL').'/#/user/details?id='.$order->order_no,
            'miniprogram' => [],
            'data' => [
                'first' => "竞价接单成功，电影开场还有{$time}",
                'keyword1' => "{$order->citys}·{$order->cinemas}  {$order->halls}",//电影名称
                'keyword2' => "【{$order->movie_name}】 x{$order->ticket_count}张",//电影名称
                'keyword3' => round($order->success_money,2).'(接单价)',//放映时间
                'keyword4' => '请在'.$offer_out_ttl.'分钟内出票',//放映时间
                'remark' => '点击立即出票',//备注
            ],
        ]);
    }

    //催单
    function pushOfferSuccessMsg2($storeOpenId,StoreOfferOrder $order,$nickname = '',$mobile = ''){
        if($order->offer_status != 1){
            return false;
        }
        $app = \EasyWeChat\Factory::officialAccount(config('wechat.official_account.default'));
        $difftime = $order->show_time - time();
        $hours = intval($difftime / 3600);
        $minute = intval(intval($difftime%3600) / 60);
        $time = $hours.'时'.$minute.'分';
        $app->template_message->send([
            'touser' => $storeOpenId,//oHb-H5qPi7TbVeHHslJ9baAPBfkI
            'template_id' => 'BCE6w8sqGNgChe_1mvsZQgarDoVIJDSRq-n4m4jj_dE',
            'url' => env('APP_URL').'/#/user/details?id='.$order->order_no,
            'miniprogram' => [],
            'data' => [
                'first' => "跟离电影开场还有{$time}，请及时出票",
                'keyword1' => ['value'=>"{$order->citys}·{$order->cinemas}  {$order->halls}",'color'=>'#FF0000'],//电影名称
                'keyword2' => ['value'=>"【{$order->movie_name}】 x{$order->ticket_count}张",'color'=>'#FF0000'],//电影名称
                'keyword3' => ['value'=>round($order->success_money,2).'(接单价)','color'=>'#FF0000'],//放映时间
                'keyword4' => ['value'=>'该订单长时间未出票，请及时出票','color'=>'#FF0000'],//放映时间
                'remark' => '点击立即出票',//备注
            ],
        ]);
    }

    /**
     * 新订单提醒
     *
     * @param [type] $storeOpenId
     * @param StoreOfferOrder $order
     * @param [type] $nickname
     * @param [type] $mobile
     * @return void
     */
    function pushWechatMsg($storeOpenId,StoreOfferOrder $order,$nickname,$mobile){
        //1特惠购票 实时单 2快速购票 急单
        $app = \EasyWeChat\Factory::officialAccount(config('wechat.official_account.default'));
        $limitTime = (int) Helpers::getSetting('offer_ttl');
        $remark = '【实时单】';
        if($order->buy_type == 2){
            $remark = '【急单】';
        }
        $app->template_message->send([
            'touser' => $storeOpenId,//oHb-H5qPi7TbVeHHslJ9baAPBfkI
            'template_id' => 'KtJRu3QTI4JPJBxTAPlIWZ9Wzhhz1yvue0-NPyL_XwI',
            'url' => env('APP_URL'),//env('APP_URL').'/#/user/details?id='.$order->order_no,
            'miniprogram' => [],
            'data' => [
                'first' => "电影票新订单通知",
                'keyword1' => "{$order->movie_name} x{$order->ticket_count}张",//电影名称
                'keyword2' => $order->citys,//城市
                'keyword3' => "{$order->cinemas}",//影院信息
                // 'keyword4' => date('Y-m-d H:i',$order->show_time),//场次时间
                'keyword4' => ['value'=>date('Y-m-d H:i',$order->show_time),'color'=>'#FF0000'],//场次时间
                'keyword5' => sprintf('%.2f',round($order->market_price/$order->ticket_count,2)).' x'.$order->ticket_count.'张',//订单总价
                'remark' => $remark."第{$order->offer_times}轮竞价"."，剩余抢单时间{$limitTime}分钟".' ，点击抢单',//备注
            ],
        ]);
    }
}
