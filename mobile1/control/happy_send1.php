<?php

/**
 * 欢乐送
 *
 * by niro 2016/8/5
 *
 */
defined('InShopNC') or exit('Access Invalid!');

class happy_sendControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        $model_goods = Model('goods');
        $condition = array();
//         if($_GET['keyword']){
//            $condition['goods_common.goods_name'] = array('like', '%' . $_GET['keyword'] . '%');
//         }
        if($_GET['price_id']){
        $hps_id['price_id'] = intval($_GET['price_id']);
        }
        if($_GET['sex_id']){
        $hps_id['sex_id'] = intval($_GET['sex_id']);
        }
        if($_GET['year_id']){
         $hps_id['year_id']= intval($_GET['year_id']);
        }
        if($_GET['relationship_id']){
        $hps_id['relationship_id'] = intval($_GET['relationship_id']);
        }
        if( $_GET['scenes_id']){
        $hps_id['scenes_id'] = intval($_GET['scenes_id']);
        }
        $hps_id = Model('happy_send')->getAttributeList($hps_id);
        foreach ($hps_id as $key => $value) {
            $hps_id[$key] = intval($value['hps_id']);
        }
        $hps_id = $hps_id?implode(',',$hps_id):0;
        if($hps_id==0){
            output_data(array()); 
        }
        $condition['goods_common.hps_id'] = strpos($hps_id,',')?array("in",$hps_id):$hps_id;
        $condition['goods_common.is_happysend'] = 1;
        $happy_send = $model_goods->getGoodsListByRecommend($condition, 'goods.goods_id,goods.goods_name,goods.goods_promotion_price,goods.goods_price,goods.goods_image', 'goods_id desc', $this->page);
//        $page_count = $model_goods->getGoodsCommonCount(array('is_happysend'=>1));
        $page_count = $model_goods->gettotalpage();
        foreach ($happy_send as $key => $value) {
            $happy_send[$key]['goods_image'] = thumb($value);
        }
        output_data($happy_send, mobile_page($page_count));
    }
    public function arrayOp() {
        $model_happy_send = Model('happy_send');
        $hps_info['price'] = $model_happy_send->getAttributeValueList(array('type' => 'price'), 'attr_value_name,hps_value_id');
        $hps_info['sex'] = $model_happy_send->getAttributeValueList(array('type' => 'sex'), 'attr_value_name,hps_value_id');
        $hps_info['relationship'] = $model_happy_send->getAttributeValueList(array('type' => 'relationship'), 'attr_value_name,hps_value_id'); 
        $hps_info['scenes'] = $model_happy_send->getAttributeValueList(array('type' => 'scenes'), 'attr_value_name,hps_value_id');
        $hps_info['year'] = $model_happy_send->getAttributeValueList(array('type' => 'year'), 'attr_value_name,hps_value_id');
        output_data($hps_info);
    }
    
    public function price_arrayOp() {
        $model_happy_send = Model('happy_send');
        $hps_info = $model_happy_send->getAttributeValueList(array('type' => 'price'), 'attr_value_name,hps_value_id');
        output_data($hps_info);
    }

    public function sex_arrayOp() {
        $model_happy_send = Model('happy_send');
        $hps_info = $model_happy_send->getAttributeValueList(array('type' => 'sex'), 'attr_value_name,hps_value_id');
        output_data($hps_info);
    }

    public function relationship_arrayOp() {
        $model_happy_send = Model('happy_send');
        $hps_info = $model_happy_send->getAttributeValueList(array('type' => 'relationship'), 'attr_value_name,hps_value_id'); 
       
        output_data($hps_info);
    }
    
        public function scenes_arrayOp() {
        $model_happy_send = Model('happy_send');
        $hps_info = $model_happy_send->getAttributeValueList(array('type' => 'scenes'), 'attr_value_name,hps_value_id');
        output_data($hps_info);
    }

    public function year_arrayOp() {
        $model_happy_send = Model('happy_send');
        $hps_info = $model_happy_send->getAttributeValueList(array('type' => 'year'), 'attr_value_name,hps_value_id');
        output_data($hps_info);
    }

        public function commend_listOp() {
        $model_goods = Model('goods');
        $condition = array();
//         if($_GET['keyword']){
//            $condition['goods_common.goods_name'] = array('like', '%' . $_GET['keyword'] . '%');
//         }

        $condition['goods_common.is_happysend'] = 1;
        $condition['goods_common.is_recommend'] = 1;
        $happy_send = $model_goods->getGoodsListByRecommend($condition, 'goods.goods_id,goods.goods_name,goods.goods_promotion_price,goods.goods_price,goods.goods_image', 'goods_id desc', 20);
//        $page_count = $model_goods->getGoodsCommonCount(array('is_happysend'=>1));
        foreach ($happy_send as $key => $value) {
            $happy_send[$key]['goods_image'] = thumb($value);
        }
        output_data($happy_send);
    }
    
   
}
