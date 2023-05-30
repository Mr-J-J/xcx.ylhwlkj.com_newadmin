<?php

namespace App\Jobs\Wangpiao;

/**
 * 同步区域
 */
class DistrictJob extends AbstractRedisJob
{
    protected $model = \App\ApiModels\Wangpiao\District::class;

    protected $title = '同步区域';
}
