<?php
namespace App\Http\Controllers;
use App\Support\NApi;
use App\Models\Newcinemas;
use App\Models\Newmove;
use App\Models\Newmovie_schedule;
use App\Models\Region;
use App\Support\WpApi;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Support\Api\ApiHouse;
use App\ApiModels\Wangpiao as Api;
use Illuminate\Support\Facades\DB;

/**
 * 影福客接口
 */
class NApiController extends Controller
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

//        if($this->cityCode != ''){
//
//            $this->getCityId($this->cityCode);
//        }


    }
    public function ceshi(Request $request){
//        return NApi::sellticket('11111741','YP202306102019587901334','66.5','5D80E519318D99762516ABB58B4F1E3F');
//        return NApi::getorderstatus('11111741','YP202306102019587901334');
        return NApi::unlockorder('11111741','YP202306102104533390323');
    }
    /**
     * 更新排期
     *
     * @param Request $request
     * @return void
     */
    public function insechedule(Request $request){
        $city = $request->input('city_name','');
        $ok = Newmovie_schedule::where('city','=',$city)->first();
        if($ok!=null){
            logger('数据已有');
            return '数据已有';
        }
        $list = Newcinemas::where('city','=',$city)->get();
//        if(!is_array($list)){
//            $list = $list->toArray();
//        }
//        ====================================================
        foreach($list as $item){
            logger('1');
                $schedulesList = NApi::getplan($item->cinemaCode);
            $schedulesList=$schedulesList['result'];
            $l=[];
            foreach ($schedulesList as &$sitem){
                $sitem['cinemaId']=$item['cinemaCode'];
                $sitem['city']=$city;
                $l[]=$sitem;
            }
            \App\Jobs\Nsyncschedules::dispatch($l);
        }
        $codes = $city;

        // 删除这批数据
        \App\Jobs\Nsyncdelete::dispatch($codes)->delay(120*60);
        return '数据更新';
    }
    /**
     * 更新电影
     *
     * @param Request $request
     * @return void
     */
    public static function infilm(){
        Newmove::truncate();
        $list = \App\Support\NApi::getfilmlist();
        $list = $list['result'];
        $a = Newmove::syncData($list);
        $list = \App\Support\NApi::getfilmlist(1);
        $list = $list['result'];
        $a = Newmove::syncData($list,1);
        $list = \App\Support\NApi::getfilmlist(2);
        $list = $list['result'];
        $a = Newmove::syncData($list,2);
        return $a;
    }
    /**
     * 更新影院
     *
     * @param Request $request
     * @return void
     */
    public static function incinema(){
        $list = \App\Support\NApi::getcinema();
        $list = $list['result'];
//        return $list;
        $a = Newcinemas::syncData($list);
        return $a;
    }
    /**
     * 热映、即将上映的电影
     *
     * @param Request $req
     * @return void
     */
    public function currentFilmList(Request $request){
        $type = (int)$request->input('type',1);//1：热映 2：待映 0：全部
        $limit = (int) $request->input('limit',100); //0全部   0指定数量
        $city = $request->input('city_name','');
//        $lat = $request->input('lat','');
//        $lng = $request->input('lng','');
        $limit = min(100,$limit);
        $limit = max(10,$limit);
        logger($type);

        // 获取6个月之前的日期
//        $sixMonthsAgo = Carbon::now()->subMonths(6);
        // 查询数据库
        if($type==2){
            $list2=Newmovie_schedule::where('city',$city)->distinct()->get(['filmCode']);
            $result=$list2;
//            logger($list2);
            if(count($list2)==0){
                $list = Newcinemas::where('city','=',$city)->orwhere('county','=',$city)->take(2)->pluck('cinemaCode');
                $arr=$list;
                $list2 = [];
                foreach($arr as $item) {
                    $list1 = Napi::getplan($item);
                    $list2 = array_merge($list2, $list1['result']);
                }
                $result = array_unique(array_column($list2, 'filmCode'));
            }
//            $list = Newmove::whereIn('filmNo', $result)->where('state',1)->where('type','!=','影展')->get();
            $list = DB::table('newmoves')
                ->join('newmovie_schedule', 'newmoves.filmNo', '=', 'newmovie_schedule.filmCode')
                ->select('newmoves.*', DB::raw('count(newmovie_schedule.filmCode) as count'))
                ->whereIn('newmoves.filmNo', $result)->where('newmoves.state',1)->where('newmoves.type','!=','影展')
                ->groupBy('newmoves.id')
                ->orderBy('count', 'desc')
                ->limit($limit)
                ->get();
//            logger($list);
//            ======================================

        }elseif($type==1){
            // 获取今天的日期
            $today = Carbon::now();
            $schs= Newmovie_schedule::where('city','=',$city)
                ->whereRaw("STR_TO_DATE(startTime, '%Y-%m-%d') > ?", [$today])
                ->distinct()->get(['filmCode']);
            $ar=[];
            foreach ($schs as &$item){
                $ar[]=$item->filmCode;
            }
            $list = Newmove::where('state',0)
                ->whereRaw("STR_TO_DATE(startTime, '%Y-%m-%d') > ?", [$today])
                ->where('trailer','!=',null)
                ->where('type','!=','影展')
                ->where('cast','!=',null)
                ->orderByRaw("STR_TO_DATE(startTime, '%Y-%m-%d') ASC")
                ->take($limit)
                ->distinct('filmNo')
                ->get();
            foreach ($list as $item1){
                if(in_array($item1->filmNo, $ar)){
                    $item1->look=false;
                }else{
                    $item1->look=true;
                }
            }
        }else{
            $list = Newmove::where('state', $type)->get();
        }
        $list1 = $list;
        $list=[];
        foreach ($list1 as &$item){
            $item->poster=$item->trailerCover;
            $item->show_name=$item->filmName;
            $item->leading_role=$item->cast;
            $item->open_time=$item->filmName;
            $item->remark=$item->score;
//            $item['id']=$item['filmNo'];
            $list[] = $item;
        }
        return $this->success('',$list);
    }
    protected function getDistance($lat1, $lng1, $lat2, $lng2){

        if(empty($lat2) || empty($lng2)){
            return '';
        }

        $lat1 = round($lat1,6);
        $lng1 = round($lng1,6);
        $lat2 = round($lat2,6);
        $lng2 = round($lng2,6);
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1);// deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $s = 2 * asin(sqrt(pow(sin($a / 2), 2)+cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;

        return $s;

    }
    /**
     * 影片信息
     *
     * @param Request $request
     * @return void
     */
    public function filmInfo1(Request $request){
        $film_id = $request->input('film_id','');

        if(empty($film_id)){
            return $this->success('',[]);
        }
        logger($film_id);
        $info = Newmove::where('filmNo','=',$film_id)->orwhere('id','=',intval($film_id))->first();
        if(!$info){
            return $this->error('影片信息不存在');
        }
        $look = Newmovie_schedule::where('filmCode',$info->filmNo)->first();
        if($look==null){
            $info['look']=true;
        }else{
            $info['look']=false;
        }
        logger($look);
        logger('s');
        $info['description']=$info['introduction'];
        $info['show_name']=$info['filmName'];
        $info['poster']=$info['trailerCover'];
        $info['leading_role']=$info['cast'];
        $info['open_time']=$info['filmName'];
        $info['remark']=$info['score'];
        $info['country']=$info['startTime'];
        $info['trailerList']=$info['stills'];

        return $this->success('',$info);
    }
    /**
     * 根据地区查询影院
     *
     * @return void
     */
    public function getCinemaWithFilm(Request $request){
        $film_id = (int)$request->input('film_id','');
        logger('查询影院');
        $date = $this->showTime;
        $date = date('Y-m-d',strtotime($date));
        $city = $request->input('city_name','');
        $brand_id = $request->input('brand_id',''); //品牌
        $distId = $request->input('dist_id',''); //区域
        $subway = $request->input('subway',''); //地铁
        $trade_area = $request->input('trade_area',''); //商圈
        $lng = round($request->input('lng',''),6);
        $lat = round($request->input('lat',''),6);
        $show_time = strtotime($date);
        $list = array();
        $info = Newmove::where('filmNo','=',$film_id)->orwhere('id','=',intval($film_id))->first();
        $film_id = $info['filmNo'];
        if(empty($film_id)){
            return $this->success('请选择影片',$list);
        }
        $ok = Newmovie_schedule::where('city','=',$city)->distinct()
            ->pluck('cinemaId');
        logger('1');
        if(count($ok)>0){
            logger($film_id);
            $ok = Newmovie_schedule::where('filmCode','=',$film_id)->where('cinemaId','!=',null)->distinct()
                ->pluck('cinemaId');
            logger('2');
            logger($ok);
            $list = Newcinemas::whereIn('cinemaCode',$ok)->where(function ($query) use ($city){
                $query->where('city',$city)->orwhere('county',$city);
            })->get();
            logger($list);
            if(count($list)==0||$list==null){
                return $this->success('该电影暂未上映',$list);
            }
            foreach($list as $item){
                $item['cinema_name']=$item['cinemaName'];
                $item->cinema_id = $item->cinemaCode;
                if($item->latitude!=''&&$item->longitude!=''){
                    $item->distance = $this->getDistance($item->latitude,$item->longitude,$lat,$lng);
                }else{
                    $item->distance='';
                }
            }
            if(!is_array($list)){
                $list = $list->toArray();
            }
            $list = $this->sortDistance($list);
            return $this->success('',$list);
        }
        $list = Newcinemas::where('city','=',$city)->orwhere('county','=',$city)->get();

        foreach($list as $item){
            $item->cinema_id = $item->cinemaCode;
            $item->distance = $this->getDistance($item->latitude,$item->longitude,$lat,$lng);
        }
        if(!is_array($list)){
            $list = $list->toArray();
        }
        $list = $this->sortDistance($list);
//        只看前10个
//        $list = array_slice($list,0,10);
//        =====================判断影院内是否有该电影===============================
        $arr=[];
        foreach($list as $item){
                $schedulesList = Napi::getplan($item->cinemaCode);
            $schedulesList=$schedulesList['result'];
            Newmovie_schedule::syncData($schedulesList);

            $films = Newmovie_schedule::where("filmCode","like",'%'.$film_id.'%')->first();
            if(!empty($films)){
                $item['cinema_name']=$item['cinemaName'];
                $arr[]=$item;
            }
//            if(count($arr)>=5){
//                break;
//            }
        }
        $codes = $city;
        // 删除这批数据
        \App\Jobs\Nsyncdelete::dispatch($codes)->delay(60*60);
//        ==============================================
        $list=$arr;
        return $this->success('',$list);
    }
    /**
     * 影院列表
     *
     * @param Request $request
     * @return void
     */
    public function cinemaList(Request $request){
//        $cityId = $this->cityId;
        $brandId = (int)$request->input('brand_id',''); //品牌
        $distId = (int)$request->input('dist_id',''); //区域
        $subway = (int)$request->input('subway',''); //地铁
        $trade_area = $request->input('trade_area',''); //商圈
        $city = $request->input('city_name',''); //城市
        $lng = round($request->input('lng',''),6);
        $lat = round($request->input('lat',''),6);
//            $brand = Api\CinemasBrand::select(['id'])->where('id',$brandId)->first();
        logger($brandId);
            $list = Newcinemas::where(function ($query) use ($city){
                $query->where('city','=',$city)
                    ->orwhere('county','=',$city);
            })->where(function ($query) use ($brandId){
                if($brandId!=0){
                    $query->where('brandid',intval($brandId));
                }
            })->get();

        if($list->isEmpty()){
            return $this->error('暂无影院');
        }
        foreach($list as $item){
            $item->cinema_name=$item->cinemaName;
            $item->distance = $this->getDistance($item->latitude,$item->longitude,$lat,$lng);
        }

        $list = $this->sortDistance($list->toArray());
        return $this->success('',$list);
    }
    /**
     * 根据影院展示影片
     *
     * @param Request $request
     * @return void
     */
    public function getFilmWithCinema(Request $request){
        $cinema_id =(int) $request->input('cinema_id','');
        $film_id =(int) $request->input('film_id','');
        $show_time = strtotime($this->showTime);
        logger($film_id);
        logger($cinema_id);
        $cinema = Newcinemas::where('cinemaCode',$cinema_id)->orwhere('id',$cinema_id)->first();
        $cinema['cinema_name']=$cinema['cinemaName'];
        if(empty($cinema)){
            return $this->error('影院信息不存在');
        }
        logger($cinema);
        $schedulesList=Newmovie_schedule::where('cinemaId',$cinema->cinemaCode)->get();
        $list=$schedulesList;
        if(count($schedulesList)==0){
//            logger($cinema_id);
                $schedulesList = Napi::getplan($cinema->cinemaCode);
            $list=$schedulesList['result'];
        }
//        logger($schedulesList);
        $uniqueList = [];
//        $uniqueList = Newmovie_schedule::where('cinemaId',$cinema->cinemaCode)->distinct('filmCode')->get();
// 遍历$list数组中的每个元素，并且检查它们的filmName属性是否已经存在于$uniqueList数组中。如果不存在，就把这个电影对象添加到$uniqueList数组中。
        foreach ($list as $item) {
            logger($item);
            $item['show_name']=$item['filmName'];
//            $item['remark']=$item[''];
            $item['type']=$item['copyType'];
            $item['leading_role']=$item['language'];
            $item['film_time']=$item['totalTime'];
            $item['tehui']=$item['buyPrice'];
//            $item['id']=$item['planKey'];
            $item['close_time']=$item['totalTime']*60;
            $item['show_time']=0;
            // 获取当前电影对象的filmName属性值
            $filmName = $item["filmName"];

            // 定义一个布尔变量来标记当前电影对象是否已经存在于$uniqueList数组中，默认为false，表示不存在。
            $exists = false;

            // 遍历$uniqueList数组中的每个元素，并且比较它们的filmName属性值和当前电影对象的filmName属性值是否相同。如果相同，就把$exists变量设为true，表示存在。
            foreach ($uniqueList as $uniqueItem) {
                if ($uniqueItem["filmName"] == $filmName) {
                    $exists = true;
                    break; // 跳出内层循环，不需要再比较其他元素了。
                }
            }

            // 如果$exists变量仍然为false，表示当前电影对象不存在于$uniqueList数组中，就把它添加到$uniqueList数组中。
            if (!$exists) {
                array_push($uniqueList, $item);
            }
        }
        $hlist=$uniqueList;

        foreach ($hlist as &$item){
            $move = Newmove::where('filmName','=',$item['filmName'])->first();
            $item['poster']=$move['trailerCover'];
        }
        $datelist = $this->getShowDateList($list);
//        $times = Newmovie_schedule::where('cinemaId',$cinema->cinemaCode)->groupBy('filmCode')->get();
//        logger($times);
        foreach($hlist as &$item){
            $times = Newmovie_schedule::where('cinemaId',$cinema->cinemaCode)->where('filmCode',$item['filmCode'])->get();
            $item['datelist'] = $this->getShowDateList($times);
//            $item['datelist']=$datelist;
        }

        if ($film_id!=''){
            $film = Newmove::where('id',$film_id)->first();
            $film_id=$film->filmNo;
            foreach ($hlist as $key=>&$item) {
                // 判断每一项的film_id是否等于
                if ($item["filmCode"] == $film_id) {
                    $it = array_splice($hlist, $key, 1);
                    array_unshift($hlist, $it[0]);
                }
            }
        }
        $list = $hlist;
        return $this->success('',compact('cinema','list','hlist'));
    }
    protected function sortDistance($list){
        $sortDistance = array_column($list,'distance');
        // dd($list);
        array_multisort($sortDistance,SORT_ASC,$list);
        foreach($list as &$item){
            $item = (object)$item;

            if($item->distance < 1000 && $item->distance > 20){
                $item->distance = (int)$item->distance . 'm';
            }elseif($item->distance>=1000 && $item->distance < 1000000){
                $item->distance = round($item->distance / 1000,2).'km';
            }else{
                $item->distance = '';
            }
        }
        return $list;
    }
    /**
     * 放映计划
     *
     * @param Request $request
     * @return void
     */
    public function schedulesList(Request $request){
        $cinema_id =(int) $request->input('cinema_id','');
        $film_id =(int) $request->input('film_id','');
        $show_time = strtotime($this->showTime);

        $cinema = Newcinemas::where('cinemaCode',$cinema_id)->orwhere('id',$cinema_id)->first();
        if(empty($cinema)){
            return $this->error('影院信息不存在');
        }
        $film = Newmovie_schedule::where('id',$film_id)->first();
        $film_id=$film->filmCode;
        logger('排期');
        $film = Newmove::where('filmNo',$film_id)->orwhere('id','=',intval($film_id))->first();
        if(empty($film)){
            return $this->error('影片信息不存在',$film );
        }
        $schedulesList=Newmovie_schedule::where('cinemaId',$cinema->cinemaCode)->where('filmCode',$film_id)->get();
        $list=$schedulesList;
        if(count($schedulesList)==0){
//            logger($cinema_id);
            $schedulesList = Napi::getplan($cinema->cinemaCode);
            $list=$schedulesList['result'];
        }
        $schedulesList=[];
        foreach ($list as $item){
            if(strstr($item['startTime'],$this->showTime)!=false){
                $item['show_time_txt']=substr($item['startTime'], -8,5);
                $item['close_time_txt']=substr($item['endTime'], -8,5);
                $item['show_version']=$item['copyType'];
                $item['close_time']=$item['totalTime']*60;
                $item['show_time']=0;
                $item['hall_name']=$item['hallName'];
                $item['local_price']=$item['thirdReferencePrice']<$item['buyPrice']?$item['thirdReferencePrice']:$item['buyPrice'];
                $item['price']=$item['buyPrice'];
                $item['show_name']=$item['filmName'];
                $schedulesList[]=$item;
            }
        }

        //整理排期
        $duration = 0;
        foreach($schedulesList as $k=>$schedule){
            if((strtotime($schedule->startTime) - time()) < 1800){
                unset($schedulesList[$k]);continue;
            }

            // $schedule->id = $schedule->show_index;
            $duration = $schedule->duration = $film->filmName;
            $endtime = $schedule->close_time;//strtotime("+ {$film->film_duration} minute",$schedule->show_time);
//            $schedule->close_time_txt = date('H:i',$endtime);
            unset($schedule->film);

        }
        if(!is_array($schedulesList)){
            $schedulesList = $schedulesList->toArray();
        }

//        logger($schedulesList);
        foreach($schedulesList as &$schedule){
//            $schedule = (array)$schedule;
            $schedule->discount = 0;
            logger($schedule);
            $schedule->price = $schedule->buyPrice;

        }
        //循环判断$schedulesList中的id是否有相同的，如果有相同的，只保留一个
        $newSchedulesList = array();
        foreach($schedulesList as $k=>$v){
            if(!isset($newSchedulesList[$v['id']])){
                $newSchedulesList[$v['id']] = $v;
            }
        }
        $schedulesList=array_values($newSchedulesList);

        $discount_txt = '';
        $result['film_time'] = $duration;
        $result['list'] = $schedulesList;
        $allPrice = array_column($schedulesList,'price');
        $datelist = $this->getShowDateList2(time(),$film_id,$cinema->cinemaCode);
        $result['discount'] = $discount_txt;
        $result['datelist'] = $datelist;
        return $this->success('',$result);
    }
    //获取影片排片日期
    protected function getShowDateList2($openTime,$film_id = 0,$cinema_id=0){
        logger($film_id);
        logger($cinema_id);
        //聚福宝
        $date = array();
        if($film_id)
        {
            if($cinema_id!=0){
                $datelist = Newmovie_schedule::select('startTime')->where('filmCode',$film_id)->where('cinemaId',$cinema_id)->groupBy('startTime')->get();
            }else{
                $datelist = Newmovie_schedule::select('startTime')->where('filmCode',$film_id)->groupBy('startTime')->get();
            }

            foreach($datelist as $item){
                if(strtotime($item->startTime)>time()){
                    $date[] = array(
                        'title'=>substr($item->startTime,0,10),
                        'value'=>$item->startTime,
                    );
                }

            }
//            logger($date);
            if(!empty($date)){
                return $date;
            }
        }
        if($openTime<time()){
            $openTime = time();
        }
        //循环输出日期
        for($i=0;$i<1;$i++){
            $time = strtotime("+ {$i} day",$openTime);
            $date[] = array(
                'title'=>date('Y-m-d',$time),
                'value'=>date('Y-m-d',$time)
            );
        }
        return $date;
    }
    //获取影片排片日期
    protected function getShowDateList($data){
//        logger($data);
        $dates = array(); //创建一个空数组来存储日期
        foreach ($data as $movie) { //遍历每个电影信息
            $start = $movie["startTime"]; //获取startTime字段
            $date = substr($start, 0, 10); //截取前10个字符，即日期部分
            if (!in_array($date, $dates)) { //如果日期不在$dates数组中
                array_push($dates, $date); //把日期加入$dates数组
            }
        }
        $date=[];
        foreach ($dates as &$item){
            $date[] = array(
                'title'=>$item,
                'value'=>$item
            );
        }
        return $date;
    }
    /**
     * 放映计划详情
     *
     * @param Request $request
     * @return void
     */
    public function schedulesDetail(Request $request){
        $schedule_id = $request->input('paiqi_id',0);
        $schedule=Newmovie_schedule::where('id',$schedule_id)->first();
        $schedule_id=$schedule->planKey;
        $comId = (int) $request->input('com_id',0);
//        $cinema_id=$request->input('cinema_id',0);
        $cinema_id=$schedule->cinemaId;
        logger($cinema_id);
        logger($schedule_id);
        $seat = Napi::getplanseat($cinema_id,0,$schedule_id);
//        $info=[];
//        logger($seat);
        if(empty($seat['result'])){
            return $this->error('暂无放映场次');
        }
        $info['seats']=$seat['result'];



        $cinema = Newcinemas::where('cinemaCode',$cinema_id)->first();
        $info['cinema'] = $cinema;
        return $this->success('',$info);
        // $hallInfo = Api\Hall::where('id',$info->hall_id)->first();
        // if(!empty($hallInfo)){
        //     $info->seat_count = $hallInfo->seatcount;
        // }

        $info->close_time_txt = '';

        $film = Api\Film::where('id',$info->film_id)->first();
        if(!empty($film)){
            $endtime = strtotime("+ {$film->film_duration} minute",$info->show_time);
            $info->duration = $film->duration;
            $info->close_time_txt = date('H:i',$endtime);
        }

        $info->notice = '';
        $info->regular = '';
        $seatData = MovieApiFactory::app($this->apiName)->seatList($info->show_index,$info->cinema_id);
        $info->seat_count = $seatData['seat_count'];
        $info->seats = $seatData['seats'];
        $info->max_column = $seatData['max_column'];
        $info->min_column =$seatData['min_column'];
        $info->max_row = $seatData['max_row'];
        $info->min_row = $seatData['min_row'];
        $info->schedule_area = $info->getScetionInfo();

        $discount = 0; //优惠
        $cinemaBrand = Api\CinemasBrand::where('id',$cinema->brand_id)->first();
        $cinemaBrandDiscount = 0;
        if($cinemaBrand){
            $cinemaBrandDiscount = round($cinemaBrand->price_discount_rate,2) / 10;
        }
        $price = $info->price;
        $market_price = round($info->price / 100,2);
        Api\Schedules::calcLocalPrice($comId,$price,$cinemaBrandDiscount);
        $discount = bcsub($market_price,$price,2);//$schedule['market_price'] - $schedule['price'];

        $info = $info->toArray();
        $info['discount'] = $discount;
        $info['market_price'] = $market_price;
        $info['price'] = $price;

        return $this->success('',$info);
    }
    /**
     * 影片搜索
     *
     * @param Request $request
     * @return void
     */
    public function searchFilm(Request $request){
        $keyword = $request->input('search','');
        $city = $request->input('city_name','');
        $array = array(
        );
        $list = array();
        logger($keyword);
        if($keyword=='undefined'){
            logger('没搜索内容');
            $cinemaList = Newcinemas::where(function ($query) use ($city) {
                $query->where('city', $city)
                    ->orWhere('county', $city);
            })->take(20)->get(['id','cinemaCode','cinemaName','address','latitude','longitude']);
            logger($cinemaList);
            if(count($cinemaList)>0){
                $list1=[];
                foreach($cinemaList as $item){
                    $item['cinema_name']=$item['cinemaName'];
                    $list1[] = $item;
                }
                $array[]=[
                    'title'=>'影院',
                    'list'=>$list1
                ];
            }
            return $this->success('',$array);
        }
        $cinemaList = Newcinemas::where(function ($query) use ($city) {
            $query->where('city', $city)
                ->orWhere('county', $city);
        })
            ->where('cinemaName', 'like', "%$keyword%")
            ->take(20)->get(['id','cinemaCode','cinemaName','address','latitude','longitude']);
        if(count($cinemaList)>0){
            $list1=[];
            foreach($cinemaList as $item){
                $item['cinema_name']=$item['cinemaName'];
                $list1[] = $item;
            }
            $array[]=[
                'title'=>'影院',
                'list'=>$list1
            ];
        }
        $list = Newmove::whereRaw("filmName like ?",["%$keyword%"])->where("filmNo","!=","")->where('state','0')
//            ->where(function($query){
//                return $query->where(array(['city_code','=',$this->cityId],['date_type','=',2]))
//                    ->orWhere('date_type',0);
//            })
            ->take(20)->get();


        if(count($list)>0){
            $list1=[];
            foreach($list as $item){
                $item['show_name']=$item['filmName'];
                $item['poster']=$item['trailerCover'];
                $item['remark']=$item['score'];
                $item['leading_role']=$item['cast'];
                $list1[] = $item;
            }
            $array[]=[
                'title'=> '电影',
                'list' => $list1
            ];
        }
        return $this->success('',$array);
    }
}
