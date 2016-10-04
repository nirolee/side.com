<?php

/**
 * 活动
 *
 *
 *
 * * */
defined('InShopNC') or exit('Access Invalid!');

class goods_likeControl extends mobileMemberControl {

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
        if ($like_id <= 0 ) {
            $data['result'] = 'false';
            $data['message'] = '点赞失败';
        }

        $param = array();
        $param["like_object_id"] = $like_id;
        $param['like_member_id'] = $this->member_info['member_id'];
        $model_like = Model('goods_like');
        $model_goods = Model('goods');
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
                $condition['goods_id'] = $like_id;
                $model->table("goods")->where($condition)->update($update);
                $goods = $model_goods->getGoodsInfo($condition, 'like_count');
                //返回信息
                $data['like_count'] = $goods['like_count'];
                $data['result'] = 'true';
                $data['message'] = '点赞成功';
            } else {
                $data['result'] = 'false';
                $data['message'] = '保存失败';
            }
        } else {
            $data['result'] = 'false';
            $data['message'] = '已经点过赞了';
            $condition = array();
            $condition['goods_id'] = $like_id;
            $goods = $model_goods->getGoodsInfo($condition,  'like_count');
            $data['like_count'] = $goods['like_count'];
        }

        output_data($data);
    }

    /**
     * 喜欢删除
     * */
    public function like_dropOp() {
        $like_id = intval($_GET['like_id']);
        if ($like_id > 0) {
            $model_like = Model('goods_like');
            $model_goods = Model('goods');
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
            $condition['goods_id'] = $like_id;
            $model->table('goods')->where($condition)->update($update);
                if ($result) {
                    $data['result'] = 'true';
                    $data['message'] = '取消点赞成功';
                    $condition = array();
                    $condition["goods_id"] = $like_id;
                    $goods = $model_goods->getGoodsInfo($condition,'like_count');
                    $data['like_count'] = $goods['like_count'];
                }
            } else {
                $data['result'] = 'false';
                $data['message'] = '取消点赞失败';
                $condition = array();
                $condition["goods_id"] = $like_id;
                $goods = $model_goods->getGoodsInfo($condition,'like_count');
                $data['like_count'] = $goods['like_count'];
            }
        }
        output_data($data);
    }


}
