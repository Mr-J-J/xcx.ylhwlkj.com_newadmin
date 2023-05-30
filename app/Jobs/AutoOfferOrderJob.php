<?php
namespace App\Jobs;


use App\Models\store\OfferServices;
use Illuminate\Bus\Queueable;

use App\Models\store\StoreOfferOrder;
use App\Models\StoreInfo;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AutoOfferOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $storeInfo;
    protected $order;
    
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(StoreOfferOrder $order,StoreInfo $storeInfo)
    {
        $this->onQueue('order');
        $this->storeInfo = $storeInfo;
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        
        try {
            $services = new OfferServices;
            // logger('自动报价启动');
            $services->AutoOfferOrder($this->order,$this->storeInfo);            
        } catch (\Exception $e) {
            logger($e->getMessage());
            $this->delete();
            $this->fail($e->getMessage());            
        }
    }
    

}