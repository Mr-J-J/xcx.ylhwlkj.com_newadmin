<?php

namespace App\MallModels;

use App\Support\Helpers;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    use ModelTree;
    protected $table = 'mall_categories';
    protected $hidden = ['created_at','updated_at'];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setParentColumn('parent_id');
        $this->setOrderColumn('sort');
    }

    /**
     * 获取所有一级分类  用于轮播图分类
     *
     * @return void
     */
    static function getFirstList(){
        return self::where('parent_id',0)->get(['id','title'])->toArray();
    }
    /**
     * 分类列表
     *
     * @param boolean $isNav
     * @return Illuminate\Database\Eloquent\Collection
     */
    static function getList(int $parentId = 0,$isNav = false,$isTree=false){
        if($isNav){
            return self::nav()->orderBy('sort','asc')->get();
        }
        $list = self::when($parentId,function($query,$parentId) use ($isTree){
                    if($isTree) return $query;
                    return $query->where('parent_id',$parentId);
                })->orderBy('sort','asc')->get();
        // if($isTree){
        //     $list = self::formatTree($list->toArray(),$parentId);
        // }
        return $list;
    }

    static function getOlOptions(){
        $list = self::orderBy('sort','asc')->where('type',1)->get(['id','title','parent_id']);
        return self::formatLevelTree($list->toArray());
    }
    static function getOptions(){
        $list = self::orderBy('sort','asc')->where('type',0)->get(['id','title','parent_id']);
        return self::formatLevelTree($list->toArray());
    }

    static function formatLevelTree(array $data,$parent_id = 0 ,$level=0){
        $arr = [];
        foreach($data as $key => $item){
            if($item['parent_id'] == $parent_id){
                $item['level'] = $level;
                $space = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$item['level']);
                $item['title'] = $space . $item['title'];
                $arr[] = $item;
                unset($data[$key]);
                $arr = array_merge($arr,self::formatLevelTree($data,$item['id'],$level+1));
            }
        }
        return $arr;
    }


    protected static  function formatTree(array $data,$parent_id = 0){
        $arr = [];
        foreach($data as $child){
            if($child['parent_id'] == $parent_id){
                $child['child'] = self::formatTree($data,$child['id']);
                $arr[] = $child;
            }
        }
        return $arr;
    }

    public function scopeNav($query){
        return $query->where('is_nav',1);
    }

    public function getImageAttribute($value){
        return Helpers::formatPath($value,'admin');
    }
}
