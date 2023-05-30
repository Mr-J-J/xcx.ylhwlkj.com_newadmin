<?php

namespace App\Models;
use App\Support\Helpers;
use Illuminate\Support\Facades\DB;
use App\Models\store\BrandBindsLevel;
use Illuminate\Database\Eloquent\Model;

class CinemasBrand extends Model
{
    protected $hidden = ['created_at','updated_at'];

    protected $fillable  = [];
    protected $guarded = [];
    protected $appends = [];


    

    // public static function boot(){
    //     parent::boot();

    //     static::saving(function($model){
    //         $arr = $model->attributes;
    //         if(!isset($arr['levels'])) return false;
    //         $levelids = $arr['levels'];            
    //         unset($arr['levels']);
    //         $brand_id = $arr['id'];
    //         DB::table('brand_level')->where('brand_id',$brand_id)->delete();
    //         foreach ($levelids as $item){
    //             if(!empty($item)){
    //                 $data = ['brand_id'=>$brand_id,'level_id'=>$item];
    //                 DB::table('brand_level')->updateOrInsert($data,$data);
    //             }
    //         }
    //         $model->attributes = $arr;
    //     });
    // }


    static function saveData($data){
        $result = '';
        $data = self::formatField($data);       
        try {
           $result =  self::updateOrCreate(['id'=>$data['id']],$data);
        } catch (\Exception $e) {
            Helpers::exception($e->getMessage());
        }
        return $result;
    }

    static function formatField($data){        
        $fields = array(
            'id'             => $data['ID'],
            'brand_name'      => $data['Name'],
        );

        return $fields;
    }
    /**
     * 品牌下拉选项
     *
     * @return void
     */
    public static function brandsOptions(){
        $list = self::select('id','brand_name')->get();
        return $list;
    }
    // public function getLevelsAttribute(){
    //     $brand_id = $this->attributes['levels'];
    //     $levelArr = BrandBindsLevel::where('brand_id',$brand_id)->get('level_id');
    //     return array_column($levelArr->toArray(),'level_id');
    // }

    // public function setLevelsAttribute($value){
    //     unset($this->attributes['levels']);
    // }

    public function cinemas(){
        return $this->hasMany('\App\Models\Cinema','brand_id');
    }

    public function setLevelsIdAttribute($value){
        $this->attributes['levels_id'] = implode(',',$value);
    }

    public function getLevelsIdAttribute($value){
        return explode(',',$value);
    }


    // public function levels(){
    //     return $this->hasMany('\App\Models\store\StoreLevel','brand_level');
    // }
}
