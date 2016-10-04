<?php

/**
 * 答礼卡
 */
defined('InShopNC') or exit('Access Invalid!');

class get_greeting_cardsControl extends mobileHomeControl {
	
    public $order_id = "";
    public $cards_key = "";
	
    public function __construct() {
		
        parent::__construct();
		
        $post = array();
        if(!empty($_GET['m']) && !empty($_GET['t'])) {

            $post['m'] = $_GET['m'];
            $post['t'] = $_GET['t'];

        }

        if(!empty($_POST['m']) && !empty($_POST['t'])) {

            $post['m'] = $_POST['m'];
            $post['t'] = $_POST['t'];

        }
		
        if(!empty($post)) {

            $greeting_cards_key = Model('greeting_cards_key');
            $key = getSign($post);

            $this -> cards_key = $key;

            $data = $greeting_cards_key -> getOne(array(cards_key => $key,flag_state => 0));

            if(!empty($data)) {

                $this -> order_id = $data['order_id'];

            }

        }
		
    }
    
    /*
     * 获取送卡人的信息
     * version 1.5.3
     */
    public function get_member_infoOp() {
        
        $member_id = $_GET['m'];
        
        $model_order = Model('order');
        $update_array['happysend_done'] = 1;
        $condition['order_id'] = $order_id;
        $data = $model_order->editOrderCommon($update_array, $condition);
        
        $order_id = $this -> order_id;
        if(empty($order_id)) {
            
            $state = 0;
            
        } else {
            
            $state = 1;
            
        }
        $member = Model('member');
        $member_info = $member -> getMemberInfo(array('member_id' => $member_id), 'member_name,member_truename,member_avatar');
        $member_info['state'] = $state;
        output_data($member_info);
        
    }
	
    /**
     * 礼品卡信息
     * version 1.5.3
     */
    public function get_cart_info_1_5_3Op() {

        $key = $this -> cards_key;
        $greeting_cards_key = Model('greeting_cards_key');
        $data = $greeting_cards_key -> getOne(array(cards_key => $key));
        $order_id = $data['order_id'];
        
        $greeting_cards = Model('greeting_cards');
        $rs = $greeting_cards -> getOne(array(order_id => $order_id,card_type => 's'));

        if(!empty($rs)) {

            $returnArray['status'] = 1;
            $returnArray['message'] = $rs['message'];
            $friend_frommid = $rs['friend_frommid'];
            $member_data = Model('member') -> getMemberInfo(array(member_id => $friend_frommid), 'member_name,member_truename,member_avatar');
            $returnArray['member_name'] = $member_data['member_name'];
            $returnArray['member_avatar'] = getMemberAvatar($member_data['member_avatar']);
            if(empty($rs['bg_img'])) {
                
                $returnArray['bg_img'] = "";
                
            } else {
                
               $returnArray['bg_img'] = UPLOAD_SITE_URL.DS.'gift_card_model_1_5_3'.DS.$rs['bg_img']; 
                
            }
            
            if(empty($rs['image'])) {
                
                $order_goods = Model('order');
                $goods = Model('goods');
                
                $goods_data = $order_goods -> getOrderGoodsInfo(array(order_id => $order_id), 'goods_id');
                $goods_id = $goods_data['goods_id'];
                $goods_info = $goods -> getGoodsInfo(array(goods_id => $goods_id), 'goods_image');
                $returnArray['image'] = thumb($goods_info,360);
            } else {
                
                $returnArray['image'] = UPLOAD_SITE_URL.DS.'greeting_cards'.DS.$rs['friend_frommid'].DS.$rs['image']; 
                
            }
            
        }

        output_data($returnArray);

    }
    
    //礼品卡信息
    public function get_cart_infoOp() {

        $order_id = $this -> order_id;
        if(empty($order_id)) {

            output_data(array(status => 0, message => '无效key'));

        }
        $greeting_cards = Model('greeting_cards');
        $rs = $greeting_cards -> getOne(array(order_id => $order_id,card_type => 's'));

        if(!empty($rs)) {

            $returnArray['status'] = 1;
            $returnArray['message'] = $rs['message'];
            $friend_frommid = $rs['friend_frommid'];
            $member_data = Model('member') -> getMemberInfo(array(member_id => $friend_frommid), 'member_name');
            $returnArray['member_name'] = $member_data['member_name'];
            if(!empty($rs['mp3'])) {

                $returnArray['mp3'] = UPLOAD_SITE_URL.DS.'mp3'.DS.$rs['friend_frommid'].DS.$rs['mp3'];

            } else {

                $returnArray['mp3'] = null;

            }

            $returnArray['image'] = UPLOAD_SITE_URL.DS.'greeting_cards'.DS.$rs['friend_frommid'].DS.$rs['image'];

        }

        output_data($returnArray);

    }
    
    /**
     * 判断商品是否存在
     * version 1.5.
     */
    public function check_orderOp() {

        $order_id = $this -> order_id;
        if(empty($order_id)) {

            output_data(array(status => 0, message => '无效key'));

        } else {
            
            output_data(array(status => 1, message => '有效key'));
            
        }
       

    }
	
