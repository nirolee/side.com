<?php

/**
 * 活动
 *
 *
 *
 * * */
defined('InShopNC') or exit('Access Invalid!');

class special_shareControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 点赞保存
     * */
    public function share_saveOp() {

//        $data = array();
//        $data['result'] = 'true';
//        $data['message'] = '点赞成功';
        $share_id = intval($_GET['special_id']);

        
        $model = Model('mb_special');
        //喜欢计数加1
        $update = array(
        'share_count' => array('exp', 'share_count+1')
        );
        $condition = array();
       $condition['special_id'] = $share_id;
        $model->table('mb_special')->where($condition)->update($update);

        //返回信息
        $data['result'] = 'true';
        $data['message'] = '分享成功';

        
         output_data($data);
    }

  

}
