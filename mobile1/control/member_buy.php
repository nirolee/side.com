<?php

/** * 
 *
 *
 * menber_vr_buy虚拟商品
 * 非虚拟商品
 * by www.shopnc.cn ShopNc商城V17 大数据版
 */
defined('InShopNC') or exit('Access Invalid!');

class member_buyControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 购物车、直接购买第一步:选择收获地址和配置方式
     */
    public function buy_step1Op() {
        $cart_id = explode(',', $_POST['cart_id']);

        $logic_buy = logic('buy');

        //得到购买数据
        $result = $logic_buy->buyStep1($cart_id, $_POST['ifcart'], $this->member_info['member_id'], $this->member_info['store_id'], $_POST['is_happysend']);
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            $result = $result['data'];
        }
        //地址 
        if (intval($_POST['address_id']) > 0) {
            $result['address_info'] = Model('address')->getDefaultAddressInfo(array('address_id'=>intval($_POST['address_id']),'member_id'=>$this->member_info['member_id']));
        }
        //整理数据
        $store_cart_list = array();
        $sum = 0;
        foreach ($result['store_cart_list'] as $key => $value) {
            $store_cart_list[$key]['goods_list'] = $value;
            $store_cart_list[$key]['store_goods_total'] = $result['store_goods_total'][$key];
            if (!empty($result['store_premiums_list'][$key])) {
                $result['store_premiums_list'][$key][0]['premiums'] = true;
                $result['store_premiums_list'][$key][0]['goods_total'] = 0.00;
                $store_cart_list[$key]['goods_list'][] = $result['store_premiums_list'][$key][0];
            }
            $store_cart_list[$key]['store_mansong_rule_list'] = $result['store_mansong_rule_list'][$key];
            $store_cart_list[$key]['store_voucher_list'] = $result['store_voucher_list'][$key];
            $store_cart_list[$key]['store_voucher_info'] = array();
            if ($store_cart_list[$key]['store_voucher_list']) {
                foreach ($store_cart_list[$key]['store_voucher_list'] as $k => $v) {
                    $store_cart_list[$key]['store_voucher_info'] = array('voucher_price' => $store_cart_list[$key]['store_voucher_list'][$k]['voucher_price']);
                }
            }
            if (!empty($result['cancel_calc_sid_list'][$key])) {
                $store_cart_list[$key]['freight'] = '0';
                $store_cart_list[$key]['freight_message'] = $result['cancel_calc_sid_list'][$key]['desc'];
            } else {
                $store_cart_list[$key]['freight'] = '1';
                foreach ($store_cart_list[$key]['goods_list'] as $k => $v) {
                    $store_cart_list[$key]['store_freight_total'] +=$store_cart_list[$key]['goods_list'][$k]['goods_freight']; //每个店铺总运费
                    if ($_POST['is_happysend']) {
                        $store_cart_list[$key]['freight'] = '0'; //如果是欢乐送，免邮费
                        $store_cart_list[$key]['goods_list'][$k]['goods_freight'] = 0;
                        $store_cart_list[$key]['store_freight_total'] = 0;
                    }
                    $sum_freight = $store_cart_list[$key]['store_freight_total'];
                }
            }
            $store_cart_list[$key]['store_name'] = $value[0]['store_name'];
            $store_cart_list[$key]['store_id'] = $value[0]['store_id'];
            if ($_POST['is_happysend']) {
                $sum_freight = 0;
            }
            $store_cart_list[$key]['store_goods_total'] = $store_cart_list[$key]['store_goods_total'] + $sum_freight; //每个店铺总价+总运费
            
            $sum += $store_cart_list[$key]['store_goods_total'];
            foreach ($value as $k1 => $v1) {
                if ($value[$k1]['is_firstcut'] == 1) {
                    $is_exist = Model()->table('order')->where(array('buyer_id' => $this->member_info['member_id']))->find();
                    $firstcut_special = Model()->table('mb_special')->where(array('promotion_type' => 1))->find();
                    $goods_list = Model()->table('mb_special_item')->where(array('special_id' => $firstcut_special['special_id'], 'item_type' => 'goods'))->find();
                    $goods_list = unserialize($goods_list['item_data'])['item'];
                    if (!$is_exist && in_array($value[$k1]['goods_id'],$goods_list)) {
                        $sum -= 20;break;
                    }
                }
            }
        }

        $buy_list = array();
        $buy_list['store_cart_list'] = array_merge($store_cart_list);
        $buy_list['freight_hash'] = $result['freight_list'];
        $buy_list['address_info'] = $result['address_info'];
        $buy_list['ifshow_offpay'] = $result['ifshow_offpay'];
        $buy_list['vat_hash'] = $result['vat_hash'];
        $buy_list['inv_info'] = $result['inv_info'];
        $buy_list['available_predeposit'] = $result['available_predeposit'];
        $buy_list['available_rc_balance'] = $result['available_rc_balance'];

        if (is_array($result['rpt_list']) && !empty($result['rpt_list'])) {
            foreach ($result['rpt_list'] as $k => $v) {
                unset($result['rpt_list'][$k]['rpacket_id']);
                unset($result['rpt_list'][$k]['rpacket_end_date']); 
                unset($result['rpt_list'][$k]['rpacket_owner_id']);
                unset($result['rpt_list'][$k]['rpacket_code']);
            }
        }
        $buy_list['rpt_list'] = $result['rpt_list'] ? $result['rpt_list'] : array();
        $buy_list['zk_list'] = $result['zk_list'];
        $buy_list['order_amount'] = $sum;
        $buy_list['rpt_info'] = '';

        $buy_list['address_api'] = logic('buy')->changeAddr($_POST['freight_hash'], $result['address_info']['city_id'], $result['address_info']['area_id'], $this->member_info['member_id']);

        $buy_list['store_final_total_list'] = array('1' => ncPriceFormat($sum));

        output_data($buy_list);
    }

    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     */
    public function buy_step2Op() {
        $param = array();
        $param['ifcart'] = $_POST['ifcart'];                           //是否购物车
        $param['cart_id'] = explode(',', $_POST['cart_id']);           //购物车ID
        $param['address_id'] = $_POST['address_id'];                   //地址ID
        $param['vat_hash'] = $_POST['vat_hash'];                       //是否开增值税发票
        $param['offpay_hash'] = $_POST['offpay_hash'];                 //是否支持货到付款
        $param['offpay_hash_batch'] = $_POST['offpay_hash_batch'];     //是否支持货到付款 具体到各个店铺
        $param['pay_name'] = $_POST['pay_name'];                       //付款方式:在线支付/货到付款
        $param['invoice_id'] = $_POST['invoice_id'];                   //验证发票信息
        $param['rpt'] = $_POST['rpt'];
        if ($_POST['is_happysend']) {
            $param['is_happysend'] = $_POST['is_happysend'];
        } else {
            $param['is_happysend'] = 0;
        }
        if ($_SESSION['invite_mycode']) {
//             $param['share_id'] = Model('member')->get
            $param['share_id'] = Model('member')->table('member')->getfby_invite_mycode($_SESSION['invite_mycode'], 'member_id');
        }
        //处理代金券
        $voucher = array();
        $post_voucher = explode(',', $_POST['voucher']);
        if (!empty($post_voucher)) {
            foreach ($post_voucher as $value) {
                list($voucher_t_id, $store_id, $voucher_price) = explode('|', $value); //list() 函数用于在一次操作中给一组变量赋值
                $voucher[$store_id] = $value;
            }
        }
        $param['voucher'] = $voucher;

        //手机端暂时不做支付留言，页面内容太多了
        $param['order_message'] = json_decode($_POST['order_message']);
        $param['pd_pay'] = $_POST['pd_pay'];                                  //使用预存款支付
        $param['rcb_pay'] = $_POST['rcb_pay'];                                //使用充值卡支付 
        $param['onempf_pay'] = $_POST['onempf_pay'];                             //使用壹积金支付 
        $param['password'] = $_POST['password'];                              //支付密码
        $param['fcode'] = $_POST['fcode'];                                    //F码
        $param['order_from'] = 2;                                             //订单来源：1为PC 2为手机
        $logic_buy = logic('buy');
        $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email']);
        if (!$result['state']) {
            output_error($result['msg']);
        }

        output_data(array('pay_sn' => $result['data']['pay_sn'], 'order_sn' => $result['data']['order_list']));
    }

    /**
     * 验证密码
     */
    public function check_passwordOp() {
        if (empty($_POST['password'])) {
            output_error('参数错误');
        }

        $model_member = Model('member');

        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if ($member_info['member_paypwd'] == md5($_POST['password'])) {
            output_data('1');
        } else {
            output_error('密码错误');
        }
    }

    /**
     * 更换收货地址
     */
    public function change_addressOp() {
        $logic_buy = Logic('buy');
        if (empty($_POST['city_id'])) {
            $_POST['city_id'] = $_POST['area_id'];
        }

        $data = $logic_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $this->member_info['member_id']);
        if (!empty($data) && $data['state'] == 'success') {
            output_data($data);
        } else {
            output_error('地址修改失败');
        }
    }

    /**
     * 支付方式
     */
    public function payOp() {
        $pay_sn = $_POST['pay_sn'];
        $condition = array();
        $condition['pay_sn'] = $pay_sn;
        $order_info = Model('order')->getOrderList($condition);
        foreach ($order_info as $key => $value) {
            $order_info['order_amount'] += $order_info[$key]['order_amount'];
            $order_info['pd_amount'] += $order_info[$key]['pd_amount'];
        }
        $payment_list = Model('mb_payment')->getMbPaymentList();
        $pay_info['pay_amount'] = $order_info['order_amount'];
        $pay_info['member_available_pd'] = $this->member_info['available_predeposit'];
        $pay_info['member_available_rcb'] = $this->member_info['available_rc_balance'];
        $pay_info['member_available_onempf'] = $this->member_info['member_onempf'];
        $pay_info['member_paypwd'] = true;
        if (empty($this->member_info['member_paypwd'])) {
            $pay_info['member_paypwd'] = false;
        }
        $pay_info['pay_sn'] = $order_info['pay_sn'];
        $pay_info['payed_amount'] = $order_info['pd_amount'];

        if ($pay_info['payed_amount'] > '0.00') {
            $pay_info['pay_amount'] = $pay_info['pay_amount'] - $pay_info['payed_amount'];
        }

        $pay_in["pay_info"] = $pay_info;
        $pay_in["pay_info"]["payment_list"] = $payment_list;
        output_data($pay_in);
    }

    /**
     * 支付密码确认
     */
    public function check_pd_pwdOp() {
        if ($this->member_info['member_paypwd'] != md5($_POST['password'])) {
            output_error('支付密码错误');
        } else {
            output_data('OK');
        }
    }

    /**
     * 购买礼品卡
     * add by lizh 14:15 2016/8/4
     */
    public function buy_cardOp() {

        $param = array();

        $param['pd_pay'] = 0;                                        //使用预存款支付
        $param['rcb_pay'] = 0;                                                //使用充值卡支付
        $param['password'] = "";                                              //支付密码
        $param['fcode'] = "";                                                 //F码
        $param['order_from'] = 2;                                             //订单来源：1为PC 2为手机
        $param['quantity'] = 1;                                               //礼品卡数量

        if (!empty($_POST['member_mobile'])) {

            $member = Model('member');
            $member_info = $member->getMemberInfo(array(member_mobile => $_POST['member_mobile']), 'member_id');
            $member_id = $member_info['member_id'];
        }

        if (empty($_POST['member_id'])) {

            if (!empty($member_id)) {

                $param['member_id'] = $member_id;                            //礼品卡所属用户ID
            } else {

                $param['member_id'] = 0;                            //礼品卡所属用户ID
            }
        } else {

            $param['member_id'] = $_POST['member_id'];                            //礼品卡所属用户ID
        }

        $param['card_price'] = $_POST['card_price'];                          //礼品卡金额
        $param['member_mobile'] = $_POST['member_mobile'];                    //手机号码
        $param['card_id'] = 0;                                                //礼品卡ID
        $param['use_state'] = 1;                                              //使用状态
        $param['create_time'] = time();                                       //创建时间
        $param['content'] = $_POST['content'];                                //内容
        $param['bill_state'] = 0;                                             //支付状态
        $param['create_member_id'] = $this->member_info['member_id'];         //创建用户

        $logic_buy = logic('buy');
        $result = $logic_buy->create_card_order($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email']);

        output_data(array('pay_sn' => $result['data']['pay_sn'], 'order_sn' => $result['data']['combination_data']));
    }

    /**
     * 礼品卡支付方式
     * add by lizh 16:38 2016/8/4
     */
    public function card_payOp() {

        $pay_sn = $_POST['pay_sn'];
        $condition = array();
        $condition['pay_sn'] = $pay_sn;
        $order_info = Model('card_combination')->getOne($condition);

        $payment_list = Model('mb_payment')->getMbPaymentList();
        $pay_info['pay_amount'] = $order_info['card_price'];

        //取消余额、充值卡、壹基金等支付发送
        //$pay_info['payed_amount'] = $order_info['pd_amount'];
        $pay_info['payed_amount'] = "";

        $pay_info['member_available_pd'] = $this->member_info['available_predeposit'];
        $pay_info['member_available_rcb'] = $this->member_info['available_rc_balance'];
        $pay_info['member_available_onempf'] = $this->member_info['member_onempf'];
        $pay_info['member_paypwd'] = true;

        if ($pay_info['payed_amount'] > '0.00') {
            $pay_info['pay_amount'] = $pay_info['pay_amount'] - $pay_info['payed_amount'];
        }

        if (empty($this->member_info['member_paypwd'])) {
            $pay_info['member_paypwd'] = false;
        }

        $pay_info['pay_sn'] = $order_info['pay_sn'];

        $pay_in["pay_info"] = $pay_info;
        $pay_in["pay_info"]["payment_list"] = $payment_list;
        output_data($pay_in);
    }

    /**
     * 获取礼品发送信息
     * add by lizh 16:13 2016/8/5
     */
    public function get_send_infoOp() {

        $card_combination = logic('card_combination');
        $rs = $card_combination->getKey($_POST['pay_sn'], $_POST['member_mobile']);
        output_data($rs['msg']);
    }

}
