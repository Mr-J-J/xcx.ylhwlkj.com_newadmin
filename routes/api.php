<?php

use App\Support\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// header('Access-Control-Allow-Origin:*');

//商家端
Route::get('stores/getcode', 'H5\LoginController@getCode');
Route::post('stores/login', 'H5\LoginController@doLogin');

Route::group(['namespace'=>"H5",'middleware'=>'auth.stores'],function() {
    //商家
    Route::post('stores/info','StoreController@info');
    Route::post('stores/nopop','StoreController@nopop');
    Route::post('stores/register','StoreController@register');
    Route::post('stores/mode','StoreController@mode');
    Route::post('stores/edit','StoreController@editStoreInfo');

    //报价规则
    Route::post('stores/offer_rule','OfferController@addRules');
    Route::get('stores/rule_list','OfferController@ruleList');


    Route::post('stores/create_offer','OfferController@addOffer');
    Route::post('stores/cancel_offer','OfferController@cancelOffer');

    //提现
    Route::get('stores/withdraw_list','WithdrawController@lists');
    Route::post('stores/apply-withdraw','WithdrawController@applyWithdraw');



    Route::get('stores/order_list','OrderController@index');
    Route::get('stores/order_info','OrderController@info');
    Route::post('stores/create_ticket','OrderController@insertTicket');
    Route::post('stores/out_ticket','OrderController@confirmOutTicket');
    Route::post('stores/out_ticket_one','OrderController@confirmOutTicketOne');
    Route::post('stores/ignore_order','OrderController@ignoreOrder');
    Route::post('stores/release-seat','OrderController@releaseSeat'); //释放锁座
    Route::post('stores/backout','OrderController@backOutOrder'); //释放锁座


    //意见反馈
    Route::post('stores/suggestion','StoreController@suggestion');

    // 图片上传
    Route::post('stores/upload','StoreController@upload');


    //测试

    Route::post('stores/zhongbiao','TestController@chooseStore');

});
 Route::post('stores/test','H5\TestController@index');
//  Route::post('apiRoute/{route}','ApiController@apiRoute');


//小程序
Route::post('user/login', 'MiniPro\LoginController@doLogin');
Route::post('user/wxlogin', 'MiniPro\LoginController@wxLogin');
Route::any('insechedule', 'NApiController@insechedule');
//支付回调

Route::any('user/notify/{comId?}', 'MiniPro\NotifyController@index')->name('notify');
Route::any('user/refund-notify/{comId?}', 'MiniPro\NotifyController@refundBackNotify')->name('refundnotify');
Route::any('user/mall-refund-notify/{comId?}', 'MiniPro\NotifyController@mallRefundNotify')->name('mallrefundnotify');
Route::any('mall/mallNotify/{comId?}', 'MiniPro\NotifyController@mallNotify')->name('mallnotify');
Route::any('card/cardNotify/{comId?}', 'MiniPro\NotifyController@cardNotify')->name('cardnotify');
Route::any('user/pwNotify', 'MiniPro\NotifyController@pwNotify')->name('pwnotify'); //票务支付成功回调
Route::any('user/pwRefundNotify', 'MiniPro\NotifyController@pwRefundNotify')->name('pwrefundnotify');

//票付通订单回调通知
Route::any('pw/pwOrderNotify', 'MiniPro\NotifyController@pwOrderNotify')->name('pwordernotify');
//核销通知
Route::any('pw/pwOutTicketNotify', 'MiniPro\NotifyController@pwOutTicketNotify')->name('pwOutTicketNotify');
//票付通产品变更
Route::any('pw/pwProductNotify', 'MiniPro\NotifyController@pwProductNotify')->name('pwProductNotify');

Route::any('order/jufubao_order', 'MiniPro\NotifyController@jufubao_order')->name('jufubao_order_notify'); //聚福宝出票通知

