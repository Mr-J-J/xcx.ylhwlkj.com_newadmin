<?php

namespace App\Jobs;

use App\Models\ApiUserOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
/**
 * API接口订单支付成功
 */
class ApiUserOrderPaySuccessJob implements ShouldQueue
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
        $this->onQueue('order');
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(empty($this->data)){
            logger($this->data.'----111同步失败');
            return true;
        }
        try {
            $order = ApiUserOrder::where('order_no',$this->data)->first();
            if($order){
                $order->apiStorePaySuccess();
            }
        } catch (\Exception $e) {
            Log::error("接口订单支付失败:{$e->getMessage()},data:".json_encode($this->data,256));
        }
    }
}
