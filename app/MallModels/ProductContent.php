<?php

namespace App\MallModels;

use Illuminate\Database\Eloquent\Model;

class ProductContent extends Model
{
    protected $table = 'mall_product_content';
    protected $primaryKey = 'product_id';
    protected $hidden = ['created_at','updated_at'];
    protected $fillable = [];
    protected $guarded = [];
}
