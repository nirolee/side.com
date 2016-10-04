<?php

/**
 * 活动
 *
 *
 *
 * * */
defined('InShopNC') or exit('Access Invalid!');

class cms_article_likeControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 点赞保存
     * */
    public function like_saveOp() {

        $data = array();
        $data['result'] = 'true';
        $data['message'] = '点赞成功';
        $like_id = intval($_GET['like_id']);
        
        if ($like_id <= 0) {
            $data['result'] = 'false';
            $data['message'] = '点赞失败';
        }

        $param = array();
        $param["like_object_id"] = $like_id;
        $param['like_member_id'] = $this->member_info['member_id'];
        $model_like = Model('cms_article_like');
        $model_cms_article = Model('cms_article');
        $is_exist = $model_like->isExist($param);
        if (!$is_exist) {
            $param['like_time'] = time();
            $result = $model_like->save($param);
            if ($result) {
                $model = Model();
                //喜欢计数加1
                $update = array(
                    'like_count' => array('exp', 'like_count+1')
                );
                $condition = array();
                $condition['article_id'] = $like_id;
                $model->table("cms_article")->where($condition)->update($update);
  
                //返回信息
                $cms_article = $model_cms_article->getOne($condition, '', 'like_count');
                if(empty($cms_article['like_count'])) {
                
                    $cms_article['like_count'] = 0; 

                }
                $data['like_count'] = $cms_article['like_count'];
                $data['result'] = 'true';
                $array = array("点赞棒棒哒","爱你mua~~","点完赞关注我一下嘛","爱我你就赞赞我","敢不敢给我32个赞","我欣赏你的点赞","感谢你真诚的点赞","你点赞的样子好美","爱点赞的都颜值高","我也喜欢你");
                $rand=array_rand($array,1);
                $data['message'] = $array[$rand];
            } else {
                $data['result'] = 'false';
                $data['message'] = '保存失败';
            }
        } else {
            $data['result'] = 'false';
            $data['message'] = '已经点过赞了';
            $condition = array();
            $condition['article_id'] = $like_id;
            $cms_article = $model_cms_article->getOne($condition, '', 'like_count');
            if(empty($cms_article['like_count'])) {
                
                $cms_article['like_count'] = 0; 
                
            }
            $data['like_count'] = $cms_article['like_count'];
        }

        output_data($data);
    }

    /**
     * 喜欢删除
     * */
    public function like_dropOp() {
        $like_id = intval($_GET['like_id']);
        if ($like_id > 0) {
            $model_like = Model('cms_article_like');
            $model_cms_article = Model('cms_article');
            $param = array();
            $param["like_object_id"] = $like_id;
            $param['like_member_id'] = $this->member_info['member_id'];
            $is_exist = $model_like->isExist($param);
            if ($is_exist) {
                $model = Model();
                $result = $model_like->drop(array("like_object_id" => $like_id));
                $update = array(
                    'like_count' => array('exp', 'like_count-1')
                );

                $condition = array();
                $condition['$model_cms_article'] = $like_id;
                $model->table('cms_article')->where($condition)->update($update);
                if ($result) {
                    $data['result'] = 'true';
                    $data['message'] = '取消点赞成功';
                    $condition = array();
                    $condition["$model_cms_article"] = $like_id;
                    $cms_article = $model_cms_article->getOne($condition, '', 'like_count');
                    $data['like_count'] = $cms_article['like_count'];
                }
            } else {
                $data['result'] = 'false';
                $data['message'] = '取消点赞失败';
                $condition = array();
                $condition["article_id"] = $like_id;
                $cms_article = $model_cms_article->getOne($condition, '', 'like_count');
                $data['like_count'] = $cms_article['like_count'];
            }
        }
        output_data($data);
    }

   
	
  

}
