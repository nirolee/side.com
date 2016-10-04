<?php

/**
 * 我的商城
 *
 *
 *
 *
 * by www.shopnc.cn ShopNc商城V17 大数据版
 */
//use Shopnc\Tpl;

defined('InShopNC') or exit('Access Invalid!');

class member_indexControl extends mobileMemberControl {

    private $member_id;

    public function __construct() {
        parent::__construct();
        $this->member_id = $this->member_info['member_id'];
    }

    /**
     * 我的商城
     */
    public function indexOp() {
        $member_info = array();
        $member_info['member_name'] = $this->member_info['member_name'];
        $member_info['member_truename'] = $this->member_info['member_truename'];
        $member_info['member_sex'] = $this->member_info['member_sex'];
        $member_info['avatar'] = getMemberAvatarForID($this->member_info['member_id']);
        $member_info['point'] = $this->member_info['member_points'];
        $member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];
        $member_info['member_qq'] = $this->member_info['member_qq'];
        $member_info['member_email'] = $this->member_info['member_email'];
        $member_info['member_id'] = $this->member_info['member_id'];
        $member_info['member_yuju'] = $this->member_info['member_yuju']; //个性签名
        $favorites_model = Model('favorites');
        $member_info['favorites_store'] = $favorites_model->getStoreFavoritesCount($this->member_info['member_id']); //店铺收藏数
        $member_info['favorites_goods'] = $favorites_model->getGoodsFavoritesCount($this->member_info['member_id']); //商品收藏数
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
        output_data(array('member_info' => $member_info));
    }

    /**
     * 我的邀请码
     */
    public function invite_mycodeOp() {
        $model_member = Model('member');
        $condition['member_id'] = $this->member_info['member_id'];

        $invite_mycode = $model_member->where($condition)->field('invite_mycode')->find();
        if (empty($invite_mycode['invite_mycode'])) {
            for ($i = 0;; $i++) {
                $invite_mycod_tmp = MakeStr(10);
                $condition2['invite_mycode'] = $invite_mycod_tmp;

                $invite_mycode_exist = $model_member->where($condition2)->field('invite_mycode')->find();
                if (empty($invite_mycode_exist['invite_mycode'])) {
                    break;
                }
            }

            $model_member->editMember(array('member_id' => $this->member_info['member_id']), array('invite_mycode' => $invite_mycod_tmp));
            $invite_mycode['invite_mycode'] = $invite_mycod_tmp;
        }

        require_once(BASE_RESOURCE_PATH . DS . 'phpqrcode' . DS . 'index.php');
        $PhpQRCode = new PhpQRCode();
        $PhpQRCode->set('pngTempDir', BASE_UPLOAD_PATH . DS . ATTACH_MYCODE . DS);
        $PhpQRCode->set('date', $invite_mycode['invite_mycode']);
        $photo_name = md5('2016829' . $invite_mycode['invite_mycode'] . '2016829');
        $PhpQRCode->set('pngTempName', $photo_name . '.png');
        $PhpQRCode->init();

        output_data($invite_mycode);
    }

    //获取邀请码二维码
    public function get_invite_mycodeOp() {

        $member_id = $this->member_info['member_id'];
        $invite_mycode = $this->member_info['invite_mycode'];
        $photo_name = md5('2016829' . $invite_mycode . '2016829');
        output_data(array('photo_url' => UPLOAD_SITE_URL . DS . ATTACH_MYCODE . DS . $photo_name . '.png', 'invite_mycode' => $invite_mycode));
    }

    /**
     * 我的商城_1_5
     * add by lizh 16:12 2016/7/11
     */
    public function index_1_5Op() {

        $member_info = array();
        $member_info['member_name'] = $this->member_info['member_name'];
        $member_info['member_truename'] = $this->member_info['member_truename'];
        $member_info['member_sex'] = $this->member_info['member_sex'];
        $member_info['member_birthday'] = $this->member_info['member_birthday'];
        $member_info['avatar'] = getMemberAvatar($this->member_info['member_avatar']);
        $member_info['point'] = $this->member_info['member_points'];
        $member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];
        $member_info['member_qq'] = $this->member_info['member_qq'];
        $member_info['member_email'] = $this->member_info['member_email'];
        $member_info['member_id'] = $this->member_info['member_id'];
        $member_info['member_yuju'] = $this->member_info['member_yuju']; //个性签名
        $member_info['member_areainfo'] = $this->member_info['member_areainfo']; //地区内容
        $favorites_model = Model('favorites');
        $member_info['favorites_store'] = $favorites_model->getStoreFavoritesCount($this->member_info['member_id']); //店铺收藏数
        $member_info['favorites_goods'] = $favorites_model->getGoodsFavoritesCount($this->member_info['member_id']); //商品收藏数

        /**
         * @interested_person：我关注的人
         * @fans：我的粉丝
         * @member_showcase：我的橱窗
         */
        $interested_person = Model()->table('sns_friend')->where(array(friend_frommid => $member_info['member_id'], friend_tomid => array('neq', 0)))->count(); //我--关注的人
        $fans = Model()->table('sns_friend')->where(array(friend_tomid => $member_info['member_id']))->count(); //我--我的粉丝
        $member_info['interested_person'] = $interested_person;
        $member_info['fans'] = $fans;
        $member_info['member_showcase'] = 0;

        /**
         * @member_showcase:关注橱窗数
         * @micro_personal_class_list:我的橱窗列表
         * @micro_personal_class_list_count:我的橱窗数
         * update by lizh 10:46 2016/7/21
         */
        $favorites_class = Model('favorites_class');
        $micro_personal_class_count = $favorites_class->getCount(array(member_id => $member_info['member_id'], favorites_class_type => 'showcase', visible_state => 1));

        $micro_personal_class_data = $favorites_class->getFavoritesList(array(member_id => $member_info['member_id'], favorites_class_type => 'showcase'), 'favorites_class_name,visible_state,favorites_class_id');

        $favorites = Model('favorites');
        $micro_personal_class_list = $favorites->getShowcaseList($micro_personal_class_data);
        $member_info['micro_personal_class_list_count'] = count($micro_personal_class_list);

        $member_info['member_showcase'] = $favorites->getShowcase_classFavoritesCountByBrandsId($member_info['member_id']);

        //我的瞬间
        $micro_personal = Model()->table('micro_personal')->field('commend_image,commend_member_id,personal_id')->where(array(commend_member_id => $member_info['member_id']))->order("personal_id desc")->limit(20)->select();
        //p();
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

        output_data(array('member_info' => $member_info, micro_personal => $micro_personal, micro_personal_class_list => $micro_personal_class_list));
    }

    /**
     * 更多我的橱窗
     * add by lizh 11:34 2016/8/4
     */
    public function more_myself_showcaseOp() {

        $favorites_class = Model('favorites_class');
        $micro_personal_class_data = $favorites_class->getFavoritesList(array(member_id => $this->member_info['member_id'], favorites_class_type => 'showcase'), 'favorites_class_name,visible_state,favorites_class_id', 0, 'favorites_count desc', 0);

        $favorites = Model('favorites');
        $micro_personal_class_list = $favorites->getShowcaseList($micro_personal_class_data);
        output_data(array(micro_personal_class_list => $micro_personal_class_list));
    }

    /**
     * 更多我关注的橱窗
     * add by lizh 11:50 2016/8/4
     */
    public function more_myself_favorites_showcaseOp() {

        $favorites = Model('favorites');
        $more_favorites_list = $favorites->getFavoritesList(array(member_id => $this->member_info['member_id'], fav_type => 'showcase_class'), 'fav_id');

        $favorites_class = Model('favorites_class');
        $n = 0;
        foreach ($more_favorites_list as $k => $v) {

            $data = array();
            $data = $favorites_class->getOneFavorites(array(favorites_class_id => $v['fav_id']));
            $micro_personal_class_data[$n]['favorites_class_name'] = $data['favorites_class_name'];
            $micro_personal_class_data[$n]['visible_state'] = $data['visible_state'];
            $micro_personal_class_data[$n]['favorites_class_id'] = $data['favorites_class_id'];
            $n++;
        }

        $micro_personal_class_list = $favorites->getShowcaseList($micro_personal_class_data);
        output_data(array(micro_personal_class_list => $micro_personal_class_list));
    }

    /**
     * 我的积分
     */
    public function my_assetOp() {
        $member_info = $this->getMemberAndGradeInfo(true);

        $point = $this->member_info['member_points'];
        $predepoit = $this->member_info['available_predeposit'];
        $balance = $this->member_info['available_rc_balance'];
        $voucher = Model('voucher')->getCurrentAvailableVoucherCount($this->member_info['member_id']); //取得当前有效代金券数量
        $onempf = $this->member_info['member_onempf'];
        //$redpacket =  Model('redpacket')->getCurrentAvailableRedpacketCount($this->member_info['member_id']); //取得当前有效红包数量
        $card_combination = Model('card_combination');
        $card_count = $card_combination->getCount(array(member_id => $this->member_info['member_id'], use_state => 1, bill_state => 1));
        if ($_GET["fields"] == 'predepoit') {
            output_data(array('predepoit' => $predepoit));
        } elseif ($_GET["fields"] == 'available_rc_balance') {
            output_data(array('available_rc_balance' => $balance));
        } else {
            output_data(array('point' => $point, 'predepoit' => $predepoit, 'available_rc_balance' => $balance, /* 'redpacket'=>$redpacket, */ 'voucher' => $voucher, 'member_onempf' => $onempf, 'card_count' => $card_count));
        }
    }

    /**
     * 我的礼品卡列表
     * add by lizh 11:34 2016/8/4
     */
    public function myself_cardOp() {

        $card_combination = Model('card_combination');
        $card_combination_data = $card_combination->getList(array(member_id => $this->member_info['member_id'], use_state => 1, bill_state => 1), 0, 'combination_id desc', 'card_price,create_member_id,content');
        $member = Model('member');
        foreach ($card_combination_data as $k => $v) {

            $card_combination_data[$k]['member_avatar'] = getMemberAvatarForID($v['create_member_id']);
            $member_data = $member->getMemberInfo(array(member_id => $v['create_member_id']), 'member_name');
            $card_combination_data[$k]['member_name'] = $member_data['member_name'];
        }
        output_data(array(card_combination_data => $card_combination_data));
    }

    protected function getMemberAndGradeInfo($is_return = false) {
        $member_info = array();
        //会员详情及会员级别处理
        if ($this->member_info['member_id']) {
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfoByID($this->member_info['member_id'], "*");
            if ($member_info) {
                $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
                $member_info = array_merge($member_info, $member_gradeinfo);
                $member_info['security_level'] = $model_member->getMemberSecurityLevel($member_info);
            }
        }
        if ($is_return == true) {//返回会员信息
            return $member_info;
        } else {//输出会员信息
            Tpl::output('member_info', $member_info);
        }
    }

    /**
     * 个人信息
     * add by lizh 2016/9/9 14:33
     * version 1.5.3
     */
    public function get_memberinfoOp() {

        $new_array = array();
        $new_array['member_id'] = $this->member_info['member_id'];
        $new_array['member_truename'] = $this->member_info['member_truename'];
        $new_array['member_avatar'] = getMemberAvatar($this->member_info['member_avatar']);
        $card = $this->get_service_card_file();
        output_data(array(member_info => $new_array, card_list => $card));
    }

    /**
     * 获取服务器欢乐送贺卡文件夹里所有卡片
     * 图片类型目前只有：'jpg','png'
     * add by lizh 2016/9/9 17:08
     * version 1.5.3 
     */
    private function get_service_card_file() {

        $dir = BASE_UPLOAD_PATH . DS . "gift_card_model_1_5_3/";

        $card = array();
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {

                $i = 0;
                while (($file = readdir($dh)) !== false) {

                    $file_array = explode('.', $file);
                    $last_num = count($file_array) - 1;
                    if (count($file_array) >= 2) {

                        $img_name = $file_array[0];

                        $value = $file_array[$last_num];
                        if (!empty($value)) {

                            if ($value == 'jpg' || $value == 'png') {

                                $card[$i]['img_name'] = $file;
                                $card[$i]['img_url'] = UPLOAD_SITE_URL . DS . 'gift_card_model' . DS . $file;
                                $i++;
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }

        return $card;
    }

    /**
     * POST 上传背景图
     * add by lbb 2016/9/27 22:24
     */
    public function uploadBackgroundOp() {
        // var_dump(123);exit;
        if (!isset($_POST)) {
            output_error("请求方式错误");
            die;
        }
        import('function.thumb');
        $member_id = $this->member_id;
        //上传图片
        $upload = new UploadFile();
        $upload->set('thumb_width', 800);
        $upload->set('thumb_height', 600);
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $upload->set('file_name', "background_$member_id.$ext");
        $upload->set('thumb_ext', '');
        $upload->set('ifremove', false);
        $upload->set('default_dir', ATTACH_AVATAR);
        $result = $upload->upfile('file');
        if (!$result) {
            output_error("上传失败");
            die;
        } else {
            Model('member')->editMember(array('member_id' => $this->member_id), array('member_background' => 'background_' . $this->member_id . '.jpg'));
        }
//        } else {
//            output_error('上传失败，请尝试更换图片格式或小图片');
//            die;
//        }
//        $thumb_img = array('newfile' => $upload->thumb_image,
//            'height' => get_height(BASE_UPLOAD_PATH . '/' . ATTACH_AVATAR . '/' . $upload->thumb_image),
//            'width' => get_width(BASE_UPLOAD_PATH . '/' . ATTACH_AVATAR . '/' . $upload->thumb_image));
//        $avatarfile = BASE_UPLOAD_PATH . DS . ATTACH_AVATAR . DS . "avatar_{$_SESSION['member_id']}.jpg";
        output_data(array('status' => 'success'));
    }

}
