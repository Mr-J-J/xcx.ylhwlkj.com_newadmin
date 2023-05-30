<?php

namespace App\Jobs\Wangpiao;

/**
 * 同步影片
 */
class FilmJob extends AbstractRedisJob
{
    protected $model = \App\ApiModels\Wangpiao\Film::class;

    protected $title = '同步影片';
}
