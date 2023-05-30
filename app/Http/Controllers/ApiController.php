<?php
namespace App\Http\Controllers;
use App\ApiModels\Wangpiao\Napi;
use App\Models\Region;
use App\Support\WpApi;
use App\Models\Setting;

use Illuminate\Http\Request;
use App\Support\Api\ApiHouse;
use App\ApiModels\Wangpiao as Api;

/**
 * 聚福宝接口
 */
class ApiController extends Controller
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
     * 影片搜索
     *
     * @param Request $request
     * @return void
     */
    public function searchFilm(Request $request){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']==1){
            $api = new NApiController();
            return $api->searchFilm($request);
        }
        $keyword = $request->input('search','');
        $array = array(
        );
        if($keyword=='undefined'){
            $cinemaList = Api\Cinema::where('city_code',$this->cityId)
                ->take(20)
                ->get(['id','cinema_name','address','latitude','longitude','grade']);
            $a=[];
            foreach($cinemaList as $item){
                $a[] = $item;
            }
            if(count($a)>0){
                $array[]=[
                    'title'=>'影院',
                    'list'=>$a
                ];
            }
            return $this->success('',$array);
        }
        $list = array();
        if(empty($keyword)){
            return $this->success('',$array);
        }

        $cinemaList = Api\Cinema::where('city_code',$this->cityId)
                        ->whereRaw("cinema_name like ?",["%$keyword%"])
                        ->take(20)
                        ->get(['id','cinema_name','address','latitude','longitude','grade']);
        $a=[];
        foreach($cinemaList as $item){
            $a[] = $item;
        }
        if(count($a)>0){
            $array[]=[
                'title'=>'影院',
                'list'=>$a
            ];
        }

        $filed = array(
            'id',
            'show_name',
            'city_code',
            'poster',
            'remark',
            'date_type',
            'director',
            'duration',
            'leading_role'
        );
        $list = Api\CurrentFilm::search($keyword)
                ->where(function($query){
                    return $query->where(array(['city_code','=',$this->cityId],['date_type','=',2]))
                                    ->orWhere('date_type',0);
                })
                ->take(20)->get($filed);
        $a=[];
        $b=[];
        foreach($list as $item){
            if($item->date_type==1){
                $a[]=$item;
            }
            if($item->date_type==2){
                $b[]=$item;
            }
        }
        if(count($a)>0){
            $array[]=[
                'title'=>'即将上映',
                'list'=>$a
            ];
        }
        if(count($b)>0){
            $array[]=[
                'title'=>'正在热映',
                'list'=>$b
            ];
        }

        return $this->success('',$array);
    }
    /**
     * 影院列表
     *
     * @param Request $request
     * @return void
     */
    public function cinemaList(Request $request){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']==1){
            $api = new NApiController();
            return $api->cinemaList($request);
        }
        $cityId = $this->cityId;
        $brandId = (int)$request->input('brand_id',''); //品牌
        $distId = (int)$request->input('dist_id',''); //区域
        $city_code = (int)$request->input('city_code',''); //区域
        $r = Region::where('qu_code',$city_code)->first();
        $region_name='';
        if(!empty($r)){
            $region_name = $r->qu_name;
        }
        $subway = (int)$request->input('subway',''); //地铁
        $trade_area = $request->input('trade_area',''); //商圈
        $lng = round($request->input('lng',''),6);
        $lat = round($request->input('lat',''),6);
        $valid = compact('brandId','distId','subway','trade_area','city_code','region_name');
        foreach($valid as $key=>$value){
            if(is_null($value) || $value == 'undefined' || $value == 'tick'){
                $valid[$key] = '';
            }
        }
        list($brandId,$distId,$subway,$trade_area) = array_values($valid);

        $list = Api\Cinema::search($cityId,$distId,$brandId,$subway,$trade_area,$region_name)->get();
