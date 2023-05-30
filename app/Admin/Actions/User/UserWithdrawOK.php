<?php
namespace App\Admin\Actions\User;

use App\Models\user\Commision;
use Encore\Admin\Form;
use App\Models\Setting;

use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

/**
 * 同意用户提现
 */
class UserWithdrawOK extends RowAction
{


    protected $selector = '.audit-ok-user-withdraw';

    public function handle(Model $model,Request $request){
        if($model->state > 0){
            return $this->response()->error('审核操作失败')->refresh();
        }
        $wd = new \App\Support\WithDraw;

        $result = $wd->wechatDraw(0,$model->money,$model->draw_account,$model->trade_no,false);
        if($result['status'] == 'ERROR'){
            $model->remark = $result['msg'];
            $model->save();
            return $this->response()->error('操作失败:'.$result['msg'])->refresh();
        }

        $model->success_time = time();
        $model->state = 1;
        $model->remark = '提现成功';
        $model->save();
        Commision::addCommision($model->user,2,$model->money * -1,0,$model->trade_no,'WithDraw');
        return $this->response()->success('提现申请已通过')->refresh();
    }

    public function name(){
        return '同意提现';
    }

    public function dialog(){
        // $this->radio('type','指定类型')->options(['平台默认','指定院线']);
        // $this->select('brand','指定院线')->options(['平台默认','指定院线']);
        $this->confirm('确定要通过此用户的提现申请吗？');
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
