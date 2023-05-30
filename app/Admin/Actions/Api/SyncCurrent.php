<?php

namespace App\Admin\Actions\Api;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class SyncCurrent extends BatchAction
{
    public $name = '批量上架';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            (new \App\UUModels\UUScenicSpotInfo)->updateTicketByNotify((int)$model->UUid,0);
            logger($model->UUid);
        }

        return $this->response()->success('Success message...')->refresh();
    }
}
