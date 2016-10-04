<?php

/**
 * 活动
 *
 * 
 *
 *

 */
defined('InShopNC') or exit('Access Invalid!');

class wap_messageModel extends Model {

    public function __construct() {
        parent::__construct('wap_message');
    }

    /**
     * @插入数据
     * add by lizh 17:59 2016/7/7
     */
    public function insertMessage($array) {
        $result = $this->insert($array);
        return $result;
    }
//        public function delMessage($id) {
//        $condition = array('id' => array('in', $id));
//        return $this->where($condition)->delete();
//    }
    public function getMessage($condition, $page = null, $field='*',$order = 'ftime desc',$group = '') {
        $result = $this->where($condition)->field($field)->page($page)->order($order)->group($group)->select();
        return $result;
    }
    public function getMessageById($member_id, $page = null, $field='*',$order = 'ftime desc') {
    $condition['member_id'] = $member_id;     
    $result = $this->where($condition)->field($field)->page($page)->order($order)->select();
    return $result;
    }
    public function editMessageReadState($member_id){
        $condition['member_id'] = $member_id;
        $update['read_state'] = 1;
         return $this->where($condition)->update($update);
    }
    public function editMessageFeedbackState($member_id){
        $condition['member_id'] = $member_id;
        $update['feedback_state'] = 1;
         return $this->where($condition)->update($update);
    }
}
