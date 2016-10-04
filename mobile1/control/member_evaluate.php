
<?php

/**
 * 会员中心——买家评价
 * * */
defined('InShopNC') or exit('Access Invalid!');

class member_evaluateControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 订单添加评价
     */
    public function evaluate_addOp() {
        $order_id = intval($_POST['order_id']);


        $model_order = Model('order');
        $model_store = Model('store');
        $model_evaluate_goods = Model('evaluate_goods');
        $model_evaluate_store = Model('evaluate_store');

        //获取订单信息
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id));
        //查询店铺信息
        $store_info = $model_store->getStoreInfoByID($order_info['store_id']);
        //获取订单商品
        $order_goods = $model_order->getOrderGoodsList(array('order_id' => $order_id));
        //处理积分、经验值计算说明文字

        $evaluate_goods_info = array();
        $evaluate_goods_info['geval_orderid'] = $order_id;
        $evaluate_goods_info['geval_orderno'] = $order_info['order_sn'];
        $evaluate_goods_info['geval_goodsid'] = $order_goods[0]['goods_id'];
        $evaluate_goods_info['geval_goodsname'] = $order_goods[0]['goods_name'];
        $evaluate_goods_info['geval_goodsprice'] = $order_goods[0]['goods_price'];
        $evaluate_goods_info['geval_goodsimage'] = $order_goods[0]['goods_image'];
        $evaluate_goods_info['geval_scores'] = $_POST['evaluate_score'];
        $evaluate_goods_info['geval_content'] = $_POST['evaluate_comment'];
        if ($_POST['image']) {
            $evaluate_goods_info['geval_image'] = $_POST['image'];
        }
        $evaluate_goods_info['geval_isanonymous'] = $_POST['anony'] ? 1 : 0;
        $evaluate_goods_info['geval_addtime'] = TIMESTAMP;
        $evaluate_goods_info['geval_storeid'] = $store_info['store_id'];
        $evaluate_goods_info['geval_storename'] = $store_info['store_name'];
        $evaluate_goods_info['geval_frommemberid'] = $this->member_info['member_id'];
        $evaluate_goods_info['geval_frommembername'] = $this->member_info['member_name'];
        $evaluate_goods_array[] = $evaluate_goods_info;
        $goodsid_array[] = $order_goods[0]['goods_id'];
        $data = $model_evaluate_goods->addEvaluateGoodsArray($evaluate_goods_array, $goodsid_array);

        $store_desccredit = intval($_POST['store_desccredit']);
        if ($store_desccredit <= 0 || $store_desccredit > 5) {
            $store_desccredit = 5;
        }
        $store_servicecredit = intval($_POST['store_servicecredit']);
        if ($store_servicecredit <= 0 || $store_servicecredit > 5) {
            $store_servicecredit = 5;
        }
        $store_deliverycredit = intval($_POST['store_deliverycredit']);
        if ($store_deliverycredit <= 0 || $store_deliverycredit > 5) {
            $store_deliverycredit = 5;
        }
//             //添加店铺评价
        if (!$store_info['is_own_shop']) {
            $evaluate_store_info = array();
            $evaluate_store_info['seval_orderid'] = $order_id;
            $evaluate_store_info['seval_orderno'] = $order_info['order_sn'];
            $evaluate_store_info['seval_addtime'] = time();
            $evaluate_store_info['seval_storeid'] = $store_info['store_id'];
            $evaluate_store_info['seval_storename'] = $store_info['store_name'];
            $evaluate_store_info['seval_memberid'] = $this->member_info['member_id'];
            $evaluate_store_info['seval_membername'] = $this->member_info['member_name'];
            $evaluate_store_info['seval_desccredit'] = $_POST['store_desccredit'];
            $evaluate_store_info['seval_servicecredit'] = $_POST['store_servicecredit'];
            $evaluate_store_info['seval_deliverycredit'] = $_POST['store_deliverycredit'];
        }
        $model_evaluate_store->addEvaluateStore($evaluate_store_info);
        //更新订单信息并记录订单日志
        $state = $model_order->editOrder(array('evaluation_state' => 1), array('order_id' => $order_id));
        $model_order->editOrderCommon(array('evaluation_time' => TIMESTAMP), array('order_id' => $order_id));
        if ($state) {
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = 'buyer';
            $data['log_msg'] = L('order_log_eval');
            $model_order->addOrderLog($data);
        }

        //添加会员积分
        if (C('points_isuse') == 1) {
            $points_model = Model('points');
            $points_model->savePointsLog('comments', array('pl_memberid' => $this->member_info['member_id'], 'pl_membername' => $this->member_info['member_name']));
        }
        //添加会员经验值
        Model('exppoints')->saveExppointsLog('comments', array('exp_memberid' => $this->member_info['member_id'], 'exp_membername' => $this->member_info['member_name']));


        output_data('1');
    }

    public function upload_picOp() {
        $data = array();
        $data['status'] = 'success';
        if (isset($this->member_info['member_id'])) {
            if (!empty($_FILES['file']['name'])) {
                $upload = new UploadFile();
                $upload->set('default_dir', ATTACH_EVALUATE . DS . $this->member_info['member_id']);
                $upload->set('thumb_width', '60,240');
                $upload->set('thumb_height', '5000,50000');
                $upload->set('thumb_ext', '_60,_360,_1024');
                $result = $upload->upfile('file');
                if (!$result) {
                    $data['status'] = 'fail';
                    $data['error'] = $upload->error;
                }
                $data['file'] = $upload->getSysSetPath() . $upload->file_name;
            }
        } else {
            $data['status'] = 'fail';
            $data['error'] = '未登录';
        }
        output_data($data);
    }

}