    //查看礼物信息
    public function get_goods_infoOp() {

        $order_id = $this -> order_id;
        if(empty($order_id)) {

            output_data(array(status => 0, message => '无效key'));

        }
        //$order_id = 1662;
        $order_goods = Model();

        $data = $order_goods -> table('order_goods') ->  field('goods_id') -> where(array(order_id => $order_id)) -> find();
        if(!empty($data)) {

            $goods_id = $data['goods_id'];

            $goods_detail = Model('goods') -> getGoodsDetail($goods_id);
            $goods_info = $goods_detail['goods_info'];

            $return_data['status'] = 1;
            $return_data['goods_id'] = $goods_info['goods_id'];
            $return_data['goods_name'] = $goods_info['goods_name'];

            $return_data['goods_image'] = UPLOAD_SITE_URL.DS.ATTACH_GOODS.DS.$goods_info['store_id'].DS.$goods_info['goods_image'];

        }

        output_data($return_data);

    }
	
    //更替礼物
    public function reset_goodsOp() {
	 	
        $order_id = $this -> order_id;
        if(empty($order_id)) {

            output_data(array(status => 0, message => '无效key'));

        }
        //$order_id = 882;
        $order_goods = Model();

        $data = $order_goods -> table('order_goods') ->  field('goods_id') -> where(array(order_id => $order_id)) -> find();
		
        if(!empty($data)) {

            $goods_id = $data['goods_id'];
            $goods_detail = Model('goods') -> getGoodsInfo(array(goods_id => $goods_id));
            $goods_price = $goods_detail['goods_price'];

            $field = 'goods.goods_name,goods.goods_image,goods.goods_spec,goods.goods_id,goods.store_id';
            //echo "goods.goods_price = $goods_price and goods_common.is_happysend = 1";
            $goods_list = Model('goods') -> getGoodsListByRecommend("goods.goods_price = $goods_price and goods_common.is_happysend = 1", $field);

            foreach($goods_list as $k => $v) {

                $spec_str = "";
                $goods_spec = $v['goods_spec'];
                if($goods_spec != 'N;' && $goods_spec != false) {

                    $goods_spec = unserialize($goods_spec);

                    foreach($goods_spec as $k1 => $v1) {

                        if(empty($spec_str)) {

                            $spec_str .= $v1;

                        } else {

                            $spec_str .= '/'.$v1;

                        }

                    }

                    $goods_list[$k]['goods_spec'] = $spec_str;

                } else {

                    $goods_list[$k]['goods_spec'] = "";

                }

                $goods_list[$k]['goods_image'] = UPLOAD_SITE_URL.DS.ATTACH_GOODS.DS.$v['store_id'].DS.$v['goods_image'];
                unset($goods_list[$k]['CONCAT(goods.goods_commonid)']);
            }
	
        }
		
        output_data(array(goods_list => $goods_list,status => 1));
		
    }
	
    //测试KEY
    public function get_orderKeyOp() {

        $order_id = $_POST['order_id'];
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
        $rs = $greeting_cards_key -> save($insert_array);

        $url = WAP_SITE_URL.DS.'gift/cailiwu.html?m='.$post['m'].'&t='.$post['t'];
        output_data(array(url => $url));

    }
	
    //更新欢乐颂订单地址
    public function update_order_addressOp() {

        $order_id = $this -> order_id;
        if(empty($order_id)) {

            output_data(array(status => 0, message => '无效key'));

        }

        $update_array = array();
        $reciver_info_array['address'] = $_POST['area'].' '.$_POST['address'];
        $reciver_info_array['phone'] = $_POST['mob_phone'];
        $reciver_info_array['mob_phone'] = $_POST['mob_phone'];
        $reciver_info_array['area'] = $_POST['area'];
        $update_array['reciver_info'] = serialize($reciver_info_array);
        $update_array['reciver_name'] = $_POST['reciver_name'];

        $area_array = explode(" ",$_POST['area']);
        $province = $area_array[0];
        $city = $area_array[1];
        $area = Model('area');
        $province_data = $area -> getAreaInfo(array(area_name => $province), 'area_id');
        $city_data = $area -> getAreaInfo(array(area_name => $city), 'area_id');
        $update_array['reciver_province_id'] = $province_data['area_id'];
        $update_array['reciver_city_id'] = $city_data['area_id'];
        $model_order = Model('order');
        //$update_array['happysend_done'] = 1;
        $condition = array();
        $condition['order_id'] = $order_id;
        $data = $model_order->editOrderCommon($update_array, $condition);

        if($data) {
            
            $cards_key = $this -> cards_key;
            $greeting_cards_key = Model('greeting_cards_key');
            $rs = $greeting_cards_key -> modify(array(flag_state => 1), array(cards_key => $cards_key));
            //unset($_SESSION[$key]);
            output_data(array(status => 1, message => '提交成功!', data => $rs));

        } else {

            output_data(array(status => 0, message => '提交失败!'));

        }

    }
	
