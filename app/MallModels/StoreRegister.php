<?php

namespace App\MallModels;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class StoreRegister extends Model
{
    protected $table = 'mall_store_register';
    protected $hidden = ['created_at','updated_at'];

    public function getKefuQrcodeAttribute($value){
        return Helpers::formatPath($value,'admin');
    }
}
