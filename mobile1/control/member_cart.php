<?php

/**
 * 我的购物车
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('InShopNC') or exit('Access Invalid!');

class member_cartControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 购物车列表
	 * update by lizh 14:28 2016/7/29
     */
    public function cart_listOp() {
        $model_cart = Model('cart');

        $condition = array('buyer_id' => $this->member_info['member_id']);
        $cart_list = $model_cart->listCart('db', $condition);

        // 购物车列表 [得到最新商品属性及促销信息]
        $cart_list = logic('buy_1')->getGoodsCartList($cart_list, $jjgObj);

        $model_goods = Model('goods');
        $sum = 0;
        $cart_a = array();
        foreach ($cart_list as $key => $val) {
            $cart_a[$val['store_id']]['store_id'] = $val['store_id'];
            $cart_a[$val['store_id']]['store_name'] = $val['store_name'];
            $goods_data = array();
            $goods_data = $model_goods->getGoodsOnlineInfoForShare($val['goods_id']);
            $goods_data['cart_id'] = $val['cart_id'];
            $goods_data['goods_num'] = $val['goods_num'];
            $goods_data['goods_image_url'] = cthumb($val['goods_image'], $val['store_id']);
            if ($goods_data['goods_spec'] == 'N;' || $goods_data['goods_spec'] == false) {
                $goods_data['goods_spec'] = '';
            } else {
				
				$goods_data['goods_spec'] = json_encode(unserialize($goods_data['goods_spec']));
				$goods_spec = json_decode($goods_data['goods_spec']);
				
				$goods_spec_str = "";
				foreach($goods_spec as $k => $v) {
					
					if(empty($goods_spec_str)) {
						
						$goods_spec_str .= $v;
						
					} else {
						
						$goods_spec_str .= ' '.$v;
						
					}
				}
				
				$goods_data['goods_spec'] = $goods_spec_str;
				
			}
            
			if ($goods_data['goods_promotion_type']) {
				
				$goods_data['goods_price'] = $goods_data['goods_promotion_price'];
            
			}
			
            $goods_data['gift_list'] = $val['gift_list'];
            $cart_list[$key]['goods_sum'] = ncPriceFormat($val['goods_price'] * $val['goods_num']);
            $cart_a[$val['store_id']]['goods'][] = $goods_data;
			$sum += $cart_list[$key]['goods_sum'];
            $key++;
        }
        $cart_a = array_merge($cart_a);
        output_data(array('cart_list' => $cart_a, 'sum' => ncPriceFormat($sum), 'cart_count' => count($cart_list)));
    }
	
		
	/**
	 * 添加多个商品到购物车
	 * add bu lizh 11:22 2016/7/25
	 */
	public function many_cart_addOp() {
		
		$goods_id = $_POST['goods_id'];
        $quantity = 1;
        if (empty($goods_id)) {
            output_error('参数错误');
        }

        $model_goods = Model('goods');
        $model_cart = Model('cart');
        $logic_buy_1 = Logic('buy_1');
		
		$goods_id_array = explode(',', $goods_id);
		
		$buy_goods_false = array();
		$buy_goods_false['status'] = 0; 
		$buy_goods_false['message'] = '0个商品无法添加到购物袋';
				
		$buy_goods_true = array();
		$buy_goods_true['status'] = 1; 
		$buy_goods_true['message'] = '0个商品成功添加到购物袋';
				
		$false_num = 1;
		$true_num = 1;

		foreach($goods_id_array as $v) {

			$goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($v);

			//验证是否可以购买
			if (empty($goods_info)) {

				$buy_goods_false['status'] = 0; 
				$buy_goods_false['message'] = $false_num.'个商品无法添加到购物袋;商品已下架或不存在';
				$false_num++;
				continue;
			}
			

			//团购
			$logic_buy_1->getGroupbuyInfo($goods_info);

			//限时折扣
			$logic_buy_1->getXianshiInfo($goods_info, $quantity);

			if ($goods_info['store_id'] == $this->member_info['store_id']) {

				$buy_goods_false['status'] = 0; 
				$buy_goods_false['message'] = $false_num.'个商品无法添加到购物袋;不能购买自己发布的商品';
				$false_num++;
				continue;
				
			}
			
			if (intval($goods_info['goods_storage']) < 1 || intval($goods_info['goods_storage']) < $quantity) {

				$buy_goods_false['status'] = 0; 
				$buy_goods_false['message'] = $false_num.'个商品无法添加到购物袋;库存不足';
				$false_num++;
				continue;
			}

			$param = array();
			$param['buyer_id'] = $this->member_info['member_id'];
			$param['store_id'] = $goods_info['store_id'];
			$param['goods_id'] = $goods_info['goods_id'];
			$param['goods_name'] = $goods_info['goods_name'];
			$param['goods_price'] = $goods_info['goods_price'];
			$param['goods_image'] = $goods_info['goods_image'];
			$param['store_name'] = $goods_info['store_name'];

			$result = $model_cart->addCart($param, 'db', $quantity);
			if ($result) {
				
				$buy_goods_true['status'] = 1; 
				$buy_goods_true['message'] = '已成功添加到购物袋';
				$true_num++;

			} else {

				$buy_goods_false['status'] = 0; 
				$buy_goods_false['message'] = $false_num.'个商品无法添加到购物袋;添加失败';
				$false_num++;
				
			}
			
			
		}
		
		output_data(array(buy_goods_true => $buy_goods_true, buy_goods_false => $buy_goods_false));
        
	}
	
    /**
     * 购物车添加
     */
    public function cart_addOp() {
        $goods_id = intval($_POST['goods_id']);
        $quantity = intval($_POST['quantity']);
        if ($goods_id <= 0 || $quantity <= 0) {
            output_error('参数错误');
        }

        $model_goods = Model('goods');
        $model_cart = Model('cart');
        $logic_buy_1 = Logic('buy_1');

        $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($goods_id);

        //验证是否可以购买
        if (empty($goods_info)) {
            output_error('商品已下架或不存在');
        }

        //团购
        $logic_buy_1->getGroupbuyInfo($goods_info);

        //限时折扣
        $logic_buy_1->getXianshiInfo($goods_info, $quantity);

        if ($goods_info['store_id'] == $this->member_info['store_id']) {
            output_error('不能购买自己发布的商品');
        }
        if (intval($goods_info['goods_storage']) < 1 || intval($goods_info['goods_storage']) < $quantity) {
            output_error('库存不足');
        }

        $param = array();
        $param['buyer_id'] = $this->member_info['member_id'];
        $param['store_id'] = $goods_info['store_id'];
        $param['goods_id'] = $goods_info['goods_id'];
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_price'] = $goods_info['goods_price'];
        $param['goods_image'] = $goods_info['goods_image'];
        $param['store_name'] = $goods_info['store_name'];

        $result = $model_cart->addCart($param, 'db', $quantity);
        if ($result) {
            output_data('1');
        } else {
            output_error('收藏失败');
        }
    }

    /**
     * 购物车删除
     */
    public function cart_delOp() {
        $cart_id = intval($_POST['cart_id']);

        $model_cart = Model('cart');

        if ($cart_id > 0) {
            $condition = array();
            $condition['buyer_id'] = $this->member_info['member_id'];
            $condition['cart_id'] = $cart_id;

            $model_cart->delCart('db', $condition);
        }

        output_data('1');
    }

    /**
     * 更新购物车购买数量
     */
    public function cart_edit_quantityOp() {
        $cart_id = intval(abs($_POST['cart_id']));
        $quantity = intval(abs($_POST['quantity']));
        if (empty($cart_id) || empty($quantity)) {
            output_error('参数错误');
        }

        $model_cart = Model('cart');

        $cart_info = $model_cart->getCartInfo(array('cart_id' => $cart_id, 'buyer_id' => $this->member_info['member_id']));

        //检查是否为本人购物车
        if ($cart_info['buyer_id'] != $this->member_info['member_id']) {
            output_error('参数错误');
        }

        //检查库存是否充足
        if (!$this->_check_goods_storage($cart_info, $quantity, $this->member_info['member_id'])) {
            output_error('超出限购数或库存不足');
        }

        $data = array();
        $data['goods_num'] = $quantity;
        $update = $model_cart->editCart($data, array('cart_id' => $cart_id));
        if ($update) {
            $return = array();
            $return['quantity'] = $quantity;
            $return['goods_price'] = ncPriceFormat($cart_info['goods_price']);
            $return['total_price'] = ncPriceFormat($cart_info['goods_price'] * $quantity);
            output_data($return);
        } else {
            output_error('修改失败');
        }
    }

    /**
     * 检查库存是否充足
     */
    private function _check_goods_storage(& $cart_info, $quantity, $member_id) {
        $model_goods = Model('goods');
        $model_bl = Model('p_bundling');
        $logic_buy_1 = Logic('buy_1');

        if ($cart_info['bl_id'] == '0') {
            //普通商品
            $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($cart_info['goods_id']);

            //团购
            $logic_buy_1->getGroupbuyInfo($goods_info);
            if ($goods_info['ifgroupbuy']) {
                if ($goods_info['upper_limit'] && $quantity > $goods_info['upper_limit']) {
                    return false;
                }
            }

            //限时折扣
            $logic_buy_1->getXianshiInfo($goods_info, $quantity);

            if (intval($goods_info['goods_storage']) < $quantity) {
                return false;
            }
            $goods_info['cart_id'] = $cart_info['cart_id'];
            $cart_info = $goods_info;
        } else {
            //优惠套装商品
            $bl_goods_list = $model_bl->getBundlingGoodsList(array('bl_id' => $cart_info['bl_id']));
            $goods_id_array = array();
            foreach ($bl_goods_list as $goods) {
                $goods_id_array[] = $goods['goods_id'];
            }
            $bl_goods_list = $model_goods->getGoodsOnlineListAndPromotionByIdArray($goods_id_array);

            //如果有商品库存不足，更新购买数量到目前最大库存
            foreach ($bl_goods_list as $goods_info) {
                if (intval($goods_info['goods_storage']) < $quantity) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 检查购物车数量
     */
    public function cart_countOp() {
        $model_cart = Model('cart');
        $count = $model_cart->countCartByMemberId($this->member_info['member_id']);
        $data['cart_count'] = $count;
        output_data($data);
    }
	
	/**
     * 更新购物车商品规格
	 * add by lizh 19:34 2016/7/29
     */
    public function cart_edit_specOp() {
		
        $cart_id = intval(abs($_POST['cart_id']));
        $goods_id = intval(abs($_POST['goods_id']));
        $goods_num = intval(abs($_POST['goods_num']));
		
        if (empty($cart_id) || empty($goods_id)) {
            output_error('参数错误');
        }

        $model_cart = Model('cart');

        $cart_info = $model_cart->getCartInfo(array('cart_id' => $cart_id, 'buyer_id' => $this->member_info['member_id']));

        //检查是否为本人购物车
        if ($cart_info['buyer_id'] != $this->member_info['member_id']) {
            output_data(array(status => 0, message=>'参数错误'));
        }

        //检查库存是否充足
        if (!$this->_check_goods_storage($cart_info, $goods_num, $this->member_info['member_id'])) {
			
			output_data(array(status => 0, message=>'超出限购数或库存不足'));
            //output_error('超出限购数或库存不足');
        }
		
		$goods = Model('goods');
		$goods_data = $goods -> getGoodsOnlineInfoAndPromotionById($goods_id);
		
		//验证是否可以购买
        if (empty($goods_data)) {
			output_data(array(status => 0, message=>'商品已下架或不存在'));
            //output_error('商品已下架或不存在');
        }
		
		$logic_buy_1 = Logic('buy_1');
		//团购
		$logic_buy_1->getGroupbuyInfo($goods_data);

		//限时折扣
		$logic_buy_1->getXianshiInfo($goods_data, $goods_num);

		if ($goods_data['store_id'] == $this->member_info['store_id']) {
			
			output_data(array(status => 0, message=>'不能购买自己发布的商品'));
			
			
		}
		
        $data = array();
        $data['goods_num'] = $goods_num;
        $data['goods_id'] = $goods_data['goods_id'];
        $data['goods_name'] = $goods_data['goods_name'];
        $data['goods_price'] = $goods_data['goods_price'];
        $data['goods_image'] = $goods_data['goods_image'];

        $update = $model_cart->editCart($data, array('cart_id' => $cart_id));
        
		if ($update) {
            
			$return = array();
            $return['quantity'] = $goods_num; 
            $return['goods_price'] = ncPriceFormat($goods_data['goods_price']);
            $return['total_price'] = ncPriceFormat($goods_data['goods_price'] * $goods_num);
            $return['goods_storage'] = $goods_data['goods_storage'];;
            $return['goods_image'] =  cthumb($goods_data['goods_image'], $goods_data['store_id']);
			
			output_data(array(status => 1, message=>'修改成功',info => $return));
            //output_data($return);
        
		} else {
			
			output_data(array(status => 0, message=>'修改失败'));
            //output_error('修改失败');
        
		}
    }

}
