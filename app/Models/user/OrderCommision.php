<?php

namespace App\Models\user;
use Illuminate\Support\Facades\DB;
use App\Models\TicketUser;
use Illuminate\Database\Eloquent\Model;

/**
 * 订单佣金日志
 * 购票订单出票成功后 计算佣金并记录，定时进行结算
 * 卡券订单支付成功后，计算佣金并记录，定时进行结算
 */
class OrderCommision extends Model
{
    protected $table = 'order_commision_logs';

    protected $guarded = [];
    protected $fillable = [];
    /**
     * 待结算的佣金
     *
     * @param integer $user_id
     * @return collect
     */
    static function waitCheckOut($user_id = 0){
        $checkOutList = self::when($user_id,function($query,$user_id){
            return $query->where(function($query) use ($user_id){
                return $query->where('level1_id',$user_id)->orWhere('level2_id',$user_id);
            });
        })
        ->where('endtime','<',time())
        ->where('state',0)
        ->orderBy('created_at','asc')
        ->get();

        return $checkOutList;
    }

    /**
     * 佣金结算
     *
     * @return void
     */
    public function doCheckOut(){
        $model = $this;
        
        if($model->endtime > time()){
            return false;
        }
        if($model->state == 1){ 
            return false;
        }
                
        DB::beginTransaction();
        try {            
            $model->endLogs();//已结算
            if($model->level1_id){
                //一级
                $level1Info = TicketUser::where('id',$model->level1_id)->first();  
                $level1Info->total_balance = $level1Info->total_balance+$model->level1_money;
                $level1Info->balance = $level1Info->balance + $model->level1_money;
                $level1Info->save();
                Commision::addCommision($level1Info,1,$model->level1_money,$model->order_id, $model->order_no,$model->module);
            }
            if($model->level2_id){
                //二级
                $level2Info = TicketUser::where('id',$model->level2_id)->first();
                $level2Info->total_balance = $level2Info->total_balance+$model->level2_money;
                $level2Info->balance = $level2Info->balance + $model->level2_money;
                $level2Info->save();
                Commision::addCommision($level2Info,1,$model->level2_money,$model->order_id, $model->order_no,$model->module);
            }           
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();            
            logger('佣金结算:'.$e->getMessage());
        }
    }
    
    /**
     * 待结算佣金日志写入
     *
     * @param [type] $data
     * @return void
     */
    static function addLogs(array $data){
        extract($data);
        $logs = self::where('order_id',$order_id)->firstOr(function(){
            return new OrderCommision;
        });
        $logs->fill($data)->save();
    }

    /**
     * 已结算状态修改
     *
     * @return void
     */
    public function endLogs(){
        $this->state = 1;
        $this->save();
    }
    
}
