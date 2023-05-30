<?php

namespace App\Jobs;

use App\Models\Movie;
use App\Support\MApi;
use App\Models\Cinema;
use App\Models\JobList;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
/**
 * 同步影片
 */
class SyncMovies implements ShouldQueue
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
        // $jobSetting = JobList::where('names','sync_movies')->first();
        // $nexttime = time();        
        // if($jobSetting->next_time > 0 ){
        //     if($jobSetting->last_time == 0){
        //         $jobSetting->last_time = time();
        //     }
        //     $nexttime = strtotime("+ {$jobSetting->next_time} day",$jobSetting->last_time);
        //     if(time() < $nexttime){
        //          Log::info(date('Y-m-d H:i:s',$nexttime).'还没到同步影片时间');
        //         return;
        //     }
        // }
        // $allCinemas = Cinema::all();

        // foreach($allCinemas as $item){ 
        //     $list = MApi::filmList($item->id);
        //     if(empty($list)){
        //         continue;
        //     }
        //     foreach($list as $film){
        //         $film['cinema_id'] = $item->id;
        //         Movie::saveFilms($film);
        //     }
        // }


        try {
            Movie::saveFilms($this->data);
        } catch (\Exception $e) {
            Log::error("同步电影数据:{$e->getMessage()},data:".json_encode($this->data,256));
        }
    }
}
