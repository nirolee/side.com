<?php

/**
 * 活动
 *
 *
 *
 * * */
defined('InShopNC') or exit('Access Invalid!');

class cms_likeControl extends mobileMemberControl {

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
        $article_id = intval($_GET['article_id']);
        $param = array();
        $param['like_object_id'] = $article_id;
        $param['like_member_id'] = $this->member_info['member_id'];
        $model_like = Model('cms_like');
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
                $condition['article_id'] = $article_id;
                $model->table('cms_article')->where($condition)->update($update);

                //返回信息
                $data['result'] = 'true';
                $data['message'] = '点赞成功';
                $cms = $model_cms_article->getOne($condition, '','article_id,like_count');
                $data['like_count'] = $cms['like_count'];
            } else {
                $data['result'] = 'false';
                $data['message'] = '保存失败';
            }
        } else {
            $data['result'] = 'false';
            $data['message'] = '已经点过赞了';
            $condition = array();
            $condition['article_id'] = $article_id;
            $cms = $model_cms_article->getOne($condition, '', 'article_id,like_count');
            $data['like_count'] = $cms['like_count'];
        }

        output_data($data);
    }

    /**
     * 喜欢删除
     * */
    public function like_dropOp() {
        $article_id = intval($_GET['article_id']);

        $param = array();
        $param['like_object_id'] = $article_id;
        $param['like_member_id'] = $this->member_info['member_id'];
        $model_like = Model('cms_like');
        $model_cms_article = Model('cms_article');
        $is_exist = $model_like->isExist($param);

        if ($is_exist) {
            $model = Model();
            $result = $model_like->drop(array('like_object_id' => $article_id));
            //喜欢计数加1
            $update = array(
                'like_count' => array('exp', 'like_count-1')
            );

            $condition = array();
            $condition['article_id'] = $article_id;
            $model->table('cms_article')->where($condition)->update($update);

            //返回信息
            if ($result) {
                $data['result'] = 'true';
                $data['message'] = '取消点赞成功';
                $cms = $model_cms_article->getOne($condition, '', 'like_count');
                $data['like_count'] = $cms['like_count'];
            }
        } else {
            $data['result'] = 'false';
            $data['message'] = '已经取消点赞了';
            $condition = array();
            $condition['article_id'] = $article_id;
            $cms = $model_cms_article->getOne($condition, '', 'like_count');
            $data['like_count'] = $cms['like_count'];
        }

        output_data($data);
    }

}
