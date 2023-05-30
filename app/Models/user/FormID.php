<?php

namespace App\Models\user;

use Illuminate\Database\Eloquent\Model;
/**
 * 收集小程序formid
 */
class FormID extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'minpro_formid';

    /**
     * 获取formid
     *
     * @param [type] $user_id
     * @return void
     */
    public static function getFormId($user_id){
        $formid = self::where('user_id',$user_id)->first();

        if(empty($formid)) return '';

        if($formid->expire_time < time()){
            return '';
        }
        return $formid->formid;
    }
    /**
     * 存储formid
     *
     * @param [type] $formId
     * @param [type] $user_id
     * @return void
     */
    public static function saveFormId($formId,$user_id){
        $model = new FormID;
        $model->user_id = $user_id;
        $model->formid = $formId;
        $model->expire_time = strtotime('+ 6 day',time());
        $model->save();
    }

    /**
     * 删除过期FormId
     *
     * @return void
     */
    public static function deletFormId(){
        self::where('expire_time','<',time())->delete();
    }

}
