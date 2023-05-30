<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    protected $table = 'order_logs';
    protected $fillable = [];
    protected $guarded = [];
    static function addLogs($order_id,$order_no,$remark,$content = ''){
        self::create(compact('order_id','order_no','remark','content'));
    }
}
