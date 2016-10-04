<?php

/**
 * 商品分类
 *
 *
 *
 * by www.shopnc.cn ShopNc商城V17 大数据版
 */
defined('InShopNC') or exit('Access Invalid!');

class goods_classControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        if (!empty($_GET['gc_id']) && intval($_GET['gc_id']) > 0) {
            $this->_get_class_list($_GET['gc_id']);
        } else {
            $this->_get_root_class();
        }
    }

    public function get_child_allOp() {
        if (!empty($_GET['gc_id']) && intval($_GET['gc_id']) > 0) {
            $this->_get_class_list($_GET['gc_id']);
        }
    }

    /**
     * 返回一级分类列表
     */
    private function _get_root_class() {
        $model_goods_class = Model('goods_class');
        $model_mb_category = Model('mb_category');
        $model_goods = Model('goods');
        $goods_class_array = Model('goods_class')->getGoodsClassForCacheModel();
        $class_list = $model_goods_class->getGoodsClassListByParentId(0);
        $mb_categroy = $model_mb_category->getLinkList(array());
        $mb_categroy = array_under_reset($mb_categroy, 'gc_id');
        foreach ($class_list as $key => $value) {
            if (!empty($mb_categroy[$value['gc_id']])) {
                $class_list[$key]['image'] = UPLOAD_SITE_URL . DS . ATTACH_MOBILE . DS . 'category' . DS . $mb_categroy[$value['gc_id']]['gc_thumb'];
            } else {
                $class_list[$key]['image'] = UPLOAD_SITE_URL . DS . 'index' . DS . $class_list[$key]['gc_id'] . '.png?1';
            }

            $class_list[$key]['text'] = '';
            $child_class_string = $goods_class_array[$value['gc_id']]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_list[$key]['text'] .= $goods_class_array[$child_class]['gc_id'] . '/';
            }

            $class_list[$key]['text'] = rtrim($class_list[$key]['text'], '/');
            $condition['gc_id_1'] = $class_list[$key]['gc_id'];
//            $field = 'goods_name,gc_id,goods_marketprice,goods_price,goods_promotion_price,goods_promotion_type';
//            $class_list[$key]['goods_list'] =  $model_goods->getGoodsList($condition,$field, $order = 'goods_i
//            d desc', $limit = 0, 6);
//           $class_list[$key]['brands_list'] = Model('brand')->getBrandPassedList(array('class_id' => $class_list[$key]['gc_id']));
//               if (!empty($class_list[$key]['brands_list'])) {
//            foreach ($class_list[$key]['brands_list'] as $bkey => $bval) {
//                $class_list[$key]['brands_list'][$bkey]['brand_pic'] = brandImage($bval['brand_pic']);
//            }
//        }
        }

        output_data(array('class_list' => $class_list));
    }

    /**
     * 根据分类编号返回下级分类列表
     */
    private function _get_class_list($gc_id) {
        $goods_class_array = Model('goods_class')->getGoodsClassForCacheModel();

        $goods_class = $goods_class_array[$gc_id];

        if (empty($goods_class['child'])) {
            //无下级分类返回0
            output_data(array('class_list' => '0'));
        } else {
            //返回下级分类列表
            $class_list = array();
            $child_class_string = $goods_class_array[$gc_id]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_item = array();
                $class_item['gc_id'] .= $goods_class_array[$child_class]['gc_id'];
                $class_item['gc_name'] .= htmlspecialchars_decode($goods_class_array[$child_class]['gc_name']);
               
                $class_item['image'] .= UPLOAD_SITE_URL . DS . 'shop' . DS . 'common' . DS . 'category-pic-' . $goods_class_array[$child_class]['gc_id'] . '.jpg';
                $class_list[] = $class_item;
            }
            output_data(array('class_list' => $class_list));
        }
    }

    /**
     * 返回一级分类列表
     * 
     */
    
    public function root_class_1_5_4Op() {

        $model = Model('goods_class');
        $goods_class_top = $model->getTreeClassList(1);
       
        foreach ($goods_class_top as $key => $value) {
            if ($key < 8) {
            $class_list[$key]['gc_id'] = $value['gc_id'];
            $class_list[$key]['gc_en_name'] = $value['gc_en_name'];
            $class_list[$key]['gc_name'] = $value['gc_name'];
            }
        }

        output_data($class_list);
    }
    

    /**
     * 根据分类编号返回下级分类列表
     */
    public function class_list_1_5_4Op() {
           //轮播图
        $condition['ap_id'] = 1280;
        $condition['field'] = 'adv_content,adv_title,store_id';
        $adv_list = Model('adv')->getList($condition, '', '1', 'adv_id asc');

        foreach ($adv_list as $key => $value) {
            //$adv_list[$key] = unserialize($value['adv_content']);
            $adv_list[$key]['special_id'] = $value['store_id'];
            $adv_list[$key]['special_title'] = $value['adv_title'];
            $adv_list[$key]['special_banner'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . unserialize($value['adv_content'])['adv_pic'];
            unset($adv_list[$key]['adv_content']);
        }
       
        if (!empty($_GET['gc_id']) && intval($_GET['gc_id']) > 0){
          
            $gc_id = $_GET['gc_id'];
            
            //分类下的热门品牌
            $model_store = Model('store');
            $store_list = array();
            $store_list = $model_store->getStoreListByGoodsClassId_1_5_4($gc_id);
            foreach ($store_list as $key => $value) {
           if(empty($store_list[$key]['store_avatar'])){
               $store_list[$key]['store_avatar']='';
           }else{
           
            $store_list[$key]['store_avatar'] = UPLOAD_SITE_URL . "/" . ATTACH_STORE . "/" . $value['store_avatar'];
           }
        }
            
            //二级分类
            
            $model_goods_class = Model('goods_class');
            $class_top = array(); 
            $class_top[] = $model_goods_class->getOneGoodsClassInfoByGcId($gc_id,'gc_name,gc_en_name'); 
            
          
            $goods_class_array = $model_goods_class->getGoodsClassForCacheModel();

            $goods_class = $goods_class_array[$gc_id];

            if (empty($goods_class['child'])) {
                //无下级分类返回0
                $class_list = 0; 
            } else {
                //返回下级分类列表
                $class_list = array();
                $child_class_string = $goods_class_array[$gc_id]['child'];
                $child_class_array = explode(',', $child_class_string);
                foreach ($child_class_array as $child_class) {
                    $class_item = array();
                    $class_item['gc_id'] .= $goods_class_array[$child_class]['gc_id'];
                    //$class_item['gc_parent_id'] .= $gc_id;
                    $class_item['gc_name'] .= htmlspecialchars_decode($goods_class_array[$child_class]['gc_name']);
                    if(mb_strlen($class_item['gc_name'],'utf-8')>4){
                    $class_item['gc_name'] = mb_substr($class_item['gc_name'],0,4,'utf-8').'..';
                    }
                    $class_item['image'] .= UPLOAD_SITE_URL . DS . 'shop' . DS . 'common' . DS . 'category-pic-' . $goods_class_array[$child_class]['gc_id'] . '.jpg';
                    $class_list[] = $class_item;
                }
                
            }
        }
        output_data(array('adv_list'=>$adv_list,'store_list'=>$store_list,'class_top'=>$class_top,'class_list'=>$class_list));
    }

}
