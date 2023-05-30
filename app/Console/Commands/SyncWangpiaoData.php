<?php

namespace App\Console\Commands;


use App\Support\WpApi;
use App\Models\JobList;
use App\Support\Helpers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncWangpiaoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncwpdata {task} {--auto}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步接口数据';

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
        
        // 入口方法
        // $jobSetting = JobList::all()->toArray();
        // $keys = array_column($jobSetting,'names');
        // $jobSetting = array_combine($keys,$jobSetting);
        // extract($jobSetting);
        
        try {
            switch($task){
                case 'city':
                    $rate = 30;//30天
                    // if(!$this->checkTime($sync_citys,$rate)) return;
                    // $this->cities();
                    Log::info(date('Y-m-d H:i:s').'同步了城市数据0001');
                    break;
                // case 'district':
                //     // $rate = 30;//30天
                //     // if(!$this->checkTime($sync_citys,$rate)) return;
                //     // $this->district();
                //     // Log::info(date('Y-m-d H:i:s').'同步了城市区域数据1');
                //     break;
                // case 'trading':
                //     // $rate = 30;//30天
                //     // if(!$this->checkTime($sync_citys,$rate)) return;
                //     // $this->trading();
                //     // Log::info(date('Y-m-d H:i:s').'同步了城市商圈');
                //     break;
                case 'cinema':
                    $rate = 30;//30天
                    // if(!$this->checkTime($sync_cinemas,$rate)) return;
                    $this->cinemas();
                    Log::info(date('Y-m-d H:i:s').'同步了影院数据2');
                    break;
                    
                case 'hot':
                    $rate = 5;//5天                                        
                    // if(!$this->checkTime($sync_hot_film,$rate)) return;
                    $this->currentMovies(2);
                    Log::info(date('Y-m-d H:i:s').'同步了热映数据3');
                    break;
                case 'rightnow':
                    $rate = 5;//5天                                        
                    // if(!$this->checkTime($sync_rightnow_film,$rate)) return;
                    $this->currentMovies(1);
                    Log::info(date('Y-m-d H:i:s').'同步了即将上映数据4');
                    break;
                    
                case 'movies':
                    $rate = 1;//5天                    
                    // if(!$this->checkTime($sync_movies,$rate)) return;
                    $this->movies();
                    // Log::info(date('Y-m-d H:i:s').'同步了影片数据5');
                    break;
                case 'schedules':
                    $rate = 1;//5天
                    // if(!$this->checkTime($sync_paiqi,$rate)) return;
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
        $this->info('城市、区域、商圈、地铁信息同步');
        //同步城市
        $list = WpApi::getCityList();        
        foreach($list as $city){
            \App\Jobs\Wangpiao\CityJob::dispatch($city);
        }

        //已有业务城市查询
        $list = WpApi::getCityBillList();
        foreach($list as $city){
            \App\Jobs\Wangpiao\CityJob::dispatch($city);
        } 
        
        //同步区域
        $list = WpApi::getCityDistrict();
        foreach($list as $district){
            \App\Jobs\Wangpiao\DistrictJob::dispatch($district);
        }

        //同步商圈
        $list = WpApi::getTradingArea();
        foreach($list as $area){
            \App\Jobs\Wangpiao\TradingAreaJob::dispatch($area);
        }

        //同步地铁
        $list = WpApi::getSubWay();
        foreach($list as $subway){
            \App\Jobs\Wangpiao\SubWayJob::dispatch($subway);
        }
    }
    


    /**
     * 同步影院
     *
     * @return void
     */
    private function cinemas(){
        $this->info('影院信息同步');
        // $list = WpApi::getCinemaLineList();
        // foreach($list as $line){
        //     \App\Jobs\Wangpiao\CinemaLineJob::dispatch($line);
        // }
        
        $list = WpApi::getCinemaList();
        if($list){
            \App\ApiModels\Wangpiao\Cinema::truncate();
        }
        foreach($list as $cinema){
            \App\Jobs\Wangpiao\CinemaJob::dispatch($cinema);
        }
        \App\ApiModels\Wangpiao\Hall::truncate();
        foreach($list as $cinema){
            $halls = WpApi::getCinemaHall($cinema['ID']);
            if($halls){                
                foreach($halls as $hall){
                    \App\Jobs\Wangpiao\HallsJob::dispatch($hall);
                }
            }
        }
        // $cityList = \App\ApiModels\Wangpiao\City::all();
        // $date = date('Y-m-d H:i:s');
        // foreach($cityList as $city){
        //     $list = WpApi::getCinemaQueryList($city->id,$date);
        //     if(!$list){
        //         continue;
        //     }
        //     foreach($list as $cinema){
        //         \App\Jobs\Wangpiao\CinemaJob::dispatch($cinema);
        //     }

        //     foreach($list as $cinema){
        //         $halls = WpApi::getCinemaHall($cinema['ID']);
        //         if($halls){
        //             \App\ApiModels\Wangpiao\Hall::truncate();
        //             foreach($halls as $hall){
        //                 \App\Jobs\Wangpiao\HallsJob::dispatch($hall);
        //             }
        //         }
        //     }
        // }
    }

    /**
     * 同步影片
     *
     * @return void
     */
    private function movies(){
        $this->info('影片信息同步');
        \App\ApiModels\Wangpiao\Film::truncate();
        $list = WpApi::getPlanFilm();
        foreach($list as $film){
            $film['ShowType'] = 1;
            $film['CityID'] = 0;
            \App\Jobs\Wangpiao\FilmJob::dispatch($film);
        }
        
        $cityList = \App\ApiModels\Wangpiao\City::all();
        foreach($cityList as $city){
            $list = WpApi::getCurrentFilm($city->id);
            if(!$list){
                continue;
            }
            foreach($list as $film){
                $film['ShowType'] = 2;
                $film['CityID'] = $city->id;
                \App\Jobs\Wangpiao\FilmJob::dispatch($film);
            }
        }
    }

    /**
     * 放映计划
     *
     * @return void
     */
    private function schedule(){
        // $this->info('放映计划信息同步');
        // \App\ApiModels\Wangpiao\Schedules::truncate();
        // $cinemaList = \App\ApiModels\Wangpiao\Cinema::all();
        // $date = date('Y-m-d H:i:s');
        // foreach($cinemaList as $cinema){
        //     $list = WpApi::getFilmShowByDate($cinema->id,$date);
        //     if(!$list){
        //         continue;
        //     }
        //     foreach($list as $schedule){
        //         \App\Jobs\Wangpiao\SchedulesJob::dispatch($schedule);
        //     }
        // }
    }
    
}
