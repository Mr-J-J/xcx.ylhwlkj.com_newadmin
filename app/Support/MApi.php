<?php
namespace App\Support;

use GuzzleHttp\Client;
use Overtrue\Pinyin\Pinyin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;

/**
 * 聚福宝Api
 */
class MApi {


    // const BASEURI = 'http://sandbox-c.jufubao.cn';
    // const ACCOUNT_ID = 3;
    // const SECRET = 'qwcf123456'; // 553323e710d7090371d08988375aba0b
    // const DES_SECRET = '123456789';

    const BASEURI = 'https://c.jufubao.cn';
    const ACCOUNT_ID = 187118;
    const SECRET = 'oUTlHsu8yGg0EiBzrfk7Wndxb5jYt6aP'; // 553323e710d7090371d08988375aba0b
    const DES_SECRET = 'yh7TDoearctjpxiK5RMmlB9q';


//     卡号：681001000000007
// 密码：924843973087    （100元）


    const TOKEN_URI = '/oauth/access-token'; //获得token
    const CITY_LIST_URI = '/api/film/city/list'; //城市列表
    const HOT_FILM_LIST = '/api/film/hot/list'; //热映影片
    const RIGHTNOW_FILM_LIST = '/api/film/rightnow/list';//即将上映
    const CINEMAS_LIST = '/api/film/cinema/list';//影院列表
    // const FILM_LIST = '/api/film/list' ; //影院下的电影
    const FILM_PAIQI_LIST = '/api/film/paiqi/list'; //排期list
    const FILM_SEAT_LIST = '/api/film/seat/list';//座位图


    static function verifyNotifySign($request_data){
        ksort($request_data);
        $sign_str = self::ACCOUNT_ID;
        foreach ($request_data as $k => $v) {
            $sign_str .= $k . $v;
        }
        $sign_str .= self::SECRET;
        $signature                 = hash('sha256', $sign_str);
        return base64_encode($signature);
    }
    /**
     * 支付完成出票
     *
     * @param [type] $order_id
     * @param [type] $channel_order_id
     * @return void
     */
    static function payOrder($order_id,$channel_order_id){
        $uri = self::makeUri('/api/film/notify');
        $data = array(
            'token'=> self::getToken(),
            'order_id'=>$order_id,
            'channel_order_id'=>$channel_order_id
        );
        $result = self::post($uri,$data);
        return $result;
    }

