<?php
namespace App\CardModels;

use App\Models\TicketUser;
use App\Support\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * 影旅卡批次
 */
class OlCardBatch extends Model
{
    use SoftDeletes;
    protected $table = 'ol_card_batch';
    protected $hidden = ['updated_at'];
    protected $fillable = ['title','number'];

    // static function createBatch($number,$title = ''){
    //     if(!$number) return false;
    //     $title = $title?:'影城卡(线下卡)- '.date('Y-m-d');
    //     return OlCardBatch::create(['title'=>$title,'number'=>$number]);
    // }
    
    public function batchCreateCard(){
        $batchList = array();
        $nowdate = date('Y-m-d H:i:s');
        $model = new OlCard;
        $initId = 668800000;
        $maxid = $model->withTrashed()->max('id');
        $product = OlCardProduct::where('id',$this->product_id)->first();    
        if($this->number > 6000){
            $this->number = 6000;
            $this->save();
        }
        for($i=0;$i<$this->number;$i++){
            $no = 'YLH'.($initId+$maxid+$i+1);
            $key = $model->createKey();
            $card = array(
                'batch_id'=>$this->id,
                'card_no'=>$no,
                'card_key'=>$key,
                'product_id'=>$this->product_id,
                'type'=>2,
                'open_time'=>0,
                'brand_ids'=>implode(',',$product->rules->brand_ids),
                'cinema_ids'=>implode(',',$product->rules->cinema_ids),
                'start_time'=>strtotime($product->check_start),
                'expire_time'=>strtotime($product->check_end),
                'number' => $product->rules->number,
                'created_at'=>$nowdate,
                'updated_at'=>$nowdate
            );
            $batchList[] = $card;
        }
        OlCard::insert($batchList);
    }



    public function batchCreateRsCard(){
        $batchList = array();
        $nowdate = date('Y-m-d H:i:s');
        $model = new RsOlCard;
        $initId = 626200000;
        $maxid = $model->withTrashed()->max('id');
        // $product = OlCardProduct::where('id',$this->product_id)->first();
        $cardId = $this->product_id;
        $cardModel = Cards::where('id',$cardId)->first();
        $cardPrice = $cardModel->price;
        CardPrice::getCardPriceById($this->store_id,$cardModel->id,$cardPrice);
        $cardInfo = array(
            'id'=>$cardModel->id,
            'title'=>$cardModel->title,
            'image'=>$cardModel->list_image,
            'number'=>1,
            'price'=>$cardPrice,
            'card_money'=>$cardModel->card_money
        );
        if($this->number > 6000){
            $this->number = 6000;
            $this->save();
        }
        for($i=0;$i<$this->number;$i++){
            $no = 'YLK'.($initId+$maxid+$i+1);
            $key = $model->createKey();
            $card = array(
                'batch_id'=>$this->id,
                'card_no'=>$no,
                'card_key'=>$key,
                'store_id'=>$this->store_id,
                'rs_card_id'=>$cardId,
                'type'=>2,
                'card_money'=>$cardInfo['card_money'],
                'card_info'=>json_encode($cardInfo,256),
                'title'=>$cardModel->title,
                'open_time'=>0,
                'brand_ids'=>'',
                'cinema_ids'=>'',
                'start_time'=>0,
                'expire_time'=>0,
                'number' => 1,
                'created_at'=>$nowdate,
                'updated_at'=>$nowdate
            );
            $batchList[] = $card;
        }
        RsOlCard::insert($batchList);
    }
    
    public function store(){
        return $this->belongsTo('App\CardModels\RsStores','store_id','id');
    }

    public function product(){
        return $this->hasOne('App\CardModels\Cards','id','product_id');
    }
}
