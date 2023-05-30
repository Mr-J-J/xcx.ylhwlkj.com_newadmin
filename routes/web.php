<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use Illuminate\Support\Facades\Route;
Route::get('/',function(){
    return view('welcome');
});
Route::post('/stores/dologin', Stores\StoreController::class.'@dologin')->name('login');
Route::post('/stores/doregister', Stores\StoreController::class.'@doregister');
Route::post('/stores/getinfo', Stores\StoreljController::class.'@getinfo');
Route::post('/stores/img', Stores\StoreljController::class.'@img');
Route::post('/stores/haibao', Stores\StoreljController::class.'@myCode');
Route::post('/stores/getdata', Stores\StoreljController::class.'@getdata');
Route::post('/stores/account1', Stores\StoreljController::class.'@account');
Route::post('/stores/getfans', Stores\StoreljController::class.'@getfans');
Route::post('/stores/getwithdrawList', Stores\StoreljController::class.'@withdrawList');
Route::post('/stores/dowithdraw1', Stores\StoreljController::class.'@dowithdraw');
Route::post('/stores/upload', Stores\StoreljController::class.'@upload');
Route::post('/stores/upstore', Stores\StoreljController::class.'@upstore');
Route::post('/stores/getmystore', Stores\StoreljController::class.'@getmystore');
Route::post('/stores/instore', Stores\StoreljController::class.'@instore');
Route::post('/stores/upmystore', Stores\StoreljController::class.'@upmystore');
Route::post('/stores/getessay', Stores\StoreljController::class.'@getessay');
Route::post('/stores/getproject', Stores\StoreljController::class.'@getproject');
Route::post('/stores/gettalk', Stores\StoreljController::class.'@gettalk');
Route::post('/stores/getmsg', Stores\StoreljController::class.'@getmsg');
Route::post('/stores/getmsgnum', Stores\StoreljController::class.'@getmsgnum');
Route::post('/stores/getsetting', Stores\StoreljController::class.'@getsetting');
Route::post('/stores/getcardlist', Stores\StoreljController::class.'@getcardlist');
Route::post('/stores/upcard', Stores\StoreljController::class.'@upcard');
Route::post('/stores/getcarousel', Stores\StoreljController::class.'@getcarousel');
Route::post('/stores/gethaibao', Stores\StoreljController::class.'@gethaibao');

Route::post('/stores/addSuggestion1', Stores\StoreljController::class.'@addSuggestion1');
Route::group(['prefix'=>'stores','middleware'=>['web','rsstore']],function(){
    Route::get('/', Stores\HomeController::class.'@index');
    Route::get('/login', Stores\LoginController::class.'@showLogin')->name('stores.login');
    Route::post('/login', Stores\LoginController::class.'@login');
//    Route::post('/dologin', Stores\LoginController::class.'@dologin');
    Route::post('/getnum',Stores\HomeController::class.'@getnum');
    Route::get('/gongzhong',Stores\HomeController::class.'@gongzhong');
    Route::get('/more',Stores\HomeController::class.'@more');
    Route::get('/yongjin',Stores\HomeController::class.'@yongjin');
    Route::match(['get','post'],'/logout', Stores\LoginController::class.'@logout')->name('stores.logout');
    Route::post('/qrcode',Stores\HomeController::class.'@myCode');
    Route::get('/profile',Stores\HomeController::class.'@profile');
    Route::get('/account',Stores\HomeController::class.'@account');
    Route::get('/settle',Stores\HomeController::class.'@settle');
    Route::get('/withdraw',Stores\HomeController::class.'@withdraw');
    Route::post('/dowithdraw',Stores\HomeController::class.'@dowithdraw');
    Route::get('/withdrawList',Stores\HomeController::class.'@withdrawList');
    Route::get('/card',Stores\HomeController::class.'@card');
    Route::post('/card',Stores\HomeController::class.'@card_handle');
    Route::get('/order',Stores\HomeController::class.'@order');
    Route::get('/orderpiao',Stores\HomeController::class.'@orderpiao');
    Route::get('/member',Stores\HomeController::class.'@member');
    Route::get('/card-detail/{userId}',Stores\HomeController::class.'@card_detail');
    Route::get('/img',Stores\HomeController::class.'@img');
    Route::get('/msg',Stores\HomeController::class.'@msgs');
    Route::get('/ts',Stores\HomeController::class.'@ts');
});
