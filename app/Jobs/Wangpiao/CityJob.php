<?php

namespace App\Jobs\Wangpiao;

/**
 * 同步城市
 */
class CityJob extends AbstractRedisJob
{
    protected $model = \App\ApiModels\Wangpiao\City::class;

    protected $title = '同步城市';
}
