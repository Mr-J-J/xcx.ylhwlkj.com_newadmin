<?php

namespace App\Models;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    protected $hidden = ['created_at','updated_at'];

    static function getList(int $positionId = 0,$limit = 5){
        $comId = (int)request('com_id',0);
        $source = $comId?1:0;
        $tagId = (int)request('tag_id',0);
        if($tagId){
            $positionId = -1;
        }
        $list = self::where('source',$source)->when($positionId >= 0,function($query) use ($positionId){
                    return $query->where('category_id',$positionId);
                })->when($tagId,function($query,$tagId){
                    return $query->where('tag_id',$tagId);
                })->orderBy('sort')->take($limit)->get(['id','image','url','full_url']);  
        foreach($list as $item){
            $item->image = Helpers::formatPath($item->image,'admin');
        }
        return $list;
    }
    
    // public function setUrlAttribute($value){
    //     if(!$value){
    //         $this->attributes['url'] = '';
    //     }
    // }
}
