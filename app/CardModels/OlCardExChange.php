<?php
namespace App\CardModels;

use App\Models\UserOrder;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

/**
 * 影旅卡批次
 */
class OlCardExChange extends Model
{
    
    protected $table = 'ol_card_exchange';
    protected $hidden = ['updated_at'];
    

    static function getList($cardId){
        return self::where("card_id",$cardId)->get();
    }

    static function createLog(UserOrder $order){
        $model = new OlCardExChange;
        $model->ex_no = Helpers::makeOrderNo('E');
        $model->user_id = $order->user_id;
        $model->order_id = $order->id;
        $model->order_no = $order->order_no;
        $model->card_id = $order->ol_card_id;
        $model->ex_time = date('Y-m-d H:i:s');
        $model->ex_number = $order->ticket_count;
        $model->save();
        return true;
    }


    public function card(){
        return $this->hasOne('App\CardModels\OlCard','id','card_id');
    }
    
    
}
