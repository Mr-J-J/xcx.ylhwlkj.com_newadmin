<?php
namespace App\UUModels;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通数据类
 */
class UUScenicSpotInfo extends Model
{
    use ApiTrait;
    protected $table = 'pw_scenic_spot_info';
    protected $guarded = [];
    public $timestamps = false;
    static $type=[
        'A'=>'景点',
        'B'=> '线路',
        'C'=> '酒店',
        'F' => '套票',
        'H'=> '演出'
    ];
    static function saveData($data){

        if(empty($data)) return false;
        $nowtime = date('Y-m-d H:i:s');
        try {
            unset($data['@attributes']);
            $data['updated_at'] = $nowtime;
            UUScenicSpotInfo::upsert([$data],[
                'UUaddress','UUarea','UUruntime','UUsalerid','UUstatus','UUtel','UUbhjq','UUfax','UUimgpath','UUjqts',
                'UUjtype',
                'UUjtzn',
                'UUlng_lat_pos',
                'UUp_type',
                'UUtitle',
                'UUtopics',
                'UUopen_section',
                'UUprovince_code',
                'UUcity_code',
                'UUopentime',
                'updated_at'
            ]);
        } catch (\Throwable $th) {
            logger('UUScenicSpotInfo:'.$th->getMessage());
        }


    }


    /**
     * 产品变更
     *
     * @return void
     */
    function updateTicketByNotify($UUlid,$UUtids){
        $api = \App\Support\SoapApi::getInstance();
//        logger($UUlid);
        $ticketList = $api->Get_Ticket_List((int)$UUlid);
        logger($ticketList);
        UUScenicSpotTicket::saveData($ticketList);

        $spotInfo = $api->Get_ScenicSpot_Info((int)$UUlid);
        logger($spotInfo);
        UUScenicSpotInfo::saveData($spotInfo);

        $nowDate = \Illuminate\Support\Carbon::now();
        $currentStartDate = $nowDate->toDateString();
        $endDateObj = $nowDate->endOfMonth();
        $currentMonthEndDate = $endDateObj->toDateString();

        $nextMonthDate = $endDateObj->addDay(1);
        $nextMonthStartDate = $nextMonthDate->startOfMonth()->toDateString();
        $nextMonthEndDate = $nextMonthDate->endOfMonth()->toDateString();

        $ticketList = UUScenicSpotTicket::where('UUlid',$UUlid)->get();
        // $newTicketId = $UUtids['tid']??'';

        foreach($ticketList as $ticket){
            // if($ticket->UUid == $newTicketId){
            //     UUTicketStorage::where('UUid',$ticket->UUid)->delete();
            //     $ticket->getPriceList($currentStartDate,$currentMonthEndDate);
            //     $ticket->getPriceList($nextMonthStartDate,$nextMonthEndDate);
            // }
            // UUTicketStorage::where('UUid',$ticket->UUid)->delete();
            //接口最多请求31天数据

            $ticket->getPriceList($currentStartDate,$currentMonthEndDate);
            $ticket->getPriceList($nextMonthStartDate,$nextMonthEndDate);
        }
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
        return UUTicketSaleNum::where('UUlid',$this->UUid)->whereYear('created_at',date('Y',$datetime))->whereMonth('created_at',date('m',$datetime))->sum('salenum');
    }
    /**
     * 获取产品详情
     *
     * @param [type] $id
     * @return model
     */
    static function getDetail($id){
        $detail = self::where('id',$id)->first();
        // $detail = self::with(['ticketList'])->where('UUid',$id)->first();
        return $detail;
    }

    public function ticketList(){
        return $this->hasMany(UUScenicSpotTicket::class,'UUlid','UUid')->orderBy('UUtprice');
    }

    public function getTypeTxt(){
        return self::$type[$this->UUp_type]??'';
    }
    /**
     * 按类型搜索
     *
     * @param [type] $query
     * @param [type] $type
     * @return void
     */
    public function scopeType($query,$type){
        return $query->when($type,function($query,$type){
            return $query->where('UUp_type',$type);
        });
    }


}
