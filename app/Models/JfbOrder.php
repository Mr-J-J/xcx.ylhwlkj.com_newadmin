<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JfbOrder extends Model
{
    protected $table = 'jfb_orders';

    protected $guarded = [];
    static $state_enum = ['2002'=>'购票失败','2000'=>'已出票','2001'=>'出票中','2003'=>'已取消'];
    //（失败订单：2002， 出票中：2001，已出票：2000，已取消：2003）
    function getStateTxt(){
        return self::$state_enum[$this->state]??'';
    }
}