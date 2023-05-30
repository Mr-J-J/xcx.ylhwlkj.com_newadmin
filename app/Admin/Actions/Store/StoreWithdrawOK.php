<?php
namespace App\Admin\Actions\Store;

use App\Models\Msg;
use App\Models\Store;
use Encore\Admin\Form;
use App\Models\Setting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

/**
 * 同意商家提现
 */
class StoreWithdrawOK extends RowAction
{


    protected $selector = '.audit-ok-store-withdraw';

    public function handle(Model $model,Request $request){
        if($model->state > 0){
            return $this->response()->error('审核操作失败')->refresh();
        }
        $wd = new \App\Support\WithDraw;
        if($model->trade_name == 'alipay'){
            $result = $wd->alipayDraw($model->money,$model->draw_account,$model->account_name,'商家提现');
            if($result['status'] == 'ERROR'){
                $model->remark = $result['msg'];
                $model->save();
                return $this->response()->error('操作失败:'.$result['msg'])->refresh();
            }
        }elseif($model->trade_name == 'wechat'){
            $result = $wd->wechatDraw(0,$model->money,$model->draw_account,$model->trade_no,true);
            if($result['status'] == 'ERROR'){
                $model->remark = $result['msg'];
                $model->save();
                return $this->response()->error('操作失败:'.$result['msg'])->refresh();
            }
        }else{
            return $this->response()->error('操作失败:请选择提现方式')->refresh();
        }

        $model->success_time = time();
        $model->state = 1;
        // $model->trade_no = $result['order_id']??'';
        $model->remark = '提现成功';
        $model->save();
        $msg = new Msg();
        $msg->title='提现到账通知';
        $msg->content='您的提现已到账,请检查账户.'.$model->draw_account.';金额:'.$model->money;
        $msg->userid=$model->store_id;
        $msg->usertype=1;
        $msg->save();
        return $this->response()->success('提现申请已通过')->refresh();
    }

    private function drawfail($model,$msg=''){
        $store = Store::where('id',$model->store_id)->first();
        DB::beginTransaction();
        try {
            $model->state = 2;
            $model->remark = $msg;
            $model->save();
            $storeInfo = $store->storeInfo;
            $storeInfo->balance = $storeInfo->balance + $model->money;
            $limitMoney = $storeInfo->settle_money - $model->money;
            $storeInfo->settle_money = ($limitMoney>0)?:0;
            $storeInfo->save();
        } catch (\Throwable $th) {
            logger('拒绝提现失败'.$th->getMessage());
            DB::rollback();
        }

        DB::commit();

    }

    public function name(){
        return '同意提现';
    }

    public function dialog(){
        // $this->radio('type','指定类型')->options(['平台默认','指定院线']);
        // $this->select('brand','指定院线')->options(['平台默认','指定院线']);
        $this->confirm('确定要通过此商家的提现申请吗？');
    }

    // public function html(){
    //     return "<a class='default-posts btn btn-sm btn-primary'><i class='fa fa-gear'></i>设置服务商</a>";
    // }

    public function render()
    {
        if ($href = $this->href()) {
            return "<a href='{$href}' class='btn btn-success btn-xs {$this->getElementClass()}'>{$this->name()}</a>";
        }

        $this->addScript();

        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='btn btn-success btn-xs  %s' {$attributes}>%s</a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );

    }


}
