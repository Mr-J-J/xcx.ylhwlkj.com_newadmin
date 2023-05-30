<?php
namespace App\UUModels;


use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通数据类
 */
class UUTicketStorage extends Model
{
    use ApiTrait;
    protected $table = 'pw_ticket_storage';
    protected $guarded = [];
    public $timestamps = false;
    
    /**
     * 数据入库
     *
     * @param [type] $data
     * @param [type] $UUaid 供应商id
     * @param [type] $UUid  门票id
     * @param [type] $UUpid 价格id
     * @param integer $type 1分时价格库存  2日历价格库存  3演出
     * @return array
     */
    static function saveData($data,$UUaid,$UUid,$UUpid,$UUtprice,$type = 1){
        if(empty($data)) return false;
        $tickeId = $UUid;
        $gysId = $UUaid;
        $priceId = $UUpid;
        if($type == 1){
            $UUpid = 0;
        }elseif($type == 2){
            $UUid = 0;
        }
        $storage_id = sprintf('%d_%d_%d_%d',$type,$gysId,$tickeId,$priceId);
        try {
            $list = [];            
            foreach($data as $item){
                // unset($item['@attributes']);
                if(empty($item)){
                    continue;
                }
                $item['UUaid'] = $gysId;
                $item['UUid'] = $tickeId;
                $item['UUpid'] = $priceId;
                $item['storage_id'] = $storage_id;
                $item['market_price'] = $UUtprice * 100;
                if($type == 1){
                    $item['storage'] = $item['remain'] = 0;
                    $item['periodList'] = json_encode($item['periodList'],256);
                }elseif($type == 2){
                    $item['periodList'] = json_encode(array(
                        'start_time'=>'',
                        'end_time'=>'',
                        'storage'=>$item['storage'],
                        'remain'=>$item['remain']
                    ),256);
                }
                $list[]=$item;
            }            
            UUTicketStorage::upsert($list,['storage','remain','periodList','market_price','buy_price','retail_price']);
            return $list;
        } catch (\Throwable $th) {
            logger('UUTicketStorage:'.$th->getMessage().json_encode($data));
        }
    }
    
}