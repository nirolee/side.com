<?php

/**
 * 抢购
 *
 *
 *
 * * */
defined('InShopNC') or exit('Access Invalid!');

class groupbuyControl extends mobileHomeControl {

    /**
     * 抢购
     * add by niro 2016/9/26
     */
    public function indexOp() {
        $model_groupbuy = Model('groupbuy');
        $model_setting = Model('setting');
        $condition = array();
        $
        $condition['groupbuy_type'] = 0; //限时抢
        $condition['state'] = 20;
        $list1 = $model_groupbuy->getGroupbuyList($condition, $this->page);
        $page_count = $model_groupbuy->gettotalpage();
        foreach ($list1 as $key => $value) {
            if($value['start_time']>time()){
            $groupbuy_list['not_start'][$key]['groupbuy_image'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image'];
            $groupbuy_list['not_start'][$key]['groupbuy_image1'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image1'];
            $groupbuy_list['not_start'][$key]['start_time'] = date('Y-m-d H:i:s', $value['start_time']);
            $groupbuy_list['not_start'][$key]['end_time'] = date('Y-m-d H:i:s', $value['end_time']);
            $groupbuy_list['not_start'][$key]['time'] = date('d天' . 'H:i:s', $value['end_time'] - time());
            }else{
            $groupbuy_list['start'][$key]['groupbuy_image'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image'];
            $groupbuy_list['start'][$key]['groupbuy_image1'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image1'];
            $groupbuy_list['start'][$key]['start_time'] = date('Y-m-d H:i:s', $value['start_time']);
            $groupbuy_list['start'][$key]['end_time'] = date('Y-m-d H:i:s', $value['end_time']);
            $groupbuy_list['start'][$key]['time'] = (string)date('d天' . 'H:i:s', $value['end_time'] - time());
            }
        }
         $condition['groupbuy_type'] = 0; //限时抢
        $condition['state'] = 32;
        $list = $model_groupbuy->getGroupbuyList($condition, $this->page);
        $page_count = $model_groupbuy->gettotalpage();
        foreach ($list as $key => $value) {
            $groupbuy_list['finish'][$key]['groupbuy_image'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image'];
            $groupbuy_list['finish'][$key]['groupbuy_image1'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image1'];
            $groupbuy_list['finish'][$key]['start_time'] = date('Y-m-d H:i:s', $value['start_time']);
            $groupbuy_list['finish'][$key]['end_time'] = date('Y-m-d H:i:s', $value['end_time']);
            $groupbuy_list['finish'][$key]['time'] = date('d天' . 'H:i:s', $value['end_time'] - time());
        }
//        $rules = $model_setting->where(array('name'=>'groupbuy_rules'))->find();
//        $groupbuy_list['groupbuy_rules'] = $rules['value'];
        $banner = $model_setting->where(array('name' => 'groupbuy_banner'))->find();
        $groupbuy_list['groupbuy_banner'] = UPLOAD_SITE_URL . DS . ATTACH_LIVE . DS . $banner['value'];
        output_data($groupbuy_list, mobile_page($page_count));
    }
    //未开始
    public function not_startOp() {
        $model_setting = Model('setting');
       $model_groupbuy = Model('groupbuy');
        $condition = array();
        $condition['groupbuy_type'] = 0; //限时抢
        $condition['state'] = 20;
        $list = $model_groupbuy->getGroupbuyList($condition, $this->page);
        $page_count = $model_groupbuy->gettotalpage();
        foreach ($list as $key => $value) {
             if(date('m-d',time()+86400)==date('m-d',$value['start_time'])){
            $groupbuy_list['groupbuy_list'][$key]['groupbuy_image'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image'];
            $groupbuy_list['groupbuy_list'][$key]['groupbuy_image1'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image1'];
            $groupbuy_list['groupbuy_list'][$key]['start_time'] = date('Y-m-d H:i:s', $value['start_time']);
            $groupbuy_list['groupbuy_list'][$key]['end_time'] = date('Y-m-d H:i:s', $value['end_time']);
            $time = (string)($value['start_time'] - time());
//            $h = floor($time/3600);
//            $m = floor(($time-$h*3600)/60);
//            $s = ($time-$h*3600)%60;
           $groupbuy_list['groupbuy_list'][$key]['time'] = $time;
            $groupbuy_list['groupbuy_list'][$key]['store_id'] = $value['store_id'];
            $groupbuy_list['groupbuy_list'][$key]['goods_id'] = $value['goods_id'];
            $groupbuy_list['groupbuy_list'][$key]['buy_quantity'] = $value['buy_quantity'];
           $groupbuy_list['groupbuy_list'][$key]['quantity'] = $value['quantity'] ;
            $groupbuy_list['groupbuy_list'][$key]['goods_price'] = $value['goods_price'];
            $groupbuy_list['groupbuy_list'][$key]['groupbuy_price'] = $value['groupbuy_price'];
            $groupbuy_list['groupbuy_list'][$key]['goods_name'] = $value['goods_name'];
            $groupbuy_list['groupbuy_list'][$key]['store_name'] = $value['store_name'];
            $groupbuy_list['groupbuy_list'][$key]['text'] = date('m-d',time()+86400);
            }
        }
        if($groupbuy_list['groupbuy_list']){
        sort($groupbuy_list['groupbuy_list']);
        }
        $banner = $model_setting->where(array('name' => 'groupbuy_banner'))->find();
        $groupbuy_list['groupbuy_banner'] = UPLOAD_SITE_URL . DS . ATTACH_LIVE . DS . $banner['value'];
        output_data($groupbuy_list, mobile_page($page_count));
    }
    
    //开始
    
    
     public function startOp() {
         $model_setting = Model('setting');
       $model_groupbuy = Model('groupbuy');
        $condition = array();
        $condition['groupbuy_type'] = 0; //限时抢
        $condition['state'] = 20;
        $list = $model_groupbuy->getGroupbuyList($condition, $this->page);
        $page_count = $model_groupbuy->gettotalpage();
        foreach ($list as $key => $value) {
             if(date('m-d',time())==date('m-d',$value['start_time'])){
            $groupbuy_list['groupbuy_list'][$key]['groupbuy_image'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image'];
            $groupbuy_list['groupbuy_list'][$key]['groupbuy_image1'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image1'];
            $groupbuy_list['groupbuy_list'][$key]['start_time'] = date('Y-m-d H:i:s', $value['start_time']);
            $groupbuy_list['groupbuy_list'][$key]['end_time'] = date('Y-m-d H:i:s', $value['end_time']);
            $time = $value['end_time'] - time();
//            $h = floor($time/3600);
//            $m = floor(($time-$h*3600)/60);
//            $s = ($time-$h*3600)%60;
           $groupbuy_list['groupbuy_list'][$key]['time'] = (string)$time ;
            $groupbuy_list['groupbuy_list'][$key]['store_id'] = $value['store_id'];
            $groupbuy_list['groupbuy_list'][$key]['goods_id'] = $value['goods_id'];
              $groupbuy_list['groupbuy_list'][$key]['buy_quantity'] = $value['buy_quantity'];
           $groupbuy_list['groupbuy_list'][$key]['quantity'] = $value['quantity'] ;
           $groupbuy_list['groupbuy_list'][$key]['goods_price'] = $value['goods_price'];
            $groupbuy_list['groupbuy_list'][$key]['groupbuy_price'] = $value['groupbuy_price'];
            $groupbuy_list['groupbuy_list'][$key]['goods_name'] = $value['goods_name'];
            $groupbuy_list['groupbuy_list'][$key]['store_name'] = $value['store_name'];
            }
        }
        if($groupbuy_list['groupbuy_list']){
        sort($groupbuy_list['groupbuy_list']);
        }
        $banner = $model_setting->where(array('name' => 'groupbuy_banner'))->find();
        $groupbuy_list['groupbuy_banner'] = UPLOAD_SITE_URL . DS . ATTACH_LIVE . DS . $banner['value'];
        output_data($groupbuy_list, mobile_page($page_count));
    }
    
    public function endOp() {
        $model_setting = Model('setting');
        $model_groupbuy = Model('groupbuy');
        $condition['groupbuy_type'] = 0; //限时抢
        $condition['state'] = 32;
        $list = $model_groupbuy->getGroupbuyList($condition, $this->page);
        $page_count = $model_groupbuy->gettotalpage();
        foreach ($list as $key => $value) {
            if(date('m-d',time())==date('m-d',$value['end_time'])){
             $groupbuy_list['groupbuy_list'][$key]['groupbuy_image'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image'];
             $groupbuy_list['groupbuy_list'][$key]['groupbuy_image1'] = UPLOAD_SITE_URL . '/shop/groupbuy/' . $value['store_id'] . '/' . $value['groupbuy_image1'];
            $groupbuy_list['groupbuy_list'][$key]['start_time'] = date('Y-m-d H:i:s', $value['start_time']);
            $groupbuy_list['groupbuy_list'][$key]['end_time'] = date('Y-m-d H:i:s', $value['end_time']);
            $groupbuy_list['groupbuy_list'][$key]['time'] = (string)(date( 'H:i:s', $value['end_time'] - time()));
            $groupbuy_list['groupbuy_list'][$key]['store_id'] = $value['store_id'];
            $groupbuy_list['groupbuy_list'][$key]['goods_id'] = $value['goods_id'];
            $groupbuy_list['groupbuy_list'][$key]['buy_quantity'] = $value['buy_quantity'];
           $groupbuy_list['groupbuy_list'][$key]['quantity'] = $value['quantity'] ;
            $groupbuy_list['groupbuy_list'][$key]['goods_price'] = $value['goods_price'];
            $groupbuy_list['groupbuy_list'][$key]['groupbuy_price'] = $value['groupbuy_price'];
            $groupbuy_list['groupbuy_list'][$key]['goods_name'] = $value['goods_name'];
            $groupbuy_list['groupbuy_list'][$key]['store_name'] = $value['store_name'];
            $groupbuy_list['groupbuy_list'][$key]['text'] = date('m-d',time()-86400);
            }
        }
        if($groupbuy_list['groupbuy_list']){
        sort($groupbuy_list['groupbuy_list']);
        }
//        $rules = $model_setting->where(array('name'=>'groupbuy_rules'))->find();
//        $groupbuy_list['groupbuy_rules'] = $rules['value'];
        $banner = $model_setting->where(array('name' => 'groupbuy_banner'))->find();
        $groupbuy_list['groupbuy_banner'] = UPLOAD_SITE_URL . DS . ATTACH_LIVE . DS . $banner['value'];
        output_data($groupbuy_list, mobile_page($page_count));
    }
}
