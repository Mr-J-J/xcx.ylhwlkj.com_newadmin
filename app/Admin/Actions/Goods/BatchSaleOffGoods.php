<?php

namespace App\Admin\Actions\Goods;

use App\MallModels\Product;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * 批量下架
 */
class BatchSaleOffGoods extends BatchAction
{
    public $name = '批量下架';

    public function handle(Collection $collection)
    {
        
        $ids = array();
        foreach ($collection as $model) {
            $ids[] = $model->id;
        }
        Product::whereIn('id',$ids)->update(['state'=>Product::STATE_UNSALE]);

        return $this->response()->success('下架成功')->refresh();
    }
    
    public function dialog(){
        $this->confirm('确定要下架选中的商品吗？');
    }        
}