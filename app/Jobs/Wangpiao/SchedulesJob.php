<?php

namespace App\Jobs\Wangpiao;

/**
 * 同步放映计划
 */
class SchedulesJob extends AbstractRedisJob
{
    protected $model = \App\ApiModels\Wangpiao\Schedules::class;
    
    protected $title = '同步放映计划';
}