    //更新欢乐颂商品
    public function update_order_goodsOp() {

        $order_id = $this -> order_id;
        if(empty($order_id)) {

            output_data(array(status => 0, message => '无效key'));

        }

        $update_array = array();
        $update_array['goods_id'] = $_POST['g'];

        $order = Model('order');

        $data = $order -> editOrderGoods($update_array,array(order_id => $order_id));

        if($data) {
            
            output_data(array(status => 1, message => '提交成功!'));

        } else {

            output_data(array(status => 0, message => '提交失败!'));

        }

    }
	
    //确认接收界面
    public function remove_keyOp() {

        $order_id = $this -> order_id;
        $cards_key = $this -> cards_key;
        if(empty($cards_key)) {

            output_data(array(status => 0, message => '无效key'));

        }

        $greeting_cards_key = Model('greeting_cards_key');
        $rs = $greeting_cards_key -> drop(array(cards_key => $cards_key));

        if($rs) {

            output_data(array(status => 1, message => '删除成功!'));

        } else {

            output_data(array(status => 0, message => '删除失败!'));

        }

    }
	
    /**
     * 获取服务器欢乐送贺卡文件夹里所有卡片
     * 卡片命名格式必须是：图片名 + '@' + 图片倍数 + '.' + 图片类型
     * 图片类型目前只有：'jpg','png'
     * add by lizh 2016/8/11 10:52 
     */
    public function get_service_card_fileOp() {

        $dir = BASE_UPLOAD_PATH.DS."gift_card_model/";

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
                                $card[$i]['img_url'] = UPLOAD_SITE_URL.DS.'gift_card_model'.DS.$file;
                                $i++;
                            }

                        }

                    }

                } 
                closedir($dh);
            }
        }

        output_data($card);

    }
	
    /**
     * 创建礼品卡
     */
    public function create_cardOp() {

        $cards_key = $_POST['key'];

        if(empty($cards_key)) {

            output_data(array('state' => 0, 'message' => '答谢卡制作失败'));

        }
        $insert_array['order_id'] = $_POST['order_id'];
        $insert_array['card_type'] = $_POST['card_type'];
        //$insert_array['is_see'] = $_POST['is_see'];
        $insert_array['ctime'] = time();
        $insert_array['message'] = "";
        $insert_array['mp3'] = "";
        if(empty($_POST['order_id'])) {

            output_data(array('state' => 0, 'message' => '答谢卡制作失败'));
        }

        $img_name = $_POST['img_name'];
        $content = $_POST['content'];
        $dir = BASE_UPLOAD_PATH.DS."gift_card_model".DS.$img_name;
        $type = $this -> get_type($dir);
        $new_file_name = random(8);
        //$new_dir = BASE_UPLOAD_PATH.DS."greeting_cards" . DS. $insert_array['card_type']. DS. $new_file_name .'.'.$type;

        if($insert_array['card_type'] == 'r') {

            $new_dir = BASE_UPLOAD_PATH.DS."greeting_cards" . DS. $insert_array['card_type']. DS. $new_file_name .'.'.$type;

        } else {

            $folder = BASE_UPLOAD_PATH.DS."greeting_cards" . DS. $_POST['member_id'];
            if (!is_dir($folder)) {

                mkdir($folder);

            }

            $new_dir = $folder . DS. $new_file_name .'.'.$type;

        }

        $content_leng = mb_strlen($content,'utf8');
        if($content_leng > 10) {

            $content_1 = mb_substr($content,0,$content_leng/2,'utf8');

            $content_2 = mb_substr($content,$content_leng/2,$content_leng-1,'utf8');

            $content = $content_1 . "\n". $content_2;

        } else {

            $content = $content;

        }

        //$content = $content . "\n". '123456';
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
        $rs = $gdImage -> setWater($new_dir);

        if($rs) {

            $insert_array['image'] = $new_file_name .'.'.$type;
            $greeting_cards = Model('greeting_cards');
            $id = $greeting_cards -> save($insert_array);

            $greeting_cards_key = Model('greeting_cards_key');
            $rs = $greeting_cards_key -> drop(array(cards_key => $cards_key));
            output_data(array('state' => 1, 'message' => '答谢卡制作成功','id' => $id));

        } else {

            output_data(array('state' => 0, 'message' => '答谢卡制作失败'));

        }

    }

    //合成图片
    public function synthesis_photoOp() {

        $img_url = $_GET['img_url'];
        $content = $_GET['content'];
        $gdImage = new GdImage();
        $array = array(

            'wm_text' => $content,
            'wm_text_font' => "simsun",
            'wm_text_pos' => 0,
            'wm_text_size' => 50,
            'wm_text_angle' => 0,
            'wm_text_color' => "#000000"
        );
        $gdImage -> setWatermark($array);
        $rs = $gdImage -> create($img_url);

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
    
}
