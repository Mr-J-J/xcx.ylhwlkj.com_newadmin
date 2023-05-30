<?php
namespace App\Jobs;


use App\Models\store\OfferServices;
use Illuminate\Bus\Queueable;

use App\Models\store\StoreOfferOrder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PushStoreMsgJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $storeOpenId;
    protected $order;
    protected $nickname;
    protected $mobile;
    protected $msgTemp;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(StoreOfferOrder $order,$storeOpenId,$userNickName,$userMobile,$templet = 'pushWechatMsg')
    {
        $this->onQueue('pushmessage');
        $this->storeOpenId = $storeOpenId;
        $this->order = $order;
        $this->nickname = $userNickName;
        $this->mobile = $userMobile;
        $this->msgTemp = $templet;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $msgFunc = $this->msgTemp;
            $services = new OfferServices;
            $services->$msgFunc($this->storeOpenId,$this->order,$this->nickname,$this->mobile);
            $orderNo = $this->order->order_no;
            // logger("模板消息发送：{$this->storeOpenId},{$orderNo}");
        } catch (\Exception $e) {
            logger($e->getMessage());
            $this->delete();
            $this->fail($e->getMessage());
        }
    }


}
