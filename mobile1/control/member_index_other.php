<?php

/**
 * 他人首页
 * by www.shopnc.cn ShopNc商城V17 大数据版
 */
defined('InShopNC') or exit('Access Invalid!');

class member_index_otherControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 我的商城_1_5
     * add by lizh 10:33 2016/8/5
     */
    public function index_1_5Op() {

        $member = Model('member');
        $sns_friend = Model('sns_friend');
        $member_id = $_GET['member_id'];
        $key = $_GET['key'];

        $member_info = array();
        $myself_state = '0';
        if (!empty($key)) {
            $model_mb_user_token = Model('mb_user_token');
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
            $myself_id = $mb_user_token_info['member_id'];
            if ($myself_id == $member_id) {

                $myself_state = '1';
                $member_info['is_friend'] = 1;
            } else {

                $isFriend = $sns_friend->countFriend(array(friend_frommid => $myself_id, friend_tomid => $member_id));
                if ($isFriend > 0) {

                    $member_info['is_friend'] = 1;
                } else {

                    $member_info['is_friend'] = 0;
                }
            }
        } else {

            $member_info['is_friend'] = 0;
        }

        $member_data = $member->getMemberInfoByID($member_id);

        $member_info['member_id'] = $member_data['member_id'];
        $member_info['member_name'] = $member_data['member_name'];
        $member_info['member_truename'] = $member_data['member_truename'];
        $member_info['member_sex'] = $member_data['member_sex'];
        $member_info['avatar'] = getMemberAvatarForID($member_data['member_id']);
        $member_info['point'] = $member_data['member_points'];
        $member_info['available_rc_balance'] = $member_data['available_rc_balance'];
        $member_info['member_qq'] = $member_data['member_qq'];
        $member_info['member_email'] = $member_data['member_email'];
        $member_info['member_id'] = $member_data['member_id'];
        $member_info['member_yuju'] = $member_data['member_yuju']; //个性签名
        $member_info['member_areainfo'] = $member_data['member_areainfo']; //地区内容
        $favorites_model = Model('favorites');
        $member_info['favorites_store'] = $favorites_model->getStoreFavoritesCount($member_data['member_id']); //店铺收藏数
        $member_info['favorites_goods'] = $favorites_model->getGoodsFavoritesCount($member_data['member_id']); //商品收藏数

        /**
         * @interested_person：他关注的人
         * @fans：他的粉丝
         * @member_showcase ：关注橱窗数
         */
        $interested_person = Model()->table('sns_friend')->where(array(friend_frommid => $member_info['member_id']))->count(); //他--关注的人
        $fans = Model()->table('sns_friend')->where(array(friend_tomid => $member_info['member_id']))->count(); //他--粉丝
        $member_info['interested_person'] = $interested_person;
        $member_info['fans'] = $fans;
        $member_info['member_showcase'] = 0;

        /**
         * @member_showcase:关注橱窗数
         * @micro_personal_class_list:他的橱窗列表
         * @micro_personal_class_list_count:他的橱窗数
         */
        $favorites_class = Model('favorites_class');
        $micro_personal_class_count = $favorites_class->getCount(array(member_id => $member_info['member_id'], favorites_class_type => 'showcase', visible_state => 1));
        $micro_personal_class_data = $favorites_class->getFavoritesList(array(member_id => $member_info['member_id'], favorites_class_type => 'showcase', visible_state => 1), 'favorites_class_name,visible_state,favorites_class_id');

        $favorites = Model('favorites');
        $micro_personal_class_list = $favorites->getShowcaseList($micro_personal_class_data);

        $member_info['micro_personal_class_list_count'] = count($micro_personal_class_list);
        $member_info['member_showcase'] = $favorites->getShowcase_classFavoritesCountByBrandsId($member_info['member_id']);

        //我的瞬间
        $micro_personal = Model()->table('micro_personal')->field('commend_image,commend_member_id,personal_id')->where(array(commend_member_id => $member_info['member_id']))->order('personal_id desc')->limit(21)->select();
        foreach ($micro_personal as $k2 => $v2) {
            $micro_personal[$k2]['commend_image'] = UPLOAD_SITE_URL . DS . ATTACH_MICROSHOP . DS . $v2['commend_member_id'] . '/' . $v2['commend_image'];
        }
        $member_info['micro_personal_count'] = count($micro_personal);

        $model_order = Model('order');
        $member_info['order_state_new'] = $model_order->getOrderStateNewCount(array('buyer_id' => $this->member_info['member_id'])); //待付款订单数量
        $member_info['order_state_pay'] = $model_order->getOrderStatePayCount(array('buyer_id' => $this->member_info['member_id'])); //待发货订单数量
        $member_info['order_state_send'] = $model_order->getOrderStateSendCount(array('buyer_id' => $this->member_info['member_id'])); //待收货订单数量
        $member_info['order_state_success'] = $model_order->getOrderStateEvalCount(array('buyer_id' => $this->member_info['member_id'])); //待评价订单数量
        $member_info['order_count'] = $model_order->getOrderCount(array('buyer_id' => $this->member_info['member_id'])); //总订单数量
        $model_cart = Model('cart');
        $member_info['cart_count'] = $model_cart->countCartByMemberId($this->member_info['member_id']);
        $model_refund_return = Model('refund_return');
        $member_info['refund_count'] = $model_refund_return->getRefundReturnCount(array('buyer_id' => $this->member_info['member_id']));

        output_data(array('member_info' => $member_info, micro_personal => $micro_personal, micro_personal_class_list => $micro_personal_class_list, 'myself_state' => $myself_state));
    }

    /*
     * add by niro 2016/9/21
     * 他人首页1.5.4版本
     */

    public function index_1_5_4Op() {

        $member = Model('member');
        $sns_friend = Model('sns_friend');
        $member_id = $_GET['member_id'];
        $key = $_GET['key'];
        $member_info = array();
        $myself_state = '0';
        if (!empty($key)) {
            $myself_id = $this->member_info['member_id'];
            if ($myself_id == $member_id) {

                $myself_state = '1';
                $member_info['is_friend'] = 1;
            } else {

                $isFriend = $sns_friend->countFriend(array(friend_frommid => $myself_id, friend_tomid => $member_id));
                if ($isFriend > 0) {

                    $member_info['is_friend'] = 1;
                } else {

                    $member_info['is_friend'] = 0;
                }
            }
        } else {

            $member_info['is_friend'] = 0;
        }

        $member_data = $member->getMemberInfoByID($member_id);
        $member_info['member_id'] = $member_data['member_id'];
        $member_info['member_truename'] = $member_data['member_truename'];
        $member_info['member_sex'] = $member_data['member_sex'];
        $member_info['member_areainfo'] = $member_data['member_areainfo'];
        $member_info['avatar'] = getMemberAvatarForID($member_data['member_id']);
        $member_info['member_qq'] = $member_data['member_qq'];
        $member_info['member_email'] = $member_data['member_email'];
        $member_info['member_id'] = $member_data['member_id'];
        $member_info['member_yuju'] = $member_data['member_yuju']; //个性签名
        $member_info['member_areainfo'] = $member_data['member_areainfo']; //地区内容
        /**
         * @interested_person：他关注的人
         * @fans：他的粉丝
         * @member_showcase ：关注橱窗数
         */
        $favorites_class = Model('favorites_class');
        $interested_person = Model()->table('sns_friend')->where(array(friend_frommid => $member_info['member_id']))->count(); //他--关注的人
        $fans = Model()->table('sns_friend')->where(array(friend_tomid => $member_info['member_id']))->count(); //他--粉丝
        $member_info['interested_person'] = $interested_person;
        $member_info['fans'] = $fans;
        $member_info['liked_count'] = Model()->table('micro_personal')->where(array('commend_member_id' => $this->member_info['member_id']))->sum('like_count'); //他的被赞总数量
        $member_info['micro_personal_count'] = Model()->table('micro_personal')->field('*')->where(array(commend_member_id => $member_id))->count();
        $member_info['micro_personal_class_count'] = $favorites_class->getCount(array(member_id => $member_info['member_id'], favorites_class_type => 'showcase'));

        output_data(array('member_info' => $member_info));
    }

    public function micro_personal_1_5_4Op() {
        //她人的瞬间动态
        $model_micro_personal = Model('micro_personal');
        $member_id = $_GET['member_id'];
        $micro_personal = $model_micro_personal->getList(array(commend_member_id => $member_id), $this->page, 'personal_id desc', 'commend_image,commend_member_id,personal_id,like_count,commend_time,commend_message');
        $page_count = $model_micro_personal->gettotalpage();
        foreach ($micro_personal as $k => $v) {
            $micro_personal[$k]['commend_image'] = UPLOAD_SITE_URL . DS . ATTACH_MICROSHOP . DS . $v['commend_member_id'] . '/' . $v['commend_image'];
            $micro_personal[$k]['collect_count'] = Model()->table('favorites')->where(array(fav_id => $v['personal_id'], member_id => $member_id, fav_type => 'showcase'))->count();
            $micro_personal[$k]['commend_time'] = date('m' . '月' . 'd' . '日', $v['commend_time']);
        }
        output_data($micro_personal, mobile_page($page_count));
    }

    public function article_list_1_5_4Op() {
        //他人的瞬间橱窗
        $model_cms_article_like = Model('cms_article_like');
        if($_GET['member_id']){
        $like_list = $model_cms_article_like->getList(array(like_member_id=>$_GET['member_id']),$this->page,'like_id desc','like_object_id');
        foreach ($like_list as $key => $value) {
            $like_list[$key] = $value['like_object_id'];
        }
        if($like_list[1]){
            $like_id_string = implode(',', $like_list);
            $condition = array();
            $condition['article_id'] = array('in',$like_id_string);
        }  else {
            $like_id_string = $like_list[0];
            $condition = array();
            $condition['article_id'] = $like_id_string;
        }
        $model_cms_article = Model('cms_article');
        $article_list = $model_cms_article->getList($condition, $this->page, 'article_id desc', 'article_id,article_title,article_image,article_publisher_id,article_abstract,article_publish_time,like_count');
        
            p();
        foreach($article_list as $k => $v) {
            //$article_image = $v['article_image'];
            $article_list[$k]['article_image'] = UPLOAD_SITE_URL . DS . ATTACH_CMS . DS . 'article' . DS . $v['article_publisher_id'] . DS . unserialize($v['article_image'])['name'];
            $article_list[$k]['article_publish_time'] = date('Y-m-s', $v['article_publish_time']);
        }
        $page_count = $model_cms_article->gettotalpage();
        }else{
            output_error('该用户不存在');
        }
        output_data($article_list, mobile_page($page_count));
    }

    public function micro_personal_class_list_1_5_4Op() {
        //他人的瞬间橱窗
        $member_id = $_GET['member_id'];
        $model_favorites = Model('favorites');
        $model_favorites_class = Model('favorites_class');
        $micro_personal_class_data = $model_favorites_class->getFavoritesList(array(member_id => $member_id, favorites_class_type => 'showcase'), 'favorites_class_name,visible_state,favorites_class_id', $this->page);
        $page_count = $model_favorites_class->gettotalpage();
        $micro_personal_class_list = $model_favorites->getShowcaseList($micro_personal_class_data);
        output_data($micro_personal_class_list, mobile_page($page_count));
    }

}
