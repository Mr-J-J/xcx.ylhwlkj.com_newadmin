<?php

namespace App\Http\Controllers;

use App\Support\Code;
use App\Models\Cinema;
use App\Models\Setting;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
/**
 * 开放接口
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $setting = [];

    protected $city_code;
    
    public function __construct(){
        
        $this->setting = Setting::getSettings();
        
        // $this->city_code = request('city_code','110100');
    }

    

    /**
     * 影片搜索
     *
     * @param Request $req
     * @return void
     */
    public function searchFilm(Request $req){
        $keywords = $req->input('search','');
        $city_code = $this->city_code;
        $list = [];
        if(empty($keywords) || empty($city_code)){
            return $this->success('',$list);
        }

        $result = \App\Models\CurrentMovie::searchFilm($keywords,$city_code);
        return $this->success('',$result);
    }

    public function paiqiDetail(Request $req){
        $paiqi_id = $req->input('paiqi_id','');
        $info =  \App\Models\Schedule::where('id',$paiqi_id)->first();
        return $this->success('',$info);
    }
    
    /**
     * 电影排期
     */
    public function paiqiList(Request $req){
        $cinema_id = $req->input('cinema_id','');
        $film_id = $req->input('film_id','');
        $show_time = $req->input('show_time',date('Y-m-d H:i:s'));
        $show_time = strtotime($show_time);

        $end_time = strtotime(date('Y-m-d',$show_time).' 23:59:59');
        $film_time = 0;
        $list = [];
        if(!empty($cinema_id)){
            $map = array(
                'cinema_id'=>$cinema_id                
            );
            if(!empty($film_id)){
                $map['film_id'] = $film_id;
            }
            $filmInfo =  \App\Models\Schedule::where('film_id',$film_id)->first(['show_time','close_time']);
            if(!empty($filmInfo)){
                $film_time = intval(($filmInfo->close_time - $filmInfo->show_time) / 60);
            }
            // \Illuminate\Support\Facades\DB::enableQueryLog(); 
            $list = \App\Models\Schedule::where($map)->whereBetween('show_time',[$show_time,$end_time])->get();
            if(!empty($list[0])){
                $film_time = intval(($list[0]->close_time - $list[0]->show_time)) / 60;
            }
        }

        return $this->success('成功',compact('film_time','list'));
    }


    /**
     * 影院下的电影
     *
     * @param Request $req
     * @return void
     */
    public function filmList(Request $req){
        $cinema_id = $req->input('cinema_id','');
        $film_id = $req->input('film_id','');
        $list = [];
        if(!empty($cinema_id)){
            $cinema = \App\Models\Cinema::where('id',$cinema_id)->first();
            // $list = \App\Models\Movie::where('cinema_id',$cinema_id)->get();
            $field= array(
                'id',
                'show_name',
                'show_name_en',
                'remark',
                'type',
                'poster',
                'leading_role'
            );
            $list = \App\Models\Movie::select($field)->where('cinema_id',$cinema_id)->limit(10)->get();
            foreach($list as &$item){
                $item->current = false;
                if($film_id && $film_id == $item->id){
                    $item->current = true;
                }
            }
        }

        return $this->success('成功',compact('cinema','list'));
    }

    /**
     * 热映、即将上映的电影
     *
     * @param Request $req
     * @return void
     */
    public function currentFilmList(Request $req){
        $type = $req->input('type',1);//1即将上映 2热映
        $city_code = $this->city_code;
        $limit = $req->input('limit',100); //0全部  >0指定数量

        $list = \App\Models\CurrentMovie::city($city_code)->where('data_type',$type)->limit($limit)->get();
        
        return $this->success('',$list);
    }

    /**
     * 影片详情
     *
     * @param Request $req
     * @return void
     */
    public function filmInfo(Request $req){
        $film_id = $req->input('film_id','');
        
        if(empty($film_id)){
            return $this->success('',[]);
        }

        $info = \App\Models\Movie::where("id",$film_id)->first();
        return $this->success('',$info);
    }
    /**
     * 指定电影 日期的影院列表
     *
     * @return void
     */
    public function cinemaListByFilmId(Request $req){
        $city_code = $this->city_code;
        $brand_id = $req->input('brand_id','');
        $show_time = $req->input('show_time',date('Y-m-d'));
        $starttime = strtotime($show_time);
        $end_time = strtotime($show_time.' 23:59:59');
        $film_id = $req->input('film_id','');
        $map = array();
        if(empty($city_code)){
            return $this->success('',$map);
        }
        if(!empty($brand_id)){
            $map['brand_id'] = intval($brand_id);
        }
        $map['film_id']  = $film_id;
        $field = array(
            'cinema_id',
            'film_id',
            DB::raw('min(price) as price'),
        );        
        
        $list = \App\Models\Schedule::select($field)->whereIn('city_code',function($query) use($city_code){
            $query->from('regions')->select('city_code')->where('parent_city_code',$city_code)->orWhere('city_code',$city_code);
            })->where($map)
            ->whereBetween('show_time',[$starttime,$end_time])->groupBy('cinema_id','film_id')->get()->makeHidden(['show_date','show_time_txt','close_time_txt','local_price']);
        $result = array();
        foreach($list as $item){
            $res = $item->toArray();            
            $cinema = $item->cinema->toArray();
            $result[] = array_merge($res,$cinema);
            
        }
        
        return $this->success('',$result);
    }

     /**
     * 影院列表
     *
     * @param Request $req
     * @return void
     */
    public function cinemaList(Request $req){
        $city_code = $this->city_code;
        $brand_id = $req->input('brand_id','');
        $map = array();
        if(empty($city_code)){
            return $this->success('',$map);
        }        
        if(!empty($brand_id)){
            $map['brand_id'] = intval($brand_id);
        }

        $cinema_id = $req->input('cinema_id','');
        if(!empty($cinema_id)){
            $map = [];
            $map['id'] = $cinema_id;
        }
        $list = \App\Models\Cinema::whereIn('city_code',function($query) use($city_code){
            $query->from('regions')->select('city_code')->where('parent_city_code',$city_code)->orWhere('city_code',$city_code);
        })->where($map)->get();
        
        return $this->success('',$list);
    }
    /**
     * 影院品牌
     *
     * @return void
     */
    public function cinemaBrand(){
        $list = \App\Models\CinemasBrand::select(['id','brand_name'])->get()->makeHidden('levels');
        return $this->success('',$list);
    }

    public function areas(Request $req){
        $city_code = $req->input('city_code','110100');

        $list = [];
        // if(empty($city_code)){
        //     return $this->success('',$list);
        // }

        $list = \App\Models\Region::where('parent_city_code',$city_code)->orderBy('city_code','asc')->get();

        return $this->success('',$list);
    }
    /**
     * 二级城市列表
     *
     * @return void
     */
    public function cities(){
        $list = \App\Models\Region::where('city_level',2)->orderBy('pinyin')->orderBy('city_code','asc')->get();
        $result = [];
        foreach($list as $item){
            $result[$item['pinyin']][] = $item;
        }
        return $this->success('',$result);
    }

    public function regionList(Request $req){
        $parent_id = $req->input('q','');
        $list = [];

        if(empty($parent_id)){
            $list = \App\Models\Region::where('city_level',1)->orderBy('city_code')->get(['city_code','city_name']);
        }else{
            $list = \App\Models\Region::where('parent_city_code',$parent_id)->orderBy('city_code')->get(['city_code','city_name']);
        }
        return $this->success('',$list);
    }
    /**
     * 获取小程序、公众号
     *
     * @param integer $type 1小程序 2公众号 3支付
     * @return void
     */
    protected function getApp($type=1,$comId = 0){
        if($type == 1){
            $config = $comId ? config('wechat.mini_program.default1'):config('wechat.mini_program.default');
            return \EasyWeChat\Factory::miniProgram($config);   
        }elseif($type == 2){
            $config = config('wechat.official_account.default');
            return \EasyWeChat\Factory::officialAccount($config);        
        }elseif($type == 3){
            $config = $comId ? config('wechat.payment.default1'):config('wechat.payment.default');
            return \EasyWeChat\Factory::payment($config);
        }
    }

    public function agreement(Request $req){
        $id = $req->input('id',0);
        $content = \App\Models\Agreement::find($id);
        return $this->success('',$content);
    }

    protected function error($msg,$data = []){
        return Code::setCode(Code::REQ_ERROR,$msg,$data); 
    }

    protected function success($msg,$data=[]){
        return Code::setCode(Code::SUCC,$msg,$data); 
    }
}
