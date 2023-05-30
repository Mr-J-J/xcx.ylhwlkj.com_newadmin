<?php

namespace App\MallModels;

use App\Support\Helpers;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $table = 'mall_partners';
    protected $hidden = ['created_at','updated_at'];


    public function getImageAttribute($value){
        return Helpers::formatPath($value,'admin');
    }
}
