<?php
/**
 * 微商城推荐商品模型 
 *
 * 
 *
 *
 
 */
defined('InShopNC') or exit('Access Invalid!');
class micro_personal_checkModel extends Model{
	
	const TABLE_NAME = 'micro_personal_check';
	 
	public function __construct(){
        parent::__construct('micro_personal_check');
    }
	
	/**
	 * 获取内容列表
	 *
	 */
	public function getList($condition,$page=null,$order='',$field='*',$limit=''){
        $result = $this->table(self::TABLE_NAME)->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
        return $result;
	}
	
    /**
	 * 根据编号获取单个内容
	 *
	 */
    public function getOne($param,$field ="*"){
		
        $result = $this-> field($field) -> where($param)->find();
        return $result;
    }

	/*
	 *  判断是否存在 
	 *  @param array $condition
     *
	 */
	public function isExist($param) {
        $result = $this->getOne($param);
        if(empty($result)) {
            return FALSE;
        }
        else {
            return TRUE;
        }
	}

	/*
	 * 增加 
	 * @param array $param
	 * @return bool
	 */
    public function save($param){
        return $this->table(self::TABLE_NAME)->insert($param);	
    }
	
	/*
	 * 更新
	 * @param array $update_array
	 * @param array $where_array
	 * @return bool
	 */
    public function modify($update_array, $where_array){
        return $this->table(self::TABLE_NAME)->where($where_array)->update($update_array);
    }
	
	/*
	 * 删除
	 * @param array $param
	 * @return bool
	 */
    public function drop($param){
        return $this->table(self::TABLE_NAME)->where($param)->delete();
    }
		
}
