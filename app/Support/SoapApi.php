<?php
namespace App\Support;


/**
 * 票付通采购商soap接口
 */
class SoapApi
{
    private $debug = false;
    //影旅汇正式接口账号
    private $_account = '65204488';  //账号
    private $_password = '0f84cf6465c5e8a5bafe62186a83aa4b'; //密码
    private $_url = "http://open.12301.cc/openService/MXSE.wsdl";

    private $account = '6035565';  //账号
    private $password = '25552e4fbdf2885c68123cc4132ab6cd'; //密码
    private $url = "http://open.12301dev.com/openService/MXSE_beta.wsdl";

    //联调测试接口
    // private $account = '100019';  //账号
    // private $password = 'a36c415c112c749aba38efd7c5abe755'; //密码

    private $client = null;
    private static $instance;
    private function __construct()
    {
        if(!$this->debug){
            $this->account = $this->_account;
            $this->password = $this->_password;
            $this->url = $this->_url;
        }
        libxml_disable_entity_loader(false);
        if(!$this->client){
            // $testurl = "http://open.12301.cc/openService/MXSE.wsdl";
            // $testurl = "http://open.12301dev.com/openService/MXSE_beta.wsdl";
            $this->client = new \SoapClient($this->url,array('encoding' =>'UTF-8','cache_wsdl' => 0));//票付通接口地址
        }
    }

    /**
     * 获取校验码
     *
     * @return void
     */
    function getVerifyCode(){
        return md5($this->account.$this->password);
    }

    static function getInstance(){
        if(!(self::$instance instanceof self)){
            self::$instance = new self();
        }
        return self::$instance;
    }


    private function format($data,$function = ''){
        $obj=simplexml_load_string($data,'SimpleXMLElement',LIBXML_NOCDATA);

        $res = json_decode(json_encode($obj),true);

        if(empty($res)){
            return array();
        }

        return $res;
    }

    /**
     * 获取城市编码
     *
     * @param integer $pagesize
     * @param integer $page
     * @return void
     */
    function Get_Area_Code_List(int $pagesize = 200,int $page = 1){
        $data = $this->client->__soapCall("Get_Area_Code_List",array("ac"=>$this->account,"pw"=>$this->password,"page"=>$page,"pageNum"=>$pagesize));
        $res = $this->format($data);
        $result = $res['Rec'];
        if(!empty($result['UUerrorcode'])){
            logger('票付通 Get_Area_Code_List接口：'.json_encode($result,256));
            return array();
        }
    }
    /**
     * 查询景区列表
     */
    function Get_ScenicSpot_List(int $pagesize = 20,int $page = 1){
        $n = ($page-1) * $pagesize;
        $param = array("ac"=>$this->account,"pw"=>$this->password,"n"=>$n,"m"=>$pagesize);
        $data = $this->client->__soapCall("Get_ScenicSpot_List",$param);
        $res = $this->format($data);
        $result = $res['Rec'];
        if(!empty($result['UUerrorcode'])){
            logger('票付通 Get_ScenicSpot_List 接口：'.json_encode($result,256));
            return array();
        }
        if(count($result)+1 == count($result,1)){
            return [$result];
        }
        return $result;
    }

    /**
     * 查询景区详细信息
     */
    function Get_ScenicSpot_Info($spotId){
        $data =  $this->client->__soapCall("Get_ScenicSpot_Info",array("ac"=>$this->account,"pw"=>$this->password,"n"=>$spotId));
        $res = $this->format($data);
        $result = $res['Rec'];

        if(!empty($result['UUerrorcode'])){
            logger('票付通 Get_ScenicSpot_Info 接口：'.json_encode($result,256));
            return array();
        }
        return $result;
    }

    /**
     * 门票列表
     * n和m不可同时为空,只传n时返回多个门票二维数组，该景区下只有一个门票时回传一维数组
     * 传m为精确查找，只返回该门票的一维数组,特殊情况下，一个门票有多个供应商供应也会返回二维数组
     */
    function Get_Ticket_List($spotId,$ticketId = ''){
        $data =  $this->client->__soapCall("Get_Ticket_List",array("ac"=>$this->account,"pw"=>$this->password,"n"=>$spotId,"m"=>$ticketId));
        logger('列表');
        $res = $this->format($data);

        $result = $res['Rec'];
        if(!empty($result['UUerrorcode'])){
//            logger('票付通 Get_Ticket_List 接口：'.json_encode($result,256));
            return array();
        }
        if(count($result)+1 == count($result,1)){
            return [$result];
        }
        return $result;
    }

