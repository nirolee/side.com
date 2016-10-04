<?php

/**
 * 贺卡
 * niro
 * add by 2016.6.8
 */
defined('InShopNC') or exit('Access Invalid!');

class member_greeting_cardsControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    public function mp3_uploadOp() {
        $data = array();
        $data['status'] = 'success';
        if (isset($this->member_info['member_id'])) {
            if (!empty($_FILES['mp3']['name'])) {
                $upload = new UploadFileOther();
                $upload->set('default_dir', 'mp3' . DS . $this->member_info['member_id']);
                $result = $upload->upfile('mp3');
                if (!$result) {
                    $data['status'] = 'fail';
                    $data['error'] = $upload->error;
                }
                $data['file'] = $upload->getSysSetPath() . $upload->file_name;

            }
        } else {
            $data['status'] = 'fail';
            $data['error'] = '未登录';
        }
        output_data($data);
    }

    public function mp3_uploadossOp() {
        $data = array();
        $data['status'] = 'success';
        if (isset($this->member_info['member_id'])) {
            if (!empty($_FILES['mp3']['name'])) {
                $upload = new UploadFileOss();
                $upload->set('default_dir', 'mp3' . DS . $this->member_info['member_id']);
                $result = $upload->upfile('mp3');
                if (!$result) {
                    $data['status'] = 'fail';
                    $data['error'] = $upload->error;
                }
                $data['file'] = $upload->getSysSetPath() . $upload->file_name;
                $data['mp3'] = UPLOAD_SITE_URL . DS . 'mp3' . DS . $this->member_info['member_id'] . DS . $data['file'];
            }
        } else {
            $data['status'] = 'fail';
            $data['error'] = '未登录';
        }
        output_data($data); 
    }

    public function image_uploadOp() {
        $data = array();
        $data['status'] = 'fail';
        if (isset($this->member_info['member_id'])) {
            if (!empty($_FILES['file']['name'])) {
                $data['status'] = 'success';
                $upload = new UploadFile();
                $upload->set('default_dir', 'greeting_cards' . DS . $this->member_info['member_id']);
                $upload->set('thumb_width', '60,240');
                $upload->set('thumb_height', '5000,50000');
                $upload->set('thumb_ext', '_tiny,_list');
                $result = $upload->upfile('file');
                if (!$result) {
                    $data['status'] = 'fail';
                    $data['error'] = $upload->error;
                }
                $data['file'] = $upload->getSysSetPath() . $upload->file_name;
                $data['pic'] = UPLOAD_SITE_URL . DS . 'greeting_cards' . DS . $this->member_info['member_id'] . DS . $data['file'];
            } else {
                $data['error'] = '没有上传图片';
                
            }
        } else {
            $data['status'] = 'fail';
            $data['error'] = '未登录';
        }
        output_data($data);
    }

    public function image_uploadtestOp() {
        $data = array();
        $data['status'] = 'success';
        if (isset($this->member_info['member_id'])) {
            if (!empty($_FILES['file']['name'])) {
                $upload = new UploadFile();
                $upload->set('default_dir', 'greeting_cards' . DS . $this->member_info['member_id']);
                $upload->set('thumb_width', '60,240');
                $upload->set('thumb_height', '5000,50000');
                $upload->set('thumb_ext', '_tiny,_list');
                $result = $upload->upfile('file');
                if (!$result) {
                    $data['status'] = 'fail';
                    $data['error'] = $upload->error;
                }
                $data['file'] = $upload->getSysSetPath() . $upload->file_name;
//                $data['pic'] = UPLOAD_SITE_URL . DS . 'greeting_cards' . DS . $this->member_info['member_id'] . DS . $data['file'];
            }
        } else {
            $data['status'] = 'fail';
            $data['error'] = '未登录';
        }
        output_data($data);
    }
    
    //制作贺卡
    public function card_saveOp() {
        
        $model_order = Model('order');
        $param = array();
        $param['friend_frommid'] = $this->member_info['member_id'];
        $param['order_id'] = $condition['order_id'] = $_POST['order_id'];
        $order_info = $model_order->getOrderCommonInfo($condition,'happysend_done');
        if($order_info['happysend_done']==1){
            $data['status'] = 'fail';
            $data['message'] = '你已经发送过贺卡了';
            output_data($data);
            exit;
        }
        $param['card_type'] = 's';
        $param['ctime'] = time();
        if ($_POST['message']) {
            $message = $_POST['message'];
        }  else {
            $message = '';
        }
        
        if ($_POST['image']) {
            $param['image'] = $img_name = $_POST['image'];
            $new_dir = BASE_UPLOAD_PATH.DS."greeting_cards" . DS. $this->member_info['member_id']. DS. $img_name;
            $this->synthesis_photo($new_dir,$message);
        }
        if ($_POST['mp3']) {
            $param['mp3'] = $_POST['mp3'];
        }

        $result = Model()->table('greeting_cards')->insert($param);
//        $update['happysend_done'] = 1;
//        $condition = array();
//        $condition['order_id'] = $_POST['order_id'];
//        $model_order->editOrderCommon($update, $condition);
        
        if ($result) {
            $data['status'] = 'success';
            $data['message'] = '发送成功';
            $data['url'] = $this->get_orderKey($_POST['order_id']);
        } else {
            $data['status'] = 'fail';
            $data['message'] = '发送失败';
        }
        output_data($data);
        
    }
   
    //未送出的 
    public function order_listOp() {
        $condition = array();
        $condition['order_common.reciver_name'] = '';
        $condition['order_common.reciver_info'] = '';
        $condition['order_common.reciver_province_id'] = 0;
        $condition['order_common.reciver_city_id'] = 0;

        $condition['order.buyer_id'] = $this->member_info['member_id'];
        $condition['order.order_state'] = 20;
        $model_order = Model('order');
        $order_list = $model_order->getOrderAndOrderCommonList($condition, 'order.order_id', $this->page, 'order_id desc');
        $page_count = $model_order->gettotalpage();
        
        $greeting_cards_key = Model('greeting_cards_key');
        foreach ($order_list as $key => $value) {
            $order_list[$key] = $value['order_id'];

        }
        $order_goods_list = $model_order->getOrderGoodsList(array('order_id' => array('in', $order_list)));
        foreach ($order_goods_list as $key => $value) {
            $order_goods_list[$key]['goods_image'] = thumb($value);
            $state = $greeting_cards_key -> isExist(array(order_id => $order_goods_list[$key]['order_id']));
            $happysend_done = Model()->table('order_common')->getfby_order_id($order_goods_list[$key]['order_id'],'happysend_done');
            if($state) {
                
                $order_goods_list[$key]['url_state'] = 1;
                $order_goods_list[$key]['url'] = $this -> get_orderKey($value['order_id']);
            } else {
                
                $order_goods_list[$key]['url_state'] = 0;
                $order_goods_list[$key]['url'] = "";
                
            }
            if($happysend_done) {
                $order_goods_list[$key]['happysend_done'] = 1;
            } else {
                $order_goods_list[$key]['happysend_done'] = 0;
            }
        }
        output_data($order_goods_list,  mobile_page($page_count));
    }

    //已收到
    public function reciver_listOp() {
        $condition = array();
        $condition['order_common.happysend_done'] = 1;
        $condition['order_common.reciver_name'] = array('neq','');
        $condition['order_common.reciver_info'] = array('neq','');
        $condition['order_common.reciver_province_id'] = array('neq',0);
        $condition['order_common.reciver_city_id'] = array('neq',0);
        $condition['order.buyer_id'] = $this->member_info['member_id'];
       
        $model_order = Model('order');
        $order_list = $model_order->getOrderAndOrderCommonList($condition, 'order.order_id', $this->page, 'order_id desc');
        $page_count = $model_order->gettotalpage();
        foreach ($order_list as $key => $value) {
            $order_list[$key] = $value['order_id'];
        }
        $order_goods_list = $model_order->getOrderGoodsList(array('order_id' => array('in', $order_list)));
        $goods = Model('goods');
        foreach ($order_goods_list as $key => $value) {
            $order_goods_list[$key]['goods_image'] = thumb($value);
            $goods_id = $value['goods_id'];
            $goods_info = $goods -> getGoodsInfo(array(goods_id => $goods_id),'goods_spec');
            if($goods_info['goods_spec'] != 'N;' && !empty($goods_info['goods_spec']) && $goods_info['goods_spec'] != 'false') {
                
                $goods_spec = unserialize($goods_info['goods_spec']);
                $order_goods_list[$key]['goods_spec'] = implode(" ",$goods_spec);
                
            } else {
                
                 $order_goods_list[$key]['goods_spec'] = "";
                
            }
        }
        if(empty($order_goods_list)){
            $order_goods_list = '';
        }
        output_data($order_goods_list, mobile_page($page_count));
    }
    
    
    //合成图片
    public function synthesis_photo($image,$content) {

        $gdImage = new GdImage();

        $content_leng = mb_strlen($content,'utf8');
        if($content_leng > 10) {

            $content_1 = mb_strwidth($content,0,$content_leng/2,'utf8');
            $content_2 = mb_strwidth($content,$content_leng/2,$content_leng-1,'utf8');
            $content = $content_1 ."\n". $content_2;

        } else {

            $content = $content;

        }

        $array = array(

            'wm_text' => $content,
            'wm_text_font' => "simsun",
            'wm_text_pos' => 0,
            'wm_text_size' => 30,
            'wm_text_angle' => 0,
            'wm_text_color' => "#000000",
            'jpeg_quality'=>100
        );
        $gdImage -> setWatermark($array);
        return $gdImage -> setWater($image);


    }
    /**
    * 获得图片的格式，包括jpg,png,gif
    * @param string $img_name 图片文件名，包括路径名
    * @return string 字符串形式的返回结果
    */
   private function get_type($img_name) {

        $name_array = explode(".",$img_name);
        if (preg_match("/\.(jpg|jpeg|gif|png)$/", $img_name, $matches)){
            $type = strtolower($matches[1]);

        } else {

            $type = "string";
        }
        return $type;
   }
        
        	
    /**
     * 创建礼品卡
     */
    public function create_cardOp() {

        $insert_array['order_id'] = $_POST['order_id'];
        $insert_array['card_type'] = 's';
        //$insert_array['is_see'] = $_POST['is_see'];
        $insert_array['friend_frommid'] = $this->member_info['member_id'];
        $insert_array['ctime'] = time();
        $insert_array['message'] = "";
        $insert_array['mp3'] = "";
        if(empty($_POST['order_id'])) {

            output_data(array('state' => 0, 'message' => '制作失败'));
        }

        $img_name = $_POST['image'];
        $content = $_POST['content'];
        $dir = BASE_UPLOAD_PATH.DS."gift_card_model".DS.$img_name;
        $type = $this -> get_type($dir);
        $new_file_name = random(8);
        //$new_dir = BASE_UPLOAD_PATH.DS."greeting_cards" . DS. $insert_array['card_type']. DS. $new_file_name .'.'.$type;

        if($insert_array['card_type'] == 'r') {

            $new_dir = BASE_UPLOAD_PATH.DS."greeting_cards" . DS. $insert_array['card_type']. DS. $new_file_name .'.'.$type;

        } else {

        $folder = BASE_UPLOAD_PATH.DS."greeting_cards" . DS. $this->member_info['member_id'];
        if (!is_dir($folder)) {
            mkdir($folder);
        }
            $new_dir = BASE_UPLOAD_PATH.DS."greeting_cards" . DS. $this->member_info['member_id'] . DS. $new_file_name .'.'.$type;

        }

        @copy($dir,$new_dir);
        chmod($new_dir,0777);

        $gdImage = new GdImage();
        $array = array(

            'wm_text' => $content,
            'wm_text_font' => "simsun",
            'wm_text_pos' => 0,
            'wm_text_size' => 30,
            'wm_text_angle' => 0,
            'wm_text_color' => "#000000"
        );

        //设置参数
        $gdImage -> setWatermark($array);
        $rs = $gdImage -> setWater($new_dir);
        if($rs) {
            $insert_array['image'] = $new_file_name .'.'.$type;
            $greeting_cards = Model('greeting_cards');
            $state = $greeting_cards -> isExist(array(order_id => $_POST['order_id'],card_type => 's'));

            if(!$state) {

               $id = $greeting_cards -> save($insert_array);  

            }


            output_data(array('state' => 1, 'message' => '发送成功','id' => $id ,'url' => $this->get_orderKey($_POST['order_id'])));

        } else {

            output_data(array('state' => 0, 'message' => '发送失败'));

        }

    }
        
    //测试KEY
    public function get_orderKey($order_id) {

        $order = Model('order');
        $data = $order -> getOneList(array(order_id => $order_id),'buyer_id,add_time');
        $post['m'] = $data['buyer_id'];
        $post['t'] = $data['add_time'];
        $key = getSign($post);

        $insert_array['cards_key'] = $key;
        $insert_array['order_id'] = $order_id;
        $insert_array['flag_state'] = 0;
        $insert_array['create_time'] = $post['t'];
        $insert_array['last_update_time'] = time();

        $greeting_cards_key = Model('greeting_cards_key');
        $state = $greeting_cards_key -> isExist(array(order_id => $value['order_id'], cards_key => $key));
        if(!$state) {

            $greeting_cards_key -> save($insert_array); 

        } 

        $url = WAP_SITE_URL.DS.'gift/cailiwu.html?m='.$post['m'].'&t='.$post['t'];
        return $url;

    }
    
    /**
     * 制作贺卡
     * add by lizh 2016/9/9 15:52
     * version 1.5.3
     */
    public function card_save_1_5_3Op() {
        
        $model_order = Model('order');
        $param = array();
        $param['friend_frommid'] = $this->member_info['member_id'];
        $param['order_id'] = $condition['order_id'] = $_POST['order_id'];
        $order_info = $model_order->getOrderCommonInfo($condition,'happysend_done');
        if($order_info['happysend_done']==1){
            $data['status'] = 'fail';
            $data['message'] = '你已经发送过贺卡了';
            output_data($data);
            exit;
        }
        $param['card_type'] = 's';
        $param['ctime'] = time();
        if ($_POST['message']) {
            $param['message'] = $_POST['message'];
        }  else {
            $param['message'] = '';
        }
        
        if ($_POST['image']) {
            $param['image'] = $img_name = $_POST['image'];
           
        }
        if ($_POST['mp3']) {
            $param['mp3'] = $_POST['mp3'];
        }
        if($_POST['bg_img']) {         
            $param['bg_img'] = $_POST['bg_img'];
        }

        $result = Model()->table('greeting_cards')->insert($param);
        $update['happysend_done'] = 0;
        $condition = array();
        $condition['order_id'] = $_POST['order_id'];
        $model_order->editOrderCommon($update, $condition);
        
        if ($result) {
            $data['status'] = 'success';
            $data['message'] = '发送成功';
            $data['url'] = $this->get_orderKey($_POST['order_id']);
        } else {
            $data['status'] = 'fail';
            $data['message'] = '发送失败';
        }
        output_data($data);
        
    }
    
    /**
     * 未送出列表
     * add by lizh 2016/9/9 16:20
     * version 1.5.3
     */
    public function order_list_1_5_3Op() {
        
        $condition = array();
        $condition['order_common.reciver_name'] = '';
        $condition['order_common.reciver_info'] = '';
        $condition['order_common.reciver_province_id'] = 0;
        $condition['order_common.reciver_city_id'] = 0;
        $condition['order.buyer_id'] = $this->member_info['member_id'];
        $condition['order.order_state'] = 20;
        $model_order = Model('order');
        $order_list = $model_order->getOrderAndOrderCommonList($condition, 'order.order_id', $this->page, 'order_id desc');
        $page_count = $model_order->gettotalpage();
        
        $greeting_cards_key = Model('greeting_cards_key');
        foreach ($order_list as $key => $value) {
            $order_list[$key] = $value['order_id'];

        }
        $order_goods_list = $model_order->getOrderGoodsList(array('order_id' => array('in', $order_list)));
        $goods = Model('goods');
        foreach ($order_goods_list as $key => $value) {
            $order_goods_list[$key]['goods_image'] = thumb($value);
            $state = $greeting_cards_key -> isExist(array(order_id => $order_goods_list[$key]['order_id']));
            $happysend_done = Model()->table('order_common')->getfby_order_id($order_goods_list[$key]['order_id'],'happysend_done');
            $goods_id = $value['goods_id'];
            $goods_info = $goods -> getGoodsInfo(array(goods_id => $goods_id),'goods_spec');
            if($goods_info['goods_spec'] != 'N;' && !empty($goods_info['goods_spec']) && $goods_info['goods_spec'] != 'false') {
                
                $goods_spec = unserialize($goods_info['goods_spec']);
                $order_goods_list[$key]['goods_spec'] = implode(" ",$goods_spec);
                
            } else {
                
                 $order_goods_list[$key]['goods_spec'] = "";
                
            }
            
            if($state) {
                
                $order_goods_list[$key]['url_state'] = 1;
                $order_goods_list[$key]['url'] = $this -> get_orderKey($value['order_id']);
                
            } else {
                
                $order_goods_list[$key]['url_state'] = 0;
                $order_goods_list[$key]['url'] = "";
                
            }
            if($happysend_done) {
                $order_goods_list[$key]['is_send'] = 1;
            } else {
                $order_goods_list[$key]['is_send'] = 0;
            }
        }
        output_data($order_goods_list,  mobile_page($page_count));
    }
    
    /**
     * 用户信息和贺卡背景图片接口
     * add by lizh 2016/9/9 14:33
     * version 1.5.3
     */
    public function get_cardsOp() {
        
        $new_array = array();
        $new_array['member_id'] = $this -> member_info['member_id'];
        $new_array['member_truename'] = $this -> member_info['member_truename'];
        $new_array['member_avatar'] = getMemberAvatar($this -> member_info['member_avatar']);
        $card = $this -> get_service_card_file();
        output_data(array(member_info => $new_array, card_list => $card));
        
    }
    
    /**
     * 获取服务器欢乐送贺卡文件夹里所有卡片
     * 图片类型目前只有：'jpg','png'
     * add by lizh 2016/9/9 17:08
     * version 1.5.3 
     */
    private function get_service_card_file() {

        $dir = BASE_UPLOAD_PATH.DS."gift_card_model_1_5_3/";

        $card = array();
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {

                $i = 0;
                while (($file = readdir($dh)) !== false) {

                    $file_array = explode('.',$file);
                    $last_num = count($file_array) - 1;
                    if(count($file_array)>=2) {

                        $img_name = $file_array[0];

                        $value = $file_array[$last_num];
                        if(!empty($value)) {

                            if($value == 'jpg' || $value == 'png') {

                                $card[$i]['img_name'] = $file;
                                $card[$i]['img_url'] = UPLOAD_SITE_URL.DS.'gift_card_model_1_5_3'.DS.$file;
                                $i++;
                            }

                        }

                    }

                } 
                closedir($dh);
            }
        }

        return $card;

    }
    
    /**
     * 测试使用curl上传文件到OSS
     * add by lizh 2016/9/18 14:50
     */
    public function testUploadOssOp() {
        //print_r( $_FILES['file']);exit;
        //$file_str = implode(';', $_FILES['file']);
        $oss_param = $this -> get_oss_param();
        
        $file_str = BASE_UPLOAD_PATH . DS . 'gift_card_model_1_5_3' . DS .'a9.jpg';
       
        $cfile = curl_file_create($file_str,'image/jpeg');
       
        $ch = curl_init();
        $post_data = array(
            'OSSAccessKeyId' => $oss_param['accessid'],
            'callback' => '',
            'key' => $oss_param['dir'].'${filename}',
            'policy' => $oss_param['policy'],
            'signature' => $oss_param['signature'],
            'success_action_status' => '200',
            'file' => $cfile
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        //启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
        curl_setopt($ch, CURLOPT_POST, 1);  
        curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_URL, 'http://happy-send-wantease.oss-cn-shenzhen.aliyuncs.com/');
        $info= curl_exec($ch);
        curl_close($ch);
        print_r($info);
        
    }
    
    public function testShowOp() {
        
        echo '<img stc="http://happy-send-wantease.oss-cn-shenzhen.aliyuncs.com/upload/timg.jpg" />';
        
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
