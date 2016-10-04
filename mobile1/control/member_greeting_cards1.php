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
//                $data['mp3'] = UPLOAD_SITE_URL . DS . 'mp3' . DS . $this->member_info['member_id'] . DS . $data['file'];
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
                $data['pic'] = UPLOAD_SITE_URL . DS . 'greeting_cards' . DS . $this->member_info['member_id'] . DS . $data['file'];
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
                $upload = new UploadFileTest();
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
//        $param['is_see'] = $_POST['is_see'];
        
         
        $result = Model()->table('greeting_cards')->insert($param);
        $update['happysend_done'] = 1;
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

    //未送出的 
    public function order_listOp() {
        $condition = array();
        $condition['order_common.reciver_name'] = '';
        $condition['order_common.reciver_info'] = '';
        $condition['order_common.reciver_province_id'] = 0;
        $condition['order_common.reciver_city_id'] = 0;
        $condition['order_common.happysend_done'] = 0;
        $condition['order.buyer_id'] = $this->member_info['member_id'];
        $model_order = Model('order');
        $order_list = $model_order->getOrderAndOrderCommonList($condition, 'order.order_id', $this->page, 'order_id desc');
        $page_count = $model_order->gettotalpage();
        foreach ($order_list as $key => $value) {
            $order_list[$key] = $value['order_id'];
        }
        $order_goods_list = $model_order->getOrderGoodsList(array('order_id' => array('in', $order_list)));
        foreach ($order_goods_list as $key => $value) {
            $order_goods_list[$key]['goods_image'] = thumb($value);
        }
        output_data($order_goods_list,  mobile_page($page_count));
    }

    //已收到
    public function reciver_listOp() {
        $condition = array();
        $condition['order_common.happysend_done'] = 1;
        $condition['order.buyer_id'] = $this->member_info['member_id'];
        $model_order = Model('order');
        $order_list = $model_order->getOrderAndOrderCommonList($condition, 'order.order_id', $this->page, 'order_id desc');
        $page_count = $model_order->gettotalpage();
        foreach ($order_list as $key => $value) {
            $order_list[$key] = $value['order_id'];
        }
        $order_goods_list = $model_order->getOrderGoodsList(array('order_id' => array('in', $order_list)));
        foreach ($order_goods_list as $key => $value) {
            $order_goods_list[$key]['goods_image'] = thumb($value);
        }
        if(empty($order_goods_list)){
            $order_goods_list = '暂无礼物';
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
		return $gdImage -> create($image);
		
	
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
			
//			$folder = BASE_UPLOAD_PATH.DS."greeting_cards" . DS. $_POST['member_id'];
//			if (!is_dir($folder)) {
//				
//				mkdir($folder);
//				
//			}
//			
			$new_dir = BASE_UPLOAD_PATH.DS."greeting_cards" . DS. $this->member_info['member_id'] . DS. $new_file_name .'.'.$type;
			
		}
		
		copy($dir,$new_dir);
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
		
		$rs = $gdImage -> create($new_dir);
		
		if($rs) {
			
			$insert_array['image'] = $new_file_name .'.'.$type;
			$greeting_cards = Model('greeting_cards');
			$id = $greeting_cards -> save($insert_array);
			
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
		$insert_array['create_time'] = time();
		$insert_array['last_update_time'] = time();
		
		$greeting_cards_key = Model('greeting_cards_key');
                $greeting_cards_key -> save($insert_array);
		
		$url = WAP_SITE_URL.DS.'gift/cailiwu.html?m='.$post['m'].'&t='.$post['t'];
		return $url;
		
	}
}
