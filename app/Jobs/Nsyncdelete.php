<?php

namespace App\Jobs;

use App\Models\Newmovie_schedule;
use App\Support\MApi;
use App\Models\Cinema;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
/**
 * 删除排期
 */
class Nsyncdelete implements ShouldQueue
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
        $this->onQueue('xnsyncdelete');
        $this->data = $data;
        logger($data);
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
//            Schedule::saveSchedule($this->data);
            Newmovie_schedule::where('city','=', $this->data)->delete();
        } catch (\Exception $e) {
            Log::error("同步排期数据:{$e->getMessage()},data:".json_encode($this->data,256));
        }
    }
}
