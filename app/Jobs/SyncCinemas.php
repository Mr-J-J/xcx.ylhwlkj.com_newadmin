<?php

namespace App\Jobs;

use App\Models\City;
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
 * 同步影院列表
 */
class SyncCinemas implements ShouldQueue
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
        Log::error("同步影院:data:".json_encode($this->data,256));
        try {
            Cinema::saveCinema($this->data);
        } catch (\Exception $e) {
            Log::error("同步影院:{$e->getMessage()},data:".json_encode($this->data,256));
        }
    }
}
