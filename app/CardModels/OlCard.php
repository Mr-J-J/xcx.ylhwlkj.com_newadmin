<?php
namespace App\CardModels;

use App\Models\TicketUser;
use App\Support\Helpers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * 影旅卡
 */
class OlCard extends Model
{
    use SoftDeletes;
    protected $table = 'ol_card';
//    protected $hidden = ['updated_at','card_key','deleted_at'];
    protected $hidden = ['updated_at','deleted_at'];

    protected $fillable = array(
                'batch_id',
                'card_no',
                'card_key',
                'product_id',
                'type',
                'open_time',
                'expire_time',
                'user_id',
                'number',
                'state',
                );
    static $status = [
        0=> '未启用',
        1=> '已导出',
        2=> '已启用',
        10=> '已激活',
        20=>'已过期'
    ];


    /*
     * 获取绑定卡订单
     *
     * @params product_id 商品类型id
     * @params number 卡数量
     */
    static public function getCardByProduct($product_id,$number,$orderNo)
    {
        //查询$orderNo
        $order = OlCard::where('orderno',$orderNo)->get();
        //如果查询不到信息则
        if(count($order)==0){
            $list = OlCard::where('product_id', $product_id)
                ->where('state', 2)
//                不等于
//                ->where('orderno','!=',null)
                ->limit($number)
                ->get();
            //如果查不到至少一条消息则返回'卡库存不足，请稍等或联系管理员扩充库存'
            if((count($list) < $number)&&$number!=0){
                return '卡库存不足，请稍等或联系管理员扩充库存';
            }
            //将数据库中包含list的数据orderno改为$orderNo
            OlCard::whereIn('id', $list->pluck('id'))->update(['orderno' => $orderNo]);
            return $list;
        }else{
            return $order;
        }
    }
    /**
     * 影城卡列表
     *
     * @param [type] $userId
     * @return void
     */
    static function getCardList($userId,string $brand_id = '' ,int $cinema_id = 0){
        $list = OlCard::where('user_id',$userId)
                    ->whereRaw('number > use_number')
                    ->where(function($query) use ($brand_id,$cinema_id){
                        return $query->when($brand_id !== '',function($query) use ($brand_id){
                            return $query->orWhereRaw('find_in_set('.$brand_id.',brand_ids)');
                        })
                        ->when($cinema_id,function($query,$cinema_id){
                            return $query->orWhereRaw('find_in_set('.$cinema_id.',cinema_ids)');
                        });
                    })
                    ->where('state',10)
                    // ->dd();
                    ->get(['id','card_no','number','use_number','product_id']);
        foreach($list as $item){
            $item->makeHidden(['product']);
            $item->title =$item->product? $item->product->title:'';
            $item->limit_number = $item->number - $item->use_number;
        }
        return $list;
    }
    /**
     * 生成卡号
     *
     * @return void
     */
    function createNo($prefix = ''){
        $no = '';
        while(1){
            $no = 'YLH'.$prefix.$this->getRandNumber();
            $isExists = $this->getCardByNo($no);
            if(empty($isExists)){
                break;
            }
        }
        return $no;
    }
    /**
     * 生成卡密码
     *
     * @return void
     */
    function createKey(){
        // $value = 123456;
        $key = $this->makeCardPassword();
        return $key;
    }


    public function checkOfflineCard($cardKey = ''){
        if($this->state != 2){
            Helpers::exception('卡号无效');
        }

        if(empty($cardKey) || $cardKey != $this->card_key){
            Helpers::exception('卡密错误');
        }

    }
    public function getCardByNo($cardNo){
        $card = OlCard::where('card_no',$cardNo)->first();
        if(empty($card)) return false;
        return $card;
    }
    /**
     * 设置为已导出
     *
     * @return boolean
     */
    public function setExport(){
        if($this->type == 1) return false;  //线上卡不做导出
        $this->state = 1;
        $this->save();
    }

