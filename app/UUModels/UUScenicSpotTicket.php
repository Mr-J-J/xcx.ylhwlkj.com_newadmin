<?php
namespace App\UUModels;

use App\Support\SoapApi;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通数据类
 */
class UUScenicSpotTicket extends Model
{
    use ApiTrait;
    protected $table = 'pw_ticket_list';
    protected $guarded = [];
    public $timestamps = false;
    static $type=[
        'A'=>'景点',
        'B'=> '线路',
        'C'=> '酒店',
        'F' => '套票',
        'H'=> '演出'
    ];
    static $status_txt = [1=>'已上架',2=>'已下架',6=>'已删除'];
    static function saveData($data){
        if(empty($data)) return false;
        try {
            $list = [];
            $field = array();
            foreach($data as $item){
                unset($item['@attributes'],$item['UUbuy_limit_num'],$item['UUbuy_limit_date'],$item['UUbuy_limit']);
                if(strtotime($item['UUorder_end']) < 0){
                    unset($item['UUorder_end']);
                }
                if(strtotime($item['UUorder_start']) < 0){
                    unset($item['UUorder_start']);
                }
                $field = $item;
                $list[]=$item;
            }
            unset($field['UUid']);
            logger(json_encode($data,256));
            UUScenicSpotTicket::upsert($list,array_keys($field));
        } catch (\Throwable $th) {
            logger('UUScenicSpotTicket:'.$th->getMessage());
        }
    }

    /**
     * 获取门票详情
     *
     * @param [type] $id
     * @return model
     */
    static function getDetail($id){
        $detail = self::where('id',$id)->first();
        return $detail;
    }

    function getStatusTxt(){
        return self::$status_txt[$this->UUstatus];
    }
    /**
     * 门票下架
     *
     * @return void
     */
    function setTicketOff(){
        UUTicketStorage::where('UUid',$this->UUid)->delete();
        $this->delete();
    }

    /**
     * 月销量
     *
     * @param string $date Y-m-d
     * @return int
     */
    function saleNumber($date = ''){
        if(!$date){
            $date = date('Y-m-d');
        }
        $datetime = strtotime($date);
        return UUTicketSaleNum::where('UUtid',$this->UUid)->whereYear('created_at',date('Y',$datetime))->whereMonth('created_at',date('m',$datetime))->sum('salenum');
    }
    /**
     * 实时获取价格列表、库存列表
     *
     * @param [type] $startDate
     * @param [type] $endDate
     * @return void
     */
    public function getPriceList($startDate,$endDate){
        $api = SoapApi::getInstance();
        $res = array();
        $type = 1;
        if($this->UUopen_section){
            $res = $api->Time_Share_Price_And_Storage($this->UUaid,$this->UUid,$startDate,$endDate);
            $type = 1;
        }else{
            $type = 2;
            $res = $api->GetRealTimeStorage($this->UUaid,$this->UUpid,$startDate,$endDate);
        }


        if(count($res) == count($res,1)){
            $res = [$res];
        }
        $list = UUTicketStorage::saveData($res,$this->UUaid,$this->UUid,$this->UUpid,$this->UUtprice,$type);
        if($list){
            foreach($list as &$item){
                // unset($item['buy_price']);
                $item['retail_price'] = round($item['retail_price']/100,2);
            }
        }
        return $list;
    }

    static function downloadApiStorageAndPrice($ticketList,$startDate,$endDate){
        $ticket  = new UUScenicSpotTicket;
        foreach($ticketList as $item){
            $ticket->UUopen_section = $item['UUopen_section'];
            $ticket->UUid = (int)$item['UUid'];
            $ticket->UUaid = (int)$item['UUaid'];
            $ticket->UUpid = (int)$item['UUpid'];
            $ticket->UUtprice = $item['UUtprice'];
            $ticket->getPriceList($startDate,$endDate);
        }
    }

     /**
     * 获取库存和销售价格
     *
     * @return void
     */
    public function getStorageAndPrice($date = ''){
        $storageId = $this->getStorageId();
        $nowdate = $date?:date('Y-m-d');
        $storage = UUTicketStorage::where('storage_id',$storageId)->whereDate('date',$nowdate)->first();
        $retail_price = 0;
        $sale_num = $this->saleNumber($nowdate);
        $buy_price = 0;
        $remain = 0; //剩余库存
        if($storage){
            $retail_price = sprintf('%.2f',round($storage->retail_price/100,2));
            $now_storage = $storage->storage - $storage->remain;
        }
        return compact('retail_price','sale_num','buy_price','remain');
    }

