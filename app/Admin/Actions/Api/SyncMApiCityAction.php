<?php
namespace App\Admin\Actions\Api;

use App\Support\Api\ApiHouse;
use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;


class SyncMApiCityAction  extends Action
{
    protected $selector = '.down-api-citys';
    public $title = '同步接口城市列表';
 
    public function handle(Request $request)
    {
        $apiResult = ApiHouse::getCityList();
        foreach($apiResult as $item){
            \App\ApiModels\Wangpiao\City::syncData($item);
        }
        return $this->response()->success('数据更新成功')->refresh();
    }

    /**
     * @return mixed
     */
    public function addScript()
    {
        if (!is_null($this->interactor)) {
            return $this->interactor->addScript();
        }

        $parameters = json_encode($this->parameters());

        $script = <<<SCRIPT

(function ($) {
    $('{$this->selector($this->selectorPrefix)}').off('{$this->event}').on('{$this->event}', function() {
        var data = $(this).data();
        var target = $(this);
        var page = $('#sync-page').val() || 1;
        data.page = page;
        Object.assign(data, {$parameters});
        
        {$this->actionScript()}
        {$this->buildActionPromise()}
        {$this->handleActionPromise()}
    });
})(jQuery);

SCRIPT;

        Admin::script($script);
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-default down-api-citys">$this->title</a>
HTML;
    }

    /**
     * @return array
     */
    public function parameters()
    {
        return [
            
        ];
    }
}