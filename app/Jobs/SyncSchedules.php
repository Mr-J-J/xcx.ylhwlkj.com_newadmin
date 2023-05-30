<?php

namespace App\Jobs;

use App\Support\MApi;
use App\Models\Cinema;
use App\Models\JobList;
use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
/**
 * 排期同步
 */
class SyncSchedules implements ShouldQueue
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
        // $jobSetting = JobList::where('names','sync_paiqi')->first();
        // $nexttime = time();        
        // if($jobSetting->next_time > 0 ){
        //     if($jobSetting->last_time == 0){
        //         $jobSetting->last_time = time();
        //     }
        //     $nexttime = strtotime("+ {$jobSetting->next_time} day",$jobSetting->last_time);
        //     if(time() < $nexttime){
        //          Log::info(date('Y-m-d H:i:s',$nexttime).'还没到排期同步时间');
        //         return;
        //     }
        // }
        // $allCinemas = Cinema::all();

        // foreach($allCinemas as $item){  
        //     $lastkey = '';
            
        //     while(true){
        //         $list = MApi::filmPaiqiList($item->id,'',$lastkey);
        //         if(empty($list['data'])){
        //             break;
        //         }
        //         Log::debug($item->cinema_name);
        //         foreach($list['data'] as $pq){
        //             // $pq['cinema_id'] = $item->id;
        //             Schedule::saveSchedule($pq);
        //         }
        //         $lastkey = $list['last_key'];
        //         if(empty($lastkey)){
        //             break;
        //         }
        //     }
        // }


        // $jobSetting->last_time = $nexttime;
        // $jobSetting->save();
        // Log::info(date('Y-m-d H:i:s',$nexttime).'同步了排期数据');

        try {
            Schedule::saveSchedule($this->data);
        } catch (\Exception $e) {
            Log::error("同步排期数据:{$e->getMessage()},data:".json_encode($this->data,256));
        }
    }
}