    public function getStorageId(){
        $type = 1;
        if(!$this->UUopen_section){
            $type = 2;
        }
        $UUaid = $this->UUaid;
        $UUid = $this->UUid;
        $UUpid = $this->UUpid;
        // if($type == 1){
        //     $UUpid = 0;
        // }elseif($type == 2){
        //     $UUid = 0;
        // }
        return sprintf('%d_%d_%d_%d',$type,$UUaid,$UUid,$UUpid);
    }

    function ticketExplain(){
        $tags = array();


        $tags[0]['title'] = '';
        $items= array();
        $UUif_verify = $this->UUif_verify;
        $verify_tips = ['有效期内均可验证'];
        $items[] = ['title'=>'有效期内可验','content'=>$verify_tips[$UUif_verify]??''];

        $UUtourist_info_tips = ['','需提供一位取票人有效身份信息'];
        if($this->UUtourist_info){
            $items[] = ['title'=>'实名制','content'=>$UUtourist_info_tips[$this->UUtourist_info]??''];
        }

        //延迟验证
        $UUdelaytime = explode('|',$this->UUdelaytime);
        $delay_time_str = '';
        $hour = (int)($UUdelaytime[0]??0);
        $minute = (int)($UUdelaytime[1]??0);
        $delay_time_str .=$hour?$hour.'小时':'';
        $delay_time_str .=$minute?$minute.'分钟':'';
        if(!empty($delay_time_str)){
            $items[] = ['title'=>'预订成功'.$delay_time_str.'后可用','content'=>'下单成功'.$delay_time_str.'后可以使用'];
        }
        $tags[0]['list'] = $items;

        $tags[1]['title'] = '退改说明';
        $items = array();
        $UUrefund_rule = $this->UUrefund_rule;
        $refund_rule_tips = '';
        switch($UUrefund_rule){
            case 0:
                $refund_rule_tips = '有效期内未使用可申请退款';
                if($this->UUrefund_audit){
                    $refund_rule_tips.=',退票需要人工审核';
                }
                $items[] = ['title'=>'限时退','content'=>$refund_rule_tips];
                break;
            case 3:
                $items[] = ['title'=>'随时退','content'=>'未使用可随时申请退款'];
                break;

        }
        $items[] = ['title'=>'不可改签','content'=>'该产品一经预订成功，不支持改签，敬请谅解。注：是否允许改签以实际下单时改签规则为准'];
        $tags[1]['list'] = $items;

        $tags[2]['title'] = '使用说明';
        $items = array();
        $items[] = ['title'=>'有效期','content'=>'游玩日期当天有效'];
        $tags[2]['list'] = $items;

        // lt: "".concat(i, "前可订今日"),
        //     rt: "最晚需在出行当天" + i + "前购买"
        // } : {
        //     lt: "可订今日",
        //     rt: "支持当天购买入园"
        // } : 1 == a ? {
        //     lt: "最早可订明日",
        //     rt: i ? "需要在出行前一天" + i + "前购买" : "需要在出行前一天购买"
        // } : a <= 5 ? i ? {
        //     lt: "提前".concat(a, "天，").concat(i, "前预订"),
        //     rt: "需提前".concat(a, "天，并在下单当天").concat(i, "前购买")
        // } : {
        //     lt: "提前".concat(a, "天预订"),
        //     rt: "需提前".concat(a, "天购买")
        // } : {
        //     lt: "最早可订".concat(r),
        //     rt: i ? "最早需在".concat(r, "，并在").concat(i, "前购买") : "最早需在".concat(r, "前购买")
        // } : {
        //     lt: "最早可订".concat(r),
        //     rt: i ? "最早需在".concat(r, "，并在").concat(i, "前购买") : "最早需在".concat(r, "前购买")
        // }
        $notes = $this->UUnotes;
        return compact('tags','notes');
    }


    /**
     * 预判下单
     *
     * @return void
     */
    public function OrderPreCheck($tnum,$playtime,$mobile,$name,$personId,$tprice){
        $api = SoapApi::getInstance();
        $UUaid = $this->UUaid;
        $UUid = $this->UUid;
        $res =  $api->OrderPreCheck($UUid,$UUaid,$tnum,$playtime,$mobile,$name,$personId,$tprice);
        return $res;
    }


    public function scopeBatchId($query, array $batchIds){
        $ids = implode(',',$batchIds);
        return $query->whereIn('UUid',$batchIds)->orderByRaw("FIELD(UUid,$ids)");
    }

}
