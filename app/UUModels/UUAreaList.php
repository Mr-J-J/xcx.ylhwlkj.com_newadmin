<?php
namespace App\UUModels;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通数据类
 */
class UUAreaList extends Model
{
    use ApiTrait;
    protected $table = 'pw_area_list';
    protected $fillable = ['UUarea_code','UUarea_name','UUparent_area_code'];
    public $timestamps = false;
    
    static function saveData($data){
        if(empty($data)) return false;       
        try {            
            foreach($data as &$item){
                unset($item['@attributes']);
            }
            UUAreaList::upsert($data,['UUarea_name','UUparent_area_code']);
        } catch (\Throwable $th) {
            logger('UUAreaList:'.$th->getMessage());
        }
    }        
}