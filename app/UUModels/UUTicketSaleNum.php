<?php
namespace App\UUModels;


use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通数据类
 */
class UUTicketSaleNum extends Model
{
    use ApiTrait;
    protected $table = 'pw_ticket_salenum';
    protected $guarded = [];
    
    /**
     * 创建数据
     *
     * @param [type] $UUid
     * @param [type] $UUlid
     * @param integer $number
     * @return model
     */
    static function addSaleNum($UUid,$UUlid,int $number){
        UUTicketSaleNum::create(array(
            'UUtid'=>$UUid,
            'UUlid'=>$UUlid,
            'salenum'=>$number,
        ));
    }
}