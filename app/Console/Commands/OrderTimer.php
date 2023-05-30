<?php

namespace App\Console\Commands;

use App\CardModels\StoreBalanceDetail;
use App\MallModels\Order;
use App\Models\UserOrder;
use App\Models\UserPayDetail;
use App\Models\TaskList;
use Illuminate\Console\Command;
use App\Models\store\StoreCheckOut;
use App\Models\user\OrderCommision;
use App\Models\store\StoreOfferOrder;

use Illuminate\Support\Facades\Artisan;

class OrderTimer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ordertimer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '订单过期定时';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //待支付的过期//待出票的过期
        $nowtime = time();
        $orderlist = UserOrder::whereIn('order_status',[10])->where('expire_time','<=',$nowtime)->orderBy('expire_time')->get();
        foreach($orderlist as $order){
            if(!empty($order)){
                \App\Jobs\UpdateOrder::dispatch($order);
            }
        }
        
        //竞价中过期//出票过期
        $offerList = StoreOfferOrder::whereIn('offer_status',[0,1])->where('expire_time','<=',$nowtime)->orderBy('expire_time')->get();
        foreach($offerList as $item){
            // logger('storeOfferOrder：定时');
            $item->closeOrder();
        }

        //商城订单过期
       try {
        $mallOrderList = Order::getExpireOrder();        
        foreach($mallOrderList as $item){
            $item->cancelOrder();
        }        
       } catch (\Throwable $th) {
           logger('订单过期检查失败:'.$th->getMessage());
       }
        
        //佣金结算
        $commisionList = OrderCommision::waitCheckOut();
        foreach($commisionList as $item){
            $item->doCheckOut();
        }
        //用户等级升级
        UserPayDetail::detailDealTask();
        
        //商家结算
        $checkOutList = StoreCheckOut::where('state',0)->get();
        foreach($checkOutList as $item){
            $item->doCheckOut();
        }

        //分销商结算
        StoreBalanceDetail::waitSettleList();
        
        //票付通订单超时取消
        $pwList = \App\UUModels\UUPayOrder::where('pay_status','<',2)->where('expire_time','<',$nowtime)->get();
        foreach($pwList as $item){
            $item->cancelOrder();
        }
        //同步任务
        $task = new TaskList;
        $taskList = $task->getTaskList();
        $allowdo = cache('allow_do',false);
        if(!$allowdo){
            cache(['allow_do'=>true]);
            $taskList->each(function($item){
                Artisan::call($item->task_command);
                $item->state = 1;
                $item->save();
            });
            cache(['allow_do'=>false]);
        }
    }
}
