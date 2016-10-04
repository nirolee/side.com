<?php

/**
 * 活动
 *
 *
 *
 * * */
defined('InShopNC') or exit('Access Invalid!');

class special_likeControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 点赞保存
     * */
    public function like_saveOp() {

//        $data = array();
//        $data['result'] = 'true';
//        $data['message'] = '点赞成功';
        $like_id = intval($_GET['special_id']);

        
        $model = Model('mb_special');
        //喜欢计数加1
        $update = array(
        'like_count' => array('exp', 'like_count+1')
        );
        $condition = array();
       $condition['special_id'] = $like_id;
        $model->table('mb_special')->where($condition)->update($update);

        //返回信息
        $data['result'] = 'true';
        $data['message'] = '点赞成功';

        
         output_data($data);
    }

    /**
     * 喜欢删除
     * */
    public function like_dropOp() {
//         $data = array();
//        $data['result'] = 'true';
//        $data['message'] = '取消点赞成功';
        $like_id = intval($_GET['special_id']);

        
        $model = Model('mb_special');
        //喜欢计数加1
        $update = array(
        'like_count' => array('exp', 'like_count-1')
        );
        $condition = array();
       $condition['special_id'] = $like_id;
        $model->table('mb_special')->where($condition)->update($update);

        //返回信息
        $data['result'] = 'true';
        $data['message'] = '取消点赞成功';

        
         output_data($data);
    }

}
