<?php

namespace App\Jobs\Wangpiao;

/**
 * 同步影院
 */
class CinemaJob extends AbstractRedisJob
{
    protected $model = \App\ApiModels\Wangpiao\Cinema::class;

    protected $title = '同步影院';
}
