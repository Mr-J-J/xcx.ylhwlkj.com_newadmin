<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobList extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'names';
    protected $table = 'sys_joblist';



}
