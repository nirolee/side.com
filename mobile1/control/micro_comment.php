<?php

/**
 * 达人秀评论
 *
 *
 *
 * * */
defined('InShopNC') or exit('Access Invalid!');

class micro_commentControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /*
     * 评论图片上传
     */
    public function comment_image_uploadOp() {
        $data = array();
        $data['status'] = 'success';
        if (isset($this->member_info['member_id'])) {
            if (!empty($_FILES['file']['name'])) {
                $upload = new UploadFile();
                $upload->set('default_dir', ATTACH_MICROSHOP_COMMENT . DS . $this->member_info['member_id']);
                $upload->set('thumb_width', '60,240');
                $upload->set('thumb_height', '5000,50000');
                $upload->set('thumb_ext', '_tiny,_list');

                $result = $upload->upfile('file');
                if (!$result) {
                    $data['status'] = 'fail';
                    $data['error'] = $upload->error;
                }
                $data['file'] = $upload->getSysSetPath() . $upload->file_name;
            }
        } else {
            $data['status'] = 'fail';
            $data['error'] = '未登录';
        }
        output_data($data);
    }

    /**
     * 评论保存
     * 
     */
    public function comment_saveOp() {

        $data = array();
        $data['result'] = 'true';
        $comment_id = intval($_POST['comment_id']);
        $comment_type = 2;
        if ($comment_id <= 0 || empty($comment_type) || empty($_POST['comment_message']) || mb_strlen($_POST['comment_message']) > 140) {
            $data['result'] = 'false';
            $data['message'] = '参数错误';
        }

        if (!empty($this->member_info['member_id'])) {
            $param = array();
            $param['comment_type'] = 2;
            $param["comment_object_id"] = $comment_id;
            if (strtoupper(CHARSET) == 'GBK') {
                $param['comment_message'] = Language::getGBK(str_replace("\\", "\\\\", json_encode($_POST['comment_message'])));
            } else {
                $param['comment_message'] = str_replace("\\", "\\\\", json_encode($_POST['comment_message']));
            }
            $param['comment_member_id'] = $this->member_info['member_id'];
            $param['comment_image'] = trim($_POST['comment_image']);
            $param['comment_time'] = time();
            $model_comment = Model('micro_comment');
            $result = $model_comment->save($param);
            if ($result) {

                //评论计数加1
                $model = Model();
                $update = array();
                $update['comment_count'] = array('exp', 'comment_count+1');
                $condition = array();
                $condition["personal_id"] = $comment_id;
                $model->table("micro_personal")->where($condition)->update($update);

                //返回信息
                $data['result'] = 'true';
                $data['member_name'] = $this->member_info['member_name'] . '：';
                $data['member_avatar'] = getMemberAvatar($this->member_info['member_avatar']);
                $data['comment_message'] = parsesmiles(stripslashes($param['comment_message']));
                $data['comment_time'] = date('Y-m-d H:i:s', $param['comment_time']);
                $data['comment_id'] = $result;

            } else {
                $data['result'] = 'false';
                $data['message'] = '发送失败';
            }
        } else {
            $data['result'] = 'false';
            $data['message'] = '未登录';
        }
        output_data($data);
    }

    /**
     * 评论删除
     * */
    public function comment_dropOp() {
        $data['result'] = 'false';
        $data['message'] = '删除失败';
        $comment_id = intval($_POST['comment_id']);
        if ($comment_id > 0) {
            $model_comment = Model('micro_comment');
            $comment_info = $model_comment->getOne(array('comment_id' => $comment_id));
            $micro_personal = Model('micro_personal')->getOne(array('personal_id'=>$comment_info['comment_object_id']));
            if ($comment_info['comment_member_id'] == $this->member_info['member_id']||$micro_personal['commend_member_id'] ==$this->member_info['member_id']) {
                $result = $model_comment->drop(array('comment_id' => $comment_id));
                if ($result) {
                    //评论计数减1
                    $model = Model();
                    $update = array();
                    $update['comment_count'] = array('exp', 'comment_count-1');
                    $condition = array();
                    $condition['comment_object_id'] = $comment_info['comment_object_id'];
                    $model->table("micro_personal")->where($condition)->update($update);
                    $data['result'] = 'true';
                    $data['message'] = '删除成功';
                }
            }
        }
        output_data($data);
    }
	
    /**
     * 瞬间评论
     * add by lizh 16:04 2016/9/24
     * version 1.5.4
     */
    public function comment_addOp() {
	
        if(isset($this->member_info['member_id'])) {
			
            $insert_add['comment_type'] = 2;
            $insert_add['comment_object_id'] = $_POST['personal_id'];
            if(empty($_POST['up_comment_id'])) {
                
               $insert_add['up_comment_id'] = 0; 
                
            } else {
                
                $insert_add['up_comment_id'] = $_POST['up_comment_id']; 
                
            }
            
            $insert_add['comment_message'] = $_POST['comment_message'];
            $insert_add['comment_member_id'] = $this->member_info['member_id'];
            $insert_add['comment_time'] = time();

            $micro_comment = Model('micro_comment');
             
            $model_member = Model('member');
            $rs = $micro_comment -> save($insert_add);
            if($rs) {
                
                $field = "micro_comment.comment_id,micro_comment.comment_message,micro_comment.comment_time,micro_comment.up_comment_id,member.member_id,member.member_name,member.member_avatar";
                $micro_comment_list = $micro_comment -> getListWithUserInfo(array(comment_id => $rs,comment_type => 2), 0, 'micro_comment.comment_id desc', $field);
               
                foreach($micro_comment_list as $k => $v) {
                    
                    $micro_comment_data = array();
                    $micro_comment_list[$k]['comment_time'] = date('Y/m/d',$v['comment_time']);
                    $micro_comment_list[$k]['member_avatar'] = getMemberAvatar($v['member_avatar']);
                    $micro_comment_list[$k]['like_count'] = 0;
                    $micro_comment_list[$k]['comment_message'] =$micro_comment_list[$k]['comment_message'];
                    if($v['up_comment_id']) {
                      
                        $micro_comment_data = $micro_comment->getOne(array(comment_type => 2, comment_id => $v['up_comment_id']));
                       
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
                //友盟推送
                $micro_personal = Model('micro_personal');
                $micro_personal_data = $micro_personal -> getOneData(array(personal_id => $_POST['personal_id']),'commend_member_id');
                
                $param = array();
                $param['code'] = 'member_reply';
                $param['member_id'] = $micro_personal_data['commend_member_id'];
                $param['type'] = 5;
                
                $comment_message = Model()->table('micro_comment')->getfby_comment_id($rs,'comment_message');

                $param['param'] = array(
                    'member_name' =>  $this->member_info['member_truename'],
                    'comment_message' => $comment_message
                );

                $rs = set_umeng_push($micro_personal_data['commend_member_id'], '有好友评论了你', $this -> member_info['member_truename'].'评论了你', array(type => 8,id => $_POST['personal_id']));
                QueueClient::push('sendMemberMsg', $param);
                
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
	
		
    /**
     * 评论点赞
     * add by lizh 14:32 2016/7/28
     **/
    public function comment_likeOp() {
		
		$micro_comment = Model('micro_comment');
		$comment_id = $_POST['comment_id'];
		
		if(!isset($this->member_info['member_id'])) {
			
			output_error('未登录');
			
		}
		
		if(empty($comment_id)) {
			
			$data = $micro_comment -> getOne(array(comment_id => $comment_id));
			$like_count = $data['like_count'];
			output_data(array(like_count => $like_count, status => 0, message => '点赞失败'));
			
		}

		$check = $micro_comment -> isExist(array(comment_id => $comment_id));
		if(!$check) {
			
			$data = $micro_comment -> getOne(array(comment_id => $comment_id));
			$like_count = $data['like_count'];
			output_data(array(like_count => $like_count, status => 0, message => '点赞失败'));
			
		}

		$rs = $micro_comment -> modify(array(like_count => array('exp','like_count+1')), array(comment_id => $comment_id));
		
		$data = $micro_comment -> getOne(array(comment_id => $comment_id));
		$like_count = $data['like_count'];
		
        output_data(array(like_count => $like_count, status => 1, message => '点赞成功'));
		
    }

}
