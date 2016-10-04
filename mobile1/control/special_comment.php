<?php
/**
 */



defined('InShopNC') or exit('Access Invalid!');
class special_commentControl extends mobileMemberControl{

    public function __construct() {
        parent::__construct();
    } 
    
    /*
     * 评论图片上传
     */
    
     public function comment_image_uploadOp() {
        $data = array();
        $data['status'] = 'success';
        if(isset($this->member_info['member_id'])) {
            if(!empty($_FILES['file']['name'])) {
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_MB_SPECIAL . DS . $this->member_info['member_id']);
                $upload->set('thumb_width','60,240');
                $upload->set('thumb_height', '5000,50000');
                $upload->set('thumb_ext',	'_tiny,_list');	

                $result = $upload->upfile('file');
                if(!$result) {
                    $data['status'] = 'fail';
                    $data['error'] = $upload->error;
                }
                $data['file'] =  $upload->getSysSetPath().$upload->file_name;
            }
        } else {
            $data['status'] = 'fail';
            $data['error'] = '未登录';
        }
        output_data($data);
    
    }
  /**
     * 评论保存
     * */
    public function comment_saveOp() {
        $data = array();
        $data['result'] = 'true';
        $comment_id = intval($_GET['comment_id']);
        if ($comment_id <= 0  || empty($_POST['comment_message']) || mb_strlen($_POST['comment_message']) > 140) {
            $data['result'] = 'false';
            $data['message'] = '参数错误';
        }

        if (!empty($this->member_info['member_id'])) {
            $param = array();
            $param["comment_mb_id"] = $comment_id;
            if (strtoupper(CHARSET) == 'GBK') {
                $param['comment_message'] = Language::getGBK(str_replace("\\","\\\\",json_encode($_POST['comment_message'])));
            } else {
                $param['comment_message'] = str_replace("\\","\\\\",json_encode($_POST['comment_message']));
            }
            $param['comment_member_id'] = $this->member_info['member_id'];
            $param['comment_time'] = time();
            $param['comment_image'] = trim($_POST['comment_image']);
            $model_comment = Model('mb_special_comment');
            $result = $model_comment->save($param);
            if ($result) {

                //评论计数加1
                $model = Model();
                $update = array();
                $update['comment_count'] = array('exp', 'comment_count+1');
                $condition = array();
                $condition["special_id"] = $comment_id;
                $model->table("mb_special")->where($condition)->update($update);

                //返回信息
                $data['result'] = 'true';
                $data['member_name'] =$this->member_info['member_name'] . '：';
                $data['member_avatar'] = getMemberAvatar($this->member_info['member_avatar']);
//                $data['member_link'] = MICROSHOP_SITE_URL . '/index.php?act=home&member_id=' . $_SESSION['member_id'];
                $data['comment_message'] = $param['comment_message'];
                $data['comment_time'] = date('Y-m-d H:i:s', $param['comment_time']);
                $data['comment_id'] = $result;

//                //分享内容
//                if (isset($_GET['share_app_items'])) {
//                    $condition = array();
//                    $condition[$comment_type['type_key']] = $_GET['comment_id'];
//                    if ($_GET['type'] == 'store') {
//                        $info = $model->getOneWithStoreInfo($condition);
//                    } else {
//                        $info = $model->getOne($condition);
//                    }
//                    $info['commend_message'] = $param['comment_message'];
//                    $info['type'] = $_GET['type'];
//                    $info['url'] = MICROSHOP_SITE_URL . DS . "index.php?act={$_GET['type']}&op=detail&{$_GET['type']}_id=" . $_GET['comment_id'] . '#widgetcommenttitle';
//                    self::share_app_publish('comment', $info);
//                }
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
        $data['message'] = Language::get('nc_common_del_fail');
        $comment_id = intval($_GET['comment_id']);
        if ($comment_id > 0) {
            $model_comment = Model('mb_special_comment');
            $comment_info = $model_comment->getOne(array('comment_id' => $comment_id));
            if ($comment_info['comment_member_id'] == $_SESSION['member_id']) {
                $result = $model_comment->drop(array('comment_id' => $comment_id));
                if ($result) {

                    //评论计数减1
                    $comment_type = self::get_channel_type($_GET['type']);
                    if (!empty($comment_type)) {
                        $model = Model();
                        $update = array();
                        $update['comment_count'] = array('exp', 'comment_count-1');
                        $condition = array();
                        $condition[$comment_type['type_key']] = $comment_info['comment_mb_id'];
                        $model->table("micro_{$_GET['type']}")->where($condition)->update($update);
                    }

                    $data['result'] = 'true';
                    $data['message'] = Language::get('nc_common_del_succ');
                }
            }
        }
        self::echo_json($data);
    }
}
