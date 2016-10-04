<?php
/**
 * 微商城评论模型
 *
 * 
 *
 *
 
 */
defined('InShopNC') or exit('Access Invalid!');
class micro_commentModel extends Model{

    public function __construct(){
        parent::__construct('micro_comment');
    }

    /**
     * 读取列表 
     * @param array $condition
     *
     */
    public function getList($condition,$page='',$order='',$field='*'){

        $result = $this->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
    }

	/**
	 * 读取商品评论和用户信息
	 *
	 */
	public function getListWithUserInfo($condition,$page='',$order='',$field='*'){
        $on = 'micro_comment.comment_member_id = member.member_id';
        $result = $this->table('micro_comment,member')->field($field)->join('left')->on($on)->where($condition)->page($page)->order($order)->select();
        return $result;
	}



    /**
	 * 读取单条记录
	 * @param array $condition
	 *
	 */
    public function getOne($condition){
       
        $result = $this-> table('micro_comment') -> where($condition)->find();
        
        return $result;
    }

	/*
	 *  判断是否存在 
	 *  @param array $condition
     *
	 */
	public function isExist($condition) {
        $result = $this->getOne($condition);
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
        return $this->insert($param);	
    }
	
	/*
	 * 增加 
	 * @param array $param
	 * @return bool
	 */
    public function saveAll($param){
        return $this->insertAll($param);	
    }
	
	/*
	 * 更新
	 * @param array $update
	 * @param array $condition
	 * @return bool
	 */
    public function modify($update, $condition){
        return $this->where($condition)->update($update);
    }
	
	/*
	 * 删除
	 * @param array $condition
	 * @return bool
	 */
    public function drop($condition){
        return $this->where($condition)->delete();
    }
    
    /*
     * 总数
     * @param array $condition
     * @return int
     * varsion 1.5.4
     */
    public function getCommentCount($condition) {
        
        return $this->where($condition)->count();
    }
    
     /**
     * 读取列表 
     * @param array $condition
     * varsion 1.5.4
     */
    public function getList_1_5_4($condition,$page='',$order='',$field='*', $limit = 0){

        $result = $this->field($field)->where($condition)->page($page) -> limit($limit)->order($order)->select();
        return $result;
    }
	
}