    /**
     * 动态价格、库存上限获取
     *
     * @param [type] $UUaid 供应商id
     * @param [type] $UUpid 价格id
     * @param [type] $startDate
     * @param [type] $endDate
     * @return void
     */
    function GetRealTimeStorage($UUaid,$UUpid,$startDate,$endDate){
        $startDate = date('Y-m-d',strtotime($startDate));
        $endDate = date('Y-m-d',strtotime($endDate));
        $data =  $this->client->__soapCall("GetRealTimeStorage",array("ac"=>$this->account,"pw"=>$this->password,"aid"=>$UUaid,"pid"=>$UUpid,"start_date"=>$startDate,"end_date"=>$endDate));
        $res = $this->format($data);
        if(!empty($res['Rec']) && !empty($res['Rec']['UUerrorcode'])){
            logger('票付通GetRealTimeStorage接口：'.json_encode($res['Rec'],256));
            return array();
        }
        logger('票付通GetRealTimeStorage接66口：'.json_encode(compact('UUaid','UUpid')).json_encode($res,256));
        return $res['items']??array();
    }

    /**
     *分时价格库存
     *
     * @param [type] $UUaid供应商id
     * @param [type] $UUid门票id
     * @param [type] $startDate查询价格开始日期	 格式：Y-m-d,结束日期不小于今日日期,开始日期与结束日期的最大跨度为31天
     * @param [type] $endDate查询价格结束日期 格式：Y-m-d
     * @return void
     */
    function Time_Share_Price_And_Storage($UUaid,$UUid,$startDate,$endDate){
        $startDate = date('Y-m-d',strtotime($startDate));
        $endDate = date('Y-m-d',strtotime($endDate));
        $data =  $this->client->__soapCall("Time_Share_Price_And_Storage",array("ac"=>$this->account,"pw"=>$this->password,"aid"=>$UUaid,"tid"=>$UUid,"startDate"=>$startDate,"endDate"=>$endDate));
        $res = $this->format($data);
        if(!empty($res['Rec']) && !empty($res['Rec']['UUerrorcode'])){
            logger('票付通Time_Share_Price_And_Storage接口：'.json_encode($res['Rec'],256));
            return array();
        }
        return $res['items']??array();
    }

    /**
     *  预判下单 （判断提交的参数是否满足下单条件，没有生成订单；可选）
     *  return UUdone 100为校验格式成功
     * @return void
     */
    function OrderPreCheck($UUid,$UUaid,$tnum,$playtime,$mobile,$name,$personId,$tprice){
        $params = array(
            "ac"=>$this->account, //账号
            "pw"=>$this->password, //密码
            "tid"=>$UUid, //门票id
            "tnum"=>$tnum,//购买数量
            "playtime"=>$playtime,//游玩日期格式：Y-m-d 备注：为分时预约产品时，playtime格式为：Y-m-d hh:mm， hh:mm 值为分时价格库存方法返回的start_date参数。
            "ordertel"=>$mobile, //取票人手机号
            "ordername"=>$name,//游客姓名 ,多个用英文逗号隔开
            "m"=>$UUaid,//供应商id
            "paymode"=>0, //支付方式 0-账户余额，2-供应商授信额度，4-现场支付,取决于分销商和供应商的合作方式，与用户的支付方式无关
            "personid"=>$personId,//游客身份证 ,其他证件类型： 证件号 + : + 类型 （eg : G12323:2多个还是 , 分割）1=身份证, 2=护照, 3=军官证, 4=回乡证, 5=台胞证, 99=其他
            "tprice"=>$tprice, //结算价
        );
        $data = $this->client->__soapCall("OrderPreCheck",$params);

        $res = $this->format($data);
        $result = $res['Rec'];
        if(!empty($result['UUerrorcode'])){
            logger('票付通 OrderPreCheck 接口：'.json_encode($result,256));
            return array('status'=>false,'code'=>$result['UUerrorcode'],'msg'=>self::errmsg($result['UUerrorcode'],$result['UUerrorinfo']),'data'=>$params);
        }
        if(!empty($result['UUdone']) && $result['UUdone'] == 100){
            return true;
        }
        return $result;
    }

