<?php
/**
 *
 *
 *
 * 
 */
defined('InShopNC') or exit('Access Invalid!');
class favorites_classModel extends Model{

    /**
     * 收藏列表
     *
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getFavoritesList($condition, $field = '*', $page = 0 , $order = 'favorites_class_id desc', $limit = 0) {
        return $this->table('favorites_class')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
    }
	
	/**
	 * 获取列表总数
	 */
	public function getCount($condition) {
		
		return $this->table('favorites_class')->where($condition)->count();
		//p();
		
	}
	
    /**
     * 取单个收藏的内容
     *
     * @param array $condition 查询条件
     * @return array 数组类型的返回结果
     */
    public function getOneFavorites($condition) {
        return $this->table('favorites_class')->where($condition)->find();
    }

   
    /**
     * 新增收藏
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addFavorites($param) {
        if (empty($param)) {
            return false;
        }
 
        return $this->table('favorites_class')->insert($param);
    }

    /**
     * 修改记录
     *
     * @param
     * @return bool
     */
    public function editFavorites($condition, $data) {
        if (empty($condition)) {
            return false;
        }
        if (is_array($data)) {
            $result = $this->table('favorites_class')->where($condition)->update($data);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 删除
     *
     * @param array $condition 查询条件
     * @return bool 布尔类型的返回结果
     */
    public function delFavorites($condition) {
        if (empty($condition)) {
            return false;
        }
        return $this->table('favorites_class')->where($condition)->delete();
    }
    
    /**
     *  判断是否存在 
     *  @param array $condition
     *
     */
    public function isExist($condition) {
        $result = $this->getOneFavorites($condition);
        if(empty($result)) {
            return FALSE;
        }
        else {
            return TRUE;
        }
    }
}
