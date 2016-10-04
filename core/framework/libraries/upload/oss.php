<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 废墟 <r.anerg@gmail.com> <http://anerg.com>
// +----------------------------------------------------------------------


class Oss {

    //const OSS_HOST = 'happy-send-wantease.oss-cn-shenzhen.aliyuncs.com';

    /**
     * 上传文件根目录
     * @var string
     */
    //private $rootPath;

    /**
     * 上传错误信息
     * @var string
     */
//    private $error  = '';
//    private $config = array(
//        'access_id'  => '', //阿里云Access Key ID
//        'access_key' => '', //阿里云Access Key Secret
//        'bucket'     => '', //空间名称
//        'timeout'    => 90, //超时时间
//    );

    /**
     * 构造函数，用于设置上传根路径
     * @param array  $config FTP配置
     */
    public function __construct() {
        /* 默认FTP配置 */
//            $new_config = array();
//        $new_config['access_id'] = $config['username'];
//        $new_config['access_key'] = $config['password'];
//        $new_config['bucket'] = $config['bucket'];
//        $new_config['host'] = $config['host'];
//        
//         $this->config = array_merge($this->config, $new_config);
    }

    /**
     * 检测上传根目录(阿里云上传时支持自动创建目录，直接返回)
     * @param string $rootpath   根目录
     * @return boolean true-检测通过，false-检测失败
     */
    public function checkRootPath($rootpath) {
        /* 设置根目录 */
        $this->rootPath = trim($rootpath, './') . '/';
        return true;
    }

    /**
     * 检测上传目录(阿里云上传时支持自动创建目录，直接返回)
     * @param  string $savepath 上传目录
     * @return boolean          检测结果，true-通过，false-失败
     */
    public function checkSavePath($savepath) {
        return true;
    }

    /**
     * 创建文件夹 (阿里云上传时支持自动创建目录，直接返回)
     * @param  string $savepath 目录名称
     * @return boolean          true-创建成功，false-创建失败
     */
    public function mkdir($savepath) {
        return true;
    }

    /**
     * 保存指定文件
     * @param  string   $file       本地文件绝对路径
     * @param  string   $type       上传类型
     * @param  string   $save_path  上传路径
     * @param  boolean $replace     同名文件是否覆盖
     * @return boolean              保存状态，1-成功，0-失败
     */
    public function save($file, $type, $save_path, $replace = true) {
        
        $oss_param = $this -> get_oss_param();
        if(!empty($save_path)) {
            $save_path = rtrim($save_path, "/");
            $dir =  $oss_param['dir'].$save_path.'/'.'${filename}';
            
        } else {
            
            $dir =  $oss_param['dir'].'${filename}';
            
        }
        
        $cfile = curl_file_create($file,$type);
       
        $ch = curl_init();
        $post_data = array(
            'OSSAccessKeyId' => $oss_param['accessid'],
            'callback' => '',
            'key' => $dir,
            'policy' => $oss_param['policy'],
            'signature' => $oss_param['signature'],
            'success_action_status' => '200',
            'file' => $cfile
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        //启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($ch, CURLOPT_POST, true);
        
        curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        
        curl_setopt($ch, CURLOPT_URL, 'http://happy-send-wantease.oss-cn-shenzhen.aliyuncs.com/');
        $info= curl_exec($ch);
        curl_close($ch);
        return $info;
    }

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError() {
        return $this->error;
    }

    private function hex_to_base64($str) {
        $result = '';

        for ($i = 0; $i < strlen($str); $i += 2) {
            $result .= chr(hexdec(substr($str, $i, 2)));
        }

        return base64_encode($result);
    }
    
     private function get_oss_param() {
        
        $id= 'xFNSfHbFVJbap6HF';
        $key= 's7owlyWhazp017SMpv1z1DKjVj0o6T';
        $host = 'http://happy-send-wantease.oss-cn-shenzhen.aliyuncs.com';

        $now = time();
        $expire = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
        $end = $now + $expire;
        $expiration = $this -> gmt_iso8601($end);

        $dir = 'upload/';

        //最大文件大小.用户可以自己设置
        $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
        $conditions[] = $condition; 

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
        $conditions[] = $start; 


        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
        //echo json_encode($arr);
        //return;
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        //这个参数是设置用户上传指定的前缀
        $response['dir'] = $dir;
        return $response;
        
    }
    
    private function gmt_iso8601($time) {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }

}