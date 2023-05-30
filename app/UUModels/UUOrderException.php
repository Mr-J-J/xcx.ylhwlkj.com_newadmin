<?php

namespace App\UUModels;


use App\Support\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通异常订单
 */
class UUOrderException extends Model
{

    protected $table = 'pw_order_exception';
    protected $guarded = [];


    /**
     * 添加异常
     *
     * @param UUTicketOrder $order
     * @param array $apiData
     * @return void
     */
    static function addRecord(UUTicketOrder $order,$code = 0,$errinfo = '',$request_id = '')
    {
        $data = [
            'order_no' => $order->order_no,
            'order_id'=>$order->id,
            'exception_no'=>$code,
            'request_id'=>$request_id,
            'errorinfo'=>$errinfo,
            'state'=>0,
            'remark'=>''
        ];
        self::create($data);
    }

    /**
     * 标记异常已处理
     */
    function flagRecord($state = 1, $remark = '')
    {
        $record = $this;
        $record->state = (int)$state;
        $record->remark = mb_substr($remark,0,240);
        $record->save();
    }
}
