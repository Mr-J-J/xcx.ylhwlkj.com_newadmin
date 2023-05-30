<?php
namespace App\Jobs\Wangpiao;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

abstract class AbstractRedisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    protected $title;

    protected $model;
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
        try {
            $this->model::saveData($this->data);
        } catch (\Exception $e) {      
            $this->logger($e);      
            $this->delete();
            $this->fail($e->getMessage());
            
        }
    }

    public function getTitle(){
        return $this->title;
    }

    public function logger($e){

        Log::error("{$this->getTitle()}:{$e->getMessage()},data:".json_encode($this->data,JSON_UNESCAPED_UNICODE));
        
    }


}