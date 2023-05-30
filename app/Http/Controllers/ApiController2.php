<?php
namespace App\Http\Controllers;
use Cache;
use App\Support\WpApi;
use App\Models\Setting;
use App\Support\Helpers;
use Illuminate\Http\Request;
use App\ApiModels\Wangpiao as Api;
use Illuminate\Support\Facades\DB;
/**
 * 网票网开放接口
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
            // $city = Api\City::where('code',$this->cityCode)->first();
            // if(!empty($city)){
            //     $this->cityId = $city->id;
            // }
            // if(!$this->cityId){
            //     $district = Api\District::where('code',$this->cityCode)->first();
            //     if(!empty($district)){
            //         $this->distId = $district->id;
            //         $this->cityId = $district->cid;
            //     }
            // }
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
        $keyword = $request->input('search','');        
        $array = array(
            [
                'title'=>'影院',
                'list'=>[]
            ],
            [
                'title'=> '即将上映',
                'list' => []
            ],
            [
                'title'=> '正在热映',
                'list'=> []
            ]
        );        
        $list = array();
        if(empty($keyword)){
            return $this->success('',$array);
        }
        $cinemaList = Api\Cinema::where('city_code',$this->cityId)
                        ->where('cinema_name','like',"%$keyword%")
                        ->take(20)
                        ->get(['id','cinema_name','address','latitude','longitude','grade']);
        foreach($cinemaList as $item){
            $array[0]['list'][] = $item;
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
        $list = Api\Film::search($keyword)
                ->where(function($query){
                    return $query->where(array(['city_code','=',$this->cityId],['date_type','=',2]))
                                    ->orWhere('date_type',0);
                })
                ->take(20)->get($filed);
                
        foreach($list as $item){
            $array[$item->date_type]['list'][] = $item;
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
        $cityId = $this->cityId;
        $brandId = $request->input('brand_id',''); //品牌
        $distId = $request->input('dist_id',''); //区域
        $subway = $request->input('subway',''); //地铁
        $trade_area = $request->input('trade_area',''); //商圈
        $list = Api\Cinema::search($cityId,$distId,$brandId,$subway,$trade_area)->get();
        if($list->isEmpty()){
            return $this->error('暂无影院');
        }
        
        return $this->success('',$list);
    }
    /**
     * 放映计划详情
     *
     * @param Request $request
     * @return void
     */
    public function schedulesDetail(Request $request){        
        $schedule_id = $request->input('paiqi_id',0);
        $info = Api\Schedules::getSchedulesInfo($schedule_id);
        if(empty($info)){
            return $this->error('暂无放映场次');
        }
        $cinema = Api\Cinema::where('id',$info->cinema_id)->first();
        $info->cinema_name = $cinema->cinema_name;
        $hallInfo = Api\Hall::where('id',$info->hall_id)->first();
        if(!empty($hallInfo)){
            $info->seat_count = $hallInfo->seatcount;
        }
        $seatsList = (array)WpApi::getSeatByShowIndex($info->show_index,$info->cinema_id);
        $sellSeatsList = (array)WpApi::getSellSeatInfo($info->show_index,$info->cinema_id);
        $sellSeatArr = array();
        if(!empty($sellSeatsList)){
            $sellSeatArr = array_column($sellSeatsList,'SeatID');
        }
        $film = Api\Film::where('id',$info->film_id)->first();
        $info->close_time_txt = '';
        
        if(!empty($film)){
            $endtime = strtotime("+ {$film->film_duration} minute",$info->show_time);
            $info->duration = $film->duration;
            $info->close_time_txt = date('H:i',$endtime);
        }
        
        $info->notice = '';
        $info->regular = '';
        
        $letter = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $info->seat_count = count($seatsList);
                
        $columnsArr = array_unique(array_column($seatsList,'ColumnID'));
        $rowsArr = array_unique(array_column($seatsList,'RowID'));
        
        sort($rowsArr);
        sort($columnsArr);
        $rowStep = $colStep = 0;
        if($seatsList){
            $rowStep = abs($rowsArr[0] - $rowsArr[1]);
            $colStep = abs($columnsArr[0] - $columnsArr[1]);
        }
        foreach($seatsList as &$item){
            $item['api_seat'] = $item;
            $item['id'] = $item['SeatIndex'];
            $seatName = explode(':',$item['Name']);
            $row = $seatName[0];
            $column = (int) $seatName[1];
            $item['name'] = "{$row}排{$column}座";
            $row_letter_pos = strpos($letter , $row);
            if($row_letter_pos !== false){
                $row = $row_letter_pos;
            }
            $item['top_px'] = (int)($item['RowID'] / $rowStep) + 1;
            $item['left_px'] = (int)($item['ColumnID'] / $colStep);

            $item['RowID'] = $row;
            $item['ColumnID'] = $column;
            $item['status'] = ($item['Status'] == 'Y')? 1: 0;
            if(in_array($item['SeatID'],$sellSeatArr)){
                $item['status'] = 0;
            }
            $item['flag'] = $item['LoveFlag']; //情侣座标识 0：普通座位 1：情侣座首  座位标记 2：情侣座第二座位标记
                    
            unset($item['SeatIndex'],$item['Status'],$item['LoveFlag'],$item['Name']);
            
        }
        $info->seats = $seatsList;
               
        $info->max_column = empty($columnsArr) ? 0 : max($columnsArr);
        $info->min_column = 0;
        $info->max_row = empty($rowsArr) ? 0 : max($rowsArr);
        $info->min_row = 0;
                
        return $this->success('',$info);
    }
    /**
     * 放映计划
     *
     * @param Request $request
     * @return void
     */
    public function schedulesList(Request $request){
        $cinema_id = $request->input('cinema_id','');
        $film_id = $request->input('film_id','');
        $date = $this->showTime;
        $list = array();
        if(date('Y-m-d',strtotime($date)) == date('Y-m-d')){
            $date .= date(' H:i:s');
        }
        $show_time = strtotime($date);
        
        $show_time = strtotime('+ 30 minute',$show_time);
        if(empty($cinema_id) || empty($film_id)){
            return $this->error('请选择影院和影片',$list);
        }
        $film = Api\Film::where('id',$film_id)->first();
        if(empty($film)){
            return $this->error('影片信息不存在',$list);
        }
        $result = array();
        
        $schedulesList = Api\Schedules::searchFilm($cinema_id,$show_time,$film_id)->oldest('show_time')->get();
        if($schedulesList->isEmpty()){
            $apiResult = WpApi::getFilmShowByDate($cinema_id,$date);
            $schedulesList = Api\Schedules::syncData($apiResult,$film_id);            
        }
        $duration = 0;
        foreach($schedulesList as $schedule){
            $schedule->id = $schedule->show_index;
            $duration = $schedule->duration =  $film->film_duration ;            
            $endtime = strtotime("+ {$film->film_duration} minute",$schedule->show_time);
            $schedule->close_time_txt = date('H:i',$endtime);
            unset($schedule->film);
        }
        $result['film_time'] = $duration;
        $result['list'] = $schedulesList;
     
        return $this->success('',$result);
    }
    /**
     * 影片信息
     *
     * @param Request $request 
     * @return void
     */
    public function filmInfo(Request $request){
        $film_id = $request->input('film_id','');
        
        if(empty($film_id)){
            return $this->success('',[]);
        }
        $info = Api\Film::where('id',$film_id)->first();
        return $this->success('',$info);
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
        $list = array();
        
        $cinema = Api\Cinema::where('id',$cinema_id)->select('id','city_code as city_id','latitude','longitude','cinema_name','address')->first();
        if(empty($cinema)){
            return $this->error('影院信息不存在');
        }
        if(!$cinema->is_sync_hall){
            try {
                \App\Jobs\Wangpiao\HallsJob::dispatch($cinema);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        $filmIds = array();
        $schedulesList = Api\Schedules::searchFilm($cinema_id,$show_time)->orderBy('show_time','asc')->get();
     
     
        if($schedulesList->isEmpty()){
            $apiResult = (array) WpApi::getFilmShowByDate($cinema_id,date('Y-m-d 00:00:00'));            
            $schedulesList = Api\Schedules::syncData($apiResult);            
        }
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
                    ->where('city_code',$cinema->city_id)
                        // ->orderBy('remark','desc')
                        ->orderByRaw('FIELD(id,'.$sortFilmIds.')')
                        ->get($field);
        }
        
        return $this->success('',compact('cinema','list'));
    }
    
    /**
     * 根据电影、日期、城市、区域、品牌、查询影院
     *
     * @return void
     */
    public function getCinemaWithFilm(Request $request){                  
        $film_id = $request->input('film_id','');
        $date = $this->showTime;
        $date = date('Y-m-d',strtotime($date));
        $brand_id = $request->input('brand_id',''); //品牌
        $distId = $request->input('dist_id',''); //区域
        $subway = $request->input('subway',''); //地铁
        $trade_area = $request->input('trade_area',''); //商圈
        
        $show_time = strtotime($date);
        $list = array();
        if(empty($film_id)){
            return $this->success('请选择影片',$list);
        }
        $list = Api\ScheduleCinema::film($film_id)
                    ->search($this->cityId,$distId,$brand_id,$subway,$trade_area)
                    ->showTime($show_time)
                    ->get();
        if($list->isEmpty()){
            $apiResult = (array) WpApi::getCinemaQueryList($this->cityId,date('Y-m-d H:i:s',$show_time),$film_id);
            $list = Api\ScheduleCinema::syncData($apiResult,$film_id,$show_time);
        }
         foreach($list as $item){
            $item->id = $item->cinema_id;
        }
        return $this->success('',$list);
    }
    
    /**
     * 热映、即将上映的电影
     *
     * @param Request $req
     * @return void
     */
    public function currentFilmList(Request $req){
        $type = (int)$req->input('type',1);//1即将上映 2热映
        $limit = (int) $req->input('limit',100); //0全部   0指定数量
        $cityId = $this->cityId;
        if($type == 1){
            $list = Api\Film::where('date_type',1)
                        ->where('city_code',0)
                        ->take($limit)
                        ->get();
            if($list->isEmpty()){
                $apiResult = (array)WpApi::getPlanFilm(); //即将上映            
                $list = Api\Film::syncData($apiResult,1);
            }
            return $this->success('',$list);
        }
        if(empty($cityId)){
            return $this->success('请选择您所在的城市',array());
        }        
        $list = Api\Film::where('date_type',$type)
                        ->where('city_code',$cityId)
                        ->take($limit)
                        ->orderBy('remark','desc')
                        ->get();
        if($list->isEmpty()){
            $list = array();
            $apiResult = (array)WpApi::getCurrentFilm($cityId);
            $list = Api\Film::syncData($apiResult,2,$cityId);
            $list = array_splice($list,0,$limit);            
            $remarkArr = array_column($list,'remark');
            array_multisort($remarkArr,SORT_DESC,$list);
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
            $apiResult = (array)WpApi::getCinemaLineList();
            $list = Api\CinemasBrand::syncData($apiResult);
        }
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
                $field = [
                    'id as city_id',
                    'name as city_name',
                    'cid as parent_city_code',
                    'ccode as city_code'
                ];
                $list = Api\District::select($field)->city($cityId)->get();
                break;
            case 'subway':
                $list = Api\SubWay::city($cityId)->get();
                break;
            case 'tradearea':
                $list = Api\TradingArea::city($cityId)->get();
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
        $categoryId = $request->input('category_id',0);
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
    
    public function apiRoute(Request $request,$route = ''){
        $list = array();
        $params = $request->all();
        if(!empty($route)){
            $list = WpApi::__callStatic($route,$params);
        }
        return response()->json($list);
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
