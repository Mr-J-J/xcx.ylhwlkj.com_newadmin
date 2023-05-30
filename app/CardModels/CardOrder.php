<?php
namespace App\CardModels;
use App\Support\Helpers;
use App\Models\TicketUser;
use Illuminate\Support\Facades\DB;
use EasyWeChat\Kernel\Messages\Card;
use Illuminate\Database\Eloquent\Model;
/**
 * 影旅卡订单
 */
class CardOrder extends Model
{
    const CANCEL = 0;
    const NOPAY = 10;
    const SUCCESS = 20;
    static $status = [
        self::CANCEL=>'已取消',
        self::NOPAY=>'待付款',
        self::SUCCESS=>'已付款'
    ];
    protected $table = 'rs_card_order';
    protected $fillable = ['order_sn','user_id','com_id','card_id','number','order_amount','mobile','order_status','expire_time','transaction_id','card_info','card_money','remark'];

    /**
     * 支付成功
     *
     * @param [type] $params
     * @return void
     */
    public function paySuccess($params = []){
        $order =$this;
        if($order->order_status != self::NOPAY){  //不在待付款状态
            return false;
        }
        DB::beginTransaction();
        try {
            $order->order_status = self::SUCCESS; //支付成功、待使用
            $order->transaction_id = $params['transaction_id']??'';
            $order->save();
            UserWallet::addMoneyFromOrder($order);
            RsStores::settleOrder($order);
        } catch (\Throwable $th) {
            logger('CardOrder PaySuccess Error:'.$th->getMessage().':'.json_encode($order->toArray()));
            DB::rollBack();
            return false;
            // throw $th;
        }
        DB::commit();
        return true;
    }
    /**
     * 创建订单
     *
     * @param TicketUser $user
     * @param integer $comId
     * @param Cards $card
     * @param boolean $isFirstTime
     * @return Model
     */
    static function createOrder(TicketUser $user,int $comId,Cards $card,bool $isFirstTime = true){
        $cardPrice = $card->price;

//        CardPrice::getCardPriceById($comId,$card->id,$cardPrice);
        // if($cardPrice <= 0){
        //     Helpers::exception('影旅卡已暂停销售');
        // }

        if($cardPrice == 0){
            $lastTime = CardOrder::where('user_id',$user->id)->latest()->value("created_at");
            if($lastTime){
                $diff = time()-strtotime($lastTime);
                if($diff < 10){
                    Helpers::exception('操作太频繁!');
                }
            }
        }

        $cardInfo = array(
            'id'=>$card->id,
            'title'=>$card->title,
            'image'=>$card->list_image,
            'number'=>1,
            'price'=>$cardPrice,
            'card_money'=>$card->card_money
        );

        $delay = (int)self::getDelayTime('order_pay_ttl');
        $data = array(
            'order_sn'       => Helpers::makeOrderNo('YL'),
            'user_id'        => $user->id,
            'com_id'         => $comId,
            'card_id'        => $card->id,
            'order_amount'   => $cardPrice,
            'mobile'         => $user->mobile,
            'number'         => 1,
            'card_money'     => $card->card_money,
            'card_info'      => json_encode($cardInfo,256),
            'order_status'   => self::NOPAY,
            'expire_time'    => time() + $delay,
            'transaction_id' => '',
            'remark'=>'购卡充值'
        );

        $order = CardOrder::create($data);

        if($cardPrice == 0){ // 支付金额为0,直接支付成功
            $order->paySuccess();
        }
        return $order;
    }


    /**
     * 创建订单[影旅卡激活专用]
     *
     * @param TicketUser $user
     * @param integer $comId
     * @param Cards $card
     * @param boolean $isFirstTime
     * @return Model
     */
    static function createOrderV2(TicketUser $user,int $comId,$cardInfo,$remark=''){
        // $cardInfo = array(
        //     'id'=>$card->id,
        //     'title'=>$card->title,
        //     'image'=>$card->list_image,
        //     'number'=>1,
        //     'price'=>$cardPrice,
        //     'card_money'=>$card->card_money
        // );
        $delay = (int)self::getDelayTime('order_pay_ttl');
        $data = array(
            'order_sn'       => Helpers::makeOrderNo('YL'),
            'user_id'        => $user->id,
            'com_id'         => $comId,
            'card_id'        => $cardInfo->id,
            'order_amount'   => 0,
            'mobile'         => $user->mobile,
            'number'         => 1,
            'card_money'     => $cardInfo->card_money,
            'card_info'      => json_encode($cardInfo,256),
            'order_status'   => self::NOPAY,
            'expire_time'    => time() + $delay,
            'transaction_id' => '',
            'remark'=>$remark?:'卡密充值'
        );

        $order = CardOrder::create($data);
        $order->paySuccess();
        return $order;
    }


    /**
     * 订单取消
     *
     * @return void
     */
    public function cancelOrder(){
        $this->order_status = CardOrder::CANCEL;
        $this->save();
    }
    /**
     * 是否可以取消
     *
     * @return boolean
     */
    public function canCancel(){
        return $this->order_status == CardOrder::NOPAY;
    }

    static function getOrderByNo($orderNo){
        return self::where('order_sn',$orderNo)->first();
    }
    /**
     * 获取订单超时时间
     *
     * @param [type] $key
     * @return void
     */
    static function getDelayTime($key){
        $ttl = (int)Helpers::getSetting($key);//分钟
        return $ttl * 60;
    }
}
