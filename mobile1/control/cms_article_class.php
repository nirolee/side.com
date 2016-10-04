<?php
/**
 * cms文章 
 * ShopNc - www.shopnc.cn * 
 **/

defined('InShopNC') or exit('Access Invalid!');
class cms_article_classControl extends mobileHomeControl{

	public function __construct() {
        parent::__construct();
    }
    
    public function indexOp() {
        $cms_article_class_model	= Model('cms_article_class');
        $cms_article_model	= Model('cms_article');
        $condition	= array();

        $cms_article_class = $cms_article_class_model->getList($condition);
        output_data(array('cms_article_class' => $cms_article_class));		
    }
}
