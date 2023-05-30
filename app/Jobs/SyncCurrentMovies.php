<?php

namespace App\Jobs;

use App\Models\City;
use App\Support\MApi;
use App\Models\JobList;
use App\Models\CurrentMovie;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
/**
 * 热映  即将上映
 */
class SyncCurrentMovies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type; //1即将上映 2热映
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
        // $keys = 'sync_rightnow_film';
        // if($this->type == 2){
        //     $keys = 'sync_hot_film';
        // }
        // $jobSetting = JobList::where('names',$keys)->first();
        // $nexttime = time();        
        // if($jobSetting->next_time > 0 ){
        //     if($jobSetting->last_time == 0){
        //         $jobSetting->last_time = time();
        //     }
        //     $nexttime = strtotime("+ {$jobSetting->next_time} day",$jobSetting->last_time);
        //     if(time() < $nexttime){
        //          Log::info(date('Y-m-d H:i:s',$nexttime).'还没到热映  即将上映时间');
        //         return;
        //     }
        // }
        // $cities = City::all();
        // $i = 1;
        // foreach($cities as $city){
        //     $list = MApi::currentFilmList($city->city_code,$this->type);
        //     if(empty($list)){
        //         continue;
        //     }
        //     foreach($list as $item){
        //         if($item['id'] == 0){
        //             $item['id'] = $i;
        //             $i++;
        //         }
        //         $item['data_type'] = $this->type;
        //         CurrentMovie::saveFilms($item);
        //     }
        // }

        // $jobSetting->last_time = $nexttime;
        // $jobSetting->save();
        // Log::info(date('Y-m-d H:i:s',$nexttime).'同步了热映电影数据');


        try {
            CurrentMovie::saveFilms($this->data);
        } catch (\Exception $e) {
            Log::error("同步热映、即将上映:{$e->getMessage()},data:".json_encode($this->data,256));
        }
    }
}
