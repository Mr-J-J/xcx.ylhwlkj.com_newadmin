<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\store\StoreOfferOrder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateOfferOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    protected $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(StoreOfferOrder $order)
    {
        $this->onQueue('order');
        // $this->data = $data;
        $this->order = $order;   
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->order->expire_time <= time()){
            logger($this->order->order_no . '报价过期');
            // $this->order->closeOrder($this->order);
            $this->order->closeOrder();
        }
    }
}
