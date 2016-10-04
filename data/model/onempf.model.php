<?php
/**
 * 壹基金及壹基金日志管理
 *
 *
 *
 *
 
 */
defined('InShopNC') or exit('Access Invalid!');

class onempfModel extends Model {
	/**
	 * 操作壹积金
	 * @author niro
	 * @param  string $stage 操作阶段 regist(注册),login(登录),comments(评论),order(下单),system(系统),other(其他),pointorder(壹基金礼品兑换),app(同步壹基金兑换)
	 * @param  array $insertarr 该数组可能包含信息 array('member_id'=>'会员编号','membername'=>'会员名称','available_amount'=>'壹基金','description'=>'描述','orderprice'=>'订单金额','order_sn'=>'订单编号','order_id'=>'订单序号','point_ordersn'=>'壹基金兑换订单编号');
	 * @param  bool $if_repeat 是否可以重复记录的信息,true可以重复记录，false不可以重复记录，默认为true
	 * @return bool
	 */
	function saveOnempfLog($stage,$insertarr,$if_repeat = true){
		if (!$insertarr['member_id']){
			return false;
		}
		//记录原因文字
		switch ($stage){
//			case 'comments':
//				if (!$insertarr['description']){
//					$insertarr['description'] = '评论商品';
//				}
//				$insertarr['available_amount'] = intval(C('onempf_comments'));
//				break;
			case 'order':
				if (!$insertarr['description']){
					$insertarr['description'] = '订单'.$insertarr['order_sn'].'购物消费';
				}
				$insertarr['onempf_amount'] = 0;
				if ($insertarr['orderprice']){
					$insertarr['onempf_amount'] = @intval($insertarr['orderprice']/C('points_orderrate'));
				
				}
				//订单添加赠送壹基金列
				$obj_order = Model('order');
				$data = array();
				$data['order_pointscount'] = array('exp','order_pointscount+'.$insertarr['available_amount']);
				$obj_order->editOrderCommon($data,array('order_id'=>$insertarr['order_id']));
				break;
//			case 'system':
//				break;
//			case 'pointorder':
//				if (!$insertarr['description']){
//					$insertarr['description'] = '兑换礼品信息'.$insertarr['point_ordersn'].'消耗壹基金';
//				}
//				break;
//                        case 'app':
//				if (!$insertarr['description']){
//					$insertarr['description'] = Language::get('points_pointorderdesc_app');
//				}
//				
//				break;
			case 'inviter':
				if (!$insertarr['description']){
					$insertarr['description'] = '邀请新会员['.$insertarr['invited'].']注册';
				}
				$insertarr['available_amount'] = intval($GLOBALS['setting_config']['onempf_invite']);
				break;
			case 'rebate':
                            //邀请壹基金返利 
				if (!$insertarr['description']){
					$insertarr['description'] = '被邀请人['.$_SESSION['member_name'].']消费';
				}
				$insertarr['available_amount'] = $insertarr['rebate_amount'];
				break;
                        case 'share':
                            //分享瞬间返利
                            if (!$insertarr['description']){
					$insertarr['description'] = '被邀请人['.$_SESSION['member_name'].']消费';
				}
				$insertarr['available_amount'] = $insertarr['rebate_amount'];
				break;
			case 'other':
				break;
		}
		$save_sign = true;
		if ($if_repeat == false){
			//检测是否有相关信息存在，如果没有，入库
			$condition['member_id'] = $insertarr['member_id'];
			$condition['type'] = $stage;
			$log_array = self::getOnempfInfo($condition,$page);
			if (!empty($log_array)){
				$save_sign = false;
			}
		}
		if ($save_sign == false){
			return true;
		}
		//新增日志
		$value_array = array();
		$value_array['member_id'] = $insertarr['member_id'];
		$value_array['member_name'] = $insertarr['member_name'];
	
		$value_array['onempf_amount'] = $insertarr['available_amount'];
		$value_array['ctime'] = time();
		$value_array['description'] = $insertarr['description'];
		$value_array['type'] = $stage;
		$result = false;
		if($value_array['available_amount'] != '0'){
			$result = self::addOnempfLog($value_array);
		}
		if ($result){
			//更新member内容
			$obj_member = Model('member');
			$upmember_array = array();
			$upmember_array['member_onempf'] = array('exp','member_onempf+'.$insertarr['available_amount']);
			$obj_member->editMember(array('member_id'=>$insertarr['member_id']),$upmember_array);
                          $param = array();
                                $param['code'] = 'onempf';
                                $param['member_id'] = $insertarr['member_id'];
                                $param['type'] = 3;
                                $param['param'] = array(
                                    'amount' => $insertarr['available_amount']
                                );
                                QueueClient::push('sendMemberMsg', $param);
			return true;
		}else {
			return false;
		}

	}
	/**
	 * 添加壹基金日志信息
	 *
	 * @param array $param 添加信息数组
	 */
	public function addOnempfLog($param) {
		if(empty($param)) {
			return false;
		}
		$result	= Db::insert('onempf_log',$param);
		return $result;
	}
	/**
	 * 壹基金日志列表
	 *
	 * @param array $condition 条件数组
	 * @param array $page   分页
	 * @param array $field   查询字段
	 */
	public function getOnempfLogList($condition,$page='',$field='*'){
		$condition_str	= $this->getCondition($condition);
		$param	= array();
		$param['table']	= 'onempf_log';
		$param['where']	= $condition_str;
		$param['field'] = $field;
		$param['order'] = $condition['order'] ? $condition['order'] : 'onempf_log.id desc';
		$param['limit'] = $condition['limit'];
		$param['group'] = $condition['group'];
		return Db::select($param,$page);
	}
	/**
	 * 壹基金日志详细信息
	 *
	 * @param array $condition 条件数组
	 * @param array $field   查询字段
	 */
	public function getOnempfInfo($condition,$field='*'){
		//得到条件语句
		$condition_str	= $this->getCondition($condition);
		$array			= array();
		$array['table']	= 'onempf_log';
		$array['where']	= $condition_str;
		$array['field']	= $field;
		$list		= Db::select($array);
		return $list;
	}
	/**
	 * 将条件数组组合为SQL语句的条件部分
	 *
	 * @param	array $condition_array
	 * @return	string
	 */
	private function getCondition($condition_array){
		$condition_sql = '';
		//壹基金日志会员编号
		if ($condition_array['member_id']) {
			$condition_sql	.= " and `onempf_log`.member_id = '{$condition_array['member_id']}'";
		}
		//操作阶段
		if ($condition_array['type']) {
			$condition_sql	.= " and `onempf_log`.type = '{$condition_array['type']}'";
		}
		//会员名称
		if ($condition_array['member_name']) {
			$condition_sql	.= " and `onempf_log`.member_name like '%{$condition_array['member_name']}%'";
		}
		
		//添加时间
		if ($condition_array['saddtime']){
			$condition_sql	.= " and `onempf_log`.ctime >= '{$condition_array['saddtime']}'";
		}
		if ($condition_array['eaddtime']){
			$condition_sql	.= " and `onempf_log`.ctime <= '{$condition_array['eaddtime']}'";
		}
		//描述
		if ($condition_array['description_like']){
			$condition_sql	.= " and `onempf_log`.description like '%{$condition_array['description_like']}%'";
		}
		return $condition_sql;
	}
        
