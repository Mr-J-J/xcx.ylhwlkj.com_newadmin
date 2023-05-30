<?php

namespace App\Jobs;

use App\Models\UserOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    protected $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(UserOrder $order)
    {
        $this->onQueue('order');
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        logger($this->order->order_no . '订单过期updateorder');
        if($this->order->expire_time <= time()){
            if($this->order->order_status == 10){
                $this->order->cancelOrder();
                // $this->order->cancelOrder($this->order);
            }elseif($this->order->order_status == 20){
               // $this->order->refundOrder($this->order);
            }
        }
    }
}
