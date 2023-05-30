<?php

namespace App\Console\Commands;

use App\Support\WpApi;
use App\MallModels\Order;
use Illuminate\Console\Command;
use App\MallModels\OrderCheckCode;
use App\ApiModels\Wangpiao\ScheduleCinema;

class DataTimer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataclear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定期清理过期数据';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        logger(1);
        //重置当天的放映场次
        \App\ApiModels\Wangpiao\Schedules::truncate();
        //清空过期的放映计划影院
        \App\ApiModels\Wangpiao\ScheduleCinema::truncate();
        \App\ApiModels\Wangpiao\CinemaMovie::truncate();
        // \App\ApiModels\Wangpiao\Film::truncate(); //清空影片信息
        \App\ApiModels\Wangpiao\CurrentFilm::truncate(); //清空影片信息
        \App\Http\Controllers\NApiController::infilm(2);//更新电影
//        \App\Http\Controllers\NApiController::incinema();
        try {
            //核销过期的订单
            $checkList = OrderCheckCode::getCheckEndCode();
            $expireCheckOrderIds = array();
            $checkIds = array();
            foreach($checkList as $item){
                $checkId[] = $item->id;
                $expireCheckOrderIds[] = $item->order_id;
            }
            if(!empty($checkIds)){
                OrderCheckCode::whereIn('id',$checkIds)->update(['state'=>1]);
            }
            if(!empty($expireCheckOrderIds)){
                Order::whereIn('id',$expireCheckOrderIds)->update(['order_status'=>Order::EXPIRE]);
            }
        } catch (\Throwable $th) {
            logger('核销过期的订单失败：'.$th->getMessage());
        }

    }
}
