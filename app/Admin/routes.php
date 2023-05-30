<?php
use App\Admin\Forms\Setting;
use Illuminate\Routing\Router;
Admin::routes();
Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {
    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('newcinemas', NcinemaController::class);

    $router->resource('users', UsersController::class);
    $router->resource('msgs', MsgController::class);
    $router->resource('avatars', AvatarController::class);//默认头像列表
    $router->resource('stores', StoresController::class);
    $router->resource('store-levels', StoreLevelController::class);
    $router->resource('user-orders', OrderController::class);
    $router->resource('jfb-orders', JfbOrderController::class);
    $router->resource('user-api-orders', ApiOrderController::class);
    $router->resource('cinemas', CinemaController::class);
    $router->resource('cinemas-brands', BrandController::class);
    $router->resource('carousels', CarouselController::class);
    $router->resource('agreements', AgreementController::class);
    $router->resource('settings', SettingController::class);
    $router->resource('store-checkout', StoreCheckOutController::class);
    $router->resource('store-withdraw', StoreWithdrawController::class);
    $router->resource('user-withdraw', UserWithdrawController::class);
    $router->resource('cities', CityController::class);
    $router->resource('poster', PosterController::class);
    $router->resource('films', FilmController::class);
    $router->resource('suggestions', SuggestionController::class);
    $router->resource('offer-orders', OfferController::class);
    $router->resource('common-orders', CommonOrderController::class);
    $router->resource('teyue',TeyueController::class);
    $router->resource('kefu',KefuController::class);
    $router->resource('kefu_tel',KefuTelController::class);
    $router->resource('user-commision',UserCommisionController::class);
    $router->get('/region','HomeController@region');

    // $router->get('form', Setting::class);
    //吃喝玩乐
    $router->get('selectCity/{level}','HomeController@selectCity'); //省市区
    $router->resource('store-registers', Mall\StoreRegisterController::class); //商家入驻-关于我们
    $router->resource('partners', Mall\PartnerController::class); // 合作伙伴
    $router->resource('products', Mall\ProductController::class); //商品卡券
    $router->resource('categories', Mall\CategoryController::class); //商品分类
    $router->resource('orders', Mall\OrdersController::class); //商城订单
    $router->resource('mall-settle', Mall\SettleController::class); //商城订单
    $router->resource('mall-stores', Mall\MallStoreController::class); //商城商家
    $router->resource('mall-groups', Mall\GroupController::class); //商城商家
    $router->resource('mall-active-list', Mall\ActiveController::class); //商城商家

    //影城卡
    $router->resource('ol-cards-1', Mall\OlCard1Controller::class);
    $router->resource('ol-cards-2', Mall\OlCard2Controller::class);
    $router->resource('ol-card-batches', Mall\BatchCardController::class);
    $router->resource('ol-card-types', Mall\OlCategoryController::class);
    $router->resource('ol-card-goods', Mall\OlCardGoodsController::class);
    $router->resource('ol-card-orders', Mall\OlCardOrdersController::class);
    $router->resource('ol-exchange', Mall\OlCardExchangeController::class);
    $router->get('api/cinemas','CinemaController@getList');
    $router->get('api/cardgoods','Mall\OlCardGoodsController@getList');



    //影旅卡
    $router->resource('card-users', Card\UsersController::class);
    $router->resource('olcard-users', Card\UserOlCardController::class);
    $router->resource('card-settings', Card\SettingController::class);
    $router->resource('cards', Card\CardController::class);
    $router->resource('rs-stores', Card\StoresController::class);
    $router->resource('card-orders', Card\OrderController::class);
    $router->resource('card-settle', Card\SettleController::class);
    $router->resource('rs-store-withdraw', Card\RsStoreWithdrawController::class);
    $router->resource('rs-store-balance', Card\RsStoreBalanceController::class);
        //批量生成

    $router->resource('rs-card-batches', Card\BatchRsCardController::class);
    $router->resource('rs-cards-list', Card\RsOlCard2Controller::class);

    //票付通
    $router->resource('pw-orders', Pft\PwOrdersController::class);
    $router->resource('pw-products', Pft\ScenicSpotController::class);
    $router->resource('pw-products-sale',Pft\ScenicSpotInfoController::class);
    $router->resource('pw-exception-order',Pft\PwExceptionOrdersController::class);
    $router->resource('pw-refund-order',Pft\PwRefundOrdersController::class);
    $router->resource('pw-ticket-order',Pft\PwTicketOrdersController::class);
    //公众号文章
    $router->resource('essays', EssayController::class);
    $router->resource('projects', ProjectController::class);
    $router->resource('talks', TalkController::class);
    $router->resource('rscarousels', RscourselController::class);
    $router->resource('rshaibao', RshaibaoController::class);

});
