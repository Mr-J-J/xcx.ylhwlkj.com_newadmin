<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskList extends Model
{
    protected $table = 'wp_task_list';
    
    protected $fillable = ['task_command','title','state','remark'];

    public function getTaskList(){
        return TaskList::where('state',0)->latest()->get();
    }
}