<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Msg extends Model
{
    //
    protected $table = 'msg';

    /**
     * 获取信息数量
     */
    public static function getnum(){
        //获取所有数据有多少条
        $num = Msg::where(function ($query){
            $query->where('userid','')
                ->orWhere('userid',Auth::user()->id);
        })
            ->count();
        return $num;
    }
    /**
     * 获取所有信息
     */
    public static function getmsg(){
        //获取所有数据有多少条
        $num = Msg::where(function ($query){
            $query->where('userid','')
                ->orWhere('userid',Auth::user()->id);
        })->get();
        logger(Auth::user()->id);
        return $num;
    }
}
