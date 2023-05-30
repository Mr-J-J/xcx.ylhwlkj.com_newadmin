<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Jtk extends Model
{
    //
    const URL = 'http://api.jutuike.com';
    const KEY = 'fxwuK0Kz5lJscXJlN3V8QS5G6tXK3tQF';
    const ID = '57606';

    const ORDERURL = '/union/orders';

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
        return $result;
    }

    /**
     * 拼接链接
     * @param $uri
     * @return string
     */
    private static function makeUri($uri){

        return self::URL . $uri;
    }
    /**
     * 获取订单信息
     */
    static function getorder($page = 1){
        $query = array(
            'apikey'=>self::KEY,
            'page'=>$page
        );
        $url = self::makeUri(self::ORDERURL);
        $res = self::post($url,$query);
        return $res;
    }
}