    /**
     * 提交订单
     *
     * @return void
     */
    private $UUlid = '';//产品id,对应 Get_Ticket_List.UUlid
    private $UUid = '';//门票id
    private $UUaid = '';//供应商id
    private $orderNo = '';//贵方订单号,请确保唯一
    private $tprice = '';//供应商配置的结算单价，单位：分
    private $tnum = '';//购买数量
    private $playtime = '';//游玩日期
    private $ordername = '';//客户姓名,多个用英文逗号隔开，不支持特殊符号
    private $ordertel = '';//取票人手机号
    private $contactTEL = '';//多个用英文逗号隔开，不支持特殊符
    private $smsSend = 0;//0 -票付通发送短信 1-票付通不发短信（前提是票属性上有勾选下单成功发短信给游客）
    private $paymode = 0;//扣款方式（0使用账户余额2使用供应商处余额4现场支付
    private $ordermode = 0;//下单方式（0正常下单1手机用户下单）
    private $assembly = '';//集合地点
    private $series = '';//团号
    private $concatID = 0;//联票ID
    private $pCode = 0;//套票ID
    private $personID = '';//身份证,
    private $orderRemark = '';//备注
    private $OrderCallbackUrl = '';//核销/退票回调地址


    function PFT_Order_Submit(){
        $params = array(
            "ac"=>$this->account, //账号
            "pw"=>$this->password, //密码
            "lid"=>$this->UUlid, //产品id,对应 Get_Ticket_List.UUlid
            "tid"=>$this->UUid,//门票id
            "remotenum"=>$this->orderNo,//贵方订单号,请确保唯一
            "tprice"=>$this->tprice, //供应商配置的结算单价，单位：分
            "tnum"=>$this->tnum,//购买数量
            "playtime"=>$this->playtime,//游玩日期
            "ordername"=>$this->ordername, //客户姓名,多个用英文逗号隔开，不支持特殊符号
            "ordertel"=>$this->ordertel,//取票人手机号
            "contactTEL"=>$this->contactTEL, //多个用英文逗号隔开，不支持特殊符
            "smsSend"=>$this->smsSend,//0 -票付通发送短信 1-票付通不发短信（前提是票属性上有勾选下单成功发短信给游客）
            "paymode"=>$this->paymode,//扣款方式（0使用账户余额2使用供应商处余额4现场支付
            "ordermode"=>$this->ordermode,//下单方式（0正常下单1手机用户下单）
            "assembly"=>$this->assembly,//集合地点
            "series"=>$this->series,//团号
            "concatID"=>$this->concatID,//联票ID
            "pCode"=>$this->pCode,//套票ID
            "m"=>$this->UUaid,//供应商id
            "personID"=>$this->personID, //身份证,
            "memo"=>$this->orderRemark,//备注
            "callbackUrl"=>$this->OrderCallbackUrl,//核销/退票回调地址
        );

        $data = $this->client->__soapCall("PFT_Order_Submit",$params);
        $res = $this->format($data);

        logger('票付通 PFT_Order_Submit 接口：'.json_encode($res,256));
        $result = $res['Rec']??array();
        if(!empty($result['UUordernum'])){
            return array('status'=>true,'code'=>1,'msg'=>'下单成功','data'=>$result);
        }
        if(!empty($result['UUerrorcode'])){
            return array('status'=>false,'code'=>$result['UUerrorcode'],'msg'=>$result['UUerrorinfo'],'data'=>$params);
        }
        return array('status'=>false,'code'=>'-1','msg'=>'未知错误','data'=>$params);
        // "UUordernum": "64964693114460",
		// "UUremotenum": "202204111115279687",
		// "UUcode": "356734",
		// "UUqrcodeURL": "http://12301dev.com/ExzlMW26zR",
		// "UUqrcodeIMG": "https://open.12301.cc/code/Bmj9W.png"
    }

    /**
     * 身份证检验
     *
     * @param [type] $personId
     * @return void
     */
    function Check_PersonID($personId){
        $data =  $this->client->__soapCall("Check_PersonID",array("ac"=>$this->account,"pw"=>$this->password,"personId"=>$personId));
        $res = $this->format($data);
        //身份证检验返回
        if(!empty($res['Rec']) && !empty($res['Rec']['UUerrorcode'])){
            return false;
        }
        return true;
    }

