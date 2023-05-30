<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Helpers;

use App\CardModels\Cards;
use App\CardModels\RsStores;
use Illuminate\Http\Request;
use App\CardModels\CardPrice;
use App\CardModels\CardSend;
use App\CardModels\CardSetting;
use App\CardModels\CardsGetLogs;
use EasyWeChat\Kernel\Messages\Card;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
/**
 * 影旅卡
 */
class CardController extends Controller
{
    protected $comId = 0;
    public function __construct(){
        Setting::getSettings();
        $this->comId = (int)request('com_id',0);
        if(empty($this->comId)){
            // Helpers::exception('');
            throw new HttpResponseException(response()->json(['msg'=>'缺少必要参数']));
        }
    }

    /**
     * 影旅卡列表
     *
     * @return void
     */
    public function cardList( Request $request){
        $list = Cards::getList();
        $priceList = CardPrice::getCardPrice($this->comId);
        $card_seting = CardSetting::getSetting();
        $storeInfo = RsStores::getStoreInfo($this->comId);
        $tips = $card_seting->tips;
        $rules = $card_seting->use_rules;
        $user = Auth::guard('users')->user();

        if($user){
            // $sendModel = new CardSend;
            // $sendModel->freeSendCard($user);
        }
        $getLogsModel = new CardsGetLogs;

        //用户领取记录
        if($storeInfo){
            foreach($list as $item){
                $UserFreeCount = 0;
//                if(!empty($priceList[$item->id])){
//                    $item->price = $priceList[$item->id];
//                }
                $item->price = round($item->price,2);
//                if($item->free_num){
//                    $item->price = 0;
//                }
//                if($user && $item->free_num){
//                    //新人免费领取影旅卡
//                    $UserFreeCount = $getLogsModel->getTodayLogsCount($user->id,$item->id);
//                    if($UserFreeCount < $item->free_num){
//                        $item->price = 0;
//                    }else{
//                        $item->free_num = 0;
//                    }
//                }
                $item->card_money = round($item->card_money);
                $item->market_price = round($item->market_price);
                $item->store_logo = $storeInfo->store_logo;
                $item->store_name = $storeInfo->store_name;
            }
        }
        return $this->success('成功',compact('list','tips','rules'));
    }
}
