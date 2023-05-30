<?php
namespace App\Admin\Actions\Api;

use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;


class SyncScenicAddAction  extends Action
{
    protected $selector = '.down-pft-data';
    public $title = '同步票付通产品';
    public $currentPage = 1;
    public function __construct()
    {
        $lastPage = \App\UUModels\UUUpdateLog::getStart();
        $pagesize = \App\UUModels\UUUpdateLog::$pagesize;
        $this->currentPage = (int)($lastPage/$pagesize)+1;
    }
    public function handle(Request $request)
    {
        $page = (int)$request->input('page',1);
        $currentPage = (int)$request->input('currentPage',1);
        $lastPage = $currentPage ;
        if($page != $currentPage){
            $lastPage = $page;
        }
        $pagesize = \App\UUModels\UUUpdateLog::$pagesize;
        // dd($lastPage,$pagesize);
        $api = \App\Support\SoapApi::getInstance();

        # 获取景区列表

        $res = $api->Get_ScenicSpot_List($pagesize,(int)$lastPage);
        logger($res);
        \App\UUModels\UUScenicSpot::saveData($res);
        // foreach($res  as $item ) {
        //     $spotInfo = $api->Get_ScenicSpot_Info((int)$item['UUid']);
        //     \App\UUModels\UUScenicSpotInfo::saveData($spotInfo);
        // }
        if(count($res) == $pagesize){
            \App\UUModels\UUUpdateLog::doStep($lastPage*$pagesize,$lastPage*$pagesize+$pagesize);
        }
        return $this->response()->success('产品已同步')->refresh();
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
        <div style='display:inline-block;vertical-align:middle;width:128px'><span class='input-group'><span class='input-group-addon'>第</span><input type="text" style='width:50px;height:30px' id="sync-page" value="{$this->currentPage}" class="form-control"><span class='input-group-addon'>页</span></span></div>
        <a class="btn btn-sm btn-default down-pft-data">$this->title</a>
HTML;
    }

    /**
     * @return array
     */
    public function parameters()
    {
        return [
            'currentPage'=>$this->currentPage
        ];
    }
}
