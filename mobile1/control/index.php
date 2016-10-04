<?php

/**
 * 手机端首页控制
 *
 */
defined('InShopNC') or exit('Access Invalid!');

class indexControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 首页
     */
    public function indexOp() {
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialIndex();
        $this->_output_special($data, $_GET['type']);
    }

//    /*首页轮播图*/
//    public function adv_listOp() {
//        $model_mb_special = Model('mb_special');
//        $adv_list = $model_mb_special->table('mb_special_item')->field('item_data')->where(array('item_type'=>'adv_list'))->select();
//
//        foreach ($adv_list as $key => $value) {
//            $item_data = unserialize($value['item_data']);
//            $adv_list[$key]['item_data'] = unserialize($value['item_data']);
//            $img_item = unserialize($value['item_data']);
//            $img_arr = $item_data['item'];
//            foreach ($img_arr as $k1 => $v1) {
//                $img_list[$k1]['img_url'] = getMbSpecialImageUrl($v1['image']);
//                switch ($v1['type']) {
//                    case 'keyword':
//                        $img_list[$k1]['img_link'] = WAP_SITE_URL."/tmpl/product_list.html?keyword=".$v1['data'];
//                        break;
//                    case 'special':
//                        $img_list[$k1]['img_link'] = WapSiteUrl."/special.html?special_id=".$v1['data'];
//                        break;
//                    case 'goods':
//                        $img_list[$k1]['img_link'] = WapSiteUrl."/tmpl/product_detail.html?goods_id=".$v1['data'];
//                        break;  
//                    default:
//                        # code...
//                        break;
//                }
//                
//            }
//        }
//        output_data(array('img_list' => $img_list));
//    }

    /**
     * 专题
     */
    public function specialOp() {
        $title= Model('mb_special')->where(array('special_id' => $_GET['special_id']))->find();
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialItemUsableListByID($_GET['special_id']);
        $data[]['title'] = $title['special_desc'];
        $this->_output_special($data, $_GET['type'], $_GET['special_id']);
    }

    /**
     * 专题
     */
    public function special_listOp() {
        $model_mb_special = Model('mb_special');
        $condition['state'] = 1;
        $data['special_list'] = $model_mb_special->getMbSpecialList($condition, $this->page, 'special_id desc', '*');
        $page_count = $model_mb_special->gettotalpage();
        $model_goods = Model('goods');
        $model_setting = Model('setting');
        
        foreach ($data['special_list'] as $key => $value) {
            
            if ($data['special_list'][$key]['special_banner']) {
                $data['special_list'][$key]['special_banner'] = UPLOAD_SITE_URL . DS . ATTACH_MB_SPECIAL . DS . $data['special_list'][$key]['special_banner'];
            }
            $data['special_list'][$key]['goods_list'] = $model_mb_special->getMbSpecialItemUsableListByID($value['special_id']);
            
            foreach ($data['special_list'][$key]['goods_list']as $k => $v) {
                
                $goods_list = $v['goods']['item'];
            }
           
            if(!empty($goods_list)) {
                
                $data['special_list'][$key]['goods_list'] = $goods_list;
                
            } else {
                
                $data['special_list'][$key]['goods_list'] = array();
                
            }
            
        }
        $banner = $model_setting->where(array('name' => 'groupbuy_banner'))->find();
        $data['groupbuy_banner'] = UPLOAD_SITE_URL . DS . ATTACH_LIVE . DS . $banner['value'];

        output_data($data, mobile_page($page_count));
    }
    
    /**
     * 发现页接口 1.5版本
     * add by lizh 14:37 2016/7/15
     */
    public function special_list_1_5Op() {

        //热门话题
        $micro_personal_class = Model('micro_personal_class');
        $model_mb_special_data = $micro_personal_class->getList(array(), null, 'class_sort desc', 'class_id,class_name,class_image');
        foreach ($model_mb_special_data as $k => $v) {

            $model_mb_special_data[$k]['class_image'] = UPLOAD_SITE_URL . DS . DIR_MICROSHOP . DS . $v['class_image'];

            if (!empty($v['class_image'])) {

                $model_mb_special_data[$k]['class_image'] = UPLOAD_SITE_URL . DS . DIR_MICROSHOP . DS . $v['class_image'];
            } else {

                $model_mb_special_data[$k]['class_image'] = UPLOAD_SITE_URL . DS . defaultGoodsImage('360');
            }
        }

        //猜你喜欢
        $model_goods = Model('goods');
        $model_goods_browse = Model('goods_browse');
        if (!empty($_GET['key'])) {
            $model_mb_user_token = Model('mb_user_token');
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($_GET['key']);
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
            $guess_like = $model_goods_browse->getViewedGoodsList($member_info['member_id'], 20);
            foreach ($guess_like as $key => $value) {
                $guess_like[$key]['goods_image'] = thumb($value);
            }
        } else {
            $condition = array();
            $guess_like = $model_goods->getGoodsListByColorDistinct($condition, 'goods_id,goods_name,goods_promotion_price,goods_price,goods_image', 'goods_id desc', '8');
            foreach ($guess_like as $key => $value) {
                $guess_like[$key]['goods_image'] = thumb($value);
            }
        }

        //轮播图
        $condition['ap_id'] = 1280;
        $condition['field'] = 'adv_content,adv_title,store_id';
        $adv_list = Model('adv')->getList($condition, '', '', 'adv_id asc');
        foreach ($adv_list as $key => $value) {
            //$adv_list[$key] = unserialize($value['adv_content']);
            $adv_list[$key]['special_id'] = $value['store_id'];
            $adv_list[$key]['special_title'] = $value['adv_title'];
            $adv_list[$key]['special_banner'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . unserialize($value['adv_content'])['adv_pic'];
            unset($adv_list[$key]['adv_content']);
        }

//        $model_mb_special = Model('mb_special');
//        $special_list = $model_mb_special->getMbSpecialList($condition, 3, 'like_count desc', '*');
//        $page_count = $model_mb_special->gettotalpage();
//        foreach ($special_list as $key => $value) {
//            if ($special_list) {
//                $special_list[$key]['special_banner'] = UPLOAD_SITE_URL . DS . ATTACH_MB_SPECIAL . DS . $value['special_banner'];
//            }
//        }

        /* $adv_list = Model('adv')->getList($condition);
          foreach ($adv_list as $key => $value) {
          $adv_list[$key] = unserialize($value['adv_content']);
          $adv_list[$key]['store_id'] = $value['store_id'];
          $adv_list[$key]['adv_pic'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . unserialize($value['adv_content'])['adv_pic'];
          } */

        //热门橱窗
        $favorites_class = Model('favorites_class');
        $micro_personal_class_data = $favorites_class->getFavoritesList(array(favorites_class_type => 'showcase'), 'favorites_class_name,visible_state,favorites_class_id', 0, 'favorites_count desc, favorites_class_id desc', 10);
        $favorites = Model('favorites');
        $micro_personal_class_list = $favorites->getShowcaseList($micro_personal_class_data);
        
        output_data(array(hot_topics => $model_mb_special_data, guess_like => $guess_like, banner => $adv_list, micro_personal_class_list => $micro_personal_class_list));
    }

    //更多热门橱窗
    public function more_micro_personal_class_listOp() {

        //热门橱窗
        $favorites_class = Model('favorites_class');
        $micro_personal_class_data = $favorites_class->getFavoritesList(array(favorites_class_type => 'showcase'), 'favorites_class_name,visible_state,favorites_class_id', 0, 'favorites_count desc', 0);
        $favorites = Model('favorites');
        $micro_personal_class_list = $favorites->getShowcaseList($micro_personal_class_data);

        output_data(array(micro_personal_class_list => $micro_personal_class_list));
    }

    /**
     * 更多热门话题
     * add by lizh 19:20 2016/7/26
     */
    public function more_hot_topicsOp() {

        //热门话题
        $micro_personal_class = Model('micro_personal_class');
        $model_mb_special_data = $micro_personal_class->getList(array(), null, 'class_sort desc', 'class_id,class_name,class_image');
        foreach ($model_mb_special_data as $k => $v) {

            $model_mb_special_data[$k]['class_image'] = UPLOAD_SITE_URL . DS . DIR_MICROSHOP . DS . $v['class_image'];

            if (!empty($v['class_image'])) {

                $model_mb_special_data[$k]['class_image'] = UPLOAD_SITE_URL . DS . DIR_MICROSHOP . DS . $v['class_image'];
            } else {

                $model_mb_special_data[$k]['class_image'] = UPLOAD_SITE_URL . DS . defaultGoodsImage('360');
            }
        }

        output_data(array(hot_topics => $model_mb_special_data));
    }

    public function special_articleOp() {
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialByID($_GET['special_id']);
        output_data($data);
    }

    public function comment_listOp() {
        $comment_id = intval($_GET['comment_id']);
        $model_comment = Model('mb_special_comment');
        $page = intval($_GET['page']);
        if ($comment_id > 0) {
            $condition = array();
            $condition['comment_mb_id'] = $comment_id;
            $field = 'mb_special_comment.*,member.member_truename,member.member_name,member.member_avatar';
            $comment_list = $model_comment->getListWithUserInfo($condition, $page, 'comment_time desc', $field);
            foreach ($comment_list as $key => $value) {
                $comment_list[$key]['member_avatar'] = getMemberAvatarForID($comment_list[$key]['comment_member_id']);
                $comment_list[$key]['comment_message'] = json_decode($comment_list[$key]['comment_message']);
                if ($comment_list[$key]['comment_image']) {
                    $file_name = str_replace('.', '_' . 'tiny' . '.', $comment_list[$key]['comment_image']);
                    $file_name_list = str_replace('.', '_' . 'list' . '.', $comment_list[$key]['comment_image']);
                    $comment_list[$key]['comment_image'] = UPLOAD_SITE_URL . DS . ATTACH_MB_SPECIAL . DS . $comment_list[$key]['comment_member_id'] . DS . $comment_list[$key]['comment_image'];
                    $comment_list[$key]['comment_image_tiny'] = UPLOAD_SITE_URL . DS . ATTACH_MB_SPECIAL . DS . $comment_list[$key]['comment_member_id'] . DS . $file_name;
                    $comment_list[$key]['comment_image_list'] = UPLOAD_SITE_URL . DS . ATTACH_MB_SPECIAL . DS . $comment_list[$key]['comment_member_id'] . DS . $file_name_list;
                }
            }
            output_data($comment_list);
        }
    }

    /**
     * 输出专题
     */
    private function _output_special($data, $type = 'json', $special_id = 0) {
        $model_special = Model('mb_special');
        if ($_GET['type'] == 'html') {
            $html_path = $model_special->getMbSpecialHtmlPath($special_id);
            if (!is_file($html_path)) {
                ob_start();
                Tpl::output('list', $data);
                Tpl::showpage('mb_special');
                file_put_contents($html_path, ob_get_clean());
            }
            header('Location: ' . $model_special->getMbSpecialHtmlUrl($special_id));
            die;
        } else {
            output_data($data);
        }
    }

    /**
     * 默认搜索词列表
     */
    public function search_key_listOp() {
        //热门搜索
        $list = @explode(',', C('hot_search'));
        if (!$list || !is_array($list)) {
            $list = array();
        }

        //       历史搜索
        if (cookie('his_sh') != '') {
            $his_search_list = explode('~', cookie('his_sh'));
        }

        $data['list'] = $list;
        $data['his_list'] = is_array($his_search_list) ? $his_search_list : array();
        output_data($data);
    }

    /**
     * 品牌默认搜索词列表
     */
    public function search_brand_key_listOp() {
        //热门搜索
        //$list = @explode(',', C('hot_search_brand'));
         $list = array();
        if (!$list || !is_array($list)) {
            $list = array();
        }

        //       历史搜索
        if (cookie('his_sh') != '') {
            $his_search_list = explode('~', cookie('his_sh'));
        }

        $data['list'] = $list;
        $data['his_list'] = is_array($his_search_list) ? $his_search_list : array();
        output_data($data);
    }

    /**
     * 店铺默认搜索词列表
     */
    public function search_store_key_listOp() {
        //热门搜索
        $list = @explode(',', C('hot_search_store'));
        if (!$list || !is_array($list)) {
            $list = array();
        }

        //       历史搜索
        if (cookie('his_sh') != '') {
            $his_search_list = explode('~', cookie('his_sh'));
        }

        $data['list'] = $list;
        $data['his_list'] = is_array($his_search_list) ? $his_search_list : array();
        output_data($data);
    }

    /**
     * 热门搜索列表
     */
    public function search_hot_infoOp() {
        //热门搜索
        if (C('rec_search') != '') {
            $rec_search_list = @unserialize(C('rec_search'));
            $rec_value = array();
            foreach ($rec_search_list as $v) {
                $rec_value[] = $v['value'];
            }
        }
        output_data(array('hot_info' => $result ? $rec_value : array()));
    }

    /**
     * 高级搜索
     */
    public function search_advOp() {
        $area_list = Model('area')->getAreaList(array('area_deep' => 1), 'area_id,area_name');
        if (C('contract_allow') == 1) {
            $contract_list = Model('contract')->getContractItemByCache();
            $_tmp = array();
            $i = 0;
            foreach ($contract_list as $k => $v) {
                $_tmp[$i]['id'] = $v['cti_id'];
                $_tmp[$i]['name'] = $v['cti_name'];
                $i++;
            }
        }
        output_data(array('area_list' => $area_list ? $area_list : array(), 'contract_list' => $_tmp));
    }

    /**
     * android客户端版本号
     */
    public function apk_versionOp() {
        $version = C('mobile_apk_version');
        $url = C('mobile_apk');
        if (empty($version)) {
            $version = '';
        }
        if (empty($url)) {
            $url = '';
        }

        output_data(array('version' => $version, 'url' => $url));
    }

    public function welcome_pageOp() {
//        $img = array();
//        $w = $_GET['width'];
//        $h = $_GET['height'];
//        if (empty($w) || empty($h)) {
//            $img[0] = BASE_SITE_URL . DS . DIR_UPLOAD . DS . ATTACH_VERSION . DS . 'img' . "_0.jpg";
//            $img[1] = BASE_SITE_URL . DS . DIR_UPLOAD . DS . ATTACH_VERSION . DS . 'img' . "_1.jpg";
//            $img[2] = BASE_SITE_URL . DS . DIR_UPLOAD . DS . ATTACH_VERSION . DS . 'img' . "_2.jpg";
//        }
//        $img[0] = BASE_SITE_URL . DS . DIR_UPLOAD . DS . ATTACH_VERSION . DS . 'img' . $w . 'x' . $h . "_0.jpg";
//        $img[1] = BASE_SITE_URL . DS . DIR_UPLOAD . DS . ATTACH_VERSION . DS . 'img' . $w . 'x' . $h . "_1.jpg";
//        $img[2] = BASE_SITE_URL . DS . DIR_UPLOAD . DS . ATTACH_VERSION . DS . 'img' . $w . 'x' . $h . "_2.jpg";
        
        $condition['ap_id'] = 1282;
        $adv_list = Model()->table('adv')->where($condition)->field('adv_content,adv_title,store_id')->order('RAND()')->find();
            //$adv_list[$key] = unserialize($value['adv_content']);
            $adv_list['type'] = $adv_list['adv_title']; 
            $adv_list['id'] = $adv_list['store_id'];
            $adv_list['pic'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . unserialize($adv_list['adv_content'])['adv_pic'];
            $adv_list['url'] =  unserialize($adv_list['adv_content'])['adv_pic_url'];
            unset($adv_list['adv_content']);
            unset($adv_list['store_id']);
            unset($adv_list['adv_title']);

        output_data($adv_list);
    }

    /**
     * 热门话题列表
     * add by lizh 17:40 2016/7/20
     */
    public function hot_topic_infoOp() {

        $class_id = $_GET['class_id'];

        if (empty($class_id)) {

            $class_id = 1;
        }

        $micro_personal = Model('micro_personal');

        $micro_personal_list = $micro_personal->getList(array(class_id => $class_id), null, 'microshop_sort desc', 'commend_image,personal_id,commend_member_id,commend_message', '');
        foreach ($micro_personal_list as $k => $v) {

            $commend_member_id = $v['commend_member_id'];
            if (!empty($v['commend_image'])) {

                $micro_personal_list[$k]['commend_image'] = UPLOAD_SITE_URL . DS . ATTACH_MICROSHOP . DS . $v['commend_member_id'] . DS . $v['commend_image'];
            } else {

                $micro_personal_list[$k]['commend_image'] = UPLOAD_SITE_URL . DS . defaultGoodsImage('240');
            }
        }
        output_data(array(micro_personal_list => $micro_personal_list));
    }

    /**
     * 热门话题列表
     * add by lizh 12:09 2016/8/4
     */
    public function hot_topic_info_1_5Op() {

        $class_id = $_GET['class_id'];

        if (empty($class_id)) {

            $class_id = 1;
        }

        $micro_personal = Model('micro_personal');
        $micro_like = Model('micro_like');
        $sns_friend = Model('sns_friend');

        $field = 'micro_personal.*,member.member_name,member.member_truename,member.member_avatar,member_areainfo';
        $order = 'personal_id desc';
        $list = $micro_personal->getListWithUserInfo(array(class_id => $class_id), '', $order, $field);
        
         //用户ID
        $member_key = $_GET['key'];
        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($member_key);
        $member_id = $mb_user_token_info['member_id'];
        $micro_like = Model('micro_like');
        $sns_friend = Model('sns_friend');
    
        foreach ($list as $key => $value) {
           
            if(empty($member_key)) {

                $list[$key]['is_like'] = 0;
                $list[$key]['is_friend'] = 0;

            } else {

                $like_exist = $micro_like -> isExist(array(like_member_id => $member_id, like_object_id => $value['personal_id'], like_type => 2));
                $friend_exist = $sns_friend -> countFriend(array(friend_frommid => $member_id, friend_tomid => $value['commend_member_id']));
            
                if($like_exist) {

                    $list[$key]['is_like'] = 1;

                } else {

                    $list[$key]['is_like'] = 0;

                }

                if($friend_exist > 0) {

                    $list[$key]['is_friend'] = 1;

                } else {

                    $list[$key]['is_friend'] = 0;

                }

            }
            
            if ($list[$key]['commend_image']) {
                $file_name = str_replace('.', '_' . 'tiny' . '.', $list[$key]['commend_image']);
                $file_name_list = str_replace('.', '_' . 'list' . '.', $list[$key]['commend_image']);
                $list[$key]['commend_image'] = UPLOAD_SITE_URL . DS . ATTACH_MICROSHOP . DS . $list[$key]['commend_member_id'] . '/' . $list[$key]['commend_image'];
                $list[$key]['commend_image_tiny'] = UPLOAD_SITE_URL . DS . ATTACH_MICROSHOP . DS . $list[$key]['commend_member_id'] . DS . $file_name;
                $list[$key]['commend_image_list'] = UPLOAD_SITE_URL . DS . ATTACH_MICROSHOP . DS . $list[$key]['commend_member_id'] . DS . $file_name_list;
            }

            $list[$key]['member_avatar'] = getMemberAvatarForID($list[$key]['commend_member_id']);
            $list[$key]['image_width'] = @getimagesize($list[$key]['commend_image'])[0];
            $list[$key]['image_height'] = @getimagesize($list[$key]['commend_image'])[1];
            if ($list[$key]['position']) {
                $list[$key]['position'] = @explode('-', $list[$key]['position']);
                foreach ($list[$key]['position'] as $k => $v) {
//                        list($micro_personal_detail['position'][$key]['type'],$micro_personal_detail['position'][$key]['derection'],$micro_personal_detail['position'][$key]['x'],$micro_personal_detail['position'][$key]['y'],$micro_personal_detail['position'][$key]['text']) = explode(',', $value); 
                    $micro_personal_detailbb = explode(',', $v);
//                       //$micro_personal_detail['position'][$key]['t']=$micro_personal_detail['position'][$key][0];
//
                    $list[$key]['position'][$k] = array();
                    $micro_personal_detailcc = array();
                    $micro_personal_detailcc['type'] = $micro_personal_detailbb[0];
                    $micro_personal_detailcc['derection'] = $micro_personal_detailbb[1];
                    $micro_personal_detailcc['x'] = $micro_personal_detailbb[2];
                    $micro_personal_detailcc['y'] = $micro_personal_detailbb[3];
                    $micro_personal_detailcc['text'] = $micro_personal_detailbb[4];
                    $list[$key]['position'][$k] = $micro_personal_detailcc;
                }
            } else {

                $list[$key]['position'] = array();
            }
//            $micro_like_data = $micro_like->where(array(like_object_id => $value['personal_id'], like_type => 2))->limit(4)->select();
//            foreach ($micro_like_data as $k => $v) {
//                $micro_like_data[$k]['member_avatar'] = getMemberAvatarForID($v['like_member_id']);
//            }
            $n = 0;
            $list[$key]['micro_like_data'] = array();
            $condition = 'friend_tomid = '. $value['commend_member_id'];
            $micro_like_data = $sns_friend -> getListFriend($condition, 'friend_frommid', '', 'friend_id desc',4,'');
            if(!empty($micro_like_data)) {
                
                foreach($micro_like_data as $k1 => $v1) {

                    $list[$key]['micro_like_data'][$n]['member_avatar'] = getMemberAvatarForID($v1['friend_frommid']);
                    $list[$key]['micro_like_data'][$n]['like_member_id'] = $v1['friend_frommid'];
                    $list[$key]['micro_like_data'][$n]['friend_frommid'] = $v1['friend_frommid'];
                    $n++;
                }
                
            }
            //$micro_like_count = $micro_like -> where(array(like_object_id => $value['personal_id'], like_type => 2)) -> count();
            //$list[$key]['like_count'] = $micro_like_count;
            //$list[$key]['micro_like_data'] = $micro_like_data;

            $list[$key]['count_friend'] = $sns_friend->countFriend(array(friend_tomid => $value['commend_member_id']));
        }
        $goods_list = $list;
        output_data(array(micro_personal => $goods_list));
    }

    /**
     * 橱窗列表
     * add by lizh 10:10 2016/8/4
     */
    public function showcase_detailOp() {
        
        //用户ID
        $member_key = $_GET['key'];
        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($member_key);
        $my_member_id = $mb_user_token_info['member_id'];
        $sns_friend_array = array();
        
        $favorites_class_id = $_GET['favorites_class_id'];

        if (empty($favorites_class_id)) {

            output_error('未找到该橱窗', array('status' => '0'));
        }

        $favorites_class = Model('favorites_class');
        $favorites = Model('favorites');
        $favorites_class_list = $favorites_class->getOneFavorites(array(favorites_class_id => $favorites_class_id, favorites_class_type => 'showcase'));
        if(!empty($my_member_id)) {
            
            $favorites_state = $favorites -> isExist(array(fav_id => $favorites_class_id,fav_type => 'showcase_class',member_id => $my_member_id));
            if($favorites_state) {
                
                $favorites_class_list['is_friend'] = 1;
                
            } else {
                
               $favorites_class_list['is_friend'] = 0; 
                
            }
            
        } else {
            
            $favorites_class_list['is_friend'] = 0;
            
        }
        
        
        $member_id = $favorites_class_list['member_id'];

        $member = Model('member');
        $member_data = $member->getMemberInfo(array(member_id => $member_id), 'member_id,member_name,member_avatar,member_truename');
        
        $favorites_class_list['member_name'] = $member_data['member_name'];
        $favorites_class_list['member_truename'] = $member_data['member_truename'];
        $favorites_class_list['member_avatar'] = getMemberAvatar($member_data['member_avatar']);
        unset($favorites_class_list['favorites_class_type'], $favorites_class_list['visible_state'], $favorites_class_list['favorites_img'], $favorites_class_list['create_time']);

        //橱窗里收藏的瞬间
        $micro_personal_class_list = $favorites->getShowcaseList(array(0 => array('favorites_class_id' => $favorites_class_list['favorites_class_id'])), 0, 'personal_id,commend_member_id,commend_image,commend_message,like_count,commend_buy,commend_message,commend_time,class_id,comment_count,click_count,microshop_commend,microshop_sort,goods_id,position,flag_state,goods_url');
        $micro_personal = $micro_personal_class_list[0]['micro_personal'];

        $micro_like = Model('micro_like');
        $sns_friend = Model('sns_friend');

        foreach ($micro_personal as $k => $v) {
            
            $personal_id = $v['personal_id'];
            
            if(empty($member_key)) {

                $micro_personal[$k]['is_like'] = 0;
                $micro_personal[$k]['is_friend'] = 0;

            } else {

                $like_exist = $micro_like -> isExist(array(like_member_id => $my_member_id, like_object_id => $v['personal_id'], like_type => 2));
                $friend_exist = $sns_friend -> countFriend(array(friend_frommid => $my_member_id, friend_tomid => $v['commend_member_id']));

                if($like_exist) {

                    $micro_personal[$k]['is_like'] = 1;

                } else {

                    $micro_personal[$k]['is_like'] = 0;

                }

                if($friend_exist > 0) {

                    $micro_personal[$k]['is_friend'] = 1;

                } else {

                    $micro_personal[$k]['is_friend'] = 0;

                }

            }

            $sns_friend_array = $favorites -> getOneFavorites(array(fav_id => $personal_id, favorites_class_id => $favorites_class_list['favorites_class_id']), 'log_id');
            $micro_personal[$k]['log_id'] = $sns_friend_array['log_id'];
            if ($micro_personal[$k]['commend_image']) {
                $file_name = str_replace('.', '_' . 'tiny' . '.', $micro_personal[$k]['commend_image']);
                $file_name_list = str_replace('.', '_' . 'list' . '.', $micro_personal[$k]['commend_image']);
                $micro_personal[$k]['commend_image'] = $micro_personal[$k]['commend_image'];
                $micro_personal[$k]['commend_image_tiny'] = $file_name;
                $micro_personal[$k]['commend_image_list'] = $file_name_list;
            }
            
            if ($micro_personal[$k]['commend_image']) {
                $file_name = str_replace('.', '_' . 'tiny' . '.', $micro_personal[$k]['commend_image']);
                $file_name_list = str_replace('.', '_' . 'list' . '.', $micro_personal[$k]['commend_image']);
                $micro_personal[$k]['commend_image'] = $micro_personal[$k]['commend_image'];
                $micro_personal[$k]['commend_image_tiny'] = $file_name;
                $micro_personal[$k]['commend_image_list'] = $file_name_list;
            }

            if ($micro_personal[$k]['position']) {
                $micro_personal[$k]['position'] = @explode('-', $micro_personal[$k]['position']);
                foreach ($micro_personal[$k]['position'] as $k1 => $v1) {

                    $micro_personal_detailbb = explode(',', $v1);
                    $micro_personal[$k]['position'][$k1] = array();
                    $micro_personal_detailcc = array();
                    $micro_personal_detailcc['type'] = $micro_personal_detailbb[0];
                    $micro_personal_detailcc['derection'] = $micro_personal_detailbb[1];
                    $micro_personal_detailcc['x'] = $micro_personal_detailbb[2];
                    $micro_personal_detailcc['y'] = $micro_personal_detailbb[3];
                    $micro_personal_detailcc['text'] = $micro_personal_detailbb[4];
                    $micro_personal[$k]['position'][$k1] = $micro_personal_detailcc;
                }
            } else {

                $micro_personal[$k]['position'] = array();
            }

            $micro_personal[$k]['member_avatar'] = getMemberAvatarForID($v['commend_member_id']);
            //$micro_like_count = $micro_like -> where(array(like_object_id => $v['personal_id'], like_type => 2)) -> count();
            //$micro_personal[$k]['like_count'] = $micro_like_count;
            $member_data = array();
            $member_data = $member->getMemberInfo(array(member_id => $v['commend_member_id']), 'member_id,member_name,member_avatar,member_truename');

            $micro_personal[$k]['member_name'] = $member_data['member_name'];
            $micro_personal[$k]['member_truename'] = $member_data['member_truename'];
            $micro_personal[$k]['member_areainfo'] = $member_data['member_areainfo'];

            $n = 0;
            $micro_personal[$k]['micro_like_data'] = array();
            $condition = 'friend_tomid = '. $v['commend_member_id'];
            $micro_like_data = $sns_friend -> getListFriend($condition, 'friend_frommid', '', 'friend_id desc',4,'');
            if(!empty($micro_like_data)) {
                
                foreach($micro_like_data as $k1 => $v1) {

                    $micro_personal[$k]['micro_like_data'][$n]['member_avatar'] = getMemberAvatarForID($v1['friend_frommid']);
                    $micro_personal[$k]['micro_like_data'][$n]['like_member_id'] = $v1['friend_frommid'];
                    $micro_personal[$k]['micro_like_data'][$n]['friend_frommid'] = $v1['friend_frommid'];
                    $n++;
                }
                
            }
//            $micro_like_data = $micro_like->getList(array(like_object_id => $personal_id), 4, '', 'like_member_id,like_id,like_type,like_object_id,like_time');
//            foreach ($micro_like_data as $k1 => $v1) {
//
//                $like_member_id = $v1['like_member_id'];
//
//                $member_avatar = getMemberAvatarForID($like_member_id);
//
//                $micro_personal[$k]['micro_like_data'][$n]['member_avatar'] = $member_avatar;
//                $micro_personal[$k]['micro_like_data'][$n]['like_member_id'] = $like_member_id;
//                $micro_personal[$k]['micro_like_data'][$n]['like_id'] = $v1['like_id'];
//                $micro_personal[$k]['micro_like_data'][$n]['like_type'] = $v1['like_type'];
//                $micro_personal[$k]['micro_like_data'][$n]['like_time'] = $v1['like_time'];
//                $micro_personal[$k]['micro_like_data'][$n]['like_object_id'] = $v1['like_object_id'];
//                $n++;
//            }

            $micro_personal[$k]['count_friend'] = $sns_friend->countFriend(array(friend_tomid => $v['commend_member_id']));
           
        }

        output_data(array(favorites_class_list => $favorites_class_list, micro_personal => $micro_personal));
    }

    /**
     * 删除橱窗
     * add by lizh 18:29 2016/8/22
     */
    public function showcase_delOp() {

        $key = $_POST['key'];
        if (empty($key)) {
            output_error('请登录', array('login' => '0'));
        }

        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
        if (empty($mb_user_token_info)) {
            output_error('请登录', array('login' => '0'));
        }

        $favorites_class_id_str = $_POST['favorites_class_id'];
        if (empty($favorites_class_id_str)) {

            output_error('未找到该橱窗', array('status' => '0'));
        }

        $favorites_class = Model('favorites_class');
        $favorites = Model('favorites');
        $favorites_class_id_array = explode(',', $favorites_class_id_str);
        $favorites_class_array = array();
       
        $favorites_class_array['favorites_class_type'] = 'showcase';
        $favorites_class_array['favorites_class_id'] = array('in', $favorites_class_id_array);
        $rs = $favorites_class->delFavorites($favorites_class_array);

        if ($rs) {

            $favorites_array = array();
            $favorites_array['fav_type'] = 'showcase';
            $favorites_array['favorites_class_id'] = array('in', $favorites_class_id_array);
            $rs2 = $favorites->delFavorites($favorites_array);
            $favorites_class_array = array();
            $favorites_class_array['fav_id'] = array('in', $favorites_class_id_array);
            $favorites_class_array['fav_type'] = 'showcase_class';
            $rs3 = $favorites->delFavorites($favorites_class_array);
            
            output_data(array(status => 1, message => '删除成功'));
        } else {

            output_error('删除失败', array('status' => '0'));
        }
    }

    /**
     * 删除橱窗里面的瞬间
     * add by niro 22:49 2016/8/22
     */
    public function personal_delOp() {

        $key = $_POST['key'];
        if (empty($key)) {
            output_error('请登录', array('login' => '0'));
        }

        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
       
        if (empty($mb_user_token_info)) {
            output_error('请登录', array('login' => '0'));
        }
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
        $favorites_id_array = $_POST['fav_id'];
        $favorites = Model('favorites');
        $favorites_array = array();
        $favorites_array['fav_type'] = 'showcase';
        $favorites_array['member_id'] = $member_info['member_id'];
        if (strpos($favorites_id_array, ',')) {
            $favorites_array['log_id'] = array('in', $favorites_id_array);
        } else {
            $favorites_array['log_id'] = $favorites_id_array;
        }
        $result = $favorites->delFavorites($favorites_array);
        
        if ($result) {
            output_data(array(status => 1, message => '删除成功'));
        } else {
            output_data(array(status => 0, message => '删除失败'));
        }
    }

    public function personal_moveOp() {
        $key = $_POST['key'];
        if (empty($key)) {
            output_error('请登录', array('login' => '0'));
        }

        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
        if (empty($mb_user_token_info)) {
            output_error('请登录', array('login' => '0'));
        }
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
        $favorites_id_array = $_POST['personal_id'];
        $favorites = Model('favorites');
        $condition = array();
        $condition['fav_type'] = 'showcase';
        $condition['favorites_class_id'] = $_POST['favorites_class_id'];
        $condition['member_id'] = $member_info['member_id'];
        if (strpos($favorites_id_array, ',')) {
            $a = explode(',', $favorites_id_array);
            $condition['fav_id'] = array('in', $favorites_id_array);
        } else {
            $condition['fav_id'] = $favorites_id_array;
            $a = array('0'=>$favorites_id_array);
        }

        $condition1 = array();
        $condition['fav_type'] = 'showcase';
        $condition1['favorites_class_id'] = $_POST['new_favorites_class_id'];
        $condition1['member_id'] = $member_info['member_id'];
        $new_array = $favorites->getFavoritesList($condition1, 'fav_id');
        if (!empty($new_array)) {
            foreach ($new_array as $key => $value) {
                $new_array[$key] = $value['fav_id'];
            }
            
        $dif = array_diff($a,$new_array );
        $dif = array_values($dif);
        }else{
        $dif = $a;   
        }
        if (empty($dif)) {
            output_data(array(status => 0, message => '你转移的橱窗已经有该瞬间'));
            exit;
        }
        if($dif[1]){
        $dif = implode(',', $dif);
        $condition['fav_id'] = array('in', $dif);
        }  else {
          $condition['fav_id'] = $dif[0];   
        }
        $data['favorites_class_id'] = $_POST['new_favorites_class_id'];
        $result = $favorites->editFavorites($condition, $data);
        if ($result) {
            output_data(array(status => 1, message => '转移成功'));
        } else {
            output_data(array(status => 0, message => '转移失败'));
        }
    }
    
    /**
     * 买手笔记1.5.3版本查看更多
     * add by lizh 12:24 2016/9/8
     */
    public function note_buyers_1_5_3Op() {
        
        $model_mb_special = Model('mb_special');
        $condition = array();
        $data['special_list'] = $model_mb_special->getMbSpecialList($condition, $this->page, 'special_id desc', 'special_id,special_desc,special_banner');
        $page_count = $model_mb_special->gettotalpage();
       
        foreach ($data['special_list'] as $key => $value) {
            
            if ($data['special_list'][$key]['special_banner']) {
                $data['special_list'][$key]['special_banner'] = UPLOAD_SITE_URL . DS . ATTACH_MB_SPECIAL . DS . $data['special_list'][$key]['special_banner'];
            }
            
        }
        
        output_data($data, mobile_page($page_count));
    }
    
    /*
     * 最新单品
     * add by lbb 19:20 2016/9/8
     * version 1.5.3
     */
    public function new_goods_1_5_3Op() {
        
        //echo '123';exit;
        $model_goods = Model('goods');
        $brand = Model('brand');
        $condition = array();
        $condition['goods_common.is_new'] = 1;
        $goods_new = $model_goods->getGoodsListByRecommend($condition, 'goods.goods_id,goods.goods_name,goods.brand_id,goods.goods_jingle,goods.goods_promotion_price,goods.goods_marketprice,goods.goods_price,goods.goods_image', 'goods_id desc',$this->page);
        $page_count = $model_goods->gettotalpage();
        //var_dump($goods_new);exit;
        $curpage = $_GET['curpage'];
        if($curpage == 1) {
            $data = array();
            $i = 0;
            foreach ($goods_new as $key => $value) {
                //$value['goods_image'] = thumb($value);
                if(strpos(thumb($value,500),'default_goods_image_500.gif')){
                    $value['goods_image'] = thumb($value);
                }else{
                    $value['goods_image']  = thumb($value,500);
                }
                $value['goods_price'] = $value['goods_marketprice'];
                $brand_id = $value['brand_id'];
                $brand_info = $brand -> getBrandInfo(array(brand_id => $brand_id), 'brand_name');
                if(!empty($brand_info['brand_name'])) {
                    
                    $value['brand_name'] = $brand_info['brand_name'];
                } else {
                     
                    $value['brand_name'] = "";
                    
                }
                
                if($i <= 3) {
                    
                   $data['goods_head'][] = $value; 
                    
                } else {
                    
                    $data['goods_list'][] = $value; 
                    
                }
                $i++;
            }

             //轮播图
            $condition['ap_id'] = 1283;
            $condition['field'] = 'adv_content,adv_title,store_id';
            $adv_list = Model('adv')->getList($condition, '', '2', 'adv_id asc');
            foreach ($adv_list as $key => $value) {
                //$adv_list[$key] = unserialize($value['adv_content']);
                //$adv_list[$key]['special_id'] = $value['store_id'];
                $adv_list[$key]['special_title'] = $value['adv_title'];
                $adv_list[$key]['special_banner'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . unserialize($value['adv_content'])['adv_pic'];
                unset($adv_list[$key]['adv_content']);
            }

            
            $data['banner'] = $adv_list;
           // var_dump($goods_new);exit;
            if (!empty($goods_new)) {
                output_data($data, mobile_page($page_count));
            } else {
                output_data(array(),array(message => '暂无最新单品信息'));
            }

        } else {
            
            $data = array();
            foreach ($goods_new as $key => $value) {
                //$value['goods_image'] = thumb($value);
                if(strpos(thumb($value,500),'default_goods_image_500.gif')){
                    $value['goods_image'] = thumb($value);
                }else{
                    $value['goods_image']  = thumb($value,500);
                }
                $brand_id = $value['brand_id'];
                $brand_info = $brand -> getBrandInfo(array(brand_id => $brand_id), 'brand_name');
                if(!empty($brand_info['brand_name'])) {
                    
                    $value['brand_name'] = $brand_info['brand_name'];
                } else {
                    
                    $value['brand_name'] = "";
                    
                }
                $data['goods_list'][] = $value; 
            }
            $data['goods_head'] = array();
            $data['banner'] = array();
            if (!empty($goods_new)) {
                output_data($data, mobile_page($page_count));
            } else {
                output_data(array(),array(message => '暂无最新单品信息'));
            }
            
            
        }
       
    }
    
    /**
     * 设计师品牌1.5.3版本查看更多
     * add by lizh 12:24 2016/9/8
     *  version 1.5.3
     */
    public function designers_1_5_3Op() {
        
        //设计师品牌
        $model_store = Model('store');
        $favorites = Model('favorites');
        $condition = array();
        $condition['is_recommend'] = 1;
        
        //搜索条件
        if(empty($_GET['keyword'])) {
            
            $condition = array();
            
        } else {
            
            $condition['store_name'] = array('like', '%' . $_GET['keyword'] . '%');
            
        }
       
        
        //排序条件
        if(empty($_GET['order'])) {
            
            $order = 'store_sort desc';
            
        } else {
            
           $order = 'store_id desc'; 
            
        }
        
        //
        $store_list = $model_store->getStoreOnlineList($condition, $this->page, $order, 'store_id,store_name,store_avatar,store_keywords,app_banner,store_collect');
        $store_list = $model_store->getStoreSearchList_1_5_3($store_list);
        $page_count = $model_store->gettotalpage();
         
        if(!empty($_GET['key'])) {
            
            $model_mb_user_token = Model('mb_user_token');
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($_GET['key']);
            $member_id = $mb_user_token_info['member_id'];
            
        } else {
            
            $member_id = "";
            
        }
        foreach ($store_list as $key => $value) {
            $store_list[$key]['store_name'] = htmlspecialchars_decode($store_list[$key]['store_name']);
            $store_list[$key]['store_avatar'] = getStoreLogo($store_list[$key]['store_avatar'], 'store_avatar');
            $store_list[$key]['app_banner'] = $store_list[$key]['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['app_banner'] : "";
           
            $store_id =$value['store_id'];

            if(!empty($member_id)) {
                
               $rs = $favorites -> isExist(array(fav_id => $store_id,fav_type => 'store', member_id => $member_id));
               if($rs) {
                   
                   $favorites_state = 1;
                   
               } else {
                   
                   $favorites_state = 0;
                   
               }
                
            } else {
                
                $favorites_state = 0;
                
            }
            
            $store_list[$key]['is_favorate'] = $favorites_state;
            
            foreach ($store_list[$key]['search_list_goods'] as $k => $v) {
                //$store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v, 240);
                if(strpos(thumb($v,500),'default_goods_image_500.gif')){
                    $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v);
                }else{
                    $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v,500);
                }
            }
        }
        
        output_data($store_list, mobile_page($page_count));
    }

    /**
     * 设计师品牌1.5.4版本查看更多
     * add by lbb 15:00 2016/9/21
     *  version 1.5.4
     */
    public function designers_1_5_4Op() {
        //轮播图
        $condition = array();
        $condition['ap_id'] = 1284;
        $condition['field'] = 'adv_content,adv_title,store_id';
        $model_adv = Model('adv');
        $adv_list = $model_adv ->getList($condition,'', '', 'adv_id asc');
            
           
        foreach ($adv_list as $key => $value) {
            //$adv_list[$key] = unserialize($value['adv_content']);
            //$adv_list[$key]['special_id'] = $value['store_id'];
            $adv_list[$key]['special_title'] = $value['adv_title'];
            $adv_list[$key]['special_banner'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . unserialize($value['adv_content'])['adv_pic'];
            unset($adv_list[$key]['adv_content']);
        }
       // $page_count2 = $model_adv->gettotalpage();

        $data = array();
        $data['banner'] = $adv_list;
       
        //设计师品牌
        $model_store = Model('store');
        $favorites = Model('favorites');
        $condition = array();
        $condition['is_recommend'] = 1;
        
        //搜索条件
        if(empty($_GET['keyword'])) {
            
            $condition = array();
            
        } else {
            
            $condition['store_name'] = array('like', '%' . $_GET['keyword'] . '%');
            
        }
       
        
        //排序条件
        if(empty($_GET['order'])) {
            
            $order = 'store_sort desc';
            
        } else {
            
           $order = 'store_id desc'; 
            
        }
        
        //
        $store_list = $model_store->getStoreOnlineList($condition, $this->page, $order, 'store_id,store_name,store_avatar,store_keywords,app_banner,store_collect,area_info,store_description');
        $store_list = $model_store->getStoreSearchList_1_5_4($store_list);
        $page_count = $model_store->gettotalpage();
         
        if(!empty($_GET['key'])) {
            
            $model_mb_user_token = Model('mb_user_token');
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($_GET['key']);
            $member_id = $mb_user_token_info['member_id'];
            
        } else {
            
            $member_id = "";
            
        }
        foreach ($store_list as $key => $value) {
            $store_list[$key]['store_name'] = htmlspecialchars_decode($store_list[$key]['store_name']);
            $store_list[$key]['store_avatar'] = getStoreLogo($store_list[$key]['store_avatar'], 'store_avatar');
            $store_list[$key]['app_banner'] = $store_list[$key]['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['app_banner'] : "";
           
            $store_id =$value['store_id'];

            if(!empty($member_id)) {
                
               $rs = $favorites -> isExist(array(fav_id => $store_id,fav_type => 'store', member_id => $member_id));
               if($rs) {
                   
                   $favorites_state = 1;
                   
               } else {
                   
                   $favorites_state = 0;
                   
               }
                
            } else {
                
                $favorites_state = 0;
                
            }
            
            $store_list[$key]['is_favorate'] = $favorites_state;
            
            foreach ($store_list[$key]['search_list_goods'] as $k => $v) {
                //$store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v, 240);
                if(strpos(thumb($v,500),'default_goods_image_500.gif')){
                    $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v);
                }else{
                    $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v,500);
                }
            }
        }
        $data['store_list'] = $store_list;
        //output_data($adv_list, mobile_page($page_count2));
        output_data($data, mobile_page($page_count));
    }
   
    
     /**
      * 发现页--广场接口 1.5.4版本
      * add by lizh 14:26 2016/9/22
      * version 1.5.4
      */
    public function special_plaza_1_5_4Op() {
        
        //轮播图
        $condition['ap_id'] = 1280;
        $condition['field'] = 'adv_content,adv_title,store_id';
        $adv_list = Model('adv')->getList($condition, '', '', 'adv_id asc');
        foreach ($adv_list as $key => $value) {
            //$adv_list[$key] = unserialize($value['adv_content']);
            $adv_list[$key]['special_id'] = $value['store_id'];
            $adv_list[$key]['special_title'] = $value['adv_title'];
            $adv_list[$key]['special_banner'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . unserialize($value['adv_content'])['adv_pic'];
            unset($adv_list[$key]['adv_content']);
        }
       
        //设计师视频
        $model_live = Model('live');
        $live_list = $model_live -> getList(array(live_state => 0), null, 'live_sort asc, live_id desc', 'live_message,live_name,live_avatar,live_url', '20');
        foreach($live_list as $k => $v) {
            
            $live_list[$k]['live_avatar'] = UPLOAD_SITE_URL . DS . 'designers' . DS .$v['live_avatar'];
            
        }

        //设计师品牌
//        $model_goods = Model('goods');
//        $model_store = Model('store');
//        $condition = array();
//        $condition['is_recommend'] = 1;
//        $store_list = $model_store->getStoreOnlineList($condition, 20, 'store_id desc', 'store_id,store_name,store_avatar,sc_id,app_banner,store_collect');
//        foreach ($store_list as $key => $value) {
//            $store_list[$key]['store_name'] = htmlspecialchars_decode($store_list[$key]['store_name']);
//            $store_list[$key]['store_avatar'] = getStoreLogo($store_list[$key]['store_avatar'], 'store_avatar');
//            $store_list[$key]['search_list_goods'] = $model_goods->getIsrecommendGoods(array('store_id' => $value['store_id']), $this->page);
//            $store_list[$key]['app_banner'] = $store_list[$key]['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['app_banner'] : "";
//            foreach ($store_list[$key]['search_list_goods'] as $k => $v) {
//                $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v, 240);
//            }
//        }
        
        //瞬间达人
        $model_micro_personal = Model('micro_personal');
        $model_member = Model('member');
        $model_sns_friend = Model('sns_friend');
        $micro_personal_list = $model_micro_personal -> query('select count(*) as count, commend_member_id  from wantease_micro_personal group by commend_member_id order by count desc, commend_member_id desc limit 0,20');
        $personal_list = array();
        $i = 0;
        foreach($micro_personal_list as $k => $v) {
            
            $commend_member_id = $v['commend_member_id'];
            $member_info = $model_member -> getMemberInfo(array(member_id => $commend_member_id), '*');
            $fans = $model_sns_friend -> countFriend(array(friend_tomid => $commend_member_id));
            $personal_list[$i]['member_id'] = $member_info['member_id'];
            $personal_list[$i]['member_truename'] = $member_info['member_truename'];
            $personal_list[$i]['fans'] = $fans;
            $personal_list[$i]['member_avatar'] = getMemberAvatar($member_info['member_avatar']);
            $personal_list[$i]['member_background'] = getMemberAvatar($member_info['member_background']);
            $personal = $model_micro_personal -> getList(array(commend_member_id => $commend_member_id), null, 'personal_id desc','*', 4);
            $n = 0;
            foreach($personal as $k1 => $v1) {
                
                $personal_list[$i]['personal_list'][$n]['personal_id'] = $v1['personal_id'];
                $personal_list[$i]['personal_list'][$n]['commend_image'] = UPLOAD_SITE_URL.DS.ATTACH_MICROSHOP.DS.$v1['commend_member_id'].'/'.$v1['commend_image'];
                $n++;
            }
            
            $i++;
        }
        
        output_data(array(banner => $adv_list,'store_list' => $personal_list,live_list => $live_list));
    }
    
    /*
     * 匠心手记列表
     * @param int $page 页数
     * @param int $curpage 页码
     * add by lizh 15:01 2016/9/22
     * varsion 1.5.4
     */
    public function special_plaza_article_1_5_4Op() {
        
        //匠心笔记
        $model_cms_article = Model('cms_article');
        $condition = array();
        $condition['article_class_id'] = 6;
        $article_list = $model_cms_article->getList($condition, $this -> page, 'article_commend_flag desc,article_id desc', 'article_id,article_title,article_image,article_publisher_id,article_abstract,article_publish_time,like_count,article_keyword,store_id');
        $page_count = $model_cms_article->gettotalpage();
        foreach ($article_list as $key => $value) {
            $article_list[$key]['article_image'] = BASE_SITE_URL . DS . DS . DIR_UPLOAD . DS . ATTACH_CMS . DS . 'article' . DS . $article_list[$key]['article_publisher_id'] . DS . unserialize($article_list[$key]['article_image'])['name'];
            $article_list[$key]['article_publish_time'] = date('M. j', $article_list[$key]['article_publish_time']);
            $article_list[$key]['url'] = BASE_SITE_URL . DS . 'wap/tmpl/cms_article_show_1.html?article_id=' . $article_list[$key]['article_id'];
        }
        $data['article_list'] = $article_list;
        output_data($data, mobile_page($page_count));
        
    }
    
    /*
     * 设计师视频查看更多列表
     * @param int $page 页数
     * @param int $curpage 页码
     * add by lizh 14:12 2016/9/26
     * varsion 1.5.4
     */
    public function special_plaza_live_more_1_5_4Op() {

        //设计师视频
        $model_live = Model('live');
        $live_list = $model_live -> getList(array(live_state => 0), $this -> page, 'live_sort asc, live_id desc', 'live_id,live_message,live_name,live_avatar,live_url,live_title,live_banner,designer_message');
        foreach($live_list as $k => $v) {
            
            $live_list[$k]['live_avatar'] = UPLOAD_SITE_URL . DS . 'designers' . DS .$v['live_avatar'];
             $live_list[$k]['live_banner'] = UPLOAD_SITE_URL . DS . 'designers' . DS .$v['live_banner'];
            
        }
        $page_count = $model_live->gettotalpage();
        $data['live_list'] = $live_list;
        output_data($data, mobile_page($page_count));
        
    }
    
    /*
     * 设计师视频详情接口
     * @param int $live_id 视频主键
     * add by lizh 14:12 2016/9/26
     * varsion 1.5.4
     */
    public function special_plaza_live_info_1_5_4Op() {

        //设计师视频
        $model_live = Model('live');
        $live_id = $_GET['live_id'];
        $live_list = $model_live -> getOne(array(live_state => 0, live_id => $live_id), 'live_id,live_message,live_name,live_avatar,live_url,live_banner,designer_message,live_title');
        $live_list['live_avatar'] = UPLOAD_SITE_URL . DS . 'designers' . DS .$live_list['live_avatar'];
        $live_list['live_banner'] = UPLOAD_SITE_URL . DS . 'designers' . DS .$live_list['live_banner'];
        $data['live_list'] = $live_list;
        output_data($data);
        
    }
    
    /*
     * 首页
     * add by lizh 2016/9/26 15:10
     * version 1.5.4
     */
    public function index_1_5_4Op() {
        
        $model_goods = Model('goods');
        $condition = array();
        $goods_list = array();
        $goods_info = array();
         
        //滑动的banner
        $banner =  $this -> get_banner(1280,5);
        foreach($banner as $k => $v) {
            
            $goods_id = $v['banner_id'];
            $goods_info = $model_goods -> getGoodsInfo(array('goods_id' => $goods_id), 'goods_id,goods_name,store_name,goods_promotion_price');
            if(empty($goods_info)) {
                
                $goods_info['goods_id'] = $goods_id;
                $goods_info['banner_id'] = $goods_id;
                $goods_info['goods_name'] = "";
                $goods_info['store_name'] = "";
                $goods_info['goods_promotion_price'] = 0;
                $goods_info['adv_pic'] = $v['adv_pic'];

                
            } else {
                
                $goods_info['banner_id'] = $goods_id;
                $goods_info['adv_pic'] = $v['adv_pic'];
            }
            
            $goods_list[] = $goods_info;
        }
        
        //设计师品牌banner
        $designers_fixed_banner = $this -> get_banner(1285,1);
       
        //首页专题banner
        $special_fixed_banner =  $this -> get_banner(1286,2);
       
        //首页匠心banner
        $ingenuity_fixed_banner =  $this -> get_banner(1287,1);

        //新品
        $condition = array();
        $condition['goods_common.is_new'] = 1;
        $goods_new = $model_goods->getGoodsListByRecommend($condition, 'goods.goods_id,goods.goods_name,goods.goods_promotion_price,goods.goods_price,goods.goods_image', 'goods_id desc', '20');
        foreach ($goods_new as $key => $value) {
            //$goods_new[$key]['goods_image'] = thumb($value);
            if(strpos(thumb($value,500),'default_goods_image_500.gif')){
                $goods_new[$key]['goods_image'] = thumb($value);
            }else{
                $goods_new[$key]['goods_image'] = thumb($value,500);
            }
        }
        
        //尖货
        $condition = array();
        $condition['goods_common.is_popular'] = 1;
        $goods_pop = $model_goods->getGoodsListByRecommend($condition, 'goods.goods_id,goods.goods_name,goods.goods_promotion_price,goods.goods_price,goods.goods_image', 'goods_id desc', '20');
        foreach ($goods_pop as $key => $value) {
            //$goods_pop[$key]['goods_image'] = thumb($value);
            if(strpos(thumb($value,500),'default_goods_image_500.gif')){
                $goods_pop[$key]['goods_image'] = thumb($value);
            }else{
                $goods_pop[$key]['goods_image'] = thumb($value,500);
            }
        }

        output_data(array(
                        'banner' => $goods_list, 
                        'designers_fixed_banner' => $designers_fixed_banner,
                        'special_fixed_banner' => $special_fixed_banner, 
                        'ingenuity_fixed_banner' => $ingenuity_fixed_banner,
                        'goods_new' => $goods_new, 
                        'goods_popular' => $goods_pop
                    )
        );
    }
    
    /*
     * 首页 -- 设计师品牌
     * add by lizh 2016/9/26 19:40
     * version 1.5.4
     */
    public function designer_brandOp() {
        
        //设计师品牌
        $model_store = Model('store');
        $model_goods = Model('goods');
        $condition = array();
        $data = array();
        $guess_like = array();
        
        $condition['is_recommend'] = 1;
        $store_list = $model_store->getStoreOnlineList($condition, $this -> page, 'store_id desc', 'store_id,store_name,store_avatar,sc_id,app_banner,store_collect');
        $page_count = $model_store->gettotalpage();
        $i = 1;
        $goods_page = 0;
        foreach ($store_list as $key => $value) {
            $store_list[$key]['store_name'] = htmlspecialchars_decode($store_list[$key]['store_name']);
            $store_list[$key]['store_avatar'] = getStoreLogo($store_list[$key]['store_avatar'], 'store_avatar');
            $store_list[$key]['search_list_goods'] = $model_goods->getIsrecommendGoods(array('store_id' => $value['store_id']), $this->page);
            $store_list[$key]['app_banner'] = $store_list[$key]['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['app_banner'] : "";
            foreach ($store_list[$key]['search_list_goods'] as $k => $v) {
                $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v, 240);
            }
            
            if($i%5 == 0) {
                
                $goods_page += 1;
                
            }
            $i++;
        }

        if($goods_page > 0) {
            
            $guess_like = $model_goods -> getGoodsList(array(), 'goods_id,goods_name,goods_promotion_price,goods_image,goods_jingle,store_name', '', 'rand()', 0, $goods_page);
            foreach($guess_like as $k => $v) {
                
                //$guess_like[$k]['goods_image'] = thumb($v, 500);
                if(strpos(thumb($v,500),'default_goods_image_500.gif')){
                    $guess_like[$k]['goods_image'] = thumb($v,360);
                }else{
                    $guess_like[$k]['goods_image'] = thumb($v,500);
                }
                
            }
            
        }

        $data['store_list'] = $store_list;
        $data['guess_like'] = $guess_like;
        output_data($data, mobile_page($page_count));

    }
    
    /*
     * 获取广告数据
     * @param int $ap_id 广告位主键
     * @param int $limit 获取广告数
     * return array $adv_list 广告数组
     */
    private function get_banner($ap_id,$limit = 1) {
        
        //banner
        $condition['ap_id'] = $ap_id;
        $condition['field'] = 'adv_content,store_id';
        $adv_list = Model('adv')->getList($condition,'',$limit,'adv_id desc');
        if(!empty($adv_list)) {
            
            foreach ($adv_list as $key => $value) {
                $adv_list[$key] = unserialize($value['adv_content']);
                $adv_list[$key]['banner_id'] = $value['store_id'];
                $adv_list[$key]['adv_pic'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . unserialize($value['adv_content'])['adv_pic'];
            }
            
        } else {
            
            $adv_list = array();
            
        }
        
        return $adv_list;
  
    }
    
    /*
     * 优秀设计师品牌
     * @param int $page 页数
     * @param int $curpage 页码
     * add by lizh 2016/9/27 9:56
     * version 1.5.4
     */
    public function excellent_designer_listOp() {
        
        //设计师品牌
        $model_store = Model('store');
        $model_goods = Model('goods');
        $condition = array();
        $data = array();
        
        $condition['is_recommend'] = 1;
        $store_list = $model_store->getStoreOnlineList($condition, $this -> page, 'store_id desc', 'store_id,store_name,store_avatar,sc_id,app_banner,store_collect');
        $page_count = $model_store->gettotalpage();
        foreach ($store_list as $key => $value) {
            $store_list[$key]['store_name'] = htmlspecialchars_decode($store_list[$key]['store_name']);
            $store_list[$key]['store_avatar'] = getStoreLogo($store_list[$key]['store_avatar'], 'store_avatar');
            $store_list[$key]['search_list_goods'] = $model_goods->getIsrecommendGoods(array('store_id' => $value['store_id']), $this->page);
            $store_list[$key]['app_banner'] = $store_list[$key]['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['app_banner'] : "";
            foreach ($store_list[$key]['search_list_goods'] as $k => $v) {
                $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v, 240);
            }

        }

        $data['store_list'] = $store_list;
        output_data($data, mobile_page($page_count));

    }
    
     /*
     * 首页 -- 设计师品牌列表
     * @param int $page 页数
     * @param int $curpage 页码
     * @param int $type 类型 1：设计师品牌 2：优秀设计师品牌 
     * add by lizh 2016/9/27 9:56
     * version 1.5.4
     */
    public function designer_listOp() {
        
        //设计师品牌
        $model_store = Model('store');
        $model_goods = Model('goods');
        $condition = array();
        $data = array();
        
        if($_GET['type'] == 1) {
            
            $condition['is_recommend'] = 1;
            $store_list = $model_store->getStoreOnlineList($condition, $this -> page, 'store_id desc', 'store_id,store_name,store_avatar,sc_id,app_banner,store_collect');
            $page_count = $model_store->gettotalpage();
            foreach ($store_list as $key => $value) {
                $store_list[$key]['store_name'] = htmlspecialchars_decode($store_list[$key]['store_name']);
                $store_list[$key]['store_avatar'] = getStoreLogo($store_list[$key]['store_avatar'], 'store_avatar');
                $store_list[$key]['search_list_goods'] = $model_goods->getIsrecommendGoods(array('store_id' => $value['store_id']), $this->page);
                $store_list[$key]['app_banner'] = $store_list[$key]['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['app_banner'] : "";
                foreach ($store_list[$key]['search_list_goods'] as $k => $v) {
                    $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v, 240);
                }

            }
            
        } else {
            
            $condition['is_recommend'] = 1;
            $store_list = $model_store->getStoreOnlineList($condition, $this -> page, 'store_id desc', 'store_id,store_name,store_avatar,sc_id,app_banner,store_collect');
            $page_count = $model_store->gettotalpage();
            foreach ($store_list as $key => $value) {
                $store_list[$key]['store_name'] = htmlspecialchars_decode($store_list[$key]['store_name']);
                $store_list[$key]['store_avatar'] = getStoreLogo($store_list[$key]['store_avatar'], 'store_avatar');
                $store_list[$key]['search_list_goods'] = $model_goods->getIsrecommendGoods(array('store_id' => $value['store_id']), $this->page);
                $store_list[$key]['app_banner'] = $store_list[$key]['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['app_banner'] : "";
                foreach ($store_list[$key]['search_list_goods'] as $k => $v) {
                    $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v, 240);
                }

            }
            
        }
       

        $data['store_list'] = $store_list;
        output_data($data, mobile_page($page_count));

    }
}
