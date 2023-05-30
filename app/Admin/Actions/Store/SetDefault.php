<?php
namespace App\Admin\Actions\Store;

use App\Models\Store;

use Encore\Admin\Form;
use App\Models\Setting;
use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;

class SetDefault extends RowAction
{
    // public $name = '设置服务商';

    protected $selector = '.default-posts';

    public function handle(Store $model,Request $request){

        $post['offer_defualt_store']  = array('store_id'=>$model->id,'store_name'=>$model->store_name);
        Setting::updateSetting($post);
        return $this->response()->success('设置成功')->refresh();
    }

    public function name(){
        return '设置默认服务商';
    }

    public function dialog(){        
        // $this->radio('type','指定类型')->options(['平台默认','指定院线']);
        // $this->select('brand','指定院线')->options(['平台默认','指定院线']);
        $this->confirm('确定要设置为平台默认出票服务商吗？');
    }

    // public function html(){
    //     return "<a class='default-posts btn btn-sm btn-primary'><i class='fa fa-gear'></i>设置服务商</a>";
    // }

    public function render()
    {
        if ($href = $this->href()) {
            return "<a href='{$href}' class='btn btn-twitter btn-xs {$this->getElementClass()}'>{$this->name()}</a>";
        }

        $this->addScript();

        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='btn btn-twitter btn-xs  %s' {$attributes}>%s</a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );
        
    }


}