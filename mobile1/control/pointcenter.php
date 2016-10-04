<?php

//积分中心
defined('InShopNC') or exit('Access Invalid!');

class pointcenterControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 兑换代金券
     */
    public function voucherexchangeOp() {
        $vid = intval($_GET['vid']);
        if ($vid <= 0) {
            $vid = intval($_POST['vid']);
        }

        $data['result'] = true;
        $data['message'] = "";
        if ($vid <= 0) {
            $data['result'] = false;
            $data['message'] = '参数错误';
        }
        if ($data['result']) {
            //查询可兑换代金券模板信息
            $template_info = Model('voucher')->getCanChangeTemplateInfo($vid, intval($this->member_info['member_id']), intval($this->member_info['store_id']));
            if ($template_info['state'] == false) {
                $data['result'] = false;
                $data['message'] = $template_info['msg'];
            } else {
                //查询会员信息
                $data['member_info'] = Model('member')->getMemberInfoByID($this->member_info['member_id'], 'member_points');
                $data['template_info'] = $template_info['info'];
                output_data($data);
            }
        }
        output_data($data);
    }

    public function voucherexchange_saveOp() {
        $vid = intval($_GET['vid']);
        if ($vid <= 0) {
            $data['message'] = '参数错误';
        }
        $model_voucher = Model('voucher');
        //验证是否可以兑换代金券
        $data = $model_voucher->getCanChangeTemplateInfo($vid, intval($this->member_info['member_id']), intval($this->member_info['store_id']));
        if ($data['state'] == false) {
            $data['message'] = '不可兑换';
        }
        //添加代金券信息
        $data = $model_voucher->exchangeVoucher($data['info'], $this->member_info['member_id'], $this->member_info['member_name']);
        if ($data['state'] == true) {
            $data['message'] = '兑换成功';
        } else {
            $data['message'] = '兑换失败';
            ;
        }
        output_data($data);
    }
    
         /**
	 * 进入商品详情页
	 */
	public function addOp() {
		$pgid	= intval($_GET['pgid']);
		$quantity	= intval($_GET['quantity']);
		if($pgid <= 0 || $quantity <= 0) {
                    output_data('参数错误'); die;
		}
		
		//验证积分礼品是否存在购物车中
		$model_pointcart = Model('pointcart');
		//验证是否能兑换
		$data = $model_pointcart->checkExchange($pgid, $quantity, $this->member_info['member_id']);
		if (!$data['state']){
		    switch ($data['error']){
		        case 'ParameterError':
		            echo json_encode(array('done'=>false,'msg'=>$data['msg'],'url'=>'index.php?act=pointprod&op=plist')); die;
		            break;
		        default:
		            echo json_encode(array('done'=>false,'msg'=>$data['msg'])); die;
		    	    break;		    	
		    }
		}
		$prod_info = $data['data']['prod_info'];
		
		$insert_arr	= array();
		$insert_arr['pmember_id']		= $this->member_info['member_id'];
		$insert_arr['pgoods_id']		= $prod_info['pgoods_id'];
		$insert_arr['pgoods_name']		= $prod_info['pgoods_name'];
		$insert_arr['pgoods_points']	= $prod_info['pgoods_points'];
		$insert_arr['pgoods_choosenum']	= $prod_info['quantity'];
		$insert_arr['pgoods_image']		= $prod_info['pgoods_image_old'];
		$cart_state = $model_pointcart->addPointCart($insert_arr);
		echo json_encode(array('done'=>true)); die;
	}
    /**
     * 兑换订单流程第一步
     */
    public function step1Op() {
        $data = Model('pointcart')->getCartGoodsList($this->member_info['member_id']);
        if (!$data['state']) {
            output_data('购物车信息错误');
        }
        //实例化收货地址模型（不显示自提点地址）
        $address_list = Model('address')->getAddressList(array('member_id' => $this->member_info['member_id'], 'dlyp_id' => 0), 'is_default desc,address_id desc');
         output_data($address_list);
    }

    /**
     * 兑换订单流程第二步
     */
    public function step2Op() {
        $model_pointcart = Model('pointcart');
        //获取符合条件的兑换礼品和总积分
        $data = $model_pointcart->getCartGoodsList($this->member_info['member_id']);
        if (!$data['state']) {
            output_data('购物车信息错误');
        }
        $pointprod_arr = $data['data'];
        unset($data);

        //验证积分数是否足够
        $data = $model_pointcart->checkPointEnough($pointprod_arr['pgoods_pointall'], $this->member_info['member_id']);
        if (!$data['state']) {
            output_data('购物车信息错误');
        }
        unset($data);

        //创建兑换订单
        $data = Model('pointorder')->createOrder($_GET['address'], $pointprod_arr, array('member_id' => $this->member_info['member_id'], 'member_name' => $this->member_info['member_name'], 'member_email' => $this->member_info['member_email']));
        if (!$data['state']) {
             output_data($data);
        }
        $order_id = $data['data']['order_id'];
        $where = array();
        $where['point_orderid'] = $order_id;
        $where['point_buyerid'] = $this->member_info['member_id'];
        $order_info = Model('pointorder')->getPointOrderInfo($where);
        if (!$order_info){
            output_data('购物车信息错误');
        }
        output_data($order_info);
    }

}
