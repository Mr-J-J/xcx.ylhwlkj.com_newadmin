<?php
namespace App\Jobs\Wangpiao;

use App\ApiModels\Wangpiao\Cinema;
use App\Support\WpApi;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HallsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $cinema;
    protected $title;

    protected $model;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Cinema $cinema)
    {
        
        $this->onQueue('syncdata');
        $this->cinema = $cinema;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {               
        if(!$this->cinema) return;
        // if($this->cinema->is_sync_hall) return;
        
        try {
            $apiResult =(array) WpApi::getCinemaHall($this->cinema->id);
            
            \App\ApiModels\Wangpiao\Hall::syncData($apiResult,$this->cinema->id);
        } catch (\Throwable $th) {
            //throw $th;
            logger($this->cinema->id.'影厅同步失败');
            return;
        }
        // $this->cinema->update(['is_sync_hall'=>1]);
    }
    


}