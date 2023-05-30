<?php

namespace App\Jobs\Wangpiao;

/**
 * 同步地铁
 */
class SubWayJob extends AbstractRedisJob
{    
    protected $model = \App\ApiModels\Wangpiao\SubWay::class;
    
    protected $title = '同步地铁';
}
