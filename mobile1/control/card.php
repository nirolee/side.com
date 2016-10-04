<?php

/**
 * 礼品卡
 * by www.shopnc.cn ShopNc商城V17 大数据版
 */
defined('InShopNC') or exit('Access Invalid!');

class cardControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }
	
	/**
	 * 获取管理后台礼品卡
	 * add by lizh 11:03 2016/8/1
	 */
    public function get_card_listOp() {
		
        $card = Model('card');
		$card_list = $card -> getList(array(flag_state => 0), null, 'card_price asc', 'card_name,card_price');
		$member_id = $this->member_info['member_id'];
		
		$sns_friend = Model('sns_friend');
		$sns_friend_list = array();
		$sns_friend_list = $sns_friend -> getListFriend("friend_frommid = $member_id and friend_tomid != 0", 'friend_tomname,friend_tomavatar,friend_tomid,friend_addtime');
		if(!empty($sns_friend_list)) {
			
			foreach($sns_friend_list as $k => $v) {
			
				$friend_tomid = $v['friend_tomid'];
				$friend_tomavatar = $v['friend_tomavatar'];
				$friend_tomname = $v['friend_tomname'];
				$sns_friend_list[$k]['member_name'] = $friend_tomname;
				$sns_friend_list[$k]['member_avatar'] = getMemberAvatar($friend_tomavatar);
				$sns_friend_list[$k]['member_id'] = $v['friend_tomid'];
				$sns_friend_list[$k]['friend_addtime'] = date('Y-m-d',$v['friend_addtime']);
				unset($sns_friend_list[$k]['friend_tomid'],$sns_friend_list[$k]['friend_tomavatar'],$sns_friend_list[$k]['friend_tomname']);
				
			}
			
		}
		
        output_data(array('card_list' => $card_list, sns_friend_list => $sns_friend_list));
    }

}
