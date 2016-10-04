<?php

/**
 * 前台品牌分类
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

class brandControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    public function recommend_listOp() {
        $brand_list = Model('brand')->getBrandPassedList(array('brand_recommend' => '1'), 'brand_id,brand_name,brand_pic,brand_country,buyer_name,buyer_yuju,brand_banner');
        $model_goods = Model('goods');
        if (!empty($brand_list)) {
            foreach ($brand_list as $key => $val) {
                $brand_list[$key]['brand_pic'] = brandImage($val['brand_pic']);
                if (!empty($_POST['key'])) {
                    $model_mb_user_token = Model('mb_user_token');
                    $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
                    $model_member = Model('member');
                    $this->member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
                    $favorites_model = Model('favorites');
                    $favorites_info = $favorites_model->getOneFavorites(array(
                        'fav_id' => $brand_list[$key]['brand_id'],
                        'fav_type' => 'brand',
                        'member_id' => $this->member_info['member_id'],
                    ));
                    if(!empty($favorites_info)) {
                        $brand_list[$key]['brand_state'] = '1';
                    }
                }
                
                $brand_list[$key]['brand_state'] = '0';
                $condition['brand_id'] = $brand_list[$key]['brand_id'];

                $field = 'goods_id,goods_image';
                $brand_list[$key]['goods_list'] = $model_goods->getGoodsList($condition, $field, 'rand()', $limit = 0, 5);
                foreach ($brand_list[$key]['goods_list'] as $k => $v) {
                    $brand_list[$key]['goods_list'][$k]['goods_image'] = thumb($v, 240);
                }
            }
        }
        output_data(array('brand_list' => $brand_list));
    }

    public function brand_detailOp() {
        $brand_id = intval($_GET ['brand_id']);
        $model_brand = Model('brand');
        $brand_detail = $model_brand->getBrandPassedInfoByID($brand_id);
        $brand_detail['brand_pic'] = brandImage($brand_detail['brand_pic']);
        $brand_detail['brand_banner'] = brandImage($brand_detail['brand_banner']);
        $condition['brand_id'] = $brand_detail['brand_id'];
        $field = 'goods_id,goods_image,goods_price,goods_name,goods_alias';
        $brand_detail['goods_list'] = Model('goods')->getGoodsList($condition, $field);
        foreach ($brand_detail['goods_list'] as $k => $v) {
            $brand_detail['goods_list'][$k]['goods_image'] = thumb($v, 240);
        }
        output_data($brand_detail);
    }

}
