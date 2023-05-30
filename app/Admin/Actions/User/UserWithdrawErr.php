<?php
namespace App\Admin\Actions\User;


use App\Models\Store;
use Encore\Admin\Form;
use App\Models\Setting;
use App\Models\StoreInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
/**
 * 拒绝商家提现
 */
class UserWithdrawErr extends RowAction
{
    // public $name = '设置服务商';

    protected $selector = '.audit-err-user-withdraw';

    public function handle(Model $model,Request $request){
        
        DB::beginTransaction();
        try {
            $model->state = 2;
            $model->save();
            $userInfo = $model->user;
            $userInfo->balance = $userInfo->balance + $model->money;            
            $userInfo->save();
        } catch (\Throwable $th) {
            logger('拒绝提现失败'.$th->getMessage());
            DB::rollback();
        }

        DB::commit();
        
        return $this->response()->success('已拒绝提现申请')->refresh();
    }

    public function name(){
        return '拒绝提现';
    }

    public function dialog(){
        $this->confirm('确定要拒绝此用户提现申请吗？');
    }

    // public function html(){
    //     return "<a class='default-posts btn btn-sm btn-primary'><i class='fa fa-gear'></i>设置服务商</a>";
    // }

    public function render()
    {
        if ($href = $this->href()) {
            return "<a href='{$href}' class='btn btn-default btn-xs {$this->getElementClass()}'>{$this->name()}</a>";
        }

        $this->addScript();

        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='btn btn-default btn-xs  %s' {$attributes}>%s</a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );
        
    }


}