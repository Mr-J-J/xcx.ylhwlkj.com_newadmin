<?php
namespace App\Support;

use GuzzleHttp\Client;
use Overtrue\Pinyin\Pinyin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;

/**
 * 影福客api
 */
class NApi {

    const BASEURI = 'https://dyp.ylhwlkj.com/api/openapi/';
    const openapiid = 1014;
    const pwd = '	ylhwlkj';

    /*
    *  getCinema 获取电影院信息
    *  @params id clientId
    */
    public static function getcinema(){
        return self::post(self::makeUri('getcinema'));
    }
    /*
     *   getFilmList 获取影片信息
     *  @params state 1：热映 2：待映 0：全部
     */
    public static function getfilmlist($status=0){
        return self::post(self::makeUri('getfilmlist'),['status'=>$status]);
    }
    /*
     * getHall 获取影院对应的影厅信息
     * @params  cinemaNo String[11 字节] 影城编号，cinemaNo 与 cinemaCode 必传一个
     * @params  cinemaCode String[15 字节] 影城专资，cinemaNo 与 cinemaCode 必传一个
     * code mk99990007
     */
    public static function gethall($cinemaCode){
        return self::post(self::makeUri('gethall'),['cinemaCode'=>$cinemaCode]);
    }
    /*
     *  getPlan 获取影院当前所有排期
     *  clientId String[15 字节] 应用编码
     *  cinemaNo String[10 字节] 影城编号，cinemaNo 与 cinemaCode 必传一个
     *  cinemaCode String[15 字节] 影城专资，cinemaNo 与 cinemaCode 必传一个
     *  canSitArea Int 是否支持座区(0 否 1 是,非必填，默认 0)
     */
    public static function getplan($cinemaCode=0,$canSitArea=0){
        return self::post(self::makeUri('getplan'),['cinemaCode'=>$cinemaCode,'canSitArea'=>$canSitArea]);
    }
    /*
     * getPlanSeat 获取排期的座位状态
     * clientId String[11 字节] 应用编码
     * cinemaNo String[11 字节] 影城编号，cinemaNo 与 cinemaCode 必传一个
     * cinemaCode String[15 字节] 影城专资，cinemaNo 与 cinemaCode 必传一个
     * planKey String[32 字节] 排期编号
     * canSitArea Int 是否支持座区(0 否 1 是,默认 0)
     * t String[10 字节] 格式(时间戳，单位到秒)
     * sign 签名串
     */
    public static function getplanseat($cinemaCode,$canSitArea,$planKey){
        return self::post(self::makeUri('getplanseat'),['cinemaCode'=>$cinemaCode,'planKey'=>$planKey,'canSitArea'=>$canSitArea]);
    }
    /*
     *  lockSeat 检查需要订票的座位状态情况，并订票锁定实时座位
     *   1 clientId String[11 字节] 应用编码
     *   2 cinemaNo String[11 字节] 影城编号，cinemaNo 与 cinemaCode 必传一个
     *   3 cinemaCode String[15 字节] 影城专资，cinemaNo 与 cinemaCode必传一个
     *   4 planKey String[32 字节] 排期编号
     *   5 serialNum String[32 字节] 接入方订单号
     *   6 ticketList JSON 数组 需要锁座的座位数组
     *   ticketList=>[   seatNo String[10 字节] 座位编号
     *      buyPrice NUMBER(10,2) 结算价]
     *   7 mobile String[11 字节] 用户下单手机号
     *   8 sellType Int 售票方式 1 快速出票 2 特惠出票 默认 1
     *   9 isAdjust Int 是否愿意换座 0 否 1 是 默认 0,仅特惠出票时有意义
     *   10 t String[10 字节] 格式(时间戳，单位到秒)
     *   11 sign 签名串
     */
    public static function lockseat($cinemaCode,$planKey,$serialNum,$ticketList,$mobile,$sellType,$isAdjust=0){
        return self::post(self::makeUri('lockseat'),['cinemaCode'=>$cinemaCode,'planKey'=>$planKey,'serialNum'=>$serialNum,'ticketList'=>$ticketList,'mobile'=>$mobile,'sellType'=>$sellType,'isAdjust'=>$isAdjust]);
    }
    /*
     *  unLockOrder 实时解锁座位
     *   1 clientId String[32 字节] 应用编码
     *   2 cinemaNo String[11 字节] 影城编号，cinemaNo 与 cinemaCode 必传一个
     *   3 cinemaCode String[15 字节] 影城专资，cinemaNo 与 cinemaCode 必传一个
     *   4 orderNo String[32 字节] 锁座返回的系统订单编号
     *   5 t String[10 字节] 格式(时间戳，单位到秒)
     *   6 sign 签名串
     */
    public static function unlockorder($cinemaCode,$orderNo){
        return self::post(self::makeUri('unlockorder'),['cinemaCode'=>$cinemaCode,'orderNo'=>$orderNo]);
    }
    /*
     * getOrderStatus 查询订单状态
     *  注：快速出票，订单截止时间锁座后 15 分钟，特惠出票订单截止时间按照售票接口返回时间为准(如无
     *  sellEndTime 值，按调用售票接口后 60 分钟算)，特惠出票时间最多 1 小时，请刷新 1 小时时间
     *  1 clientId String[15 字节] 应用编码
     *  2 cinemaNo String[10 字节] 影城编号，cinemaNo 与 cinemaCode 必传一个
     *  3 cinemaCode String[15 字节] 影城专资，cinemaNo 与 cinemaCode 必传一个
     *  4 orderNo String[32 字节] 系统订单编号
     *  5 t String[10 字节] 格式(时间戳，单位到秒)
     *  6 sign 签名串
     */
    public static function getorderstatus($cinemaCode,$orderNo){
        return self::post(self::makeUri('getorderstatus'),['cinemaCode'=>$cinemaCode,'orderNo'=>$orderNo]);
    }
    /*
     *  sellTicket 卖常规票(带座位票)
     *  1 clientId String[11 字节] 应用编码
     *  2 cinemaNo String[11 字节] 影城编号，cinemaNo 与 cinemaCode 必传一个
     *  3 cinemaCode String[15 字节] 影城专资，cinemaNo 与 cinemaCode 必传一个
     *  4 orderNo String[32 字节] 锁座返回的系统订单编号
     *  5 payAmount NUMBER(10,2) 订单总额(票价+服务费)
     *  6 planKey String[32 字节] 排期编码
     *  7 t String[10 字节] 格式(时间戳，单位到秒)
     *  8 sign 签名串
     */
    public static function sellticket($cinemaCode,$orderNo,$payAmount,$planKey){
        return self::post(self::makeUri('sellticke'),['cinemaCode'=>$cinemaCode,'orderNo'=>$orderNo,'payAmount'=>$payAmount,'planKey'=>$planKey,'openapiid'=>self::openapiid,'pwd'=>self::pwd]);
    }
    /**
     * get请求
     *
     * @param [type] $uri
     * @param array $query
     * @return void
     */
    public static function get($uri,$query = []){
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
        return $result;
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
        return $result;
    }

    private static function makeUri($uri){

        return self::BASEURI . $uri;
    }





}
