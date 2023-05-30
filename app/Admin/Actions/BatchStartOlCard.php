<?php

namespace App\Admin\Actions;

use App\CardModels\OlCard;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * 批量启用影城卡
 */
class BatchStartOlCard extends BatchAction
{
    public $name = '批量启用';

    public function handle(Collection $collection)
    {
        
        $ids = array();
        foreach ($collection as $model) {
            $ids[] = $model->id;
        }
        OlCard::where('type',2)->whereIn('id',$ids)->update(['state'=>2]);

        return $this->response()->success('已启用')->refresh();
    }
    
    public function dialog(){
        $this->confirm('确定要启用这批卡吗？');
    }        
}