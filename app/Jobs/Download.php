<?php

namespace App\Jobs;
use Illuminate\Support\Facades\DB;
use App\Models\City;
use App\Support\MApi;
use App\Models\JobList;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
/**
 * 同步城市
 */
class Download implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($model)
    {
        $this->onQueue('download');
        $this->model = $model;        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(empty($this->model)) return true;  
        
        $this->down_file($this->model);
        
    }

    function down_file($model){
        $localPath = trim($model->zc_image1,'http://zcyshop.cn');
        $dirname = dirname($localPath);
        
        $newfilename = $localPath;
        if(!is_dir($dirname)){
            @mkdir($dirname,0755,true);
        }
        $file = fopen($model->zc_image1,'rb');
        if($file){
            $newfile = fopen($newfilename,'wb');
            if($newfile){
                while(!feof($file)){
                    fwrite($newfile,fread($file,1024*8),1024*8);
                }
            }
        }
        if($file){
            fclose($file);
        }
        DB::table('zc_product')->where('id',$model->id)->update(['is_down'=>1]);
        if($newfile) fclose($newfile);

    }
}
