<?php
namespace App\Support;
use GuzzleHttp\Client;
use Overtrue\Pinyin\Pinyin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class WpApi
{

    // const BASEURI = 'http://test.api.wangpiao.com';
    // const USER = 'test1';
    // const SECRET = '4vYwB5csWrdLbFkG';
    
    const BASEURI = 'http://channel.api.wangpiao.com:88/2.0/Default.aspx';
    const USER = 'WP_YLHWPWAPI';
    const SECRET = 'NLzu8nX5eNXAaEkY';
    
    const DELAY_TIME = 20;//20分钟

    public static function __callStatic($method, $arguments){
        try {
            $result =  call_user_func_array(array('self', $method), $arguments);
        } catch (\Throwable $th) {
           $result =  call_user_func(array('self', $method));
        }
        return $result;
    }

    /**
     * 申请停止写票
     * 3.7Sell_StopBuyTicket
     * 因网络等原因，造成在购票后过了一段时间但订单仍没成功出票，可以申请停止写票。
     * 该功能用于特殊情况，在下单时间超过设定时间（如15分钟）后才可以调用该接口，否则会返回受理请求失败。
     *  关于该接口的调用说明：
     *  1.在申请购票且超过一段较长的时间（如15分钟）后，仍未出票成功，可以调用该接口请求终止写票操作。
     *   接口返回受理成功后，请稍候（过60秒）再调用“订单查询”接口确认最终的订单状态。
     * @return void
     */
    static function stopBuyTicket(string $sid){
        $data = array(
            'SID'=>$sid
        );
        $result = self::commonRequest2('Sell_StopBuyTicket',$data);
        if($result['ErrNo'] == 0 && !empty($result['Data'])){
            return array('status'=>true,'data'=>$result['Data'][0]['Result'],'msg'=>$result['Msg']);
        }
        return array('status'=>false,'data'=>'','msg'=>$result['Msg']);
    }

    /**
     * 重发验票码
     * 重新发送验票码，同一订单最多只能发送三次。
     * Sell_ReSendMsg
     *
     * @return void
     */
    static function reSendMsg(string $sid){
        $data = array(
            'SID'=>$sid
        );

        $result = self::commonRequest2('Sell_ReSendMsg',$data);        
        if($result['ErrNo'] == 0 && !empty($result['Data'])){
            return array('status'=>true,'data'=>$result['Data'][0]['Result'],'msg'=>$result['Msg']);
        }
        return array('status'=>false,'data'=>'','msg'=>$result['Msg']);
    }


    /**
     * 订单查询 
     * 根据订单号查询订单信息。
     * Sell_SearchOrderInfoBySID
     * @return void
     */
    static function searchOrderInfoBySID(string $sid){
        $data = array(
            'SID'=>$sid
        );
        $result = self::commonRequestV2('Sell_SearchOrderInfoBySID',$data);
        $error='';
        if($result['ErrNo'] == 0 && !empty($result['Data'])){            
            return array('status'=>true,'data'=>$result['Data'][0],'msg'=>'');
        }
        logger('订单查询：'.json_encode($data,256).' -- 响应信息'.json_encode($result,256).' - 异常信息' .$error );
        return array('status'=>false,'data'=>'','msg'=>$result['Msg']);
    }

    /**
     * 申请购票
     * 在完成下单申请的前提下，申请购票。此方法有签名认证，更加安全。
     * Sell_BuyTicketSafe
     * @return void
     */
    static function buyTicket(string $sid,string $pay_no,string $plat_form_no){
        $data = array(
            'SID' => $sid,  //订单号
            'PayNo'=>$pay_no,  //该单对应的支付信息标识（业务系统支付记录标识）
            'PlatformPayNo'=>$plat_form_no, //支付平台支付成功后的支付标识（支付平台对账标识）
        );
        $result = self::commonRequestV2('Sell_BuyTicketSafe',$data);
        $error='';
        if($result['ErrNo'] == 0 && !empty($result['Data'])){
            return array('status'=>true,'data'=>$result['Data'][0]['Result'],'msg'=>'');
            // 结果，true/false。注意，该值表示系统收到购票请求后确认的影院真实出票状态。true表示影院系统已出票，false表示因网络等原因暂未出票成功，
        // 但系统仍会继续尝试出票，该状态不可做为给用户退款的依据
        }
        logger('申请购票失败：'.json_encode($data,256).' -- 响应信息'.json_encode($result,256).' - 异常信息' .$error );
        return array('status'=>false,'data'=>'','msg'=>$result['Msg']);
        
    }
    
    /**
     * 申请下单 
     * Undocumented function
     * Sell_ApplyTicketSafe
     * 申请成立一个订单，并返回该单对应的支信信息标识。此方法有签名认证，更加安全。
     * return [PayNo 该单对应的支付信息标,SID 成功后返回订单号,否则返回空]
     * //0 => array:2 [ "SID" => "0043349495" "PayNo" => "354b4c39-f235-4e85-b7c1-0d7d9295f499" ]
     * @return void
     */
    static function applyTicket(string $sid,$mobile,$amount=0,$useramount = 0){
        $mobile = self::ssl_encrypt($mobile);
        $data = array(
            'SID' => $sid,  //订单号
            'PayType'=>'9990',
            'Bank'=>'9998',
            'AID'=>'0',
            'Mobile'=> $mobile,
            'MsgType'=>1, //验票码发送方式。1需要系统发送验票码；2不需要系统发送验票码
            'Amount'=> $amount , //结算金额
            'UserAmount'=> $useramount, //UserAmount 用户支付的订单金额（影票票面价格，确保大于0，否则会读取Amount的值） 
            'GoodsType'=>1,
        );    
        $result = self::commonRequestV2('Sell_ApplyTicketSafe',$data);

        $error='';
        if($result['ErrNo'] == 0 && !empty($result['Data'])){
            return array('status'=>true,'data'=>$result['Data'],'msg'=>'');
        }
        logger('申请下单失败：'.json_encode($data,256).' -- 响应信息'.json_encode($result,256).' - 异常信息' .$error );
        return array('status'=>false,'data'=>'','msg'=>$result['Msg']);
    }

    /**
     * 座位释放
     *
     * @param string $sid
     * @return void
     */
    static function unLockSeat(string $sid){
        $data = array(
            'SID' => $sid,
        );
        return self::commonRequest('Sell_UnLockSeat',$data);
    }
    /**
     * 锁座
     *
     * @param [type] $params
     * @return void
     */
    static function lockSeat($params){
        $data = array(
            'UserID' => $params['user_id'],
            'ShowIndex' => $params['paiqi_id'],
            'CinemaID' => $params['cinema_id'],
            'SeatInfo' => $params['seat_ids'],
        );
        
        if(!empty($params['mobile'])){
            $data['Mobile'] = $params['mobile'];
        }
        if(!empty($params['contacts'])){
            $data['Contacts'] = $params['contacts'];
        }

        // $param = self::getSignParam('Sell_LockSeatSafe',$data);
        $param = self::getSignParam('Sell_LockSeat_V2',$data);
        $param = array_merge($param,$data);
        $response = self::request_post($param);
        // self::logger('锁座!; request params :'.json_encode($params,256).',result:'.json_encode($response,256));
        return $response;
        // return self::commonRequest('Sell_LockSeatSafe',$data);
    }
    
    /**
     * 用于验证某个影院的某个场次是否有效，规避因影讯改场且同步更新不及时导致的不合理锁座请求
     * 注：需要影院编号、放映流水号、放映日期同时满足。* 
     *
     * @param [type] $cinema_id
     * @param * $date
     * @param [type] $showIndex
     * @return void
     */
    static function FilmShowCheck($cinema_id,$date,$showIndex){
        $data = array(
            'CinemaID' => $cinema_id,
            'Date' => $date,
            'ShowIndex' => $showIndex,
        );
        return self::commonRequest('Base_FilmShowCheck',$data);
    }

    /**
     * 影片信息查询接口，用于查询影片信息。
     *
     * @param [type] $film_id
     * @return void
     */
    static function getFilmInfo($film_id){
        $data = array(
            'FilmID' => $film_id,
        );
        return self::commonRequest('Base_FilmHE',$data);
    }
    
    /**
     * 查询影院指定日期的放映计划
     *
     * @param [type] $cinema_id
     * @param [type] $date
     * @param string $film_id
     * @return void
     */
    static function getFilmShowByDate($cinema_id,$date,$film_id = ''){
        $data = array(
            'CinemaID' => $cinema_id,
            'Date'     => $date,
        );
        
        if($film_id != ''){
            $data['FilmID'] = $film_id;
        }
        return self::commonRequest('Base_FilmShow',$data);
    }

    /**
     * 查询影院指定三天以上的日期的放映计划，不填日期默认为所有三天以上的场次
     *
     * @param [type] $cinema_id
     * @param [type] $date yyyy-MM-dd HH:mm:ss
     * @param string $film_id
     * @return void
     */
    static function getPreFilmShow($cinema_id,$date,$film_id = ''){
        $data = array(
            'CinemaID' => $cinema_id,
            'Date'     => $date,
        );
        
        if($film_id != ''){
            $data['FilmID'] = $film_id;
        }
        return self::commonRequest('Base_PreFilmShow',$data);
    }


    

    /**
     * 座位信息查询接口，用于查询影厅的座位信息。
     *
     * 返回示例 0 => array:2 [
        *       "SeatID" => "768"
        *      "Status" => "N"
        *   ]
     * @param [type] $showIndex
     * @param [type] $cinema_id
     * @return void
     */
    static function getSellSeatInfo($showIndex,$cinema_id){
        $data = array(
            'CinemaID' => $cinema_id,
            'ShowIndex'     => $showIndex,
        );
        
        return self::commonRequest('Base_SellSeat_V2',$data);//Base_SellSeat
    }

    /**
     * 座位信息查询接口，用于【按影厅编号】查询影厅的座位信息。
     * 0 => array:8 [
      *      "SeatIndex" => 78077696   座位唯一标识
      *      "SeatID" => "778"      座位编码
      *      "Name" => "1:1"        座位名称,如 1:2，表示 1 排 2 座
      *      "RowID" => 29    行坐标
      *      "ColumnID" => 548   列坐标
      *      "LoveFlag" => 0   情侣座标识 0：普通座位 1：情侣座首座位标记 2：情侣座第二座位标记
      *      "Status" => "N"  座位状态 Y 表示座位完好,可以售票;N 表示座位损坏,不允许售票
      *      "SectionID" => "1"   
      *  ]     
     * @param [type] $hallId
     * @param [type] $cinema_id
     * @return void
     */
    static function getSeatByHallId($hallId,$cinema_id){
        $data = array(
            'HallID' => $hallId,
            'CinemaID'  => $cinema_id
        );

        return self::commonRequest('Base_HallSeat',$data);
    }
    /**
     * 座位信息查询接口，用于【按场次号】查询影厅的座位信息。
     *
     * @param [type] $showIndex
     * @param [type] $cinema_id
     * @return void
     */
    static function getSeatByShowIndex($showIndex,$cinema_id){
        $data = array(
            'ShowIndex' => $showIndex,
            'CinemaID'  => $cinema_id
        );

        return self::commonRequest('Base_Seat',$data);
    }

    /**
     * 全国影讯查询 — Base_FilmView
     *
     * @param [type] $city_id
     * @param string $cinema_id
     * @return void
     */
    static function getFilmViewList($city_id,$cinema_id = ''){
        
        $data = array(
            'CityID' => $city_id
        );
        
        if($cinema_id != ''){
            $data['CinemaID'] = $cinema_id;
        }
        return self::commonRequest('Base_FilmView',$data);
    }
    
    /**
     * 即将上映的影片信息查询
     *
     * @param string $date  :yyyy-MM-dd
     * @param string $cinema_id
     * @return void
     */
    static function getPlanFilm($date = '',$cinema_id = ''){
        $data = array();
        if($date != ''){
            $data['Date'] = $date ;
        }
        if($cinema_id != ''){
            $data['CinemaID'] = $cinema_id;
        }
        return self::commonRequest('Base_FilmIM',$data);
    }

    /**
     * 正在上映的影片信息查询
     *
     * @param [type] $city_id
     * @param string $date 日期时间转换后的 字 符 型 格 式 :yyyy-MM-dd HH:mm:ss
     * @param string $film_id
     * @return void
     */
    static function getCurrentFilm($city_id,$date = '',$cinema_id = ''){
        $data = array(
            'CityID'=>$city_id,
        );
        if($date != ''){
            $data['Date'] = $date ;
        }
        if($cinema_id != ''){
            $data['CinemaID'] = $cinema_id;
        }
        
        return self::commonRequest('Base_Film',$data);
    }

    /**
     * 影厅信息查询接口，用于查询影厅信息。
     *
     * @return void
     */
    static function getCinemaHall($cinema_id){
        $data = array(
            'CinemaID'=>$cinema_id
        );
        return self::commonRequest('Base_Hall',$data);
    }

    /**
     * 根据城市,(影片),日期查询有放映计划的影院
     *
     * @param [type] $city_id
     * @param [type] $date  日期时间转换后的字符型 格 式 :yyyy-MM-ddHH:mm:ss
     * @param [type] $film_id
     * @return void
     */
    static function getCinemaQueryList($city_id,$date,$film_id = ''){
        $data = array(
            'CityID'=>$city_id,
            'Date' =>$date,
        );
        if($film_id != ''){
            $data['FilmID'] = $film_id;
        }
        return self::commonRequest('Base_CinemaQuery',$data);
    }
    /**
     * 影院查询接口，用于查询影院信息
     *
     * @param [type] $city_code
     * @return void
     */
    static function getCinemaList(){
        return self::commonRequest('Base_Cinema');
    }

    /**
     * 用于查询院线。
     *
     * @return void
     */
    static function getCinemaLineList(){
        return self::commonRequest('Base_CinemaLine');
    }
 

    /**
     * 同步地铁
     *
     * @return void
     */
    static function getSubWay(){
        return self::commonRequest('Base_SubWay');
    }
    /**
     * 同步商圈
     *
     * @return void
     */
    static function getTradingArea(){
        return self::commonRequest('Base_TradingArea');
    }

    /**
     * 城市区域查询
     *
     * @return void
     */
    static function getCityDistrict(){
        return self::commonRequest('Base_District');
    }
    /**
     * 获取已有业务城市查询
     *
     * @return void
     */
    static function getCityBillList(){
        return self::commonRequest('Base_CityBll');        
    }    
    /**
     * 取得城市列表
     *
     * @return void
     */
    static function getCityList(){
        return self::commonRequest('Base_City');
    }

    /**
     * 公共请求
     *
     * @param [type] $target
     * @return void
     */
    static function commonRequest($target,$data = []){
        $param = self::getSignParam($target,$data);
        if(!empty($data)){
            $param = array_merge($param,$data);
        }
        if(!empty($param['Date'])){
            if(strtotime(date('Y-m-d'))> strtotime($param['Date'])){
                return false;
            }
        }
        $response = self::request_post($param);
        if($response === false){
            return array();
        }
        // logger(json_encode($param).'---'.json_encode($response));
        try {
            if($response['ErrNo'] == 11){
                // self::logger('获取数据失败!; request params :'.json_encode($param,256).',result:'.json_encode($response,256));
                $response = retry(2,function()use ($param){
                        $logger = '再次尝试请求数据:';
                        $return = self::request_post($param);
                        if(empty($return['Data'])){
                            // self::logger($logger.'请求失败');
                            // throw new \Exception('别试了..');
                            
                        }
                        // self::logger($logger.'请求成功');
                        
                        return $return;
                    },220);
            }
        } catch (\Exception $e) {
            self::logger('获取数据失败:'.$e->getMessage());
            return array();
        }
        $result = $response;
        $result['Data'] = [];
        // self::logger(json_encode(compact('param','target','result')).' 获取'.count($response['Data']).'条数据;');
        return $response['Data'];
    }


    /**
     * 购票流程专用
     */
    static function commonRequestV2($target,$data = []){
        $param = self::getSignParam($target,$data);
        if(!empty($data)){
            $param = array_merge($param,$data);
        }
        if(!empty($param['Date'])){
            if(strtotime(date('Y-m-d'))> strtotime($param['Date'])){
                return false;
            }
        }
        $request = new Client;                
        $response = $request->post(self::BASEURI,[
            'form_params'=>$param,
            'timeout' => 10,
            'connect_timeout' => 10,
            'read_timeout' => 10
        ]);
        
        $result = json_decode((string)$response->getBody(),true);        
        return $result;        
    }
    
     

    /**
     * post请求
     *
     * @param [type] $uri
     * @param array $data
     * @return void
     */
    private static function request_post($data=[]){
        $request = new Client;                
        $response = $request->post(self::BASEURI,[
            'form_params'=>$data,
            'timeout' => 50,
            'connect_timeout' => 20,
            'read_timeout' =>50
        ]);
        
        $result = json_decode((string)$response->getBody(),true);
        if($result['ErrNo'] != 0){
            return false;
        }
        if(empty($result['Data'])){
            
            return ['ErrNo'=>11,'Data'=>[]];
        }
        return $result;
    }


    /**
     * 签名
     *
     * @param string $target
     * @param array $param
     * @return void
     */
    private static function getSignParam($target = '',$param = []){        
        $query['Target'] = $target;
        $query['UserName'] = self::USER;
        $Sign = md5($target.self::USER.self::SECRET);      
        
        if(!empty($param)){
            $param = array_merge($param,$query);
            ksort($param);
            $values = array_values($param);
            $Sign = md5(join('',$values).self::SECRET);            
        }
        $query['Sign'] = $Sign;
        
        return $query;
    }
    
    /**
     * aes加密 
     *
     * @param [type] $str
     * @return void
     */
    public static function ssl_encrypt($str){
        $iv = md5(time(). uniqid(),true);
        return @openssl_encrypt($str, 'AES-128-CBC', self::SECRET);
    }
    
    public static function logger($logtxt){
        try {
            $path = storage_path('logs/request-'.date('Y-m-d').'.log');
        $log = new Logger('apirequest');
        $log->pushHandler(new StreamHandler($path, Logger::INFO));
        $log->info(json_encode(request()->ip().$logtxt,JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            @unlink(storage_path('logs/request-'.date('Y-m-d').'.log'));
        }
    }


}