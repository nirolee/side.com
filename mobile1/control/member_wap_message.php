<?php

/**
 * 我的反馈
 *
 *
 *
 *
 * by www.shopnc.cn ShopNc商城V17 大数据版
 */
defined('InShopNC') or exit('Access Invalid!');

class member_wap_messageControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 系统站内信列表
     */
    public function system_messageOp() {
        $model_message = Model('message');
        $condition['from_member_id'] = 0;
        //$condition['message_type'] = 1;
        $condition['to_member_id'] = $this->member_info['member_id'];
        $condition['no_del_member_id'] = $this->member_info['member_id'];

        $page = new Page();
        $page->setEachNum($this->page);
        $message_array = $model_message->listMessage($condition, $page, 'message.message_id,message.message_body,message_time,read_member_id,message_type,type');
        $page_count = $page->getTotalPage();

        $tmp_readid_str = '';
        if (!empty($message_array) && is_array($message_array)) {
        foreach ($message_array as $key => $value) {
            if (!empty($message_array[$key]['read_member_id'])) {
                $tmp_readid_arr = explode(',', $message_array[$key]['read_member_id']);
                if (!in_array($this->member_info['member_id'], $tmp_readid_arr)) {
                    $tmp_readid_arr[] = $this->member_info['member_id'];
                }
                foreach ($tmp_readid_arr as $readid_k => $readid_v) {
                    if ($readid_v == '') {
                        unset($tmp_readid_arr[$readid_k]);
                    }
                }
                $tmp_readid_arr = array_unique($tmp_readid_arr); //去除相同
                sort($tmp_readid_arr); //排序
                $tmp_readid_str = "," . implode(',', $tmp_readid_arr) . ",";
            } else {
                $tmp_readid_str = ",{$this->member_info['member_id']},";
            }
        }
         }

        $model_message->updateCommonMessage(array('read_member_id' => $tmp_readid_str), $condition); //改变阅读状态
        
        if (!empty($message_array) && is_array($message_array)) {
            foreach ($message_array as $k => $v) {
                $message_array[$k]['message_body'] = preg_replace("/<a(.*)\/a>/i", "", $v['message_body']);
                $message_array[$k]['message_time'] = date("y/m/d",$v['message_time']);
//                    $message_array[$k]['message_body'] = iconv('UTF-8', 'GB2312', $v['message_body']);
            }
        }  else {
            $message_array = array();
        }
     
        output_data($message_array, mobile_page($page_count));
    }

    /**
     * @客户发送给客服的内容
     *
     * add by lizh 17:02 2016/7/7
     */
    public function client_send_contentOp() {
        $insert_array['content'] = $_POST['content'];
        $insert_array['ftime'] = time();
        $insert_array['member_id'] = $this->member_info['member_id'];
        $insert_array['feedback_state'] = 0;
        $insert_array['read_state'] = 1;
        $wap_message_model = Model('wap_message');
        $result = $wap_message_model->insertMessage($insert_array);
        $condition['member_id'] = $this->member_info['member_id'];
        $condition['wap_feedback_id'] = $result;
        $info = $wap_message_model->getMessage($condition, $this->page, 'content,ftime,member_id,is_admin', 'wap_feedback_id desc');
        foreach ($info as $key => $value) {
            $info[$key]['member_avatar'] = getMemberAvatar($this->member_info['member_avatar']);
            $info[$key]['member_name'] = $this->member_info['member_name'];
        }
		//p();
        output_data(array('info' => $info));
    }

    public function content_listOp() {
        $wap_message_model = Model('wap_message');
        $condition = array();
        $condition['member_id'] = $this->member_info['member_id'];
        $info = $wap_message_model->getMessage($condition, $this->page, 'content,ftime,member_id,is_admin', 'wap_feedback_id desc');
        $wap_message_model->editMessageReadState($this->member_info['member_id']);  //改变阅读状态
        $page_count = $wap_message_model->gettotalpage();
        foreach ($info as $key => $value) {
            $info[$key]['member_avatar'] = getMemberAvatar($this->member_info['member_avatar']);
            $info[$key]['member_name'] = $this->member_info['member_name'];
        }
        if (empty($info)) {
            $info = array();
        }
        $count = count($info);
        $last_time = $info[$count - 1]['ftime'];
        output_data(array('info' => $info, 'time' => date("Y-m-d h:i:sa", $last_time)), mobile_page($page_count));
    }

    public function countOp() {
        $wap_message_model = Model('wap_message');
        $condition['read_state'] = 0;
        $condition['member_id'] = $this->member_info['member_id'];
        $content_list = $wap_message_model->getMessage($condition, $this->page, 'content,ftime,member_id', 'ftime asc');
        $info_count['servies_count'] = count($content_list);
        $message_model = Model('message');
        $info_count['system_count'] = intval($message_model->countNewMessage($this->member_info['member_id']));
        $info_count['totolcount'] = $info_count['servies_count']+$info_count['system_count'];
        output_data($info_count);
    }

}
