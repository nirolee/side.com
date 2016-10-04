<?php
/**
 * 新闻管理
 *
 *
 *
 *
 
 */
defined('InShopNC') or exit('Access Invalid!');

class newsModel{
	/**
	 * 列表
	 *
	 * @param array $condition 检索条件
	 * @param obj $page 分页
	 * @return array 数组结构的返回结果
	 */
	public function getnewsList($condition,$page=''){
		$condition_str = $this->_condition($condition);
		$param = array();
		$param['table'] = 'news';
		$param['where'] = $condition_str;
		$param['limit'] = $condition['limit'];
		$param['order']	= (empty($condition['order'])?'news_sort asc,news_time desc':$condition['order']);
		$result = Db::select($param,$page);
		return $result;
	}

	/**
	 * 连接查询列表
	 *
	 * @param array $condition 检索条件
	 * @param obj $page 分页
	 * @return array 数组结构的返回结果
	 */
	public function getJoinList($condition,$page=''){
		$result	= array();
		$condition_str	= $this->_condition($condition);
		$param	= array();
		$param['table'] = 'news,news_class';
		$param['field']	= empty($condition['field'])?'*':$condition['field'];;
		$param['join_type']	= empty($condition['join_type'])?'left join':$condition['join_type'];
		$param['join_on']	= array('news.nc_id=news_class.nc_id');
		$param['where'] = $condition_str;
		$param['limit'] = $condition['limit'];
		$param['order']	= empty($condition['order'])?'news.news_sort':$condition['order'];
		$result = Db::select($param,$page);
		return $result;
	}

	/**
	 * 构造检索条件
	 *
	 * @param int $id 记录ID
	 * @return string 字符串类型的返回结果
	 */
	private function _condition($condition){
		$condition_str = '';

		if ($condition['news_show'] != ''){
			$condition_str .= " and news.news_show = '". $condition['news_show'] ."'";
		}
		if ($condition['nc_id'] != ''){
			$condition_str .= " and news.nc_id = '". $condition['nc_id'] ."'";
		}
		if ($condition['nc_ids'] != ''){
			//if(is_array($condition['nc_ids']))$condition['nc_ids']	= implode(',',$condition['nc_ids']);
			$condition_str .= " and news.nc_id in(". $condition['nc_ids'] .")";
		}
		if ($condition['like_title'] != ''){
			$condition_str .= " and news.news_title like '%". $condition['like_title'] ."%'";
		}
		if ($condition['home_index'] != ''){
			$condition_str .= " and (news_class.nc_id <= 7 or (news_class.nc_parent_id > 0 and news_class.nc_parent_id <= 7))";
		}

		return $condition_str;
	}

	/**
	 * 取单个内容
	 *
	 * @param int $id ID
	 * @return array 数组类型的返回结果
	 */
	public function getOnenews($id){
		if (intval($id) > 0){
			$param = array();
			$param['table'] = 'news';
			$param['field'] = 'news_id';
			$param['value'] = intval($id);
			$result = Db::getRow($param);
			return $result;
		}else {
			return false;
		}
	}

	/**
	 * 新增
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function add($param){
		if (empty($param)){
			return false;
		}
		if (is_array($param)){
			$tmp = array();
			foreach ($param as $k => $v){
				$tmp[$k] = $v;
			}
			$result = Db::insert('news',$tmp);
			return $result;
		}else {
			return false;
		}
	}

	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @return bool 布尔类型的返回结果
	 */
	public function update($param){
		if (empty($param)){
			return false;
		}
		if (is_array($param)){
			$tmp = array();
			foreach ($param as $k => $v){
				$tmp[$k] = $v;
			}
			$where = " news_id = '". $param['news_id'] ."'";
			$result = Db::update('news',$tmp,$where);
			return $result;
		}else {
			return false;
		}
	}

	/**
	 * 删除
	 *
	 * @param int $id 记录ID
	 * @return bool 布尔类型的返回结果
	 */
	public function del($id){
		if (intval($id) > 0){
			$where = " news_id = '". intval($id) ."'";
			$result = Db::delete('news',$where);
			return $result;
		}else {
			return false;
		}
	}
}