    static function decrypt_code($encrypt_str)
    {
        $key = str_pad(self::DES_SECRET, 24, '0');
        $data    = base64_decode($encrypt_str);
        $decData = openssl_decrypt($data,'des-ede3',$key,OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        $text = $decData;
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) {
            return false;
        };
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        };
        $decData = substr($text, 0, -1 * $pad);
        $result = explode('|',$decData);
        $ticketcode = '';
        $validcode= '';
        if(!empty($result[0])){
            $ticketcode = substr($result[0],strpos($result[0],':')+1);
        }
        if(!empty($result[1])){
            $validcode = substr($result[1],strpos($result[1],':')+1);
        }
        return compact('ticketcode','validcode');
    }

    /**
     * 订单详情
     *
     * @param [type] $order_id
     * @return void
     */
    static function getOrder($order_id)
    {
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
        $uri = self::makeUri('/api/film/ticket/get');
        $query = array(
            'token'=> self::getToken(),
            'order_id'=>$order_id,
        );
        $result = self::get($uri,$query);
        return $result;
    }


    /**
     * 刷新锁座
     *
     * @param [type] $order_id
     * @return void
     */
    static function refreshSeat($order_id){
        $uri = self::makeUri('/api/film/seat/refresh');
        $data = array(
            'token'=> self::getToken(),
            'order_id'=>$order_id,
        );
        $result = self::post($uri,$data);
        return $result;
    }

    /**
     * 释放座位
     *
     * @param [type] $order_id
     * @return void
     */
    static function unLockSeat($order_id){
        $uri = self::makeUri('/api/film/seat/unlock');
        $data = array(
            'token'=> self::getToken(),
            'order_id'=>$order_id,
        );
        $result = self::post($uri,$data);
        return $result;
    }

    /**
     * 锁座
     *
     * @param [type] $account_id 第三方用户id（必填
     * @param string $seat_names 座位名称 多个英文逗号分割
     * @param [type] $paiqi_id 排期id
     * @param [type] $seat_ids 座位id 多个英文逗号分割
     * @param [type] $phone_num 电话号码（必填）
     * @param [type] $seat_areas 座位对应的分区 （分区场次必填） 多个英文逗号分割
     * @return void
     */
    static function seatLock($account_id,$seat_names = '',$paiqi_id,$seat_ids,$phone_num,$seat_areas){
        $uri = self::makeUri('/api/film/seat/lock');
        $data = array(
            'token'=> self::getToken(),
            'account_id'=>intval($account_id),
            'seat_names'=>$seat_names,
            'paiqi_id'=>$paiqi_id,
            'seat_ids'=>$seat_ids,
            'phone_num'=>$phone_num,
            'seat_areas'=>$seat_areas
        );
        logger('锁座:'.json_encode($data,256));
        $result = self::post($uri,$data);
        logger('锁座:'.json_encode($result,256));
        return $result;
    }

    /**
     * 获取一个场次的座位图
     *
     * @param [type] $paiqi_id
     * @return void
     */
    static function seatList($paiqi_id){
            $uri = self::makeUri(self::FILM_SEAT_LIST);
            $query = array(
                'token'=> self::getToken(),
                'paiqi_id'=>$paiqi_id,
            );
            $result = self::get($uri,$query);

            return $result;
    }
    /**
     * 排期信息
     *
     * @param [type] $cinema_id 必填
     * @param string $film_id  影片id 选填
     * @param [type] $last_key
     * @return void
     */
    static function filmPaiqiList($cinema_id,$film_id='',$last_key=''){
        $uri = self::makeUri(self::FILM_PAIQI_LIST);
        $query = array(
            'token'=> self::getToken(),
            'cinema_id'=>intval($cinema_id),
            'last_key'=>$last_key,
        );
        if($film_id != ''){
            $query['film_id'] = $film_id;
        }
        $result = self::get($uri,$query);
//        logger($query);
//        logger($result);
        return $result;
    }

    /**
     * 影院下的电影
     *
     * @param [type] $cinema_id
     * @return array
     */
    static function filmList($cinema_id){
        $uri = self::makeUri('/api/film/list');
        $query = array(
            'token'=> self::getToken(),
            'cinema_id'=>intval($cinema_id),
        );
        $result = self::get($uri,$query);
        return $result;
    }
    /**
     * 影院列表
     *
     * @param [type] $city_code
     * @return void
     */
    static function cinemaList($city_code,$last_key='',$page_size = 20,$region_code = ''){
        $uri = self::makeUri(self::CINEMAS_LIST);
        $query = array(
            'token'=> self::getToken(),
            'city_code'=>intval($city_code),
            'last_key'=>$last_key,
            'page_size'=>$page_size,
            'region_code'=>$region_code
        );
        // dd(http_build_query($query));
        $result = self::get($uri,$query);
        return $result;
    }


    /**
     * 正在上映的影片信息查询
     *
     * @return void
     */
    static function getCurrentFilm($city_code)
    {
        $uri = self::makeUri(self::HOT_FILM_LIST);
        $query = array(
            'token'=> self::getToken(),
            'city_code'=>intval($city_code)
        );
        $result = self::get($uri,$query);
        return $result;
    }


    /**
     * 即将上映的影片信息查询
     *
     * @return void
     */
    static function getPlanFilm($city_code)
    {
        $uri = self::makeUri(self::RIGHTNOW_FILM_LIST);
        $query = array(
            'token'=> self::getToken(),
            'city_code'=>intval($city_code)
        );
        $result = self::get($uri,$query);
        return $result;
    }

    /**
     * 取得城市列表
     *
     * @return void
     */
    static function getCityList(){
        $uri = self::makeUri(self::CITY_LIST_URI);
        $query = array(
            'token'=> self::getToken()
        );
        $result = self::get($uri,$query);
        return $result;
    }


    /**
     *  取token
     *
     * @param boolean $force 强制更新
     * @return void
     */
    static function getToken($force=false){
        $cache = Cache::get('mapi_token',false);
        if(!$cache || $force){
            return self::getApiToken();
        };
        return $cache;
    }

    /**
     *
     *
     * @return void
     */
    private static function getApiToken(){
        $request = new Client;
        $uri = self::makeUri(self::TOKEN_URI);
        $data =  array(
            'account_id'=>self::ACCOUNT_ID,
            'secret'=> md5(self::SECRET)
        );
        $result = self::post($uri,$data);
        Cache::put('mapi_token',$result['access_token'],3200);
        return $result['access_token'];
    }

    // /**
    //  * 缓存数据
    //  *
    //  * @param [type] $key
    //  * @param [type] $value
    //  * @param [type] $time
    //  * @return void
    //  */
    // private static function setDataCache($key,$value,$time=''){
    //     if($time){
    //        return Cache::put($key,$value,$time);
    //     }
    //     return Cache::put($key,$value);
    // }

    // private static function getCacheData($key){
    //     return Cache::get($key,false);
    // }

    /**
     * get请求
     *
     * @param [type] $uri
     * @param array $query
     * @return void
     */
    private static function get($uri,$query = []){
        $request = new Client;
        $result = [];

        try {
            $response = $request->get($uri,[
                'query'=>$query,
                'timeout' => 30,
                'connect_timeout' => 30,
                'read_timeout' => 30
            ]);

        } catch (\Throwable $e) {
            $message = $e->getMessage();
            Log::debug($message);
            return [];
            // throw $e;

        }
        $result = json_decode((string)$response->getBody(),true);
        if($result['code'] != 200){
            return [];
        }
        return $result['result'];
    }

    /**
     * post请求
     *
     * @param [type] $uri
     * @param array $data
     * @return void
     */
    private static function post($uri,$data=[]){
        $request = new Client;
        try {
            $response = $request->post($uri,[
                'form_params'=>$data,
                'timeout' => 30,
                'connect_timeout' => 30,
                'read_timeout' => 30
            ]);
            $result = json_decode((string)$response->getBody(),true);
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            Log::debug($message);
            return [];
        }

        if($result['code'] != 200){
            return [];
        }
        return $result['result'];
    }

    private static function makeUri($uri){

        return self::BASEURI . $uri;
    }
    private static function setToken($token){
        Cache::put('mapi_token',$token,3600);
    }
    /**
     * 签名
     *
     * @param [type] $params
     * @return void
     */
    static function makeSign($params){
        sort($params);
        $originalStr = http_build_query($params) . self::SECRET;
        return md5($originalStr);
    }






}
