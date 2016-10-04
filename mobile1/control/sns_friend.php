<?php

/**
 * 微商城个人中心 
 *
 *
 *

 */
defined('InShopNC') or exit('Access Invalid!');

class sns_friendControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    //首页
    public function indexOp() {
        $this->goodsOp();
    }

    /**
     * update by lizh 14:47 2016/7/9
     * update by niro 12:11 2016/8/29
     * 推荐好友
     */
    public function recommend_friendOp() {
        $member_model = Model('member');
        $friend_model = Model('sns_friend');
        $on = "member.member_id=micro_personal.commend_member_id";
        $recommend_list = Model()->table('member,micro_personal')->field("member.member_id,member.member_name,member.member_avatar,member.member_truename")->group('member.member_id')->join('right')->on($on)->where(array())->page(3)->order('rand()')->select();
        foreach ($recommend_list as $key => $value) {
            $recommend_list[$key]['member_avatar'] = getMemberAvatar($value['member_avatar']);
            $condition = array();
            $friend_frommid = $this->member_info['member_id'];
            $condition['friend_frommid'] = $friend_frommid;
            $condition['friend_tomid'] = $value['member_id'];
            $data = Model()->table('micro_personal')->field('commend_image,commend_member_id,personal_id')->where(array(commend_member_id => $value['member_id']))->limit(3)->select();

            foreach ($data as $k2 => $v2) {
                $data[$k2]['commend_image'] = UPLOAD_SITE_URL . DS . ATTACH_MICROSHOP . DS . $v2['commend_member_id'] . '/' . $v2['commend_image'];
            }
            $recommend_list[$key]['micro_personal'] = $data;

            //查询对方是否已经关注我，从而判断关注状态
            $friend_info = $friend_model->getFriendRow($condition);
            if ($friend_info) {
                $recommend_list[$key]['state'] = 1;
            } else {
                $recommend_list[$key]['state'] = 0;
            }
        }
		$list['friend'] = $recommend_list;
        $list['member_id'] = $this->member_info['member_id'];
        $list['member_name'] = $this->member_info['member_name'];
        $list['member_truename'] = $this->member_info['member_truename'];
        $list['avatar'] = getMemberAvatar($this->member_info['member_avatar']);
        output_data($list);
    }

    //手机好友
    public function mobile_friendOp() {
        $member_model = Model('member');
        $friend_model = Model('sns_friend');
        $condition = array();
        $mobile_friend = $_GET['mobile_friend'];
        if ($mobile_friend) {
            $condition['member_mobile'] = array('in', $_GET['mobile_friend']);
            $mobile_friend_list = $member_model->getMemberList($condition, 'member_id,member_name,member_avatar,member_truename,member_mobile', $this->page, 'member_id asc');
            foreach ($mobile_friend_list as $key => $value) {
                $mobile_friend_list[$key]['member_avatar'] = getMemberAvatar($value['member_avatar']);
                $condition = array();
                $condition['friend_frommid'] = $this->member_info['member_id'];
                $condition['friend_tomid'] = $value['member_id'];
                $friend_info = $friend_model->getFriendRow($condition);
                if ($friend_info) {
                    $mobile_friend_list[$key]['state'] = 1;
                } else {
                    $mobile_friend_list[$key]['state'] = 0;
                }
            }
//             $mobile_friend_list['member_id'] = $this->member_info['member_id'];
        } else {
            $mobile_friend_list = '无通讯录好友';
        }
        output_data($mobile_friend_list);
    }

    //手机好友
    public function mobile_friend_1_5Op() {
        $member_model = Model('member');
        $friend_model = Model('sns_friend');
        $model_phone_book = Model('phone_book');
        $condition = array();
        $condition['member_id'] = $this->member_info['member_id'];
        $condition['friend_name|friend_name_py'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        $list = $model_phone_book->getList($condition, $this->page,'pid desc');
        $page_count = $model_phone_book->gettotalpage();
        if ($list) {
            foreach ($list as $key => $value) {
                $list[$key]['state'] = 0;
                $list[$key]['member_avatar'] = UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.C('default_user_portrait');
                if ($member_model->getMemberInfo(array('member_mobile' => $value['mobile']))) {
                    $list[$key] = $member_model->getMemberInfo(array('member_mobile' => $value['mobile']), 'member_id,member_name,member_avatar,member_truename,member_mobile');
                    $list[$key]['member_avatar'] = getMemberAvatar($list[$key]['member_avatar']);
                    $list[$key]['friend_name'] = $list[$key]['member_truename'];
                    $condition = array();
                    $condition['friend_frommid'] = $this->member_info['member_id'];
                    $condition['friend_tomid'] = $value['member_id'];
                    $list[$key]['pid'] = '0';
                    $friend_info = $friend_model->getFriendRow($condition);
                    if ($friend_info) {
                        $list[$key]['state'] = 1;
                    } else {
                        $list[$key]['state'] = 0;
                    }                                                                                                                                                                                                                                                                                                                                                                                                                             
                   
                }
                $order_arr[]= $list[$key]['pid'];
            }
              array_multisort($list,SORT_DESC,SORT_REGULAR,$order_arr);
            
        } else {
            $list = '无该通讯录好友';
        }

        output_data($list, mobile_page($page_count));
    }

    public function addOp() {
        $model_phone_book = Model('phone_book');
        $model_member = Model('member');
        $array = $_POST['phone_book_array'];
        $pin = new pin;
        $array = explode('-', $array);
        $mobile_member_count = count($array);
        if ($mobile_member_count != $this->member_info['mobile_friend_count']) {
            $model_member->editMember(array('member_id' => $this->member_info['member_id']), array('mobile_friend_count' => $mobile_member_count));
            $model_phone_book->drop(array('member_id' => $this->member_info['member_id']));

            foreach ($array as $k => $v) {
                $phone_arraya = explode(',', $v);
                $phone_array['mobile'] = $phone_arraya[1];
                $phone_array['friend_name'] = $phone_arraya[0];
                $phone_array['member_id'] = $this->member_info['member_id'];
                $phone_array['friend_name_py'] = $pin->Pinyin($phone_arraya[0], 'UTF8');
                $result = $model_phone_book->save($phone_array);
            }
            if ($result) {
                $data['status'] = 'success';
                $data['message'] = '保存通讯录成功';
            } else {
                $data['status'] = 'fail';
                $data['message'] = '保存通讯录失败';
            }
        }
        $data['status'] = 'success';
        $data['message'] = '已存在通讯录';
        output_data($data);
    }

    //新浪好友
    public function sina_friendOp() {
        $member_model = Model('member');
        $friend_model = Model('sns_friend');
        $condition = array();
        $sina_friend = $_GET['sina_friend'];
        if ($sina_friend) {
            $condition['member_sinaopenid'] = array('in', $sina_friend);
            $sina_friend_list = $member_model->getMemberList($condition, 'member_id,member_name,member_avatar,member_truename,member_sinaopenid', $this->page, 'member_id asc');
            foreach ($sina_friend_list as $key => $value) {
                $sina_friend_list[$key]['member_avatar'] = getMemberAvatar($value['member_avatar']);
                $condition = array();
                $condition['friend_frommid'] = $this->member_info['member_id'];
                $condition['friend_tomid'] = $value['member_id'];
                $friend_info = $friend_model->getFriendRow($condition);
                if ($friend_info) {
                    $sina_friend_list[$key]['state'] = 1;
                } else {
                    $sina_friend_list[$key]['state'] = 0;
                }
            }
        } else {
//            $sina_friend_list = '无新浪微博好友';
        }
        output_data($sina_friend_list);
    }

    //好友列表
    public function friend_listOp() {
        $friend_model = Model('sns_friend');
        $condition = array();
		//$condition['friend_tomid'] = " != 0";
        $condition = "friend_frommid = ".$this->member_info['member_id']." and friend_tomid != 0";
        //$condition['group'] = 'friend_tomid';
  
        $friend_list = $friend_model->getListFriend($condition, 'friend_tomname,friend_tomid,friend_tomavatar,friend_followstate');
        //$friend_list = $friend_model->listFriend($condition, 'friend_tomname,friend_tomid,friend_tomavatar,friend_followstate');
		if(!empty($friend_list)) {
			
			foreach ($friend_list as $key => $value) {
				$friend_list[$key]['friend_tomavatar'] = getMemberAvatar($value['friend_tomavatar']);
                                $friend_list[$key]['member_truename'] = Model('member')->getfby_member_name($value['friend_tomname'],'member_truename');
			}
		} else {
			
			$friend_list = array();
		}
        
        output_data($friend_list);
    }

    /*
     * 粉丝列表 
     */
    public function fans_listOp() {
        $friend_model = Model('sns_friend');
        $condition = array();
        $condition['friend_tomid'] = $this->member_info['member_id'];
        //$condition['group'] = 'friend_tomid';
        $friend_list = $friend_model->listFriend($condition, 'friend_frommname,friend_frommid,friend_tomavatar,friend_followstate');
		//p();
		
		if(!empty($friend_list)) {
			
			foreach ($friend_list as $key => $value) {
				$friend_list[$key]['friend_tomavatar'] = getMemberAvatarForID($value['friend_frommid']);
                                $friend_list[$key]['member_truename'] = Model('member')->getfby_member_name($value['friend_frommname'],'member_truename');
				$friend_list[$key]['friend_tomid'] = $value['friend_frommid'];
				$friend_list[$key]['friend_tomname'] = $value['friend_frommname'];
				$data = $friend_model->getListFriend("friend_frommid =". $this->member_info['member_id']." and friend_tomid =". $value['friend_frommid']);
				if(!empty($data)) {
					
					$friend_list[$key]['friend_followstate'] = 1;
				} else {
					
					$friend_list[$key]['friend_followstate'] = 0;
				}
				unset($friend_list[$key]['friend_frommid']);
				unset($friend_list[$key]['friend_frommname']);
			}
			
		} else {
			
			$friend_list = array();
			
		}
       
        output_data($friend_list);
    }

    /**
     * 加关注
     */
    public function add_followOp() {
        
        $friend_model = Model('sns_friend');
        $data = array();
        $member_id = intval($_GET['member_id']);
        if($member_id == $this->member_info['member_id']) {
            
            $data['result'] = 'false';
            $data['message'] = '不能关注自己喔!';
            $data['count_friend'] = $friend_model->countFriend(array('friend_tomid' => $member_id));
            $condition = 'friend_tomid = '.  $member_id;
            $micro_like_data = $friend_model -> getListFriend($condition, 'friend_frommid', '', 'friend_id desc',4,'');
            foreach($micro_like_data as $k => $v) {

                $micro_like_data[$k]['member_avatar'] = getMemberAvatarForID($v['friend_frommid']);
                $micro_like_data[$k]['like_member_id'] = $v['friend_frommid'];

            }
            
            if(empty($micro_like_data)) {
                
                $data['micro_like_data'] = array();
                
            } else {
                
                 $data['micro_like_data'] = $micro_like_data;
                
            }
            output_data($data);
            
        }

        //验证会员信息
        $member_model = Model('member');
        $condition_arr = array();
        $condition_arr['member_state'] = 1;
        $condition_arr['member_id'] = array('in', array($member_id, $this->member_info['member_id']));
        $member_list = $member_model->getMemberList($condition_arr);
        $self_info = array();
        $member_info = array();
        foreach ($member_list as $k => $v) {
            if ($v['member_id'] == $this->member_info['member_id']) {
                $self_info = $v;
            } else {
                $member_info = $v;
            }
        }
        if (empty($self_info) || empty($member_info)) {
            $data['result'] = 'false';
            $data['message'] = '关注失败';
        }

        $count = $friend_model->countFriend(array('friend_frommid' => $this->member_info['member_id'], 'friend_tomid' => $member_id));
        
        if (!empty($count)) {//判断是否已经存在好友记录
            $data['result'] = 'false';
            $data['message'] = '已经关注了呢';
            $data['count_friend'] = $friend_model->countFriend(array('friend_tomid' => $member_id));
            $condition = 'friend_tomid = '.  $member_id;
            $micro_like_data = $friend_model -> getListFriend($condition, 'friend_frommid', '', 'friend_id desc',4,'');
            foreach($micro_like_data as $k => $v) {

                $micro_like_data[$k]['member_avatar'] = getMemberAvatarForID($v['friend_frommid']);
                $micro_like_data[$k]['like_member_id'] = $v['friend_frommid'];

            }
            
            if(empty($micro_like_data)) {
                
                $data['micro_like_data'] = array();
                
            } else {
                
                 $data['micro_like_data'] = $micro_like_data;
                
            }
            output_data($data);
        } else {
            
            //查询对方是否已经关注我，从而判断关注状态
            $friend_info = $friend_model->getFriendRow(array('friend_frommid' => "{$member_id}", 'friend_tomid' => "{$this->member_info['member_id']}"));
            $insert_arr = array();
            $insert_arr['friend_frommid'] = "{$self_info['member_id']}";
            $insert_arr['friend_frommname'] = "{$self_info['member_name']}";
            $insert_arr['friend_frommavatar'] = "{$self_info['member_avatar']}";
            $insert_arr['friend_tomid'] = "{$member_info['member_id']}";
            $insert_arr['friend_tomname'] = "{$member_info['member_name']}";
            $insert_arr['friend_tomavatar'] = "{$member_info['member_avatar']}";
            $insert_arr['friend_addtime'] = time();
            if (empty($friend_info)) {
                $insert_arr['friend_followstate'] = '1'; //单方关注
            } else {
                $insert_arr['friend_followstate'] = '2'; //双方关注
            }
            $result = $friend_model->addFriend($insert_arr);
            
        }
        if ($result) {
            //更新对方关注状态
            if (!empty($friend_info)) {
                $friend_model->editFriend(array('friend_followstate' => '2'), array('friend_id' => "{$friend_info['friend_id']}"));
            }
            
            $data['result'] = 'true';
            $data['message'] = '关注成功';
            $data['count_friend'] = $friend_model->countFriend(array('friend_tomid' => $member_id));
            $condition = 'friend_tomid = '.  $member_id;
            $micro_like_data = $friend_model -> getListFriend($condition, 'friend_frommid', '', 'friend_id desc',4,'');
            foreach($micro_like_data as $k => $v) {

                $micro_like_data[$k]['member_avatar'] = getMemberAvatarForID($v['friend_frommid']);
                $micro_like_data[$k]['like_member_id'] = $v['friend_frommid'];

            }
            
            if(empty($micro_like_data)) {
                
                $data['micro_like_data'] = array();
                
            } else {
                
                 $data['micro_like_data'] = $micro_like_data;
                
            }
            //友盟推送
            $rs = set_umeng_push($member_id, '有好友关注了你', $this -> member_info['member_truename'].'关注了你', array(type => 7,id => $this -> member_info['member_id']));
            // 发送买家消息
            $param = array();
            $param['code'] = 'fans_notice';
            $param['member_id'] = $member_id;
            $param['type'] = 4;
            $param['param'] = array(
                'member_name' => $this->member_info['member_truename']
            );
            QueueClient::push('sendMemberMsg', $param);
                
        } else {
            $data['result'] = 'false';
            $data['message'] = '关注失败';
            $data['count_friend'] = $friend_model->countFriend(array('friend_tomid' => $member_id));
            $condition = 'friend_tomid = '.  $member_id;
            $micro_like_data = $friend_model -> getListFriend($condition, 'friend_frommid', '', 'friend_id desc',4,'');
            foreach($micro_like_data as $k => $v) {

                $micro_like_data[$k]['member_avatar'] = getMemberAvatarForID($v['friend_frommid']);
                $micro_like_data[$k]['like_member_id'] = $v['friend_frommid'];

            }
            
            if(empty($micro_like_data)) {
                
                $data['micro_like_data'] = array();
                
            } else {
                
                 $data['micro_like_data'] = $micro_like_data;
                
            }
        }
        output_data($data);
    }

    /**
     * 取消关注 
     */
    public function del_followOp() {
        $member_id = intval($_GET['member_id']);
        $friend_model = Model('sns_friend');
        $result = $friend_model->delFriend(array('friend_frommid' => $this->member_info['member_id'], 'friend_tomid' => $member_id));

        if ($result) {
            //更新对方的关注状态
            $friend_model->editFriend(array('friend_followstate' => '1'), array('friend_frommid' => "$member_id", 'friend_tomid' => "{$this->member_info['member_id']}"));
            $count = $friend_model->countFriend(array('friend_tomid' => $member_id));
            $data['count_friend'] = $count;
            $data['message'] = '成功取消关注';
            $data['result'] = 1;
            $condition = 'friend_tomid = '.  $member_id;
            $micro_like_data = $friend_model -> getListFriend($condition, 'friend_frommid', '', 'friend_id desc',4,'');
            foreach($micro_like_data as $k => $v) {

                $micro_like_data[$k]['member_avatar'] = getMemberAvatarForID($v['friend_frommid']);
                $micro_like_data[$k]['like_member_id'] = $v['friend_frommid'];

            }
            
            if(empty($micro_like_data)) {
                
                $data['micro_like_data'] = array();
                
            } else {
                
                 $data['micro_like_data'] = $micro_like_data;
                
            }
            output_data($data);
        } else {
            
            $count = $friend_model->countFriend(array('friend_tomid' => $member_id));
            $data['count_friend'] = $count;
            $data['message'] = '失败取消关注';
            $data['result'] = 0;
            $condition = 'friend_tomid = '.  $member_id;
            $micro_like_data = $friend_model -> getListFriend($condition, 'friend_frommid', '', 'friend_id desc',4,'');
            foreach($micro_like_data as $k => $v) {

                $micro_like_data[$k]['member_avatar'] = getMemberAvatarForID($v['friend_frommid']);
                $micro_like_data[$k]['like_member_id'] = $v['friend_frommid'];

            }
            
            if(empty($micro_like_data)) {
                
                $data['micro_like_data'] = array();
                
            } else {
                
                 $data['micro_like_data'] = $micro_like_data;
                
            }
            output_data($data);
        }
    }
    
   

}
