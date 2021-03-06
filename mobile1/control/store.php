<?php

/**
 * 商品
 *
 *
 *
 * by www.shopnc.cn ShopNc商城V17 大数据版
 */
defined('InShopNC') or exit('Access Invalid!');

class storeControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 商品列表
     */
    public function goods_listOp() {
        $model_goods = Model('goods');

//查询条件
        $condition = array();
        if (!empty($_GET['store_id']) && intval($_GET['store_id']) > 0) {
            $condition['store_id'] = $_GET['store_id'];
        } elseif (!empty($_GET['keyword'])) {
            $condition['goods_name|goods_jingle'] = array('like', '%' . $_GET['keyword'] . '%');
        }

//所需字段
        $fieldstr = "goods_id,goods_commonid,store_id,goods_name,goods_price,goods_marketprice,goods_image,goods_salenum,evaluation_good_star,evaluation_count";

//排序方式
        $order = $this->_goods_list_order($_GET['key'], $_GET['order']);

        $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fieldstr, $order, $this->page);
        $page_count = $model_goods->gettotalpage();

//处理商品列表(团购、限时折扣、商品图片)
        $goods_list = $this->_goods_list_extend($goods_list);

        output_data(array('goods_list' => $goods_list), mobile_page($page_count));
    }

    /**
     * 商品列表排序方式
     */
    private function _goods_list_order($key, $order) {
        $result = 'goods_id desc';
        if (!empty($key)) {

            $sequence = 'desc';
            if ($order == 1) {
                $sequence = 'asc';
            }

            switch ($key) {
//销量
                case '1' :
                    $result = 'goods_salenum' . ' ' . $sequence;
                    break;
//浏览量
                case '2' :
                    $result = 'goods_click' . ' ' . $sequence;
                    break;
//价格
                case '3' :
                    $result = 'goods_price' . ' ' . $sequence;
                    break;
            }
        }
        return $result;
    }

    /**
     * 处理商品列表(团购、限时折扣、商品图片)
     */
    private function _goods_list_extend($goods_list) {
//获取商品列表编号数组
        $commonid_array = array();
        $goodsid_array = array();
        foreach ($goods_list as $key => $value) {
            $commonid_array[] = $value['goods_commonid'];
            $goodsid_array[] = $value['goods_id'];
        }

//促销
        $groupbuy_list = Model('groupbuy')->getGroupbuyListByGoodsCommonIDString(implode(',', $commonid_array));
        $xianshi_list = Model('p_xianshi_goods')->getXianshiGoodsListByGoodsString(implode(',', $goodsid_array));
        foreach ($goods_list as $key => $value) {
//团购
            if (isset($groupbuy_list[$value['goods_commonid']])) {
                $goods_list[$key]['goods_price'] = $groupbuy_list[$value['goods_commonid']]['groupbuy_price'];
                $goods_list[$key]['group_flag'] = true;
            } else {
                $goods_list[$key]['group_flag'] = false;
            }

//限时折扣
            if (isset($xianshi_list[$value['goods_id']]) && !$goods_list[$key]['group_flag']) {
                $goods_list[$key]['goods_price'] = $xianshi_list[$value['goods_id']]['xianshi_price'];
                $goods_list[$key]['xianshi_flag'] = true;
            } else {
                $goods_list[$key]['xianshi_flag'] = false;
            }

//商品图片url
            $goods_list[$key]['goods_image_url'] = cthumb($value['goods_image'], 360, $value['store_id']);

            unset($goods_list[$key]['store_id']);
            unset($goods_list[$key]['goods_commonid']);
            unset($goods_list[$key]['nc_distinct']);
        }

        return $goods_list;
    }

    /**
     * 商品详细页
     */
    public function store_detailOp() {
        $store_id = (int) $_REQUEST['store_id'];
// 商品详细信息
        $model_store = Model('store');
        $store_info = $model_store->getStoreOnlineInfoByID($store_id);

        if (empty($store_info)) {
            output_error('店铺不存在');
        }
        $store_info['app_banner'] = $store_info['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_info['app_banner'] : '';

// 店铺头像
        $store_info['store_avatar'] = $store_info['store_avatar'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_info['store_avatar'] : UPLOAD_SITE_URL . '/' . ATTACH_COMMON . DS . C('default_store_avatar');
// 页头背景图
//       $store_info['mb_title_img'] = $store_online_info['mb_title_img'] ? UPLOAD_SITE_URL.'/'.ATTACH_STORE.'/'.$store_online_info['mb_title_img'] : '';
//开店时间        
        $store_info['store_time_text'] = $store_info['store_time'] ? @date('Y-m-d', $store_info['store_time']) : '';

// 如果已登录 判断该店铺是否已被收藏
//        if ($memberId = $this->getMemberIdIfExists()) {
//            $c = (int) Model('favorites')->getStoreFavoritesCountByStoreId($store_id, $memberId);
//            $store_info['is_favorate'] = $c ;
//        } else {
//            $store_info['is_favorate'] = false;
//        }


        $store_detail['store_info'] = $store_info;
// //店铺导航
// $model_store_navigation = Model('store_navigation');
// $store_navigation_list = $model_store_navigation->getStoreNavigationList(array('sn_store_id' => $store_id));
// $store_detail['store_navigation_list'] = $store_navigation_list;
//幻灯片图片
        if ($this->store_info['store_slide'] != '' && $this->store_info['store_slide'] != ',,,,') {
            $store_detail['store_slide'] = explode(',', $this->store_info['store_slide']);
            $store_detail['store_slide_url'] = explode(',', $this->store_info['store_slide_url']);
        }

//店铺详细信息处理
        $store_detail = $this->_store_detail_extend($store_info);

//店主推荐
        $where = array();
        $where['store_id'] = $store_id;
        $where['goods_commend'] = 1; //是否推荐
//$where['is_book'] = 0;// 默认不显示预订商品

        $model_goods = Model('goods');
        $goods_fields = $this->getGoodsFields();
        $page = $_GET['page'];
        $goods_list = $model_goods->getGoodsListByColorDistinct($where, $goods_fields, 'goods_id desc', $page);
        $goods_list = $this->_goods_list_extend($goods_list);
        $goods_list_all = $model_goods->getGoodsListByColorDistinct($where, $goods_fields, 'goods_id desc', '');
        $goods_list_all = $this->_goods_list_extend($goods_list_all);
        pagecmd('setEachNum', $page);
        if (empty($page)) {
            $page_count = 1;
        } else {
            $page_count = ceil(count($goods_list_all) / $page);
        }
        output_data(array(
            'store_info' => $store_detail,
            'rec_goods_list_count' => count($goods_list),
            'rec_goods_list' => $goods_list,
            'page_count' => $page_count,
        ));
    }

    /**
     * 店铺首页
     * add by lizh 15:07 2016/7/26
     */
    public function store_detail_1_5Op() {

        $store_id = (int) $_GET['store_id'];
        // 商品详细信息
        $model_store = Model('store');
        $store_info = $model_store->getStoreOneList(array(store_id => $store_id), 'store_id,store_avatar,store_name,area_info,store_introduction,app_banner,store_description');
      
        $store_info['store_avatar'] = getStoreLogo($store_info['store_avatar'], 'store_avatar');
        $store_info['app_banner'] = $store_info['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_info['app_banner'] : "";
        if (empty($store_info['store_introduction'])) {

            if (empty($store_info['store_description'])) {

                $store_info['store_description'] = '点击查看公司介绍';
            }

            $store_info['store_introduction'] = '暂无介绍';
        } else {

            if (empty($store_info['store_description'])) {

                $store_info['store_description'] = '点击查看公司介绍';
            }

            $store_info['store_introduction'] = htmlspecialchars_decode($store_info['store_introduction']);
        }

        if (empty($store_info) || empty($store_id)) {

            output_data(array());
        }

        $where = array();
        $where['store_id'] = $store_id;
        $order = 'goods_id desc';

        $model_goods = Model('goods');
        $goods_fields = 'goods_id,goods_name,goods_price,goods_image';
        $goods_list = $model_goods->getGoodsListByColorDistinct($where, $goods_fields, $order, 0, $show_num);
        $goods_list = $this->_goods_list_extend($goods_list);
        

        output_data(array(store_info => $store_info, goods_list => $goods_list));
    }

    /**
     * 店铺首页
     * add by lbb 10:28 2016/9/22
     */
    public function store_detail_1_5_4Op() {

        $store_id = (int) $_GET['store_id'];
        // 商品详细信息
        $model_store = Model('store');
        $store_info = $model_store->getStoreOneList(array(store_id => $store_id), 'store_id,store_avatar,store_name,store_collect,area_info,store_introduction,store_description,store_state');
        $store_info['store_name'] = htmlspecialchars_decode($store_info['store_name']);
        $store_info['store_avatar'] = getStoreLogo($store_info['store_avatar'], 'store_avatar');
      
            if(strpos($store_info['area_info'],"\t")!=FALSE){
            $array = explode("\t",$store_info['area_info']);
            $store_info['area_info'] = $array[1]; 
            }else{
            $store_info['area_info'] = '未知';    
            }
        
        // $store_info['app_banner'] = $store_info['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_info['app_banner'] : "";
        if (empty($store_info['store_introduction'])) {

            if (empty($store_info['store_description'])) {

                $store_info['store_description'] = '点击查看公司介绍';
            }

            $store_info['store_introduction'] = '暂无介绍';
        } else {

            if (empty($store_info['store_description'])) {

                $store_info['store_description'] = '点击查看公司介绍';
            }

            $store_info['store_introduction'] = htmlspecialchars_decode($store_info['store_introduction']);
        }

        if (empty($store_info) || empty($store_id)) {

            output_data(array());
        }

        $where = array();
        $where['store_id'] = $store_id;
        //获取商品排行列表
        if (!empty($_GET['order'])) {
            $link = $_GET['order'];
            switch ($link) {
                case 1:
                    $order = 'goods_click desc';
                    break;
                case 2:
                    $order = 'goods_salenum desc';
                    break;
                case 3:
                    $order = 'goods_price asc';
                    break;
                case 4:
                    $order = 'goods_price desc';
                    break;
            }
        } else {
            $order = 'goods_click desc';
        }

        //选取该品牌中3个最新商品展示
        $new_where = array();
        $new_where['store_id'] = $store_id;
        $new_where['goods_state'] = 1;
        $new_goods_order = 'goods_id desc';
        $model_goods = Model('goods');
        $goods_fields = 'goods_id,goods_name,goods_price,goods_promotion_price,goods_image';
        $goods_new_list = $model_goods->getGoodsList($new_where, $goods_fields, '', $new_goods_order, '3');
         foreach($goods_new_list as $k=>$v){
            
             $goods_new_list[$k]['goods_image'] = thumb($v);
        }   
        $goods_list = $model_goods->getGoodsListByColorDistinct($where, $goods_fields, $order, 0, $show_num);
        $goods_list = $this->_goods_list_extend($goods_list);
        foreach($goods_list as $k=>$v){
            
            $goods_list[$k]['goods_image'] = thumb($v);
        }   

        if (!empty($_GET['key'])) {
            $model_mb_user_token = Model('mb_user_token');
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($_GET['key']);
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
            $favorites = Model('favorites');

            $member_info = $member_info['member_id'];
            $rs = $favorites->isExist(array(fav_id => $store_id, fav_type => 'store', member_id => $member_id));
            if ($rs) {

                $favorites_state = '1';
            } else {

                $favorites_state = '0';
            }
            //登陆显示是否收藏该商品

            foreach ($goods_list as $key => $value) {
                $where = array();
                $where['member_id'] = $member_info['member_id'];
                $where['fav_id'] = $value['goods_id'];
                $where['fav_type'] = 'goods';
                $collected = $favorites->isExist($where);  //查询当前用户是否已收藏该商品
                if ($collected) {
                    $goods_list[$key]['is_collect'] = 1;   //已收藏
                } else {
                    $goods_list[$key]['is_collect'] = 0;   //未收藏
                }
            }
        } else {
            $favorites_state = '0';
            foreach ($goods_list as $key => $value) {
                $goods_list[$key]['is_collect'] = 0;   //未收藏
            }
        }
        $store_info['is_favorate'] = $favorites_state;

        output_data(array(store_info => $store_info, goods_new_list => $goods_new_list, goods_list => $goods_list));
    }

    public function store_listOp() {
        $model_store = Model('store');
        $condition['store_state'] = 1;
        $condition['is_artisan'] = 1;
        $condition['sc_id'] = 4;
        $field = "store_id,store_name,seller_name,store_avatar,store_collect";
        $store_list = $model_store->where($condition)->field($field)->order('store_id desc')->page(5)->select();
//        $store_list = $model_store->getStoreSearchList($store_list);
//
        foreach ($store_list as $key => $value) {
            $store_list[$key]['store_avatar'] = getStoreLogo($store_list[$key]['store_avatar'], $type = 'store_avatar');
//            foreach ( $store_list[$key]['search_list_goods'] as $k => $v) {
//               $store_list[$key]['search_list_goods'][$k]['goods_image'] = UPLOAD_SITE_URL.'/'.ATTACH_GOODS.'/'.$store_list[$key]['store_id'].'/'. $store_list[$key]['search_list_goods'][$k]['goods_image'];
//            }
        }
        output_data($store_list);
    }

    public function keywordOp() {
        $model_store = Model('store');
        $condition['store_state'] = 1;
//        output_data($_GET);
        if ($_GET['sc_id'] == 2) {
            $condition['sc_id'] = array('in', "1,2");
        } else {
            $condition['sc_id'] = $_GET['sc_id'];
        }
        $keyword = $_GET['keyword'];

        $field = "store_id,store_name,seller_name,sc_id,store_avatar,store_collect";
        $store_list = $model_store->where($condition)->field($field)->order('store_id asc')->page(5)->select();
        $store_list = $model_store->getStoreSearchList($store_list);
//
        foreach ($store_list as $key => $value) {
            $store_list[$key]['store_avatar'] = getStoreLogo($store_list[$key]['store_avatar'], $type = 'store_avatar');
            foreach ($store_list[$key]['search_list_goods'] as $k => $v) {
                $store_list[$key]['search_list_goods'][$k]['goods_image'] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/' . $store_list[$key]['store_id'] . '/' . $store_list[$key]['search_list_goods'][$k]['goods_image'];
            }
            $store_list[$key]['store_state'] = 0;
        }
        if (!empty($_POST['key'])) {
            $model_mb_user_token = Model('mb_user_token');
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($_POST['key']);
            $model_member = Model('member');
            $this->member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
            $favorites_model = Model('favorites');
            $insert_arr = array();
            $insert_arr['member_id'] = $this->member_info['member_id'];
            $insert_arr['member_name'] = $this->member_info['member_name'];
            $insert_arr['fav_id'] = $fav_id;
            $insert_arr['fav_type'] = 'store';
            $insert_arr['fav_time'] = time();
            $result = $favorites_model->addFavorites($insert_arr);

            if ($result) {
                //增加收藏数量
                $store_model = Model('store');
                $store_model->editStore(array('store_collect' => array('exp', 'store_collect+1')), array('store_id' => $fav_id));
                $favorites_info = $favorites_model->getOneFavorites(array(
                    'fav_id' => $store_list[$key]['store_id'],
                    'fav_type' => 'store',
                    'member_id' => $this->member_info['member_id'],
                ));
            }
            if (!empty($favorites_info)) {
                $store_list[$key]['store_state'] = '1';
            }
        } output_data($store_list);
    }

    public function keyword_1_5_3Op() {
        $model_store = Model('store');
        if (!empty($_GET['keyword'])) {
            $condition['store_name'] = array('like', $_GET['keyword'] . '%');
        }
        $keyword_list = $model_store->getStoreOnlineList($condition, $this->page, '', 'store_name');
        foreach ($keyword_list as $key => $value) {
            $keyword_list[$key] = $value['store_name'];
        }
        output_data($keyword_list);
    }

    public function store_bind_class_listOp() {
        $model_store = Model('store');
        $model_store_bind_class = Model('store_bind_class');
        $class_id = $_GET['class_id'];
        $keyword = $_GET['keyword'];
        if ($keyword != '') {
            $condition['store.store_name'] = array('like', '%' . $keyword . '%');
        }
        $condition['store.sc_id'] = '4';
        $condition['store.store_state'] = '1';
        $condition['store.is_artisan'] = '1';

        if ($class_id) {
            $condition['store_bind_class.class_1'] = $class_id;
            $store_list = $model_store_bind_class->getStorelist($condition, $this->page, 'store_id asc', 'store_id,store_name,store_avatar,store_collect');
        }
        $store_list_all = $model_store_bind_class->getStorelist($condition, '', 'store_id asc', 'store.store_id,store.store_name,store.store_avatar,store.store_collect');
        $store_list = $model_store_bind_class->getStorelist($condition, $this->page, 'store_id asc', 'store.store_id,store.store_name,store.store_avatar,store.store_collect');
        if (empty($this->page)) {
            $page_count = 1;
        } else {
            $page_count = ceil(count($store_list_all) / $this->page);
        }
        if (!empty($store_list)) {
            $store_list = $model_store->getStoreSearchList($store_list);
            foreach ($store_list as $key => $value) {
                $store_list[$key]['store_avatar'] = UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['store_avatar'];
                $store_list[$key]['app_banner'] = $store_list[$key]['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['app_banner'] : '';
                foreach ($store_list[$key]['search_list_goods'] as $k => $v) {
                    $store_list[$key]['search_list_goods'][$k]['goods_image'] = cthumb($store_list[$key]['search_list_goods'][$k]['goods_image']);
                }
                $store_list[$key]['store_state'] = '0';

                if (!empty($_GET['key'])) {
                    $model_mb_user_token = Model('mb_user_token');
                    $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($_GET['key']);
                    $model_member = Model('member');
                    $member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);


                    $favorites_model = Model('favorites');
                    $favorites_info = $favorites_model->getOneFavorites(array(
                        'fav_id' => $store_list[$key]['store_id'],
                        'fav_type' => 'store',
                        'member_id' => $member_info['member_id'],
                    ));

                    if (!empty($favorites_info)) {
                        $store_list[$key]['store_state'] = '1';
                    }
                }
            }
        }

        output_data($store_list, mobile_page($page_count));
    }

//    public function store_bind_class_listOp() {
//        $model_store = Model('store');
//        $model_store_bind_class = Model('store_bind_class');
//        $class_id = $_GET['class_id'];
//
//        if ($class_id) {
//            $condition['class_1'] = $class_id;
//            $store_id_list[] = $model_store_bind_class->getStoreBindClassList($condition, $this->page, 'store_id asc', 'store_id');
//        }
//        $store_id_list[] = $model_store_bind_class->getStoreBindClassList($condition, '1000', 'store_id asc', 'store_id');
//        $temp = $this->a_array_unique($store_id_list[0]); //二维数组去重
//        $temp = array_merge($temp); //重排序下标
//        foreach ($temp as $n => $m) {
//            $condition = array();
//            $condition['store_state'] = '1';
//            $condition['store_id'] = $m['store_id'];
//            $condition['sc_id'] = '4';
//            $condition['is_artisan '] = 1;
//            $keyword = $_GET['keyword'];
//            if ($keyword != '') {
//                $condition['store_name'] = array('like', '%' . $keyword . '%');
//            }
//
//            $store_list = $model_store->getStoreList($condition, $this->page, 'store_id asc', 'store_id,store_name,seller_name,store_avatar,app_banner,store_collect,sc_id');
//
//
//            if (!empty($store_list)) {
//                $store_list = $model_store->getStoreSearchList($store_list);
//                foreach ($store_list as $key => $value) {
//                    $store_list[$key]['store_avatar'] = UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['store_avatar'];
//                    $store_list[$key]['app_banner'] = $store_list[$key]['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['app_banner'] : '';
//                    foreach ($store_list[$key]['search_list_goods'] as $k => $v) {
//                        $store_list[$key]['search_list_goods'][$k]['goods_image'] = cthumb($store_list[$key]['search_list_goods'][$k]['goods_image']);
//                    }
//                    $store_list[$key]['goods_image'] = $store_list[$key]['search_list_goods'][0]['goods_image'];
//                }
//                $s_list[] = $store_list[0]; //数组合并新数组
//            }
//        }
//        $page_count = $model_store->gettotalpage();
//        if (!empty($_POST['key'])) {
//            $model_mb_user_token = Model('mb_user_token');
//            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($_POST['key']);
//            $model_member = Model('member');
//            $this->member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
//            $favorites_model = Model('favorites');
//            $favorites_info = $favorites_model->getOneFavorites(array(
//                'fav_id' => $s_list[$key]['store_id'],
//                'fav_type' => 'store',
//                'member_id' => $this->member_info['member_id'],
//            ));
//            if (!empty($favorites_info)) {
//                $s_list[$key]['store_state'] = '1';
//            }
//        }
//
//        output_data($s_list, mobile_page($page_count));
//    }
//二维数组去重，$array为二维数组
    private function a_array_unique($array) {
        $out = array();
        foreach ($array as $key => $value) {
            if (!in_array($value, $out)) {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    /**
     * 店铺商品
     */
    public function store_goodsOp() {
        $param = $_REQUEST;

        $store_id = (int) $param['store_id'];
        if ($store_id <= 0) {
            output_error('参数错误');
        }
        $stc_id = (int) $param['stc_id'];
        $keyword = trim((string) $param['keyword']);

        $condition = array();
        $condition['store_id'] = $store_id;

// 默认不显示预订商品
//$condition['is_book'] = 0;

        if ($stc_id > 0) {
            $condition['goods_stcids'] = array('like', '%,' . $stc_id . ',%');
        }
//促销类型
        if ($param['prom_type']) {
            switch ($param['prom_type']) {
                case 'xianshi':
                    $condition['goods_promotion_type'] = 2;
                    break;
                case 'groupbuy':
                    $condition['goods_promotion_type'] = 1;
                    break;
            }
        }
        if ($keyword != '') {
            $condition['goods_name'] = array('like', '%' . $keyword . '%');
        }
        $price_from = preg_match('/^[\d.]{1,20}$/', $param['price_from']) ? $param['price_from'] : null;
        $price_to = preg_match('/^[\d.]{1,20}$/', $param['price_to']) ? $param['price_to'] : null;
        if ($price_from && $price_from) {
            $condition['goods_promotion_price'] = array('between', "{$price_from},{$price_to}");
        } elseif ($price_from) {
            $condition['goods_promotion_price'] = array('egt', $price_from);
        } elseif ($price_to) {
            $condition['goods_promotion_price'] = array('elt', $price_to);
        }

// 排序
        $order = (int) $param['order'] == 1 ? 'asc' : 'desc';
        switch (trim($param['key'])) {
            case '1':
                $order = 'goods_id ' . $order;
                break;
            case '2':
                $order = 'goods_promotion_price ' . $order;
                break;
            case '3':
                $order = 'goods_salenum ' . $order;
                break;
            case '4':
                $order = 'goods_collect ' . $order;
                break;
            case '5':
                $order = 'goods_click ' . $order;
                break;
            default:
                $order = 'goods_id desc';
                break;
        }

        $model_goods = Model('goods');

        $goods_fields = $this->getGoodsFields();
        $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $goods_fields, $order, $this->page);
        $page_count = $model_goods->gettotalpage();

        $goods_list = $this->_goods_list_extend($goods_list);

        output_data(array(
            'goods_list_count' => count($goods_list),
            'goods_list' => $goods_list,
                ), mobile_page($page_count));
    }

    /**
     * 店铺详细信息处理
     */
    private function _store_detail_extend($store_detail) {
//整理数据
        unset($store_detail['store_info']['goods_commonid']);
        unset($store_detail['store_info']['gc_id']);
        unset($store_detail['store_info']['gc_name']);
        unset($goods_detail['goods_info']['store_id']);
        unset($goods_detail['goods_info']['store_name']);
        unset($store_detail['store_info']['brand_id']);
        unset($store_detail['store_info']['brand_name']);
        unset($store_detail['store_info']['type_id']);
        unset($store_detail['store_info']['goods_image']);
        unset($store_detail['store_info']['goods_body']);
        unset($store_detail['store_info']['goods_state']);
        unset($store_detail['store_info']['goods_stateremark']);
        unset($store_detail['store_info']['goods_verify']);
        unset($store_detail['store_info']['goods_verifyremark']);
        unset($store_detail['store_info']['goods_lock']);
        unset($store_detail['store_info']['goods_addtime']);
        unset($store_detail['store_info']['goods_edittime']);
        unset($store_detail['store_info']['goods_selltime']);
        unset($store_detail['store_info']['goods_show']);
        unset($store_detail['store_info']['goods_commend']);

        return $store_detail;
    }

// /**
//  * 商品详细页
//  */
// public function goods_bodyOp() {
//     $store_id = intval($_GET ['store_id']);
//     $model_goods = Model('goods');
//     $goods_info = $model_goods->getGoodsInfo(array('goods_id' => $goods_id));
//     $goods_common_info = $model_goods->getGoodeCommonInfo(array('goods_commonid' => $goods_info['goods_commonid']));
//     Tpl::output('goods_common_info', $goods_common_info);
//     Tpl::showpage('goods_body');
// }

    private function getGoodsFields() {
        return implode(',', array(
            'goods_id',
            'goods_commonid',
            'store_id',
            'store_name',
            'goods_name',
            'goods_alias',
            'goods_price',
            'goods_promotion_price',
            'goods_promotion_type',
            'goods_marketprice',
            'goods_image',
            'goods_salenum',
            'evaluation_good_star',
            'evaluation_count',
            'is_virtual',
            'is_presell',
            'is_fcode',
            'have_gift',
            'goods_addtime',
        ));
    }

    /**
     * 店铺商品排行
     */
    public function store_goods_rankOp() {
        $store_id = (int) $_REQUEST['store_id'];
        if ($store_id <= 0) {
            output_data(array());
        }
        $ordertype = ($t = trim($_REQUEST['ordertype'])) ? $t : 'salenumdesc';
        $show_num = ($t = intval($_REQUEST['num'])) > 0 ? $t : 10;

        $where = array();
        $where['store_id'] = $store_id;
// 默认不显示预订商品
//$where['is_book'] = 0;
// 排序
        switch ($ordertype) {
            case 'salenumdesc':
                $order = 'goods_salenum desc';
                break;
            case 'salenumasc':
                $order = 'goods_salenum asc';
                break;
            case 'collectdesc':
                $order = 'goods_collect desc';
                break;
            case 'collectasc':
                $order = 'goods_collect asc';
                break;
            case 'clickdesc':
                $order = 'goods_click desc';
                break;
            case 'clickasc':
                $order = 'goods_click asc';
                break;
        }
        if ($order) {
            $order .= ',goods_id desc';
        } else {
            $order = 'goods_id desc';
        }
        $model_goods = Model('goods');
        $goods_fields = $this->getGoodsFields();
        $goods_list = $model_goods->getGoodsListByColorDistinct($where, $goods_fields, $order, 0, $show_num);
        $goods_list = $this->_goods_list_extend($goods_list);
        output_data(array('goods_list' => $goods_list));
    }

    /**
     * 店铺商品上新
     */
    public function store_new_goodsOp() {
        $store_id = (int) $_REQUEST['store_id'];
        if ($store_id <= 0) {
            output_data(array('goods_list' => array()));
        }
        $show_day = ($t = intval($_REQUEST['show_day'])) > 0 ? $t : 30;
        $where = array();
        $where['store_id'] = $store_id;
//$where['is_book'] = 0;//默认不显示预订商品
        $stime = strtotime(date('Y-m-d', time() - 86400 * $show_day));
        $etime = $stime + 86400 * ($show_day + 1);
        $where['goods_addtime'] = array('between', array($stime, $etime));
        $order = 'goods_addtime desc, goods_id desc';
        $model_goods = Model('goods');
        $goods_fields = $this->getGoodsFields();
        $goods_list = $model_goods->getGoodsListByColorDistinct($where, $goods_fields, $order, $this->page);
        $page_count = $model_goods->gettotalpage();
        if ($goods_list) {
            $goods_list = $this->_goods_list_extend($goods_list);
            foreach ($goods_list as $k => $v) {
                $v['goods_addtime_text'] = $v['goods_addtime'] ? @date('Y年m月d日', $v['goods_addtime']) : '';
                $goods_list[$k] = $v;
            }
        }
        output_data(array('goods_list' => $goods_list), mobile_page($page_count));
    }

    /**
     * 店铺活动
     */
    public function store_promotionOp() {
        $xianshi_array = Model('p_xianshi');
        $promotion['promotion'] = $condition = array();
        $condition['store_id'] = $_POST["store_id"];
        $xianshi = $xianshi_array->getXianshiList($condition);
        if (!empty($xianshi)) {
            foreach ($xianshi as $key => $value) {
                $xianshi[$key]['start_time_text'] = date('Y-m-d', $value['start_time']);
                $xianshi[$key]['end_time_text'] = date('Y-m-d', $value['end_time']);
            }
            $promotion['promotion']['xianshi'] = $xianshi;
        }
        $mansong_array = Model('p_mansong');
        $mansong = $mansong_array->getMansongInfoByStoreID($_POST["store_id"]);
        if (!empty($mansong)) {
            $mansong['start_time_text'] = date('Y-m-d', $mansong['start_time']);
            $mansong['end_time_text'] = date('Y-m-d', $mansong['end_time']);
            $promotion['promotion']['mansong'] = $mansong;
        }
        output_data($promotion);
    }

    /**
     * 设计师品牌1.5.4版本查看更多
     * add by lbb 15:00 2016/9/21
     *  version 1.5.4
     * 优秀设计师 参数type=1
     */
    public function designers_1_5_4Op() {

        // $page_count2 = $model_adv->gettotalpage();

        $data = array();
        

        //设计师品牌
        $model_store = Model('store');
        $favorites = Model('favorites');
        $condition = array();
        $condition['is_recommend'] = 1;


        //搜索条件
        if (empty($_GET['keyword'])) {

            $condition = array();
            //轮播图
            $where = array();
            $where['ap_id'] = 1284;
            $where['field'] = 'adv_content,adv_title,store_id';
            $model_adv = Model('adv');
            $adv_list = $model_adv->getList($where, '', '', 'adv_id asc');


            foreach ($adv_list as $key => $value) {
                //$adv_list[$key] = unserialize($value['adv_content']);
                //$adv_list[$key]['special_id'] = $value['store_id'];
                $adv_list[$key]['special_title'] = $value['adv_title'];
                $adv_list[$key]['special_banner'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . unserialize($value['adv_content'])['adv_pic'];
                unset($adv_list[$key]['adv_content']);
            }
            $data['banner'] = $adv_list;
        } else {

            $condition['store_name'] = array('like', '%' . $_GET['keyword'] . '%');
        }

        $type = $_GET['type'];

        if ($type == 1) {
            
        }

        //排序条件
        if (empty($_GET['order'])) {

            $order = 'store_sort desc';
        } else {

            $order = 'store_id desc';
        }

        //
        $store_list = $model_store->getStoreOnlineList($condition, $this->page, $order, 'store_id,store_name,store_avatar,store_keywords,app_banner,store_collect,area_info,store_description');
        $store_list = $model_store->getStoreSearchList_1_5_4($store_list);
        $page_count = $model_store->gettotalpage();

        if (!empty($_GET['key'])) {

            $model_mb_user_token = Model('mb_user_token');
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($_GET['key']);
            $member_id = $mb_user_token_info['member_id'];
        } else {

            $member_id = "";
        }
        foreach ($store_list as $key => $value) {
            $store_list[$key]['store_name'] = htmlspecialchars_decode($store_list[$key]['store_name']);
            $store_list[$key]['store_avatar'] = getStoreLogo($store_list[$key]['store_avatar'], 'store_avatar');
            
            if(strpos($value['area_info'],"\t")!=FALSE){
            $array = explode("\t",$value['area_info']);
            $store_list[$key]['area_info'] = $array[1]; 
            }else{
            $store_list[$key]['area_info'] = '未知';    
            }
             
            $store_list[$key]['app_banner'] = $store_list[$key]['app_banner'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_list[$key]['app_banner'] : "";
            $store_list[$key]['store_signature'] = '';
            $store_id = $value['store_id'];

            if (!empty($member_id)) {

                $rs = $favorites->isExist(array(fav_id => $store_id, fav_type => 'store', member_id => $member_id));
                if ($rs) {

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
                if (strpos(thumb($v, 500), 'default_goods_image_500.gif')) {
                    $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v);
                } else {
                    $store_list[$key]['search_list_goods'][$k]['goods_image'] = thumb($v, 500);
                }
            }
        }
        $data['store_list'] = $store_list;
        //output_data($adv_list, mobile_page($page_count2));
        output_data($data, mobile_page($page_count));
    }

}
