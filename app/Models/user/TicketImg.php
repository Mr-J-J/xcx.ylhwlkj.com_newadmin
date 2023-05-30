<?php

namespace App\Models\user;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class TicketImg extends Model
{
    protected $table = "user_ticket_img";
    protected $guarded  = [];
    protected $hidden = ['created_at','updated_at'];

    /**
     * 添加取票码图片
     *
     * @param [type] $data
     * @return void
     */
    public static function addCode($data,$count = 0){
        extract($data);
        if(empty($images)){            
            //Helpers::exception('请上传取票码图片');
        }
        if(empty($ticket_code)){
            Helpers::exception('请录入取票码');            
        }
        // if(empty($valid_code)){
        //     Helpers::exception('请录入验证码');
        // }

        $data['ticket_code'] = encrypt($ticket_code,false);
        
        if(empty($data['id'])){
            $codeCount = self::where("order_id",$data['order_id'])->count();
            if($codeCount >= $count){
                Helpers::exception('取票码已录入');
            }
            $data['id'] = 0;
        }        
        return self::updateOrCreate(['id'=>$data['id']],$data);
    }

    /**
     * 删除取票码
     *
     * @param [type] $code_id
     * @return void
     */
    public static function delCode($code_id){
        if(empty($code_id)){
            Helpers::exception('取票码不存在');
        }
        self::destroy($code_id);
    }

    // public function setTicketCodeAttribute($value){        
    //     $this->attributes['ticket_code'] = encrypt($result,false);
    // }

    public function getTicketCodeAttribute($value){
        return decrypt($value,false);
        // return $value;
    }
}