            /**
     * 变更预存款
     * @param unknown $change_type
     * @param unknown $data
     * @throws Exception
     * @return unknown
     */
    public function changeOnempf($change_type,$data = array()) {
        $data_log = array();
        $data_onempf = array();
        $data_msg = array();

        $data_log['member_id'] = $data['member_id'];
        $data_log['member_name'] = $data['member_name'];
        $data_log['ctime'] = TIMESTAMP;
        $data_log['type'] = $change_type;

        $data_msg['time'] = date('Y-m-d H:i:s');
        $data_msg['onempf_url'] = urlShop('onempf', 'onempf_log_list');
        switch ($change_type){
            case 'order_pay':
                $data_log['onempf_amount'] = -$data['amount'];
                $data_log['description'] = '下单，支付预存款，订单号: '.$data['order_sn'];
                $data_onempf['member_onempf'] = array('exp','member_onempf-'.$data['amount']);

                $data_msg['onempf_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['description'];
                break;
            case 'order_freeze':
                $data_log['onempf_amount'] = -$data['amount'];
                $data_log['onempf_freeze_amount'] = $data['amount'];
                $data_log['description'] = '下单，冻结预存款，订单号: '.$data['order_sn'];
                $data_onempf['freeze_onempf'] = array('exp','freeze_onempf+'.$data['amount']);
                $data_onempf['member_onempf'] = array('exp','member_onempf-'.$data['amount']);

                $data_msg['onempf_amount'] = -$data['amount'];
                $data_msg['onempf_freeze_amount'] = $data['amount'];
                $data_msg['description'] = $data_log['lg_desc'];
                break;
            case 'order_cancel':
                $data_log['onempf_amount'] = $data['amount'];
                $data_log['description'] = '取消订单，订单号: '.$data['order_sn'];
                $data_onempf['member_onempf'] = array('exp','member_onempf+'.$data['amount']);

                $data_msg['onempf_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['description'];
                break;
            case 'order_comb_pay':
                $data_log['onempf_freeze_amount'] = -$data['amount'];
                $data_log['description'] = '下单，支付被冻结的预存款，订单号: '.$data['order_sn'];
                $data_onempf['freeze_onempf'] = array('exp','freeze_onempf-'.$data['amount']);

                $data_msg['member_onempf'] = 0;
                $data_msg['freeze_onempf'] = $data['amount'];
                $data_msg['description'] = $data_log['lg_desc'];
                break;
    

            case 'refund':
                $data_log['onempf_amount'] = $data['amount'];
                $data_log['description'] = '确认退款，订单号: '.$data['order_sn'];
                $data_onempf['member_onempf'] = array('exp','member_onempf+'.$data['amount']);

                $data_msg['onempf_amount'] = $data['amount'];
                $data_msg['description'] = $data_log['lg_desc'];
                break;
            case 'vr_refund':
                $data_log['onempf_amount'] = $data['amount'];
                $data_log['description'] = '虚拟兑码退款成功，订单号: '.$data['order_sn'];
                $data_onempf['onempf_amount'] = array('exp','onempf_amount+'.$data['amount']);

                $data_msg['onempf_amount'] = $data['amount'];
                $data_msg['description'] = $data_log['lg_desc'];
                break;
      
//
//				////////////////////zmr>v20////////////////////////////////////
//				case 'sys_add_money':
//                $data_log['lg_av_amount'] = $data['amount'];
//                $data_log['lg_desc'] = '管理员调节预存款【增加】，充值单号: '.$data['pdr_sn'];
//                $data_log['lg_admin_name'] = $data['admin_name'];
//                $data_onempf['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
//
//                $data_msg['av_amount'] = $data['amount'];
//                $data_msg['freeze_amount'] = 0;
//                $data_msg['desc'] = $data_log['lg_desc'];
//                break;
//				case 'sys_del_money':
//                $data_log['lg_av_amount'] = -$data['amount'];
//                $data_log['lg_desc'] = '管理员调节预存款【减少】，充值单号: '.$data['pdr_sn'];
//                $data_onempf['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);
//
//                $data_msg['av_amount'] = -$data['amount'];
//                $data_msg['freeze_amount'] = 0;
//                $data_msg['desc'] = $data_log['lg_desc'];
//                break;
//				case 'sys_freeze_money':
//                $data_log['lg_av_amount'] = -$data['amount'];
//                $data_log['lg_freeze_amount'] = $data['amount'];
//				$data_log['lg_desc'] = '管理员调节预存款【冻结】，充值单号: '.$data['pdr_sn'];
//                $data_onempf['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);
//                $data_onempf['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);
//
//                $data_msg['av_amount'] = -$data['amount'];
//                $data_msg['freeze_amount'] = $data['amount'];
//                $data_msg['desc'] = $data_log['lg_desc'];
//                break;
//				case 'sys_unfreeze_money':
//                $data_log['lg_av_amount'] = $data['amount'];
//                $data_log['lg_freeze_amount'] = -$data['amount'];
//                $data_log['lg_desc'] = '管理员调节预存款【解冻】，充值单号: '.$data['pdr_sn'];
//                $data_log['lg_admin_name'] = $data['admin_name'];
//                $data_onempf['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
//                $data_onempf['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
//
//                $data_msg['av_amount'] = $data['amount'];
//                $data_msg['freeze_amount'] = -$data['amount'];
//                $data_msg['desc'] = $data_log['lg_desc'];
//                break;
				
				//////////////////////////////////////////////////////

            default:
                throw new Exception('参数错误');
                break;
        }

        $update = Model('member')->editMember(array('member_id'=>$data['member_id']),$data_onempf);

        if (!$update) {
            throw new Exception('操作失败');
        }
       
        $insert = $this->table('onempf_log')->insert($data_log);
      
        if (!$insert) {
            throw new Exception('操作失败');
        }

        // 支付成功发送买家消息
        $param = array();
        $param['code'] = 'onempf';
        $param['member_id'] = $data['member_id'];
        $data_msg['amount'] = ncPriceFormat($data_msg['onempf_amount']);
        $param['param'] = $data_msg;
        QueueClient::push('sendMemberMsg', $param);
        return $insert;
    }
}
