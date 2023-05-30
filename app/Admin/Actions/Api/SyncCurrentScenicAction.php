<?php
namespace App\Admin\Actions\Api;

use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

/**
 * 同步单个产品
 */
class SyncCurrentScenicAction  extends RowAction
{
    public $name = '产品同步';
    public function __construct($title = '产品同步')
    {
        $this->name = $title;
    }
    public function handle(Model $model,Request $request)
    {
        (new \App\UUModels\UUScenicSpotInfo)->updateTicketByNotify((int)$model->UUid,0);

        return $this->response()->success('产品信息已同步')->refresh();
    }

    public function dialog(){
        $this->confirm('确定要同步此产品信息吗？');
    }
    public function render()
    {
        if ($href = $this->href()) {
            return "<a href='{$href}' class='btn btn-twitter btn-xs {$this->getElementClass()}'>{$this->name()}</a>";
        }

        $this->addScript();

        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='btn btn-primary btn-xs  %s' {$attributes}>%s</a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );

    }
}
