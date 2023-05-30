<?php
namespace App\Support;
use GuzzleHttp\Client;


// 接口使用在线文档说明：http://help.dahantc.com/docs/oss/1apkb302nt0tv.html
// 短信账号账号：dh40724 密码： kKx0R5aZ

// 出票成功，给用户发个通知短信
// 内容：您已订2022/2/8 22:10:00 奥斯卡影城(宏力广场店)《长津湖之水门桥》,7排4座|7排5座 请凭验证码xxxxxx至影院内自助取票机或影院前台取票。
//您已订${0,60}凭验证码${0,30}至影院内自助取票机或影院前台取票。

class CloudSms
{
    private $account = 'dh40724';
    private $password = 'kKx0R5aZ';
    const BASEURI = 'http://www.dh3t.com/json/sms/Submit';

    static function ticket_count_templet($str1,$str2){
        $templet = sprintf('【影旅汇】您已成功购买影票。请凭序列号%s验证码%s至%s取票。影票信息%s询4001058582',$str2,$str2,'<影院内自助取票机或影院前台>',$str1,);
//        $templet = str_replace('【','',$templet);
//        $templet = str_replace('[','',$templet);
        return $templet;
    }
    /**
     * 发送短信
     *
     * @param [type] $phone
     * @param [type] $content
     * @return void
     */
    function send_sms($phone,$content){
        $timestamps = time()*1000;
        $post_data = array();
        // 账号 秘钥  API接口信息获取.替换账号、秘钥、接收手机号
        $post_data['account'] = 'N4421qny7';
        $post_data['password'] = md5('8063eeea02134a50ac4935add4ddc011'.$phone.$timestamps);
        //默认测试内容，如需测试本企业签名和文案，请注册登录点集自助通申请签名和模板
        $post_data['content'] = $content;
        $post_data['mobile'] = $phone;
        $post_data['timestamps'] = $timestamps; //时间戳 单位毫秒
        $url='http://www.djzz.cn:8868/sms/mt';
        $o='';
        foreach ($post_data as $k=>$v)
        {
            $o.="$k=".urlencode($v).'&';
        }
        logger($post_data);
        $post_data=substr($o,0,-1);
//        logger($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
        $result = curl_exec($ch);
    }

    function get_report(){
        $param = [];
        $url = 'http://www.dh3t.com/json/sms/Report';
        $result = $this->request_post($url,$param);
    }

    function get_balance(){
        $url = 'http://www.dh3t.com/json/sms/Balance';
        $result = $this->request_post($url,[]);
    }

    /**
     * 获取基础请求参数
     *
     * @return array
     */
    protected function getBaseParam(){
        return array(
            "account"=> $this->account,
            "password"=> md5($this->password),
            // "msgid":"2c92825934837c4d0134837dcba00150",
            // "phones":"1571166****,1571165****",
            // "content":"您好，您的手机验证码为：430237。",
            // "sign":"【****】",
            // "subcode":"",
            // "sendtime":"201405051230"
        );
    }
    /**
     * post请求
     *
     * @param [type] $uri
     * @param array $data
     * @return void
     */
    private function request_post($url,$data=[]){
        $request = new Client;
        logger(json_encode($data,256));
        $data = array_merge($this->getBaseParam(),$data);
        $response = $request->post($url,[
            'json'=>$data,
            'timeout' => 10000,
            'connect_timeout' => 10000,
            'read_timeout' => 10000,
        ]);

        $result = json_decode((string)$response->getBody(),true);
        logger(json_encode($result,256));
        return $result;
    }
}
