<?php
namespace App\CardModels;

use App\MallModels\Category;
use App\MallModels\Product;

/**
 * 影城卡线上卡
 */
class OlCardProduct extends Product
{
    
    protected $hidden = ['updated_at'];

    public function getList($cateId){
        $category = Category::where('id',$cateId)->first();
        $categoryIds = array();
        if($category){
            if($category->parent_id == 0){
                $categoryIds = Category::select(['id'])->where('parent_id',$cateId)->pluck('id');
                $categoryIds = $categoryIds = [$cateId];
            }else{
                $categoryIds = [$category->id];
            }
        }        
        return OlCardProduct::where('type',3)->when($categoryIds,function($query,$categoryIds){
            return $query->whereIn('category_id',$categoryIds);
        })->get();
    }

    public function rules(){
        return $this->hasOne('App\CardModels\OlCardRules','product_id','id');
    }
    
}
