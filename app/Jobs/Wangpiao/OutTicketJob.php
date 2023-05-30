<?php
namespace App\Jobs\Wangpiao;


use App\Http\Controllers\NApiController;
use App\Models\ApiOrders;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
/**
 * 网票网自动出票
 */
class OutTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    public $timeout = 15;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ApiOrders $order)
    {

        $this->onQueue('outticket');
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(empty($this->order)){
            return false;
        }
        if($this->order->state == 3) {
            return true;
        }
        try {
            $setting = Setting::getSettings();
            if($setting['jiekoufang']['content']!=1){
                if($this->order->rtimes == 0){
                    logger('购票任务');
                    $this->order->buyTicket();
                }
            }

            logger('查询任务');
            $this->order->searchOrder();
        } catch (\Throwable $th) {
            logger('任务执行失败2'.$th->getMessage());
        }
    }



}