Route::namespace('MiniPro')->middleware('auth.users')->group(function() {

    Route::get('user/info', 'UserController@index');
    Route::get('user/getavatar', 'UserController@getavatar');
    Route::get('user/groupList','UserController@groupList');
    Route::get('user/fans', 'UserController@fans');
    Route::get('user/commision', 'UserController@commision');
    Route::get('user/draw_list', 'UserController@drawList');
    Route::get('user/apply-withdraw','UserController@applyWithDraw');
    Route::get('user/formid','UserController@saveFormId');
    Route::post('user/qrcode','UserController@myCode');
    Route::get('user/posterList','UserController@posterList');
    Route::post('user/edit','UserController@updateInfo');    //个人资料
    Route::post('user/edit_phone','UserController@updateMobile');    //手机号修改

    //订单
    Route::post('user/confirm_order', 'OrderController@confirmOrder');

    Route::post('user/create_order', 'OrderController@addOrder');
    Route::post('user/pay_order', 'OrderController@payOrder');
    Route::get('user/order_list', 'OrderController@index');
    Route::get('user/order_info', 'OrderController@info');
    Route::get('user/cancel_order', 'OrderController@cancelOrder');

    // 图片上传
    Route::post('user/upload','UserController@upload');
    //意见反馈
    Route::post('user/suggestion','UserController@suggestion');

    //吃喝玩乐
    Route::post('mall/orderStatistics','MallOrderController@orderStatistics');
    Route::post('mall/orderList','MallOrderController@orderList');
    Route::post('mall/orderInfo','MallOrderController@orderInfo');
    Route::post('mall/confirmOrder','MallOrderController@confirmOrder');
    Route::post('mall/createOrder','MallOrderController@createOrder');
    Route::post('mall/payOrder','MallOrderController@payOrder');
    Route::post('mall/cancelOrder','MallOrderController@cancelOrder');
    Route::post('mall/refundOrder','MallOrderController@refundOrder');
    Route::post('mall/orderComment','MallOrderController@orderComment');

    //卡券核销
    Route::post('mall/orderCheck','MallStoreController@orderCheck');
    Route::post('mall/checkList','MallStoreController@checkList');
    Route::post('mall/checkStatistics','MallStoreController@checkStatistics');
    Route::get('mall/checkProductList','MallStoreController@checkProductList');
    Route::post('mall/storeAccount','MallStoreController@storeAccount');
    Route::post('mall/settleList','MallStoreController@settleList');

    //影城卡
    Route::post('mall/activeCard','OlCardOrderController@activeCard');
    Route::post('mall/lookCard','OlCardOrderController@lookcard');
    Route::post('mall/myOlCard','OlCardOrderController@myOlCard');
    Route::post('mall/cardTips','OlCardOrderController@cardTips');
    Route::post('mall/olcard/orderlist','OlCardOrderController@orderList');
    Route::post('mall/olcard/orderInfo','OlCardOrderController@orderInfo');

    //影旅卡
    Route::post('card/createOrder','CardOrderController@createOrder');
    Route::post('card/orderList','CardOrderController@orderList');
    Route::post('card/orderInfo','CardOrderController@orderInfo');
    Route::post('card/payOrder','CardOrderController@payOrder');
    Route::post('card/cancelOrder','CardOrderController@cancelOrder');

    Route::post('card/myCardList','CardOrderController@myCardList'); //我的影旅卡
    Route::post('card/myCardListt','CardOrderController@myCardListt'); //我的影旅卡
    Route::post('card/purchaseRecords','CardOrderController@purchaseRecords'); //消费记录
    Route::post('card/sendCard','CardOrderController@sendCard'); //赠卡
    Route::post('card/cancelSendCard','CardOrderController@cancelSendCard'); //取消赠卡
    Route::post('card/sendDetail','CardOrderController@sendDetail'); //赠卡详情
    Route::post('card/applyCard','CardOrderController@applyCard'); //领取赠卡
    Route::post('card/activeCard','CardOrderController@activeCard');  //影旅卡激活
    Route::post('card/freeget','CardOrderController@freeget');  //影旅卡免费领取

    //票付通
    Route::post('pw_pre_order','PftController@pw_pre_order');
    Route::post('pw_get_payorder','PftController@pw_get_payorder'); //合并订单待支付
    Route::post('pw_pay_order','PftController@pw_pay_order'); //合并订单支付
    Route::post('pw_order_list','PftController@pw_order_list'); //
    Route::post('pw_order_info','PftController@pw_order_info'); //
    Route::post('pw_order_refund','PftController@pw_order_refund'); //

});

Route::get('ceshi','NApiController@ceshi');
Route::get('film_info','ApiController@filmInfo');
Route::get('paiqi_info','ApiController@schedulesDetail');
Route::get('paiqi_list','ApiController@schedulesList');
Route::get('search_film','ApiController@searchFilm');
Route::get('cinemas_film','ApiController@getFilmWithCinema');
Route::get('cinemas','ApiController@cinemaList');
Route::get('film_cinema_list','ApiController@getCinemaWithFilm');
Route::get('cinemas_brand','ApiController@cinemaBrand');
Route::get('current_film','ApiController@currentFilmList');
Route::get('areas','ApiController@cityAreaList');
Route::get('cityinfo','ApiController@getCityInfo');
Route::get('carousel','ApiController@carousel');
Route::get('agreement','ApiController@agreement');
Route::get('showqrcode','ApiController@showqrcode');

//票务
Route::post('pw_type','PwApiController@pw_type');
Route::post('pw_plist','PwApiController@pw_plist');
Route::post('pw_pdetail','PwApiController@pw_pdetail');
Route::post('pw_calendar_price','PwApiController@pw_calendar_price');  //日期价格
Route::post('pw_storage_price','PwApiController@pw_storage_price');//实时库存
Route::post('pw_ticket_list','PwApiController@pw_ticket_list');


//吃喝玩乐
Route::prefix('mall')->group(function($router){
    $router->get('categoryList','MallController@categoryList');
    $router->get('activeList','MallController@activeList');
    $router->get('productList','MallController@productList');
    $router->get('productDetail','MallController@productDetail');
    $router->get('productComment','MallController@productComment');
    $router->get('commentDetail','MallController@commentDetail');
    $router->get('storeRegister','MallController@storeRegister');

});

//影旅卡
Route::prefix('card')->group(function($router){
    $router->get('cardList','CardController@cardList');
});


