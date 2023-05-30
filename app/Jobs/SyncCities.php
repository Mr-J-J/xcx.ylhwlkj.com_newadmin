<?php

namespace App\Jobs;

use App\Models\City;
use App\Support\MApi;
use App\Models\JobList;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
/**
 * 同步城市
 */
class SyncCities implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->onQueue('syncdata');
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        // $jobSetting = JobList::where('names','sync_citys')->first();
        // $nexttime = time();
        // if($jobSetting->next_time > 0 ){
        //     if($jobSetting->last_time == 0){
        //         $jobSetting->last_time = time();
        //     }
        //     $nexttime = strtotime("+ {$jobSetting->next_time} day",$jobSetting->last_time);
        //     if(time() < $nexttime){
        //          Log::info(date('Y-m-d H:i:s',$nexttime).'还没到同步城市时间');
        //         return;
        //     }
        // }
                
        // $list = MApi::getCityList();
        
        // foreach($list as $city){
            // City::saveCitys($this->data);
        // }

        // $jobSetting->last_time = $nexttime;
        // $jobSetting->save();
        // Log::info(date('Y-m-d H:i:s',$nexttime).'同步了城市数据');
        try {
            City::saveCitys($this->data);
        } catch (\Exception $e) {
            Log::error("同步城市:{$e->getMessage()},data:".json_encode($this->data,256));
        }
    }
}
