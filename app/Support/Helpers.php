<?php


namespace App\Support;

use App\CardModels\RsStores;
use App\Libs\HttpClient;
use App\Models\Manager;
use App\Models\ManagerRel;
use App\Models\VillageHouse;
use App\Models\VillageHouseManagerRel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;


class Helpers
{
    static function replace_specialChar($strParam){
        $regex = "/\/|\～|\，|\。|\！|\？|\“|\”|\【|\】|\『|\』|\：|\；|\《|\》|\’|\‘|\ |\·|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        return preg_replace($regex,"",$strParam);
    }
    /**
     * 查询商家等级对应的影院品牌
     *
     * @param [type] $level_id
     * @return void
     */
    public static function storeBrandList($level_id){
        $result = DB::table('brand_level_relation')->where('level_id',$level_id)->get('brand_id');
        return array_column($result->toArray(),'brand_id');
    }

    /**
     * 调试日志
     *
     * @param [type] $msg
     * @param array $data
     * @return void
     */
    public static function debugLog($msg ,$data = []){
        Log::debug($msg.' '.json_encode($data));
    }
    /**
     * 获取系统配置项
     *
     * @param string $name
     * @return void
     */
    public static function getSetting($name = ''){
        $data = Cache::get('sys_setting');
        if($name != ''){
            return $data[$name]['content']??'';
        }
        return $data;
    }
    /**
     * 根据token获取推客id
     * @param $token
     * @return string
     */
    public static function getUserIdByToken($token)
    {
        $data = Cache::get($token, []);
        return $data ?? '';
    }
    /**
     * 生成订单号
     *
     * @return void
     */
    public static function makeOrderNo($tab = '',$length = 6 ){
        $dt = date('YmdHis');
        $str = $dt . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, $length);
        return $tab . $str ;
    }
    /**
     * 异常
     *
     * @param [type] $msg
     * @return void
     */
    public static function exception($msg){
        throw new \App\Exceptions\ApiException($msg);
    }
    /**
     * 生成 token
     *
     * @param [type] $str
     * @return void
     */
    public static function generateToken($str){
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr ( $chars, 0, 8 ) . '-'
            . substr ( $chars, 8, 4 ) . '-'
            . substr ( $chars, 12, 4 ) . '-'
            . substr ( $chars, 16, 4 ) . '-'
            . substr ( $chars, 20, 12 );
        return md5($str.$uuid);
    }

    /**
     * 格式化本地存储路径函数
     * @author zjjw
     * @param string $path     路径
     * @param string $disk     所使用的的磁盘
     * @return string   返回一个拼接后的字符串
     */
    public static function formatPath($path, $disk = 'tickets')
    {
        if (empty($path)) return $path;
        if (strstr($path, 'http://') || strstr($path, 'https://')) {
            return $path;
        }

        $path = Storage::disk($disk)->url($path);
        return $path;
    }
    static function ResizeImage($uploadfile,$maxwidth,$maxheight,$name)
    {
        //取得当前图片大小
        $width = imagesx($uploadfile);
        $height = imagesy($uploadfile);
        $i=0.5;
        //生成缩略图的大小
        if(($width > $maxwidth) || ($height > $maxheight))
        {

            $newwidth = $width * $i;
            $newheight = $height * $i;

            if(function_exists("imagecopyresampled"))
            {
                $uploaddir_resize = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($uploaddir_resize, $uploadfile, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            }
            else
            {
                $uploaddir_resize = imagecreate($newwidth, $newheight);
                imagecopyresized($uploaddir_resize, $uploadfile, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            }

            // ImageJpeg ($uploaddir_resize,$name);
            // ImageDestroy ($uploaddir_resize);
            return $uploaddir_resize;

        }
        else
        {
            // ImageJpeg ($uploadfile,$name);
            return $uploadfile;
        }
    }

    /**
     * 图片上传
     *
     * @param string $path
     * @param string $field
     * @param string $disk
     * @return void
     */
    public static function uploadImage3($path = 'store',$field = "file" ,$disk = 'tickets'){

        $imgdata= request()->input("file");

        //$imgdata = self::imgToBase64(public_path('upload/app/41321644801224.jpg'));

        $base64_str = $imgdata;
        $pos = strpos($imgdata, ",");
        if($pos !== false){
            $base64_str = substr($imgdata, $pos + 1);
        }
        $image=base64_decode($base64_str);
        //截取扩展名
        $ext = '';
        // $img_len = strlen($image);
        // $file_size = $img_len;
        // $file_size = round(($file_size/1024),2);
        // $file_size = number_format(($file_size/1024),2);
        $imageInfo = getimagesizefromstring($image);
        if($imageInfo){
            // $ext = substr($imgdata,0,strpos($imgdata, ";"));
            $mime = $imageInfo['mime'];
            $ext = substr($mime,strpos($mime, "/")+1);
        }
        if(empty($ext)){
            logger($imgdata);
            throw new \Exception('图片上传失败');
        }
        $imgname= rand(1000,10000).time().'.'.$ext;
        $newimg = self::ResizeImage(imagecreatefromstring($image),1000,1000,$imgname);
        imagejpeg($newimg,public_path('upload/app').'/'.$imgname);
        imagedestroy($newimg);
        //本地存储
        // $filepath = Storage::disk($disk)->put($imgname,$newimg);
        $img = self::formatPath($imgname,$disk);
        return compact('img');
    }
    /**
     * 图片上传
     *
     * @param string $path
     * @param string $field
     * @param string $disk
     * @return void
     */
    public static function uploadImage($path = 'store',$field = "file" ,$disk = 'tickets'){

        $imgdata= request()->input("file");
        $base64_str = substr($imgdata, strpos($imgdata, ",")+1);
        $image=base64_decode($base64_str);
        //截取扩展名
        $ext = substr($imgdata,0,strpos($imgdata, ";"));
        $ext = substr($ext,strpos($imgdata, "/")+1);
        if(empty($ext)){
            logger($imgdata);
            throw new \Exception('图片上传失败');
        }
        $imgname=rand(1000,10000).time().'.'.$ext;
        //本地存储
        $filepath = Storage::disk($disk)->put($imgname,$image);
        $img = self::formatPath($imgname,$disk);
        return compact('img');
    }


    public static function uploadImageFormData($path = 'store',$disk='tickets'){
        $file = request()->file('file');
        if(!$file){
            return '';
        }
        $imgname=rand(1000,10000).time();
        $extension = $file->extension();
        $path = $file->storeAs($path,$imgname.'.'.$extension,$disk);
        $img = self::formatPath($path,$disk);
        return compact('img');
    }

    /**
     * 上传文件
     * @param $request
     * @param $name
     * @param string $disk
     * @return bool|string
     */
    public static function uploadFile($file, $disk = 'public', $is_http = false,$is_import=false)
    {

        if (!$file) {
            return '';
        }

        if(is_array($file)){
            $fileArr= array();
            foreach($file as $key=>$v)
            {
                $fileName = date('Y_m_d').md5(rand(1,100000));

                $res = Storage::disk($disk)->put($fileName, $v);
                if (!$res) {
                    return '';
                }
                if ($is_http) {
                    $fileArr[$key] = '/storage/'.$res;
                }
            }
            $fileName = json_encode($fileArr);

        } else {
            //获取文件的扩展名
            $ext = $file->getClientOriginalExtension();

            //获取文件的绝对路径
            $path = $file->getRealPath();

            //定义文件名
            $fileName = date('Y_m_d').'/'.md5(rand(1,100000)).'.'.$ext;
            $res = Storage::disk($disk)->put($fileName, file_get_contents($path));
            if (!$res) {
                return '';
            }
            if ($is_http) {
                $fileName = '/storage/'.$fileName;
            }else{
                $fileName = './storage/'.$fileName;
            }

        }
        if($is_import){
            $ext_arr = array('csv','xlsx','xls');

            if(!in_array($ext,$ext_arr)){
                return "";
            }
        }

        return $fileName;
    }

    public static function poster2($user,$poster,$qrcode,$bg='',$width=690,$height=1227){
        // $width = 690;
        // $height = 1227;
        // $height = 1220;
//        logger($user);

        $resource = imagecreatetruecolor($width,$height);
        //创建颜色标识
        $color =imagecolorallocate($resource,230,230,230);
        //填充颜色
        imagefill($resource,0,0,$color);
        //从链接或字符串中创建图像
        $src = imagecreatefromstring(file_get_contents($poster));
        list($src_w,$src_h) = getimagesize($poster);

        //加背景图  690*1227

        if(empty($bg)){
            $bg_path =  public_path() .'/poster_bg.jpg';
            $bg_src = imagecreatefromjpeg($bg_path);
            list($bg_w,$bg_h) = getimagesize($bg_path);
            //复制到另一个
            imagecopyresampled($resource,$bg_src,0,0,0,0,$width,$height,$bg_w,$bg_h);

            //加载海报  690*949
            $src = imagecreatefromstring(file_get_contents($poster));
            list($src_w,$src_h) = getimagesize($poster);

            $scale = round($width / $src_w,2);
            $dst_w = $width;
            $dst_h = $src_h * $scale + 50;

            imagecopyresampled($resource,$src,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);
        }else{
            $bg_src = imagecreatefromstring(file_get_contents($bg));
            list($bg_w,$bg_h) = getimagesize($bg);
            imagecopyresampled($resource,$bg_src,0,0,0,0,$width,$height,$bg_w,$bg_h);

            $scale = round($width / $bg_w,2);
            $dst_w = $width;
            $dst_h = $bg_h - 70;
        }

        $qr_dst_w = 220;
        $qr_src = imagecreatefromstring(file_get_contents($qrcode));
        $qr_src_w =imagesx($qr_src);

        $qr_pos_x = $width - $qr_dst_w - 30;
        $qr_pos_y =$dst_h - $qr_dst_w /2;

        $w = $h = 550; //小程序码宽高
        $qrcode_panel  = imagecreatetruecolor($w, $h);
        $bgcolor = imagecolorallocate($qrcode_panel, 255, 255, 255);
        imagefill($qrcode_panel, 0, 0, $bgcolor);
        $panel_pos = ($w - $qr_src_w) / 2;
        imagecopyresampled($qrcode_panel,$qr_src,$panel_pos,$panel_pos,0,0,$qr_src_w,$qr_src_w,$qr_src_w,$qr_src_w);
//小程序码加上头像
        //获取logo头像==================================
        if(!empty($user->avatar)){
            $head = $user->avatar;
        }else if(!empty($user['store_logo'])){
            $head = $user['store_logo'];
        }

        logger($user);
        //============================================
        //给小程序码加logo头像
        $heada = imagecreatefromstring(file_get_contents($head));
        $head_w1 = imagesx($heada);
        $head_h1 = imagesy($heada);
        $head_w = 220;
        $head_h = 220;
        $head_pos_x = ($w - $head_w) / 2;
        $head_pos_y = ($h - $head_h) / 2;
        imagecopyresampled($qrcode_panel,$heada,$head_pos_x,$head_pos_y,0,0,$head_w,$head_h,$head_w1,$head_h1);
        //=============================================
        //圆形
        $round_qrcode  = imagecreatetruecolor($w, $h);
        imagesavealpha($round_qrcode, true);
        $bgcolor = imagecolorallocatealpha($round_qrcode, 255, 255, 255, 127);
        imagefill($round_qrcode, 0, 0, $bgcolor);
        $r = $w / 2;
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($qrcode_panel, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($round_qrcode, $x, $y, $rgbColor);
                }
            }
        }


        imagecopyresampled($resource,$round_qrcode,$qr_pos_x,$qr_pos_y,0,0,$qr_dst_w,$qr_dst_w,$w,$h);

        // imagecopyresampled($resource,$qr_src,$qr_pos_x,$qr_pos_y,0,0,$qr_dst_w,$qr_dst_w,$qr_src_w,$qr_src_w);

        ob_start();
        imagejpeg($resource);
        $result = ob_get_contents();
        ob_end_clean();
        imagedestroy($resource);
        imagedestroy($src);
        imagedestroy($qrcode_panel);
        imagedestroy($round_qrcode);
        imagedestroy($qr_src);

        $img = "data:image/png;base64,".base64_encode($result);//转base64
        return $img;
    }
    public static function poster($poster,$qrcode,$filename='a'){
        $width = 690;
        $height = 1227;
        // $height = 1220;

        $resource = imagecreatetruecolor($width,$height);
        $color =imagecolorallocate($resource,230,230,230);
        imagefill($resource,0,0,$color);

        $src = imagecreatefromstring(file_get_contents($poster));
        list($src_w,$src_h) = getimagesize($poster);

        //加背景图
        $bg_path =  public_path() .'/poster_bg.jpg';
        $bg_src = imagecreatefromjpeg($bg_path);
        list($bg_w,$bg_h) = getimagesize($bg_path);
        imagecopyresampled($resource,$bg_src,0,0,0,0,$width,$height,$bg_w,$bg_h);

        //加载海报
        $src = imagecreatefromstring(file_get_contents($poster));
        list($src_w,$src_h) = getimagesize($poster);

        $scale = round($width / $src_w,2);
        $dst_w = $width;
        $dst_h = $src_h * $scale + 50;

        imagecopyresampled($resource,$src,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);

        $qr_dst_w = 220;
        $qr_src = imagecreatefromstring(file_get_contents($qrcode));
        $qr_src_w =imagesx($qr_src);

        $qr_pos_x = $width - $qr_dst_w - 30;
        $qr_pos_y =$dst_h - $qr_dst_w /2;

        $w = $h = 550; //小程序码宽高
        $qrcode_panel  = imagecreatetruecolor($w, $h);
        $bgcolor = imagecolorallocate($qrcode_panel, 255, 255, 255);
        imagefill($qrcode_panel, 0, 0, $bgcolor);
        $panel_pos = ($w - $qr_src_w) / 2;
        imagecopyresampled($qrcode_panel,$qr_src,$panel_pos,$panel_pos,0,0,$qr_src_w,$qr_src_w,$qr_src_w,$qr_src_w);

        //圆形
        $round_qrcode  = imagecreatetruecolor($w, $h);
        imagesavealpha($round_qrcode, true);
        $bgcolor = imagecolorallocatealpha($round_qrcode, 255, 255, 255, 127);
        imagefill($round_qrcode, 0, 0, $bgcolor);
        $r = $w / 2;
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($qrcode_panel, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($round_qrcode, $x, $y, $rgbColor);
                }
            }
        }


        imagecopyresampled($resource,$round_qrcode,$qr_pos_x,$qr_pos_y,0,0,$qr_dst_w,$qr_dst_w,$w,$h);

        // imagecopyresampled($resource,$qr_src,$qr_pos_x,$qr_pos_y,0,0,$qr_dst_w,$qr_dst_w,$qr_src_w,$qr_src_w);

        ob_start();
        imagejpeg($resource);
        $result = ob_get_contents();
        ob_end_clean();
        imagedestroy($resource);
        imagedestroy($src);
        imagedestroy($qrcode_panel);
        imagedestroy($round_qrcode);
        imagedestroy($qr_src);

        $img = "data:image/png;base64,".base64_encode($result);//转base64
        // Storage::disk('admin')->put($filename, $result);
        // return Helpers::formatPath($filename,'admin');
        return $img;
    }
    /**
     * 小程序获取access_key
     * @return mixed|string
     */
    public static function XcxGetAccessToken()
    {
        $token = Cache::get('xcx_access_token', '');
        if (!empty($token)) {
            return $token;
        }
        $appid = env('WX_APPID');
        $secret = env('WX_SECRET');
        $api = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";

        $res = HttpClient::get($api);
        $res = json_decode($res, true);
        if (isset($res['access_token'])) {
            Cache::add('xcx_access_token', $res['access_token'], $res['expires_in'] - 100);
            return $res['access_token'];
        }

        return '';

    }

    /**
     * 生成小程序二维码
     * @param $manager_id
     * @return bool|string
     */
    public static function XcxGetUnlimited($manager_id)
    {
        $token = self::XcxGetAccessToken();
        $api = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$token}";
        $data = [
            'scene' => $manager_id,
        ];

        $res = HttpClient::post($api, json_encode($data));
        $res_decode = json_encode($res, true);
        if (isset($res_decode['errcode'])) {
            return false;
        }

        $file_name = 'qrcode/'.md5(time().rand(1000, 9999)).'.png';
        file_put_contents($file_name, $res);
        return env('APP_URL').'/'.$file_name;

    }

    /**
     * 给一个秒数，得出一个格式化的字符串
     * @param $second
     * @param string $format
     * @return string
     */
    public static function formatTimeSecondToText($second, $format = 'DHIS')
    {
        $day = floor($second / (60*60*24));
        $second = $second - $day*(60*60*24);
        $hours = floor($second / (60*60));
        $second = $second - $hours*(60*60);
        $minute = floor($second / 60);
        $second = $second - $minute*60;
        switch ($format) {
            case 'DHIS':
                return $day.'天'.$hours.'小时'.$minute.'分钟'.$second.'秒';
            case 'DHI':
                return $day.'天'.$hours.'小时'.$minute.'分钟';
            case 'DH':
                return $day.'天'.$hours.'小时';
            case "HIS":
                return ($day*24)+$hours.'小时'.$minute.'分钟'.$second.'秒';
        }

    }


    /**
     * 验证参数不能为空，如果为空，就返回code错误码信息
     * 需要注意，这里不适用参数为0或者参数为假的情况
     * @param $data
     * @param $request
     * @return array
     */
    public static function checkEmptyParamAndReturnCode($data, $request)
    {
        foreach ($data as $key => $val) {
            if (empty($val)) {
                return ShowArtwork::setCode(ShowArtwork::ERR_PARAMS, '', [], ['参数不能为空'], $request->input());
            }
        }

        return false;
    }
}
