<?php

/**
 * 文章 
 * */
defined('InShopNC') or exit('Access Invalid!');

class cmsControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    public function article_classOp() {
        //获取文章列表分类
        $article_class_model = Model('cms_article_class');
        $article_class = $article_class_model->where()->select();
        output_data($article_class);
    }

    public function article_listOp() {
        //获取文章列表
        $article_model = Model('cms_article');
        $condition = array();
        $condition['article_class_id'] = intval($_GET['class_id']);
        $article_list = $article_model->getList($condition, $this->page, 'article_publish_time desc', 'article_id,article_title,article_image,article_publisher_id,article_abstract,article_publish_time,like_count,article_keyword,store_id');
        
        $page_count = $article_model->gettotalpage();
       

        foreach ($article_list as $key => $value) {
            $article_list[$key]['article_image'] = BASE_SITE_URL .DS  . DS . DIR_UPLOAD . DS . ATTACH_CMS . DS . 'article' . DS . $article_list[$key]['article_publisher_id'] . DS . unserialize($article_list[$key]['article_image'])['name'];
            $article_list[$key]['article_publish_time'] = date('M. j', $article_list[$key]['article_publish_time']);
             $article_list[$key]['url'] = BASE_SITE_URL .DS . 'wap/tmpl/cms_article_show_1.html?article_id='.$article_list[$key]['article_id'];
        }
        output_data($article_list, mobile_page($page_count));
    }

    public function article_detailOp() {
        $article_model = Model('cms_article');
        $condition = array();
        $condition['article_id'] = $_GET['article_id'];
        $article = $article_model->getOne($condition);
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
<style>img{width:100%}</style>".$article['article_content']) ;
    }

    public function comment_listOp() {

        if ($_GET['article_id'] > 0) {
            $condition = array();
            $condition['comment_object_id'] = $_GET['article_id'];
            $condition['comment_type'] = 1;
            $condition['comment_quote'] = '';
            $model_cms_comment = Model('cms_comment');
            $comment_list = $model_cms_comment->getListWithUserInfo($condition, '', '', 'comment_id,comment_object_id,comment_message,comment_member_id,comment_quote,comment_time,comment_up,member_name,member_avatar');
            $comment_quote_id = '';
            $comment_quote_list = array();
            foreach ($comment_list as $key => $value) {
                $comment_list[$key]['member_avatar'] = getMemberAvatar($comment_list[$key]['member_avatar']);
                $comment_list[$key]['comment_quote_list'] = $model_cms_comment->getListWithUserInfo(array('comment_quote' => $comment_list[$key]['comment_id']), '', '', 'comment_id,comment_object_id,comment_message,comment_member_id,comment_quote,comment_time,comment_up,member_name,member_avatar');
                foreach ($comment_list[$key]['comment_quote_list'] as $k => $v) {
                    $comment_list[$key]['comment_quote_list'][$k]['member_avatar'] = getMemberAvatar($comment_list[$key]['comment_quote_list'][$k]['member_avatar']);
                }
//                 $comment_quote_id .= $value['comment_quote'].',';
            }


//  
//            if(!empty($comment_quote_id)) {
//                $comment_quote_list = $model_cms_comment->getListWithUserInfo(array('comment_id'=>array('in', $comment_quote_id)),'','','comment_id,comment_object_id,comment_message,comment_member_id,comment_quote,comment_time,member_name,member_avatar');
//            }
//            if(!empty($comment_quote_list)) {
//                $comment_quote_list = array_under_reset($comment_quote_list, 'comment_id');
//            }
            output_data($comment_list);
        } else {
            output_data('文章不存在');
        }
    }

}
