<?php

/**
 * 文章评论
 * lizh
 * add by 2016.9.26
 */
defined('InShopNC') or exit('Access Invalid!');

class member_cms_commentControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 评论
     * add by lizh 16:47 2016/9/24
     * version 1.5.4
     */
    public function comment_addOp() {

        if(isset($this->member_info['member_id'])) {
			
            $insert_add['comment_type'] = 1;
            $insert_add['comment_object_id'] = $_POST['article_id'];
            if(empty($_POST['up_comment_id'])) {
                
               $insert_add['up_comment_id'] = 0; 
                
            } else {
                
                $insert_add['up_comment_id'] = $_POST['up_comment_id']; 
                
            }
//            if (strtoupper(CHARSET) == 'GBK') {
//                $insert_add['comment_message'] = Language::getGBK(str_replace("\\", "\\\\", json_encode($_POST['comment_message'])));
//            } else {
//                $insert_add['comment_message'] = str_replace("\\", "\\\\", json_encode($_POST['comment_message']));
//            }
            $insert_add['comment_message'] = $_POST['comment_message'];
            $insert_add['comment_member_id'] = $this->member_info['member_id'];
            $insert_add['comment_time'] = time();

            $cms_comment = Model('cms_comment');
             
            $model_member = Model('member');

            $rs = $cms_comment -> save($insert_add);

            if($rs) {
                
                $field = "cms_comment.comment_id,cms_comment.comment_message,cms_comment.comment_time,cms_comment.up_comment_id,member.member_id,member.member_name,member.member_avatar";
                $micro_comment_list = $cms_comment -> getListWithUserInfo(array(comment_id => $rs,comment_type => 1), 0, 'cms_comment.comment_id desc', $field);
               
                foreach($micro_comment_list as $k => $v) {
                    
                    $micro_comment_data = array();
                    $micro_comment_list[$k]['comment_time'] = date('Y-m-d',$v['comment_time']);
                    $micro_comment_list[$k]['member_avatar'] = getMemberAvatar($v['member_avatar']);
                    $micro_comment_list[$k]['like_count'] = 0;
                    $micro_comment_list[$k]['comment_message'] = $micro_comment_list[$k]['comment_message'];
                    if($v['up_comment_id']) {
                      
                        $micro_comment_data = $cms_comment->getOne(array(comment_type => 2, comment_id => $v['up_comment_id']));
                       
                        //$micro_comment_list[$k1]['up_member_avatar'] = getMemberAvatarForID($micro_comment_data['comment_member_id']);
                        $member_data = array();
                        $member_data = $model_member -> getMemberInfo(array(member_id => $micro_comment_data['comment_member_id']), 'member_name,member_truename');
                        $micro_comment_list[$k]['up_member_truename'] = $member_data['member_truename'];
                    }
                   

                }

                //用户头像
                $login_data = array();
                if(!empty($_POST['key'])) {

                    $login_data['avatar'] = getMemberAvatarForID($this->member_info['member_id']);

                } else {

                    $login_data['avatar'] = UPLOAD_SITE_URL.DS.defaultGoodsImage('360');

                }

                $data['status'] = '1';
                $data['message'] = '评论成功';
                $data['micro_comment_list'] = $micro_comment_list;
                $data['login_data'] = $login_data;

            } else {

                $data['status'] = '0';
                $data['message'] = '评论出错';

            }

        } else {
			
            $data['status'] = '0';
            $data['message'] = '未登录';
        
        }

        output_data($data);
	
    }
}
