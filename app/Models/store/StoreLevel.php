<?php

namespace App\Models\store;

use Illuminate\Database\Eloquent\Model;

class StoreLevel extends Model
{
   
    static function getLevelsByIds(array $ids){
        return self::find($ids,['id','title']);
    }
}