//        logger($city_code);
//        logger($brandId);
//        $list = Api\Cinema::where('city_code',$city_code)->where('brand_id',$brandId)->get();
        if($list->isEmpty()){
            return $this->error('暂无影院');
        }

        foreach($list as $item){
            $item->distance = $this->getDistance($item->latitude,$item->longitude,$lat,$lng);
        }

        $list = $this->sortDistance($list->toArray());
        return $this->success('',$list);
    }
    /**
     * 放映计划详情
     *
     * @param Request $request
     * @return void
     */
    public function schedulesDetail(Request $request){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']==1){
            $api = new NApiController();
            return $api->schedulesDetail($request);
        }
        $schedule_id = $request->input('paiqi_id',0);
        $comId = (int) $request->input('com_id',0);
        $info = Api\Schedules::getSchedulesInfo($schedule_id,true);

        if(empty($info)){
            return $this->error('暂无放映场次');
        }
        $cinema = Api\Cinema::where('id',$info->cinema_id)->first();
        $info->cinema_name = $cinema->cinema_name;
        // $hallInfo = Api\Hall::where('id',$info->hall_id)->first();
        // if(!empty($hallInfo)){
        //     $info->seat_count = $hallInfo->seatcount;
        // }

        $info->close_time_txt = '';

        $film = Api\Film::where('id',$info->film_id)->first();
        if(!empty($film)){
            $endtime = strtotime("+ {$film->film_duration} minute",$info->show_time);
            $info->duration = $film->duration;
            $info->close_time_txt = date('H:i',$info->close_time);
        }

        $info->notice = '';
        $info->regular = '';
        $seatData = ApiHouse::formatSeatsJufubao($info->show_index,$info->cinema_id);
        $info->seat_count = $seatData['seat_count'];
        $info->seats = $seatData['seats'];
//        $info->a = $seatData['a'];
        $info->max_column = $seatData['max_column'];
        $info->min_column =$seatData['min_column'];
        $info->max_row = $seatData['max_row'];
        $info->min_row = $seatData['min_row'];


        $discount = 0; //优惠
        $cinemaBrand = Api\CinemasBrand::where('id',$cinema->brand_id)->first();
        if($cinemaBrand){
            $discount = round($cinemaBrand->discount,2);
        }

        $info = $info->toArray();
        if($discount && $comId && $cinemaBrand){
            $info['discount'] = $discountMoney = $cinemaBrand->calcDiscountMoney($info['local_price']);
            $info['price'] = $info['local_price'];
            $info['local_price'] = round($info['local_price'] - $discountMoney,2);
        }
        return $this->success('',$info);
    }
    /**
     * 放映计划
     *
     * @param Request $request
     * @return void
     */
    public function schedulesList(Request $request){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']==1){
            $api = new NApiController();
            return $api->schedulesList($request);
        }
        $cinema_id = (int)$request->input('cinema_id','');
        $film_id = (int)$request->input('film_id','');
        $comId = (int) $request->input('com_id',0);
        $date = $this->showTime;
        $list = array();
        $searchDate = date('Y-m-d',strtotime($date));
        if($searchDate == date('Y-m-d')){
            $date .= date(' H:i:s');
        }
        $show_time = strtotime($date);
//        $show_time = strtotime('+ 30 minute',$show_time);
        if(date('Y-m-d',$show_time) != $searchDate){
            $show_time = strtotime($date);
        }
        if(empty($cinema_id) || empty($film_id)){
            return $this->error('请选择影院和影片',$list);
        }
        $cinema = Api\Cinema::where('id',$cinema_id)->select('id','brand_id','city_code as city_id','latitude','longitude','cinema_name','address')->first();
        if(empty($cinema)){
            return $this->error('影院信息不存在');
        }
        $film = Api\Film::where('id',$film_id)->first();
        if(empty($film)){
           return $this->error('影片信息不存在',$list);
        }

        $discount = 0; //优惠
        $cinemaBrand = Api\CinemasBrand::where('id',$cinema->brand_id)->first();

        if($cinemaBrand){
            $discount = round($cinemaBrand->discount,2);
        }
        $result = array();


        // dd($apiResult);

//        $schedulesList = Api\Schedules::searchFilm($cinema_id,$show_time,$film_id)->oldest('show_time')->get();
//        if($schedulesList->isEmpty()){
//        logger($film_id);
//        logger($cinema_id);
            $schedulesList = ApiHouse::getFilmShowByDate($cinema_id,$show_time,$film_id);
            // Api\CinemaMovie::syncData($apiResult);
            // $schedulesList = Api\Schedules::syncData($apiResult,$film_id);
            // Api\Schedules::delaySync($cinema_id);
