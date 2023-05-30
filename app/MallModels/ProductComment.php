<?php

namespace App\MallModels;

use App\Support\Helpers;
use App\Models\TicketUser;
use Illuminate\Database\Eloquent\Model;

/**
 * 商品评价
 */
class ProductComment extends Model
{
    protected $table = 'mall_product_comments';

    /**
     * 用户评价
     *
     * @param TicketUser $user
     * @param Order $order
     * @param array $data
     * @return ProductComment
     */
    static function creatComment(TicketUser $user,Order $order,array $data){        
        extract($data);
        if(empty($rate) || $rate < 1){
            Helpers::exception('请评分');
        }
        if(empty($content)){
            Helpers::exception('请填写评价内容');
        }
        if(empty($images)){
            $images = '';
        }
        if(!empty($images)){
            $imgs = explode(',',$images);
            if(count($imgs) > 5){
                Helpers::exception('最多上传5张图片');
            }
        }

        $pc = new self;
        $pc->user_id = $user->id;
        $pc->order_id = $order->id;
        $pc->product_id = $order->product_id;
        $pc->rate = (int)$rate;
        $pc->rate_txt = $rate_txt ??'';
        $pc->content = mb_substr(trim($content),0,400);
        $pc->images = $images;        
        $pc->save();
        $order->commentComplate();//订单完成
        return $pc;
    }

    /**
     * 评价列表 
     *
     * @param integer $product_id
     * @param integer $user_id
     * @return void
     */
    static function getCommentList(int $product_id , int $user_id = 0){
        $limit = request('limit',10);
        $list = ProductComment::where('product_id',$product_id)
                ->orderBy('created_at','desc')
                ->paginate((int)$limit)
                ->makeHidden(['updated_at','user']);        
        foreach($list as $item){
            $user = $item->user;         
            $item->avatar = $user->avatar;
            $item->nickname = $user->nickname;
            // $item->images = empty($item->images)?array():explode(',',$item->images);
        }
        return $list;
    }

    public function user(){
        return $this->belongsTo('App\Models\TicketUser','user_id','id');
    }

    public function getImagesAttribute($value){
        if(!empty($value)){
            return explode(',',$value);
        }
        return $value;
    }
}
