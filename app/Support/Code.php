<?php


namespace App\Support;


use App\Models\ApiErrorLog;
use http\Env\Request;
use Illuminate\Support\Facades\Log;

class Code
{
    /**
     * 正常时,返回码
     */
    const SUCC = 200;

    /**
     * 100 致命错误
     */
    const REQ_ERROR = 101; // token验证失败

    /**
     * http 相关错误
     */
    const ERR_HTTP_UNAUTHORIZED = 401; // 未登录
    

    public static $msgs = [];

    /**
     * 提示代码
     * @var | int
     */
    protected static $code;

    /**
     * 提示信息
     * @var | string
     */
    protected static $msg;

    /**
     * 详情信息
     * @var
     */
    protected static $detail;

    /**
     * 需要返回的数据
     * @var
     */
    protected static $data;
    protected static $rquest_params;

    /**
     * 设置提示信息
     *
     * @param $code 提示代码
     * @param null $msg 提示信息
     * @param array $params 提示信息中动态参数
     */
    public static function setCode($code, $msg = null, $data = [], $params = [], $rquest_params = [])
    {
        $code = (int)$code;
        if (null == $msg || '' == $msg) {
            if (isset($msgs[$code])) {
                if (!empty($params)) {
                    //array_unshift($params, $msgs[$code]);
                   // self::$msg = call_user_func_array('sprintf', $params);
                } else {
                    //self::$msg = self::$msgs[$code];
                }
            } else {
                //self::$msg = '提示信息未定义';
            }
        } else {
            //self::$msg = $msg;
        }

        if (self::SUCC !== $code) {
            // save log
        }
        //self::$data = $data;
       // self::$rquest_params = $rquest_params;
        //self::addLog();
        if (empty($data)) {
            return ['code' => $code, 'message' => $msg, 'data' => []];
        } else {
            return ['code' => $code, 'message' => $msg, 'data' => $data];

        }

    }

    public static function addLog()
    {
        $log = new ApiErrorLog();
        $log->code = self::$code;
        $log->message = self::$msg;
        $log->data = self::$data;
        $log->param = self::$rquest_params;
        if (!$log->save()) {
            Log::error('请求日志写入失败');
        }
    }

}
