<?php
/**
 */



defined('InShopNC') or exit('Access Invalid!');
class desinger_joininControl extends mobileMemberControl{

    public function __construct() {
        parent::__construct();
    } 
    
    /*
     * 图片上传
     */
    
     public function image_uploadOp() {
        $data = array();
        $data['status'] = 'success';
        if(isset($this->member_info['member_id'])) {
            if(!empty($_FILES['file']['name'])) {
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_JOININ . DS . $this->member_info['member_id']);
                $upload->set('thumb_width','60,240');
                $upload->set('thumb_height', '5000,50000');
                $upload->set('thumb_ext',	'_list');	
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
     * 设计师入驻资料保存
     * */
    public function upload_saveOp() {
        $data = array();
        $data['result'] = 'true';
        $data['member_id']= $this->member_info['member_id'];
        if (!empty($data['member_id'])) {
            $param = array();
            $param['member_id'] = (int)$this->member_info['member_id'];
            $param['member_name'] = $this->member_info['member_name'];
            $param['member_truename'] = $_POST['member_truename'];
            $param['member_sex'] = $_POST['sex'];
            $param['contacts_phone'] = $_POST['phone'];
            $param['contacts_email'] = $_POST['email'];
            $param['class_name'] = $_POST['class_name'];
            $param['experience'] = $_POST['experience'];
            $param['image'] = trim($_POST['image']);
            $param['ctime'] = time();
            $model_desinger_joinin = Model('desinger_joinin');
            $check_save = $model_desinger_joinin->getOne(array('member_id'=> $this->member_info['member_id']));
            if($check_save){
                output_error('已提交审核，请耐心等待');
            }  else {
               $result = $model_desinger_joinin->save($param);
            }
            
            if ($result) {

                //返回信息
                $data['result'] = 'true';
                $data['message'] = '提交审核成功';

            } else {
                $data['result'] = 'false';
                $data['message'] = '提交失败';
            }
        } else {
            $data['result'] = 'false';
            $data['message'] = '未登录';
        }
        output_data($data);
    }
    
    public function getOp() {
        $model_desinger_joinin = Model('desinger_joinin');
        $data = $model_desinger_joinin -> getList();
        output_data($data);
    }
    public function dropOp() {
        $model_desinger_joinin = Model('desinger_joinin');
        $data = $model_desinger_joinin -> drop(array('member_id'=> $this->member_info['member_id']));
        output_data($data);
    }

}