    /**
     * 订单查询
     *
     * @return void
     */
    private $remoteOrdernum = '';//远端订单号
    private $pftOrdernum = '';//票付通订单号
    function OrderQuery(){
        if(empty($this->remoteOrdernum) && empty($this->pftOrdernum)){
            return false;
        }
        $data =  $this->client->__soapCall("OrderQuery",array(
            "ac"=>$this->account,
            "pw"=>$this->password,
            "pftOrdernum"=>$this->pftOrdernum, //票付通订单号
            "remoteOrdernum"=>$this->remoteOrdernum //远端订单号
            )
        );
        $res = $this->format($data);
        $result = $res['Rec'];
        if(!empty($result['UUerrorcode'])){
            logger('票付通 OrderQuery 接口：'.json_encode($result,256));
            return false;
        }

        return $result;
    }

    /**
     * 订单短信发送、重发接口
     *
     * @return void
     */
    function reSend_SMS_Global_PL(){
        $data =  $this->client->__soapCall("reSend_SMS_Global_PL",array(
                "ac"=>$this->account,
                "pw"=>$this->password,
                "in0"=>'票付通订单号'
            )
        );
        return $this->format($data,'reSend_SMS_Global_PL');
    }

    /**
     * 修改、取消订单
     *
     * @return void
     */
    function Order_Change_Pro($pftOrderNo,int $num,$orderTel = '',$refundNo = ''){
        $data =  $this->client->__soapCall("Order_Change_Pro",array(
                "ac"=>$this->account,
                "pw"=>$this->password,
                "ordern"=>$pftOrderNo,//票付通订单号
                "num"=> $num, //剩余数量
                "ordertel"=>$orderTel,
                "m"=>$refundNo
            )
        );
        $res = $this->format($data);
        $result = $res['Rec'];
        if(!empty($result['UUerrorcode'])){
            logger('票付通 Order_Change_Pro 接口：'.json_encode($result,256));
            return array('status'=>false,'code'=>$result['UUerrorcode'],'msg'=>self::errmsg($result['UUerrorcode'],$result['UUerrorinfo']),'data'=>["ordern"=>$pftOrderNo]);
        }
        if(!empty($result['UUdone'])){
            if($result['UUdone'] == 100){
                return array('status'=>true,'code'=>1,'msg'=>'取消成功','data'=>$result);;
            }elseif($result['UUdone'] == 1095){
                return array('status'=>true,'code'=>2,'msg'=>'退票申请成功，待审核','data'=>$result);
            }
        }
        logger('票付通 Order_Change_Pro 接口：'.json_encode($result,256));
        return array('status'=>false,'code'=>-1,'msg'=>'未知异常','data'=>["ordern"=>$pftOrderNo]);
        // "UUdone": "100",
        // "UUrefund_fee": "0",
        // "UUrefund_amount": "1000",
        // "UUserial_number": "b6f5551fb9a3834dc7296f5db0afc6e1"
        return $result;

    }

    /**
     * 凭证码查看
     *
     * @return void
     */
    function Terminal_Code_Verify(){
        $data =  $this->client->__soapCall("Terminal_Code_Verify",array("ac"=>$this->account,"pw"=>$this->password,"in0"=>"票付通订单号"));
        return $this->format($data,'Terminal_Code_Verify');
    }

    /**
     * 查询资金余额
     *
     * @return void
     */
    function PFT_Member_Fund(){
        $data =  $this->client->__soapCall("PFT_Member_Fund",array("ac"=>$this->account,"pw"=>$this->password,"in0"=>1,"in1"=>4));
        return $this->format($data,'PFT_Member_Fund');
    }


    static function errmsg($code,$msg = '')
    {
        $msgArr = array(
            1092=>'请填写取票人身份证号',
            1093=>'身份证号码错误',
            1094=>"实名制门票每次限购一张",
            1095=>'退票申请成功，请等待审核结果通知',
        );

        if(empty($msg))
        {
            $msg = '系统服务异常';
        }

        return $msgArr[$code]??$msg;
    }

    function __set($property, $value) {
        $this->$property = $value;
    }
    private function __clone(){}

}
