<?php

namespace App\Admin\Controllers;



use App\Models\Store;
use App\MallModels\Order;
use App\Models\StoreInfo;
use App\Models\UserOrder;
use App\MallModels\Stores;
use App\MallModels\Product;
use App\Models\TicketUser;
use Illuminate\Support\Arr;
use App\CardModels\RsStores;
use App\MallModels\Category;
use Illuminate\Http\Request;
use App\CardModels\CardOrder;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Facades\Admin;
use App\Models\store\StoreLevel;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        $start = request('start','');
        $end = request('end','');
        $whereTime = array();
        if(!empty($start) && !empty($end)){
            $whereTime = [$start,$end];
        }
        //电影票
        $ticketSaleMoney = UserOrder::where('order_status',UserOrder::ORDER_SUCCESS)->where('com_id',0)->sum('amount');//销售总额
        $ticketSaleMoney = round($ticketSaleMoney/100,2);
        $ticketSaleCount = UserOrder::where('order_status',UserOrder::ORDER_SUCCESS)->where('com_id',0)->count('id');//销售总额
        $waitSettleMoney = StoreInfo::sum('freeze_money');//应结
        $waitSettleMoney = round($waitSettleMoney/100,2);
        $ticketStoreSettleMoney = StoreInfo::sum('settle_money');//已结
        $ticketStoreSettleMoney = round($ticketStoreSettleMoney/100,2);
        //销售总额 - 商家应结 - 商家已结
        $ticketProfit = $ticketSaleMoney - $waitSettleMoney - $ticketStoreSettleMoney;

        //商城
        $mallSaleMoney = Order::whereIn('order_status',[Order::SUCCESS,Order::NOUSE])->sum('order_amount');
        $mallWaitSettleMoney = Stores::sum('freeze_money');//应结
        $mallSettleMoney = Stores::sum('settle_money');//已结
        $mallOrderCount = Order::whereIn('order_status',[Order::SUCCESS,Order::NOUSE])->count('id');
        $mallProfit = $mallSaleMoney - $mallWaitSettleMoney - $mallSettleMoney;
        //影旅卡
        $cardSaleMoney = CardOrder::where('order_status',CardOrder::SUCCESS)->sum('order_amount');
        $cardWaitSettleMoney = RsStores::sum('balance'); //应结
        $cardSettleMoney = RsStores::sum('settle_money');
        $cardProfit = $cardSaleMoney - $cardWaitSettleMoney - $cardSettleMoney;


        $profitMoney = $ticketProfit + $mallProfit + $cardProfit;

        $totalMemberProfit = TicketUser::sum('total_balance');

        $memberSettleMoney = TicketUser::sum('balance');
        $memberProfit = $totalMemberProfit - $memberSettleMoney;



        //排行数据
         //会员消费排行
        $memberCostList = TicketUser::select(['id','avatar','nickname','cash_money','created_at'])->orderBy('cash_money','desc')->take(50)->get();
        //粉丝数量
        $inviterNumber = TicketUser::select([DB::raw('count(id) as number'),'inviter_id'])
                                        ->groupBy('inviter_id')
                                        ->having('inviter_id','>',0)
                                        ->orderByRaw('count(id) desc')
                                        ->take(50)
                                        ->pluck('number','inviter_id')
                                        ->toArray();
        $memberIds = array_keys($inviterNumber);
        $memberInviterList = TicketUser::select(['id','avatar','nickname','created_at'])->whereIn('id',$memberIds)->get();


        //商家销售最多的
        $storeCategory = Arr::pluck(Category::getFirstList(),'title','id');
        $mallStoreTop = Stores::select(['store_name','sale_money','category_id'])->orderBy('sale_money','desc')->take(50)->get();

        $outTicketStoreTop = StoreInfo::select(['store_id','out_ticket_count','settle_money'])->orderBy('out_ticket_count','desc')->take(50)->get();
        $storeIds = array_column($outTicketStoreTop->toArray(),'store_id');
        $storeInfos = Store::select(['store_name','id','store_level','store_state'])->whereIn('id',$storeIds)->get();
        $storeInfoList = array();
        foreach($storeInfos as $store){
            if($store->store_state != 2) continue;
            $storeInfoList[$store->id] = $store;
        }
        //$storeOutTicketNumber = array_combine($storeIds,array_values($storeOutTicketNumber));
        $storeLevel = StoreLevel::all()->pluck('title','id');

        $productList = Product::orderBy('sale_num','desc')->take(20)->get();
        return $content
            ->title('数据统计')
            // ->row($form)
            ->row(view('custom.admin.statistics',compact(
                'ticketSaleCount',
                'ticketSaleMoney','waitSettleMoney','ticketStoreSettleMoney','ticketProfit',
                'mallSaleMoney','mallWaitSettleMoney','mallSettleMoney','mallProfit','mallOrderCount',
                'cardSaleMoney','cardWaitSettleMoney','cardSettleMoney','cardProfit',
                'profitMoney',
                'totalMemberProfit','memberProfit',
                'memberCostList','inviterNumber','memberInviterList',
                'storeCategory','mallStoreTop','storeInfoList','storeLevel','outTicketStoreTop',
                'productList'
            )));
    }


    public function region(Request $req){
        $q = $req->input('q','');
        $list = [];
        $list = \App\Models\Region::where('city_level','>',1)
                            ->where('city_name','like',"%{$q}%")
                            ->orderBy('city_code')
                            ->paginate(5,['city_code as id','city_name as text']);
        return response()->json($list);
    }

    public function selectCity(Request $req,$level = 1){
        $q = $req->input('q');
        $regionsList = \App\MallModels\Region::getRegions((int)$q,(int)$level);

        $list = array();
        foreach($regionsList as $item){
            $list[] = array(
                'id'=>$item->city_code,
                'text'=>$item->city_name
            );
        }
        return response()->json($list);
    }
}
