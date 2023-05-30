<?php

namespace App\Jobs;

use App\Models\UserOrder;
use Illuminate\Bus\Queueable;
use App\Models\store\StoreOfferOrder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
/**
 * 异步创建报价订单
 */
class CreateOfferOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderNo)
    {
        $this->order = UserOrder::where('order_no',$orderNo)->first();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::debug("message".$this->order);
            StoreOfferOrder::createOfferOrder($this->order);
        } catch (\Exception $e) {
            Log::debug("message".$this->order);
        }
    }
}
