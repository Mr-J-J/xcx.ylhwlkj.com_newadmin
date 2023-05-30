<?php

namespace App\Console\Commands;


use App\Support\WpApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


class ApiDataDown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apidatadown {task} {--auto}';

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
        
        try {
            switch($task){         
                case 'cinemas':
                    $this->cinemas();
                    break;
                case 'brand':
                    $this->cinemasBrand();
                    break;
                case 'city':
                    $this->city();
                    break;
                case 'hot':
                    $this->hotFilm();
                    break;
                case 'hall':
                    $this->syncHalls();
                    break;
                case 'schedule':
                    $this->syncSchedules();
                    break;
                case 'scenic'://票付通产品同步增量
                    $this->syncScenic();
                    break;
                case 'scenicall'://票付通产品同步所有
                    $this->syncScenicAll();
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
     * 同步影院
     *
     * @return void
     */
    private function cinemas(){
        
        $list = WpApi::getCinemaList();
        if($list){
            \App\ApiModels\Wangpiao\Cinema::syncData($list);
        }
    }

    /**
     * 城市、区域、商圈、地铁
     *
     * @return void
     */
    private function city(){
        //城市 
        $list = (array)WpApi::getCityList();
        $billList =(array) WpApi::getCityBillList();
        foreach($list as &$item){
            $item['Type'] = 0;
            $item['Hot'] = false;
            foreach($billList as $item2){
                if($item2['ID'] == $item['ID']){
                    $item['Type'] = $item2['Type'];
                    $item['Hot'] = $item2['Hot'];
                }
            }
        }
        !empty($list) && \App\ApiModels\Wangpiao\City::syncData($list);

        //区域
        $districtList = (array) WpApi::getCityDistrict();
        !empty($districtList) && \App\ApiModels\Wangpiao\District::syncData($districtList);

        //商圈
        $tradearea = (array)WpApi::getTradingArea();
        !empty($tradearea) && \App\ApiModels\Wangpiao\TradingArea::syncData($tradearea);

        //地铁
        $subwayList = (array)WpApi::getSubWay();
        !empty($subwayList) && \App\ApiModels\Wangpiao\SubWay::syncData($subwayList);

    }

    /**
     * 即将上映[每天同步一次]
     *
     * @return void
     */
    private function hotFilm(){
        //适用于聚福宝接口
        
        // $apiResult = (array)WpApi::getPlanFilm(); //即将上映            
        // \App\ApiModels\Wangpiao\Film::syncData($apiResult,1);
    }

    /**
     * 同步院线
     *
     * @return void
     */
    private function cinemasBrand(){
        // $apiResult = (array)WpApi::getCinemaLineList();
        // \App\ApiModels\Wangpiao\CinemasBrand::syncData($apiResult);
    }

    /**
     * 同步影厅
     *
     * @return void
     */
    private function syncHalls(){
        
        // $cinemaId = cache('synchalls',false);
        // if(!$cinemaId){
        //     return true;
        // }
        // $apiResult = (array) WpApi::getCinemaHall($cinemaId);
        // \App\ApiModels\Wangpiao\Hall::syncData($apiResult);
    }
    
    
    /**
     * 同步排期数据
     *
     * @return void
     */
    private function syncSchedules(){
        // \App\ApiModels\Wangpiao\Schedules::truncate();
        // \App\ApiModels\Wangpiao\Cinema::all()->map(function ($model){
        //     \App\Jobs\Wangpiao\SchedulesJob::dispatch($model->id);
        // });
    }
    
    
    
    //同步票付通产品增量
    private function syncScenic(){
        $logsClass = '\\App\\UUModels\\UUUpdateLog';
        $lastPage = $logsClass::getEnd();
        $pagesize = $logsClass::$pagesize;
        $api = \App\Support\SoapApi::getInstance();
        
        # 获取景区列表 
        $res = $api->Get_ScenicSpot_List($pagesize,(int)($lastPage/$pagesize));
        \App\UUModels\UUScenicSpot::saveData($res);
        // foreach($res  as $item ) {
        //     $spotInfo = $api->Get_ScenicSpot_Info((int)$item['UUid']);
        //     \App\UUModels\UUScenicSpotInfo::saveData($spotInfo);
        // }
        if(count($res) == $pagesize){
            $logsClass::doStep($lastPage,$lastPage+$pagesize);
        }
    }    

    //同步票付通产品-所有
    private function syncScenicAll(){

    }
    
}