    /**
     * 扣减用卡次数
     *
     * @param integer $number
     * @return void
     */
    public function useCard($number =1){
        $limit = $this->number - $number;
        $limit = $limit > 0 ?: 0;
        $this->number = $limit;
        $this->save();
    }

    public function canUseCard($userId,$params){
        if($this->state != 10){
            Helpers::exception('影城卡无效，请重新选择');
        }
        $seatsArray = explode(',',$params['seat_ids']);
        $limitNumber = $this->number - $this->use_number;
        if(count($seatsArray) > $limitNumber){
            Helpers::exception('影城卡可用次数不足');
        }
        if($this->use_number >= $this->number){
            Helpers::exception('影城卡已用完，请重新购买');
        }
    }


    public function setBrandIdsAttribute($value){
        $this->attributes['brand_ids'] = implode(',', $value);
    }

    public function getBrandIdsAttribute($value){
        return explode(',', $value);
    }

    public function setCinemaIdsAttribute($value){
        $this->attributes['cinema_ids'] = implode(',', $value);
    }

    public function getCinemaIdsAttribute($value){
        return explode(',', $value);
    }

    /**
     * 启用线下卡[未启用前不能激活]
     *
     * @return boolean
     */
    public function setUsing(){
        $this->state = 2;
        $this->save();
    }

    public function isExpire(){
        if($this->expire_time > time()) return false;
        $this->state = 20;
        $this->save();
    }


    /**
     * 激活卡片
     *
     * @return void
     */
    public function activeCard(TicketUser $user){
        if($this->type == 2 && $this->state != 2) return false;
        $this->state = 10;
        $this->user_id = $user->id;
        //有效期怎么算的？  先默认1年吧
        $nowtime = time();
        $this->open_time = $nowtime;
        $this->expire_time = strtotime('+1 year',$nowtime);
        $this->save();
//        设置该用户是影城卡用户
        TicketUser::where('id',$user->id)->update(['olcard'=>1]);
        return true;
    }



    public function scopeTypeOn($query){
        return $query->where('type',1);
    }

    public function scopeTypeOff($query){
        return $query->where('type',2);
    }

    public function user(){
        return $this->belongsTo('App\Models\TicketUser','user_id','id');
    }

    public function product(){
        return $this->hasOne('App\CardModels\OlCardProduct','id','product_id');
    }


    /**
     * 生成不重复的随机数字(不能超过10位数，否则while循环陷入死循环)
     * @param  int $start 需要生成的数字开始范围
     * @param  int $end 结束范围
     * @param  int $length 需要生成的随机数个数
     * @return number      生成的随机数
     */
    function getRandNumber()
    {
        $start = 0;
        $end = 9;
        $length = 8;
        //初始化变量为0
        $count = 0;
        //建一个新数组
        $temp = array();
        while ($count < $length) {
            //在一定范围内随机生成一个数放入数组中
            $temp[] = mt_rand($start, $end);
            //$data = array_unique($temp);
            //去除数组中的重复值用了“翻翻法”，就是用array_flip()把数组的key和value交换两次。这种做法比用 array_unique() 快得多。
            $data = array_flip(array_flip($temp));
            //将数组的数量存入变量count中
            $count = count($data);
        }
        //为数组赋予新的键名
        shuffle($data);
        //数组转字符串
        $str = implode(",", $data);
        //替换掉逗号
        $number = str_replace(',', '', $str);
        return $number;
    }

    //随机生成不重复的8位卡密
    function makeCardPassword()
    {
        $code = 'ABCDEFGHCJKLMNDPQRSTUVWXYZ';
        $rand = $code[rand(0, 25)]
            . strtoupper(dechex(date('m')))
            . date('d') . substr(time(), -5)
            . substr(microtime(), 2, 5)
            . sprintf('%02d', rand(0, 99));
        for (
            $a = md5($rand, true),
            $s = '0123456789ABCDEFGHAJKLMNBPQRSTUV',
            $d = '',
            $f = 0;
            $f < 8;
            $g = ord($a[$f]),
            $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
            $f++
        ) ;
        return $d;
    }


}
