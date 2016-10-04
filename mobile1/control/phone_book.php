<?php 
/*
 * add by niro 2016.8.18
 * 记录他人通讯录
 */

class phone_bookControl extends mobileMemberControl {

    function __construct() {
        parent::__construct();
    }
    
    public function indexOp() {
      $model_phone_book = Model('phone_book');  
      $condition = array();
      if($_GET['keyword']){
      $condition['friend_name_py|friend_name'] = array('like', '%' . $_GET['keyword'] . '%');
      }
      $condition['member_id'] = $this->member_info['member_id'];
      $list = $model_phone_book->getList($condition,$this->page,'id asc','mobile,member_id,friend_name');
      output_data($list);
    }
    
    public function addOp() {
        $model_phone_book = Model('phone_book');
        $array = $_POST['phone_book_array'];
           $pin = new pin;
               $array = explode('-',$array);
               foreach ($array as $k => $v) {
                   $phone_arraya = explode(',',$v);
                   $phone_array['mobile'] =  $phone_arraya[0];
                   $phone_array['friend_name'] =  $phone_arraya[1];
                   $phone_array['member_id'] = $this->member_info['member_id'];
                   $phone_array['friend_name_py'] = $pin->Pinyin($phone_arraya[1],'UTF8');
                   $result = $model_phone_book->save($phone_array);
               }
          if($result){
              $data['status'] = 'success';
              $data['message'] = '保存通讯录成功';
          }else{
              $data['status'] = 'fail';
              $data['message'] = '保存通讯录失败';
          }
          output_data($data);
       
    }
    
    
    
}