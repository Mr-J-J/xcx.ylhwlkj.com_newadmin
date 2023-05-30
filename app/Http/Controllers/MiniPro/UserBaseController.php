<?php



namespace App\Http\Controllers\MiniPro;

use App\Support\Helpers;
use App\CardModels\RsStores;
use Illuminate\Http\Request;

use EasyWeChat\Factory;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;



class UserBaseController extends Controller

{

    protected $user;

    protected $wxapp;



    public function __construct(){

        parent::__construct();

        $this->user = Auth::guard('users')->user(); 

        if($this->user && $this->user->id == 1){

            // $this->user = \App\Models\TicketUser::where('id',1)->first();

        }

        // $config = config('wechat.mini_program.default');

        // $this->wxapp = Factory::miniProgram($config);
        $comId = (int)request('com_id',0);
        $this->wxapp = $this->getApp(1,$comId);
        
        if($comId){
            $storeInfo = RsStores::getStoreInfo($comId);
            if(empty($storeInfo)){
                Helpers::exception('商户信息不存在');
            }
        }

    }

}

