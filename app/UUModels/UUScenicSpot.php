<?php
namespace App\UUModels;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通数据类
 */
class UUScenicSpot extends Model
{
    use ApiTrait;
    protected $table = 'pw_scenic_spot';
    protected $fillable = ['UUaddtime','UUarea','UUid','UUimgpath','UUtitle','UUp_type'];
    // public $timestamps = false;
    static $type=[
        'A'=>'景点',
        'B'=> '线路',
        'C'=> '酒店',
        'F' => '套票',
        'H'=> '演出'
    ];

    static function saveData($data){
        if(empty($data)) return false;   
        $now_time = date('Y-m-d H:i:s');
        try {            
            $list = array();
            foreach($data as $item){
                unset($item['@attributes']);
                $item['created_at'] = $item['updated_at'] = $now_time;
                if($item['UUp_type'] == 'A' || $item['UUp_type'] == 'F' || $item['UUp_type'] == 'H'){
                    $list[]=$item;
                }
                
            }
            UUScenicSpot::upsert($list,['UUimgpath','UUtitle','UUp_type','UUarea','UUaddtime','updated_at']);
        } catch (\Throwable $th) {
            logger('UUScenicSpot:'.$th->getMessage());
        }
    }

    public function getTypeTxt(){
        return self::$type[$this->UUp_type];
    }


    public function detail(){
        return $this->hasOne(UUScenicSpotInfo::class,'UUid','UUid');
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