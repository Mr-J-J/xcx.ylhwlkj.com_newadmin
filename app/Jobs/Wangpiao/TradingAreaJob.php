<?php

namespace App\Jobs\Wangpiao;


/**
 * 同步商圈
 */
class TradingAreaJob extends AbstractRedisJob
{
    
    protected $model = \App\ApiModels\Wangpiao\TradingArea::class;

    protected $title = '同步商圈';
    
}
