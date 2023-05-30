<?php
namespace App\UUModels;

use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 票付通数据类
 */
class UUUpdateLog extends Model
{
    
    protected $table = 'pw_update_logs';
    protected $fillable = ['UUarea_code','UUarea_name','UUparent_area_code'];
    static $pagesize = 100;
    
    static function doStep($start,$end){
        $model = self::where('id',1)->first();
        $model->start = (int)$start;
        $model->end = (int)$end;
        $model->save();
    }      
    
    static function getEnd(){
        $model = self::where('id',1)->first();
        return (int)$model->end;
    }

    static function getStart(){
        $model = self::where('id',1)->first();
        return (int)$model->start;
    }
}