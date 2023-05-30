<?php
namespace App\Http\Controllers;


use App\Models\Setting;

use Illuminate\Http\Request;

/**
 * 票付通开放接口
 */
class PwApiController extends Controller
{
    protected $cityId = 0;
    protected $cityCode = 0;
    protected $distId = 0;
    protected $showTime; //2021-8-28

    public function __construct(){
        Setting::getSettings();

        $this->cityCode = request('city_code','');
        $this->showTime = request('show_time','');

        if(!strtotime($this->showTime)){
            $this->showTime = date('Y-m-d');
        }

        //放映计划接口的时间处理,后期考虑统一修改
        $date = request('date','');
        if($date = strtotime($date)){
            $this->showTime = date('Y-m-d',$date);
        }

        if($this->cityCode != ''){

            $this->getCityId($this->cityCode);
        }


    }



    /**
     * 日历价格
     *
     * @param Request $request
     * @return void
     */
    public function pw_calendar_price(Request $request){
        $tid = (int)$request->input('ticket_id',0);
        $startDate = trim($request->input('date',''));
        $datetime = strtotime($startDate);
        if(!$datetime){
            $datetime = time();
        }

        $ticket = \App\UUModels\UUScenicSpotTicket::getDetail($tid);
        if(!$ticket || $ticket->UUstatus != 1){
            return $this->error('门票信息已下架或删除');
        }
        $today = \Illuminate\Support\Carbon::createFromTimestamp(time());
        $firstDate = $today->startOfMonth();
        $lastMonthDate = $firstDate->addMonth(3)->toDateString();
        $lastMonthDate = strtotime($lastMonthDate);
        $nowDateTime = strtotime(date('Y-m-d'));
        $startDateObj = \Illuminate\Support\Carbon::createFromTimestamp($datetime);
        $currentStartDate = $startDateObj->startOfMonth()->toDateString();
        $curentStartTime = strtotime($currentStartDate);
        if($curentStartTime < $nowDateTime){
            $currentStartDate = date('Y-m-d');
        }
        $currentMonthEndDate = $startDateObj->endOfMonth()->toDateString();
        if(strtotime($currentMonthEndDate) >= $lastMonthDate){
            return $this->success('',[]);
        }
        $storageId = $ticket->getStorageId();
        $list =  \App\UUModels\UUTicketStorage::where('storage_id',$storageId)->whereYear('date',date('Y',$datetime))->whereMonth('date',date('m',$datetime))->where('date','>=',date('Y-m-d'));
        // $count = $list->count();
        // if(!$count){
        //     $ticket->getPriceList($currentStartDate,$currentMonthEndDate);
        // }
        $ticket->getPriceList($currentStartDate,$currentMonthEndDate);
        $list = $list->get(['date','retail_price'])->each(function($item){
            $item->date = date('Y-m-d',strtotime($item->date));
            $item->retail_price = round($item->retail_price / 100 ,2);
            return $item;
        });
        return $this->success('',$list);
    }

    /**
     * 获取实时库存价格
     *
     * @param Request $request
     * @return void
     */
    public function pw_storage_price(Request $request){
        $id = (int)$request->input('id',0);
        $tid = (int)$request->input('ticket_id',0);
        $startDate = trim($request->input('date',''));
        $datetime = strtotime($startDate);
        if(!$datetime){
            $datetime = time();
        }
        $product = \App\UUModels\UUScenicSpotInfo::getDetail($id);
        if(!$product){
            return $this->error('产品信息不存在');
        }
        $ticketList = \App\UUModels\UUScenicSpotTicket::where('UUlid',$product->UUid)
                            ->where('UUstatus',1)
                            ->orderByRaw("case when id = {$tid} then 0 else 1 end")
                            ->get();
        $list = array();

        $nowDate = \Illuminate\Support\Carbon::createFromTimestamp($datetime);
        $currentStartDate = $nowDate->toDateString();
        $currentEndDate = $nowDate->addDay(5)->toDateString();

        foreach($ticketList as $ticket){
            $ticket->getPriceList($currentStartDate,$currentEndDate);
            $item = new \stdClass;

            $item->id = $ticket->id;
            $item->UUid = $ticket->UUid;
            $item->title = $ticket->UUtitle;
            $item->explain = $ticket->ticketExplain();
            $item->market_price = $ticket->UUtprice;
            $storageAndPrice = $ticket->getStorageAndPrice($currentStartDate);
            $item->sale_num = $storageAndPrice['sale_num'];
            $item->retail_price = $storageAndPrice['retail_price'];
            $item->UUaid = $ticket->UUaid;
            $list[]=$item;
        }
        return $this->success('',$list);
    }