//        }
        $duration = 0;
        foreach($schedulesList as $k=>$schedule){
            if(($schedule->show_time - time()) < 1800){
                unset($schedulesList[$k]);continue;
            }
            // $schedule->id = $schedule->show_index;
            $duration = $schedule->duration = $film->film_duration;
            $endtime = $schedule->close_time;//strtotime("+ {$film->film_duration} minute",$schedule->show_time);
            $schedule->close_time_txt = date('H:i',$endtime);
            unset($schedule->film);

        }
        if(!is_array($schedulesList)){
            $schedulesList = $schedulesList->toArray();
        }


        foreach($schedulesList as &$schedule){
            $schedule = (array)$schedule;
            $schedule['discount'] = 0;
            // if($cinemaBrand && $discount && $comId){
            //     $schedule['discount'] = $discountMoney = $cinemaBrand->calcDiscountMoney($schedule['local_price']);
            //     $schedule['price'] = $schedule['local_price'];
            //     $schedule['local_price'] = round($schedule['local_price'] - $discountMoney,2);
            // }38*
            // $schedule['price'] = $schedule['api_price'];
            $schedule['id'] = $schedule['show_index'];
            $schedule['price'] = $schedule['api_price'];
            if($cinemaBrand && $discount && $comId){
                $schedule['discount'] = $discountMoney = $cinemaBrand->calcDiscountMoney($schedule['price']);
                // $schedule['price'] = $schedule['local_price'];
                $schedule['local_price'] = round($schedule['price'] - $discountMoney,2);
            }
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
        if($discount && $comId){
            $minPrice = $allPrice[0]??0;
            $maxPrice = $allPrice[0]??0;
            if(count($allPrice) > 1){
                $minPrice = min($allPrice);
                $maxPrice = max($allPrice);
            }
            $minPrice = $cinemaBrand->calcDiscountMoney($minPrice);
            $maxPrice = $cinemaBrand->calcDiscountMoney($maxPrice);
            $discount_txt = $minPrice.'~'.$maxPrice;
            if($minPrice == $maxPrice){
                $discount_txt = $minPrice;
            }
            $discount_txt = '使用影旅卡，购票单张立减'.$discount_txt.'元';
        }
        $datelist = $this->getShowDateList(time(),$film_id);
        $result['discount'] = $discount_txt;
        $result['datelist'] = $datelist;
        return $this->success('',$result);
    }
    /**
     * 影片信息
     *
     * @param Request $request
     * @return void
     */
    public function filmInfo(Request $request){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']==1){
            $api = new NApiController();
            return $api->filmInfo1($request);
        }
        $film_id = $request->input('film_id','');

        if(empty($film_id)){
            return $this->success('',[]);
        }
        $info = Api\Film::where('id',(int)$film_id)->first();
        if(!$info){
            return $this->error('影片信息不存在');
        }
        $openTime = $info->getOriginal('open_time');

        $info->datelist = $this->getShowDateList($openTime,$film_id);
        // logger($openTime);
        $info = $info->toArray();
        // logger($info->toArray());
        $info['open_time'] = date('Y-m-d',$openTime);
        // $info->open_time = date('Y-m-d',$openTime);
        return $this->success('',$info);
    }


    /**
     * 根据影院展示影片
     *
     * @param Request $request
     * @return void
     */
    public function getFilmWithCinema(Request $request){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']==1){
            $api = new NApiController();
            return $api->getFilmWithCinema($request);
        }
        $cinema_id =(int) $request->input('cinema_id','');
        $film_id =(int) $request->input('film_id','');

        $show_time = strtotime($this->showTime);
        $list = array();

        $cinema = Api\Cinema::where('id',$cinema_id)->select('id','city_code as city_id','latitude','longitude','cinema_name','address')->first();
        if(empty($cinema)){
            return $this->error('影院信息不存在');
        }
        // if(!$cinema->is_sync_hall){
        //     try {
        //         \App\Jobs\Wangpiao\HallsJob::dispatch($cinema);
        //     } catch (\Throwable $th) {
        //         //throw $th;
        //     }
        // }
        $filmIds = array();
        $schedulesList = Api\CinemaMovie::searchFilm($cinema_id,time())->orderBy('show_time','asc')->get();
