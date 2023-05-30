<?php

namespace App\Models;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    //

    protected $fillable = [];
    protected $guarded = [];

    /**
     * 添加意见反馈
     *
     * @param [type] $data  添加的数据
     * @param [type] $user_id  用户id/商家id
     * @param integer $user_type  1商家 2用户
     * @return void
     */
    public static function addSuggestion($data,$user_id,$user_type=1){
        \extract($data);
        if(empty($type)){
            $type = '其他';
        }
        if(empty($content)){
            Helpers::exception('请填写反馈内容');
        }
        if(!empty($images)){
            if(is_array($images)){
                $images = \implode(',',$images);
            }
        }
        if(!preg_match('/^1[3456789]\d{9}$/',$phone)){            
            Helpers::exception('手机号格式不正确');
        }

        $state = 0;

        $data = compact('type','user_type','content','images','phone','user_id','state');

        try {
            self::create($data);
        } catch (\Exception $e) {
            Helpers::exception($e->getMessage());
        }
    }
}