    /**
     * 门票列表
     *
     * @param Request $request
     * @return void
     */
    public function pw_ticket_list(Request $request){
        $id = (int)$request->input('id',0);
        $tid = (int)$request->input('ticket_id',0);
        $date = trim($request->input('date',''));
        $datetime = strtotime($date);
        $date = $datetime?date('Y-m-d',$datetime):'';
        $product = \App\UUModels\UUScenicSpotInfo::getDetail($id);
        if(!$product){
            return $this->error('产品信息不存在');
        }
        $ticketCount = \App\UUModels\UUScenicSpotTicket::where('UUlid',$product->UUid)->count();
        if(!$ticketCount){

            $api = \App\Support\SoapApi::getInstance();
            $res = $api->Get_Ticket_List($product->UUid);
            \App\UUModels\UUScenicSpotTicket::saveData($res);

            $nowDate = \Illuminate\Support\Carbon::createFromTimestamp($date?$datetime:time());
            $currentStartDate = $nowDate->toDateString();
            $currentEndDate = $nowDate->addDay(5)->toDateString();
            \App\UUModels\UUScenicSpotTicket::downloadApiStorageAndPrice($res,$currentStartDate,$currentEndDate);
        }
        $ticketList = \App\UUModels\UUScenicSpotTicket::where('UUlid',$product->UUid)
                            ->where('UUstatus',1)
                            // ->orderByRaw("case when id = {$tid} then 0 else 1 end")
                            ->orderBy('UUtprice')
                            ->orderBy('UUid')
                            ->get();
        $list = array();
        foreach($ticketList as $ticket){
            $item = new \stdClass;

            $item->id = $ticket->id;
            $item->UUid = $ticket->UUid;
            $item->title = $ticket->UUtitle;
            $item->explain = $ticket->ticketExplain();
            $item->UUtourist_info = $ticket->UUtourist_info; //身份证信息,0-不需要填写，1-需要填写，2-需要填写所有游客身份证，3-随子票规则，4-取票人与所有游客填写身份信息
            $item->UUif_verify = $ticket->UUif_verify;
            $item->UUdelaytime = $ticket->UUdelaytime;
            $item->UUrefund_rule = $ticket->UUrefund_rule;
            $item->market_price = $ticket->UUtprice;
            $storageAndPrice = $ticket->getStorageAndPrice($date);
            $item->sale_num = $storageAndPrice['sale_num'];
            $item->retail_price = $storageAndPrice['retail_price']==0?$ticket->UUtprice:$storageAndPrice['retail_price'];
            $item->UUaid = $ticket->UUaid;
            if($ticket->UUtprice!=0){
                $list[]=$item;
            }

        }
        return $this->success('',$list);
    }

    /**
     * 产品详情
     *
     * @param Request $requset
     * @return void
     */
    public function pw_pdetail(Request $request){
        $id = (int)$request->input('id',0);

        $product = \App\UUModels\UUScenicSpotInfo::getDetail($id);
        if(!$product){
            return $this->error('产品信息不存在');
        }
        $product->sale_num = $product->saleNumber();
        return $this->success('',$product);
    }

    /**
     * 票付通： 获取产品列表
     *
     * @param Request $request
     * @return void
     */
    public function pw_plist(Request $request){
        $type = $request->input('type','');
        $limit = (int)$request->input('limit',10);
        $keyword = $request->input('keyword','');
        $city = $request->input('city','');
        $city = '北京';
        // $keyword = Helpers::replace_specialChar($keyword);
        $keywordArr = array_filter(explode(' ',$keyword),function($v){
            return !empty($v);
        });
        $field = array(
            'id',
            'UUtitle',
            'UUp_type',
            'UUid',
            'UUarea',
            'UUimgpath',
            'UUlng_lat_pos',
        );
        $whereLikeSql = array();
        $whereLike = array_map(function($val) use (&$whereLikeSql){
            $whereLikeSql[] = "concat(UUarea,UUtitle) like ?";
            return "%{$val}%";
        },$keywordArr);
        $list = \App\UUModels\UUScenicSpotInfo::select($field)
                        ->type($type)
//                        ->where('UUp_type',$type)
                        ->when($whereLike,function($query,$whereLike) use ($whereLikeSql){
                            return $query->whereRaw('('.implode(' or ',$whereLikeSql).')',$whereLike);
                        })
                        ->when($city,function($query,$city){
                            return $query->whereRaw("UUarea like ?","%$city%");
                        })
                        ->where('UUstatus',1)
                        ->paginate($limit);
        $list->transform(function($item){
            $item->makeHidden(['ticketList']);
            $item->type = $item->getTypeTxt();
            $ticketList = $item->ticketList()->where('UUstatus',1)->where('UUtprice','!=','0')->orderBy('UUtprice')->get();
            $item->ticket_num = count($ticketList);
            $firstTicket = $ticketList[0]??null;

            $item->market_price = 0;
            $item->sale_num = 0;
            $item->retail_price = 0;
            if($firstTicket){
                $item->ticket_id = $firstTicket->UUid;
                $item->market_price = $firstTicket->UUtprice;
                $storageAndPrice = $firstTicket->getStorageAndPrice();
                logger($storageAndPrice);
                $item->sale_num = $storageAndPrice['sale_num'];
                $item->retail_price = $storageAndPrice['retail_price']==0?$firstTicket->UUtprice:$storageAndPrice['retail_price'];
            }
            return $item;
        });
//        logger($list);
        return $this->success('',$list);
    }

    /**
     * 票付通： 获取产品类型
     *
     * @param Request $request
     * @return void
     */
    public function pw_type(Request $request){
        $list = \App\UUModels\UUScenicSpot::$type;
        $arr = array();
        foreach($list as $key=>$item){
            $arr[] = array('title'=>$item,'value'=>$key);
        }
        return $this->success('',$arr);
    }




    protected function getCityId($city_code){
        if($city_code == '') return 0;
        $cityList = cache('getCityIdByCode',false);

        if(!$cityList){
            $districtList = Api\District::select(['cid as id','code'])->orderBy('code')->where('code','<>','');
            $cityList = Api\City::where('code','<>','000')->orderBy('code')->unionAll($districtList)->pluck('id','code')->toArray();
            cache(['getCityIdByCode'=>$cityList,86400*3]);
        }

        $this->cityId = $cityList[$city_code]??0;
    }
}