//        if($schedulesList->isEmpty()){
            $apiResult = (array) ApiHouse::getFilmList($cinema_id);
//             $apiResult = (array) ApiHouse::getFilmShowByDate($cinema_id);
            // // $schedulesList = Api\CinemaMovie::syncData($apiResult);
            // // //WpApi::logger('['.date('Y-m-d 00:00:00').']getFilmWithCinema:获取到'.count($schedulesList).'条数据');
            // Api\Schedules::syncData($apiResult);
            // Api\Schedules::delaySync($cinema_id);
            $schedulesList = Api\CinemaMovie::syncData($apiResult,$cinema_id);
//        }
        foreach($schedulesList as $schedule){
            $filmIds[] = $schedule->film_id;
        }

        if($film_id){
           array_unshift($filmIds,$film_id);
        }
        $filmIds = array_unique($filmIds);
        $sortFilmIds = implode(',',$filmIds);

        if(!empty($sortFilmIds)){
            $field = array(
                'id',
                'show_name',
                'open_time',
                'city_code',
                'poster',
                'duration',
                'type',
                'show_version_list',
                'remark',
                'date_type',
                'director',
                'leading_role'
            );
            $list = Api\Film::whereIn('id',$filmIds)
                    // ->where('city_code',$cinema->city_id)
                        // ->orderBy('remark','desc')
                        ->orderByRaw('FIELD(id,'.$sortFilmIds.')')
                        ->get($field);
                        // ->dd();
        }
        foreach($list as $item){
            $openTime = $item->getOriginal('open_time');
            $item->datelist = $this->getShowDateList($openTime,$item->id,$cinema_id);
        }
        return $this->success('',compact('cinema','list'));
    }

    //获取影片排片日期
    protected function getShowDateList($openTime,$film_id = 0,$cinema_id=0){
        //聚福宝
        $date = array();
        if($film_id)
        {
            if($cinema_id!=0){
                $datelist = Api\Schedules::select('show_date')->where('film_id',$film_id)->where('cinema_id',$cinema_id)->groupBy('show_date')->get();
            }else{
                $datelist = Api\Schedules::select('show_date')->where('film_id',$film_id)->groupBy('show_date')->get();
            }

            foreach($datelist as $item){
                $date[] = array(
                    'title'=>$item->show_date,
                    'value'=>$item->show_date,
                );
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
        for($i=0;$i<7;$i++){
            $time = strtotime("+ {$i} day",$openTime);
            $date[] = array(
                'title'=>date('Y-m-d',$time),
                'value'=>date('Y-m-d',$time)
            );
        }
        return $date;
    }

    /**
     * 根据电影、日期、城市、区域、品牌、查询影院
     *
     * @return void
     */
    public function getCinemaWithFilm(Request $request){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']==1){
            $api = new NApiController();
            return $api->getCinemaWithFilm($request);
        }
        $film_id = (int)$request->input('film_id','');
        $regionname = $request->input('city_name','');
        $date = $this->showTime;
        $date = date('Y-m-d',strtotime($date));
        $brand_id = $request->input('brand_id',''); //品牌
        $distId = $request->input('dist_id',''); //区域
        $subway = $request->input('subway',''); //地铁
        $trade_area = $request->input('trade_area',''); //商圈
        $lng = round($request->input('lng',''),6);
        $lat = round($request->input('lat',''),6);
        $valid = compact('brand_id','distId','subway','trade_area','regionname');
        foreach($valid as $key=>$value){
            if(is_null($value) || $value == 'null' || $value == 'undefined' || $value == 'tick'){
                $valid[$key] = '';
            }
        }

        list($brand_id,$distId,$subway,$trade_area,$regionname) = array_values($valid);
        $show_time = strtotime($date);
        $list = array();
        if(empty($film_id)){
            return $this->success('请选择影片',$list);
        }

        // $list = Api\ScheduleCinema::film($film_id)
        //             ->search($this->cityId,$distId,$brand_id,$subway,$trade_area)
        //             ->showTime($show_time)
        //             ->get();
        // if($list->isEmpty() && implode('',$valid) == ''){
        //     $apiResult = (array) WpApi::getCinemaQueryList($this->cityId,date('Y-m-d H:i:s',$show_time),$film_id);
        //     $list = Api\ScheduleCinema::syncData($apiResult,$film_id,$show_time);
        //     //Api\ScheduleCinema::delaySync(['cityId'=>$this->cityId,'show_time'=>$show_time,'film_id'=>$film_id]);
        // }

        $list = Api\Cinema::search($this->cityId,$distId,$brand_id,$subway,$trade_area,$regionname)->get();

        foreach($list as $item){
            $item->cinema_id = $item->id;
            $item->distance = $this->getDistance($item->latitude,$item->longitude,$lat,$lng);
        }

        if(!is_array($list)){
            $list = $list->toArray();
        }
        $list = $this->sortDistance($list);
        return $this->success('',$list);
    }

    // protected function getCinemaList(){
    //     $cityCode = $this->cityId;

    // }

   protected function sortDistance($list){
        $sortDistance = array_column($list,'distance');
        // dd($list);
        array_multisort($sortDistance,SORT_ASC,$list);
        foreach($list as &$item){
            $item = (object)$item;

            if($item->distance < 1000 && $item->distance > 20){
                $item->distance = (int)$item->distance . 'm';
            }elseif($item->distance>=1000 && $item->distance < 100000){
                $item->distance = round($item->distance / 1000,2).'km';
            }else{
                $item->distance = '';
            }
        }
        return $list;
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
     * 热映、即将上映的电影
     *
     * @param Request $req
     * @return void
     */
    public function currentFilmList(Request $req){
        $setting = Setting::getSettings();
        if($setting['jiekoufang']['content']==1){
            $api = new NApiController();
            return $api->currentFilmList($req);
        }
        $type = (int)$req->input('type',1);//1即将上映 2热映
        $limit = (int) $req->input('limit',100); //0全部   0指定数量
        $cityId = $this->cityId;
        // \App\ApiModels\Wangpiao\CurrentFilm::truncate();
        $nowtime = strtotime(date('Y-m-d'));;
        if($type == 1){
            $list = Api\CurrentFilm::where('date_type',1)
                        ->where('city_code',0)
                        ->take($limit)
                        ->get();
            if($list->isEmpty()){
                // $apiResult = (array)WpApi::getPlanFilm(); //即将上映
                $apiResult = (array) ApiHouse::getPlanFilm($cityId);
                $list = Api\CurrentFilm::syncData($apiResult,1,$cityId);
                Api\Film::syncData($apiResult,2,$cityId);
            }
            foreach($list as &$item){
                if(is_array($item)){
                    $item = (object)$item;
                    $item->open2_time = strtotime(date('Y-m-d',$item->open_time));
                }else{
                    $item->open2_time = strtotime(date('Y-m-d',strtotime($item->open_time)));
                }
                $item->open2_time2 = date('Y-m-d H:i:s',$item->open2_time);
                $item->is_yushow = 0;
                // $openTime = strtotime($item->open_time);
                if($nowtime < $item->open2_time){
                    $item->is_yushow = 1;
                }
            }

            return $this->success('',$list);
        }
        if(empty($cityId)){
            return $this->success('请选择您所在的城市',array());
        }
        $list = Api\CurrentFilm::where('date_type',$type)
                        ->where('city_code',$cityId)
                        ->take($limit)
                        // ->orderBy('remark','desc')
                        ->orderBy('infoid','asc')
                        ->get();
        if($list->isEmpty()){
            $list = array();
            $apiResult = (array) ApiHouse::getCurrentFilm($cityId);
            $list = Api\CurrentFilm::syncData($apiResult,2,$cityId);
            Api\Film::syncData($apiResult,2,$cityId);
            $list = array_splice($list,0,$limit);
            $remarkArr = array_column($list,'remark');
            array_multisort($remarkArr,SORT_DESC,$list);
        }


        foreach($list as &$item){
            if(is_array($item)){
                $item = (object)$item;
                $item->open2_time = strtotime(date('Y-m-d',$item->open_time));
            }else{
                $item->open2_time = strtotime(date('Y-m-d',strtotime($item->open_time)));
            }

            $item->is_yushow = 0;
            // $openTime = strtotime($item->open_time);
            if($nowtime < $item->open2_time){
                $item->is_yushow = 1;
            }
        }
        return $this->success('',$list);
    }
    /**
     * 影院品牌
     *
     * @return void
     */
    public function cinemaBrand(){
        $list = Api\CinemasBrand::select(['id','brand_name'])->get()->makeHidden(['levels','store_id']);
        if($list->isEmpty()){
            //$apiResult = (array)WpApi::getCinemaLineList();
            $apiResult = array();
            $list = Api\CinemasBrand::syncData($apiResult);
        }
        $common = array(array('id'=>'tick','brand_name'=>'全部'));
        $list = collect($common)->merge($list);
        return $this->success('',$list);
    }
    /**
     * 城市信息
     *
     * @param Request $request
     * @return void
     */
    public function getCityInfo(Request $request){
        $cityId = $this->cityId;
        $code = $request->input('code','');
        $info = Api\City::where('id',$cityId)->orWhere('code',$code)->first();
        return $this->success('',$info);
    }
    /**
     * 地铁/商圈/区域
     *
     * @param Request $request
     * @return void
     */
    public function cityAreaList(Request $request){
        $cityId = $this->cityId;
        $type = $request->input('type','district');

        $list = array();
        switch($type){
            case 'district':
                // $field = [
                //     'id as city_id',
                //     'name as city_name',
                //     'cid as parent_city_code',
                //     'ccode as city_code'
                // ];
                // $list = Api\District::select($field)->city($cityId)->get();
                $common = array(array('city_id'=>'','city_name'=>'不限','parent_city_code'=>'','city_code'=>$this->cityCode));
                $list = \App\Models\Region::getRegions($cityId,3)->map(function($item){
                    $item->city_id = $item->city_code;
                    $item->parent_city_code = $this->cityId;
                    return $item;
                });
                $list = collect($common)->merge($list);
                break;
            case 'subway':
                // $list = Api\SubWay::city($cityId)->get();
                // $common = array(array('id'=>'','name'=>'不限','cid'=>$cityId));
                // $list = collect($common)->merge($list);
                $list = [];
                break;
            case 'tradearea':

                // $list = Api\TradingArea::city($cityId)->get();
                // $common = array(array('id'=>'','name'=>'不限','cid'=>$cityId));
                // $list = collect($common)->merge($list);
                $list = array();
                break;
        }

        return $this->success('',$list);
    }
    /**
     * 协议
     *
     * @param Request $req
     * @return void
     */
    public function agreement(Request $req){
        $id = $req->input('id',0);
        $content = \App\Models\Agreement::find($id);
        return $this->success('',$content);
    }
    /**
     * 轮播
     *
     * @return void
     */
    public function carousel(Request $request){
        $categoryId = (int)$request->input('category_id',0);
        // $tagId = (int)$request->input('tag_id',0);
        $limit = (int)$request->input('limit',5);
        if($categoryId){
            $category = \App\MallModels\Category::where('id',$categoryId)->first();
            if($category && $category->parent_id > 0){
                $categoryId = $category->parent_id;
            }
        }
        $list = \App\Models\Carousel::getList((int)$categoryId,$limit);
        return $this->success('',$list);
    }

    /**
     * 显示二维码
     *
     * @param Request $request
     * @return void
     */
    public function showqrcode(Request $request){
        $text = trim($request->input('text',''));
        if($text){
            require_once app_path('Support/phpQrCode.php');
            header("Content-type:text/html;");
            \QRcode::png($text,false,'H',10,1);
            die;
        }
        return $this->success('');
    }

    protected function getCityId($city_code){
        if($city_code == '' || $city_code== 'undefined') return 0;
        // $cityList = cache('getCityIdByCode',false);
        $cityList = \App\Models\Region::select(['sheng_code','shi_code','qu_code'])
                        ->distinct('qu_code')
                        ->whereRaw('sheng_code = ? or shi_code = ? or qu_code = ?',[$city_code,$city_code,$city_code])->first()->toArray();
        $this->cityId = $cityList['shi_code']??0;
        // if(!$cityList){
        //     $districtList = Api\District::select(['cid as id','code'])->orderBy('code')->where('code','<>','');
        //     $cityList = Api\City::where('code','<>','000')->orderBy('code')->unionAll($districtList)->pluck('id','code')->toArray();
        //     cache(['getCityIdByCode'=>$cityList,86400*3]);
        // }

    }
}
