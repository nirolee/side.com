<?php

/**
 * cms文章  
 * */
defined('InShopNC') or exit('Access Invalid!');

class cms_articleControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 文章列表
     */
    public function cms_article_listOp() {
        if (!empty($_GET['class_id']) && intval($_GET['class_id']) > 0) {
            $cms_article_class_model = Model('cms_article_class');
            $cms_article_model = Model('cms_article');

            $condition = array();
            $condition['article_state'] = 3; //1:草稿 2:待审核 3：已发布 4：回收站
            $condition['article_class_id'] = intval($_GET['class_id']);

            $cms_article_list = $cms_article_model->getList($condition, '', 'article_publish_time desc', $field = 'article_id,article_title', ''); //文章列表
            //echo '<pre>';print_r($cms_article_list);
            $cms_article_type_name = $this->cms_article_type_name(); //分类信息

            output_data(array('cms_article_list' => $cms_article_list, 'cms_article_type_name' => $cms_article_type_name));
        } else {
            output_error('缺少参数:文章分类编号');
        }
    }

    /**
     * 根据类别编号获取文章类别信息
     */
    private function cms_article_type_name() {
        if (!empty($_GET['class_id']) && intval($_GET['class_id']) > 0) {
            $cms_article_class_model = Model('cms_article_class');
            $cms_article_class = $cms_article_class_model->getOne(intval($_GET['class_id']));
            return ($cms_article_class['class_name']);
        } else {
            return ('缺少参数:文章分类编号');
        }
    }

    /**
     * 单篇文章显示
     */
    public function cms_article_showOp() {
        $cms_article_model = Model('cms_article');

        if (!empty($_GET['article_id']) && intval($_GET['article_id']) > 0) {

            $cms_article_detail = $cms_article_model->getOne(array('article_id' => intval($_GET['article_id'])));

            if (empty($cms_article_detail)) {
                echo('文章不存在');
            } else {
                //echo $cms_article_detail['article_content'];
                print_r(" <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />
                    <meta name=\"Author\" contect=\"U2FsdGVkX1+liZRYkVWAWC6HsmKNJKZKIr5plAJdZUSg1A==\">
                    <meta name=\"apple-mobile-web-app-capable\" content=\"yes\" />
                    <meta name=\"apple-touch-fullscreen\" content=\"yes\" />
                    <meta name=\"format-detection\" content=\"telephone=no\"/>
                    <meta name=\"apple-mobile-web-app-status-bar-style\" content=\"black-translucent\" />
                    <meta name=\"format-detection\" content=\"telephone=no\" />
                    <meta name=\"msapplication-tap-highlight\" content=\"no\" />
                    <meta name=\"viewport\" content=\"initial-scale=1,maximum-scale=1,minimum-scale=1\" />
                    <style>img{width:100%}</style>".$cms_article_detail['article_content']
                ) ;
            }
        } else {
            echo('缺少参数:文章编号3');
        }
    }
    
    public function cms_article_show_1Op() {
        $cms_article_model = Model('cms_article');

        if (!empty($_GET['article_id']) && intval($_GET['article_id']) > 0) {

            $cms_article_detail = $cms_article_model->getOne(array('article_id' => intval($_GET['article_id']),('article_class_id') => 6));
            
            $model_store = Model('store');
            
            $store_info = $model_store->getStoreInfoByID($cms_article_detail['store_id']);
            
            $cms_article_detail['store_avatar'] = $store_info['store_avatar'] ? UPLOAD_SITE_URL . '/' . ATTACH_STORE . '/' . $store_info['store_avatar'] : UPLOAD_SITE_URL . '/' . ATTACH_COMMON . DS . C('default_store_avatar');
            $cms_article_detail['store_name'] = $store_info['store_name'];
            $cms_article_detail['store_description'] = $store_info['store_description'];
            $favorites_model = Model('favorites');
            if (!empty($_GET['key'])) {
                $model_mb_user_token = Model('mb_user_token');
                $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($_GET['key']);
                $model_member = Model('member');
                $member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
                
                $favorites_info = $favorites_model->getOneFavorites(array(
                        'fav_id' => $cms_article_detail['store_id'],
                        'fav_type' => 'store',
                        'member_id' => $member_info['member_id'],
                    ));

                if (!empty($favorites_info)) {
                    $cms_article_detail['store_state'] = '1';

                }
            }
            
            $cms_article_detail['store_collect'] = $favorites_model->getStoreFavoritesCountByStoreId($cms_article_detail['store_id'],"");
            $cms_article_detail['like_count'] = $cms_article_detail['store_collect'];
            
            if (empty($cms_article_detail)) {
                echo('文章不存在');
            } else {
                //echo $cms_article_detail['article_content'];
                output_data($cms_article_detail) ;
            }
        } else {
            echo('缺少参数:文章编号3');
        }
    }
     
    /**
     * 每日文章列表接口
     * version 1.5.3
     */
    public function daily_articlesOp() {
        
        $class_id = 7;
        $cms_article_model = Model('cms_article');

        $condition = array();
        $condition['article_state'] = 3; //1:草稿 2:待审核 3：已发布 4：回收站
        $condition['article_class_id'] = $class_id;

        $cms_article_list = $cms_article_model->getList($condition, $this->page, 'article_id desc', $field = 'article_id,article_title,article_image,article_publisher_id,article_abstract,article_publish_time,like_count', ''); //文章列表
        $page_count = $cms_article_model->gettotalpage();
        foreach($cms_article_list as $k => $v) {
            
            //$article_image = $v['article_image'];
            $cms_article_list[$k]['article_image'] = UPLOAD_SITE_URL . DS . ATTACH_CMS . DS . 'article' . DS . $v['article_publisher_id'] . DS . unserialize($v['article_image'])['name'];
            $cms_article_list[$k]['article_publish_time'] = date('Y-m-s', $v['article_publish_time']);
        }
        //echo '<pre>';print_r($cms_article_list);
        //$cms_article_type_name = $this->cms_article_type_name(); //分类信息

        output_data(array('cms_article_list' => $cms_article_list),mobile_page($page_count));

    }

}
