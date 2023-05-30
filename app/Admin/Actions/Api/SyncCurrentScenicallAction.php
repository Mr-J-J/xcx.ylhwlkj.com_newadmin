<?php
namespace App\Admin\Actions\Api;

use App\UUModels\UUScenicSpot;
use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;


class SyncCurrentScenicallAction  extends Action
{
    protected $selector = '.down-pft-data';
    public $title = '上架全部产品';
    public function __construct()
    {

    }
    public function handle(Request $request)
    {
        $list = UUScenicSpot::all();
        $uu = (new \App\UUModels\UUScenicSpotInfo);
        foreach($list as $item){
            logger($item);
            $uu->updateTicketByNotify((int)$item->UUid,0);
        }
//        (new \App\UUModels\UUScenicSpotInfo)->updateTicketByNotify((int)$model->UUid,0);
        return $this->response()->success('产品已同步')->refresh();
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-default down-pft-data">$this->title</a>
HTML;
    }
}
