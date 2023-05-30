<?php

namespace App\Jobs\Wangpiao;

/**
 * 同步影院
 */
class ScheduleCinemaJob extends AbstractRedisJob
{
    protected $model = \App\ApiModels\Wangpiao\ScheduleCinema::class;

    protected $title = '同步影院';
}
