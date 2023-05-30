<?php

namespace App\Jobs\Wangpiao;

/**
 * 同步院线
 */
class CinemaLineJob extends AbstractRedisJob
{
    protected $model = \App\Models\CinemasBrand::class;

    protected $title = '同步院线';
}
