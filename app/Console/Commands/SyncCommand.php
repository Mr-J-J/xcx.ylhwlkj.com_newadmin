<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Support\MApi;
use App\Models\Cinema;
use App\Models\JobList;
use App\Support\Helpers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncCommand {task} {--auto}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步城市数据';

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
        $task = $this->argument('task'); // 不指定参数名的情况下用argument
        $auto = $this->option('auto') ?:0; // 不指定参数名的情况下用argument                
        /**
         * task: city cinema hot rightnow movies schedules
         * type: auto 
         */
        Log::info(date('Y-m-d H:i:s').'进入任务同步');
        // 入口方法
        $jobSetting = JobList::all()->toArray();
        $keys = array_column($jobSetting,'names');
        $jobSetting = array_combine($keys,$jobSetting);
        extract($jobSetting);
        
        try {
            switch($task){
                case 'city':
                    $rate = 30;//30天
                    if(!$this->checkTime($sync_citys,$rate)) return;
                    $this->cities();
                    Log::info(date('Y-m-d H:i:s').'同步了城市数据1');
                    break;
                case 'cinema':
                    $rate = 30;//30天
                    if(!$this->checkTime($sync_cinemas,$rate)) return;
                    $this->cinemas();
                    Log::info(date('Y-m-d H:i:s').'同步了影院数据2');
                    break;
                case 'hot':
                    $rate = 5;//5天                                        
                    if(!$this->checkTime($sync_hot_film,$rate)) return;
                    $this->currentMovies(2);
                    Log::info(date('Y-m-d H:i:s').'同步了热映数据3');
                    break;
                case 'rightnow':
                    $rate = 5;//5天                                        
                    if(!$this->checkTime($sync_rightnow_film,$rate)) return;
                    $this->currentMovies(1);
                    Log::info(date('Y-m-d H:i:s').'同步了即将上映数据4');
                    break;
                case 'movies':
                    $rate = 5;//5天                    
                    if(!$this->checkTime($sync_movies,$rate)) return;
                    $this->movies();
                    Log::info(date('Y-m-d H:i:s').'同步了影片数据5');
                    break;
                case 'schedules':
                    $rate = 5;//5天
                    if(!$this->checkTime($sync_paiqi,$rate)) return;
                    $this->schedule();
                    Log::info(date('Y-m-d H:i:s').'同步了排期数据6');
                    break;
                default:
                    throw new \Exception($task.'任务未定义');
                    break;
            }
        } catch (\Exception $e) {
            Log::error("sync 异常：".$e->getMessage());
        }
    }
    /**
     * 执行时间检查
     *
     * @param [type] $setting
     * @param [type] $rate
     * @return void
     */
    private function checkTime($setting,$rate){
        $nexttime = 0;
        if(!empty($setting)){
            $rate = intval($setting['next_time']);
            if($setting['last_time']){
                $nexttime = strtotime("+ {$rate} day",$setting['last_time']);
            }
        }
        $task = $this->argument('task');
        $auto = $this->option('auto') ?:0; // 不指定参数名的情况下用argument

        if(time() < $nexttime && $auto){
            Log::info(date('Y-m-d H:i:s')."  {$task}任务跳过 ,执行时间应在：".date('Y-m-d H:i:s',$nexttime));
            return false;
        }        
        return true;
    }

    /**
     * 同步城市
     *
     * @return void
     */
    private function cities(){
        $list = MApi::getCityList();        
        foreach($list as $city){
            \App\Jobs\SyncCities::dispatch($city);
        }
    }

    /**
     * 同步影院
     *
     * @return void
     */
    private function cinemas(){
        $cities = City::all();
        if(empty($cities)) return;

        foreach($cities as $city){
            $lastkey = '';
            while(true){
                $list = MApi::cinemaList($city->city_code,$lastkey);
                if(empty($list['data'])){
                    break;
                }
                foreach($list['data'] as $item){
                    $item['city_code'] = $city->city_code;
                    \App\Jobs\SyncCinemas::dispatch($item);
                }
                $lastkey = $list['last_key'];
                if(empty($lastkey)){
                    break;
                }
            }
        }
    }

    /**
     * 同步热映、即将上映
     *
     * @return void
     */
    private function currentMovies($type = 1){
        $cities = City::all();
        $i = 1;
        if(empty($cities)) return;
        foreach($cities as $city){
            $list = MApi::currentFilmList($city->city_code,$type);
            if(empty($list)){
                continue;
            }
            foreach($list as $item){
                if($item['id'] == 0){
                    $item['id'] = $i;
                    $i++;
                }
                $item['data_type'] = $type;
                \App\Jobs\SyncCurrentMovies::dispatch($item);
            }
        }
    }


    /**
     * 同步电影
     *
     * @return void
     */
    private function movies(){
        $allCinemas = Cinema::all();
        if(empty($allCinemas)) return;
        foreach($allCinemas as $item){ 
            $list = MApi::filmList($item->id);
            if(empty($list)){
                continue;
            }
            foreach($list as $film){
                $film['cinema_id'] = $item->id;
                \App\Jobs\SyncMovies::dispatch($film);
            }
        }
    }


    /**
     * 同步排期
     *
     * @return void
     */
    private function schedule(){
        $allCinemas = Cinema::all();
        if(empty($allCinemas)) return;
        foreach($allCinemas as $item){  
            $lastkey = '';            
            while(true){
                $list = MApi::filmPaiqiList($item->id,'',$lastkey);
                if(empty($list['data'])){
                    break;
                }
                foreach($list['data'] as $pq){
                    $pq['brand_id'] = $item->brand_id;
                    $pq['city_code'] = $item->city_code;
                    \App\Jobs\SyncSchedules::dispatch($pq);
                }
                $lastkey = $list['last_key'];
                if(empty($lastkey)){
                    break;
                }
            }
        }
    }


}
