<?php

/**
 * 文章 
 * */
defined('InShopNC') or exit('Access Invalid!');

class cms_commentControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }
    
        
    /*
     * 评论列表
     * @param int $article_id 文章ID
     * add by lizh 19:20 2016/9/28
     * version 1.5.4
     */
    public function comment_listOp() {

        $article_id = $_GET['article_id'];
        
        $cms_comment = Model('cms_comment');
        $model_member = Model('member');
        
         //用户评论
        $field = "cms_comment.comment_id,cms_comment.up_comment_id,cms_comment.comment_message,cms_comment.comment_time,member.member_id,member.member_name,member.member_truename,member.member_avatar";
        $micro_comment_list = $cms_comment -> getListWithUserInfo(array(comment_object_id => $article_id,comment_type => 1), '', 'cms_comment.comment_id desc', $field);
        
        foreach($micro_comment_list as $k => $v) {

            $micro_comment_list[$k]['comment_time'] = date('Y-m-d',$v['comment_time']);
            $micro_comment_list[$k]['member_avatar'] = getMemberAvatar($v['member_avatar']);
            $micro_comment_list[$k]['comment_message'] = $micro_comment_list[$k]['comment_message'];
            
            if($v['up_comment_id']) {
                $micro_comment_data = array();   
                $member_data = array();
                $micro_comment_data = $cms_comment -> getOne(array(comment_type => 2, comment_id => $v['up_comment_id']));
                //$micro_comment_list[$k1]['up_member_avatar'] = getMemberAvatarForID($micro_comment_data['comment_member_id']);
                $member_data = $model_member -> getMemberInfo(array(member_id => $micro_comment_data['comment_member_id']), 'member_name,member_truename');
                $micro_comment_list[$k]['up_member_truename'] = $member_data['member_truename'];
            }

        }
        
        output_data(array(micro_comment_list => $micro_comment_list));
        
    }
    
    public function comment_saveOp() {
        if (!empty($_POST['comment_id'])) {
            $param = array();
            $param['comment_type'] = 1;
            $param["comment_object_id"] = 1;
            if (strtoupper(CHARSET) == 'GBK') {
                $param['comment_message'] = Language::getGBK(trim($_POST['comment_message']));
            } else {
                $param['comment_message'] = trim($_POST['comment_message']);
            }
            $param['comment_member_id'] = $this->member_info['member_id'];
            $param['comment_time'] = time();

            $model_comment = Model('cms_comment');

            if (!empty($_POST['comment_id'])) {
                $comment_detail = $model_comment->getOne(array('comment_id' => $_POST['comment_id']));
                if (empty($comment_detail['comment_quote'])) {
                    $param['comment_quote'] = $_POST['comment_id'];
                } else {
                    $param['comment_quote'] = $comment_detail['comment_quote'] . ',' . $_POST['comment_id'];
                }
            } else {
                $param['comment_quote'] = '';
            }

            $result = $model_comment->save($param);
            if ($result) {

                //评论计数加1
                $model = Model();
                $update = array();
                $update["article_comment_count"] = array('exp', "article_comment_count" . '+1');
                $condition = array();
                $condition["comment_object_id"] = 1;
                $model->modify($update, $condition);

                //返回信息
                $data['result'] = 'true';
                $data['message'] = "评论成功";
                $data['member_name'] = $this->member_info['member_name'] ;
                $data['member_avatar'] = getMemberAvatar($this->member_info['member_avatar']);
//                $data['member_link'] = SITEURL . DS . 'index.php?act=member_snshome&mid=' . $_SESSION['member_id'];
                $data['comment_message'] = parsesmiles(stripslashes($param['comment_message']));
                $data['comment_time'] = date('Y-m-d H:i:s', $param['comment_time']);
                $data['comment_id'] = $result;
            } else {
                $data['result'] = 'false';
                $data['message'] = "评论失败";
            }
        } else {
            $data['result'] = 'false';
            $data['message'] = "未登录";
        }
    }
    
   
}
