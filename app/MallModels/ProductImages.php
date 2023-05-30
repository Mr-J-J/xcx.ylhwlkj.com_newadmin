<?php

namespace App\MallModels;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class ProductImages extends Model
{
    use \Encore\Admin\Traits\Resizable;
    protected $table = 'mall_product_images';
    protected $hidden = ['created_at','updated_at'];
    protected $fillable = [];
    protected $guarded = [];

    public function getUrlAttribute($value){
        return Helpers::formatPath($this->thumbnail('thumb750', 'url'),'admin');
        // return $this->thumbnail('thumb750', 'url');
    }
}
