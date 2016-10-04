<?php

/**
 * 我的收藏店铺
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('InShopNC') or exit('Access Invalid!');

class member_favorites_brandControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 收藏列表
     */
    public function favorites_listOp() {
        $model_favorites = Model('favorites');
        $model_brand = Model('brand');

        $favorites_list = $model_favorites->getStoreFavoritesList(array(
            'member_id' => $this->member_info['member_id'],
                ), '*', $this->page);

        $page_count = $model_favorites->gettotalpage();

        $brand_list = array();

        $favorites_list_indexed = array();
        foreach ($favorites_list as $v) {
            $item = array();
            $item['brand_id'] = $v['brand_id'];
            $item['brand_name'] = $v['brand_name'];
            $item['fav_time'] = $v['fav_time'];
            $item['fav_time_text'] = date('Y-m-d H:i', $v['fav_time']);

            $brand = $model_brand->getStoreInfoByID($v['brand_id']);
            $item['goods_count'] = $brand['goods_count'];
            $item['brand_collect'] = $brand['brand_collect'];

            $item['brand_avatar'] = $brand['brand_avatar'];
            if ($brand['brand_avatar']) {
                $item['brand_avatar_url'] = UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $brand['brand_avatar'];
            } else {
                $item['brand_avatar_url'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . DS . C('default_brand_avatar');
            }

            $brand_list[] = $item;
        }

        output_data(array('favorites_list' => $brand_list), mobile_page($page_count));
    }

    /**
     * 添加收藏
     */
    public function favorites_addOp() {

        $fav_id = intval($_POST['brand_id']);
        if ($fav_id <= 0) {
            output_error('参数错误');
        }

        $favorites_model = Model('favorites');

        //判断是否已经收藏

        $favorites_info = $favorites_model->getOneFavorites(array(
            'fav_id' => $fav_id,
            'fav_type' => 'brands',
            'member_id' => $this->member_info['member_id'],
        ));

        if (!empty($favorites_info)) {
            output_error('您已经收藏了该品牌');
        }


        //添加收藏
        $insert_arr = array();
        $insert_arr['member_id'] = $this->member_info['member_id'];
        $insert_arr['member_name'] = $this->member_info['member_name'];
        $insert_arr['fav_id'] = $fav_id;
        $insert_arr['fav_type'] = 'brands';
        $insert_arr['fav_time'] = time();
        $result = $favorites_model->addFavorites($insert_arr);
        if ($result) {
            //增加收藏数量
            $brand_model = Model('brand');
            $brand_model->editBrand( array('brand_id' => $fav_id),array('brand_collect' => array('exp', 'brand_collect+1')));
            output_data('1');
        } else {
            output_error('收藏失败');
        }
    }

    /**
     * 删除收藏
     */
    public function favorites_delOp() {
        $fav_id = intval($_POST['brand_id']);
        if ($fav_id <= 0) {
            output_error('参数错误');
        }

        $model_favorites = Model('favorites');
        $model_brand = Model('brand');

        $condition = array();
        $condition['fav_type'] = 'brands';
        $condition['fav_id'] = $fav_id;
        $condition['member_id'] = $this->member_info['member_id'];

        //判断是否已经收藏
        $favorites_info = $model_favorites->getOneFavorites($condition);
        if (empty($favorites_info)) {
            output_error('收藏删除失败');
        }

        $model_favorites->delFavorites($condition);

        

        output_data('1');
    }

}
