<?php
/**
 * 微商城喜欢模型
 *
 * 
 *
 *
 
 */
defined('InShopNC') or exit('Access Invalid!');
class micro_likeModel extends Model{

    public function __construct(){
        parent::__construct('micro_like');
    }

	/**
	 * 读取列表 
	 * @param array $condition
	 *
	 */
	public function getList($condition,$page=null,$order='',$field='*'){
        $result = $this->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
	}

    /**
     * 喜欢随心看列表
     */
    public function getGoodsList($condition,$page=null,$order='',$field='*') {
        $on = 'micro_goods.commend_id = micro_like.like_object_id,micro_goods.commend_member_id=member.member_id';
        $result = $this->table('micro_goods,micro_like,member')->field($field)->join('left')->on($on)->where($condition)->page($page)->order($order)->select();
        return $result;
    }

    /**
     * 喜欢个人秀列表
     */
    public function getPersonalList($condition,$page=null,$order='',$field='*') {
        $on = 'micro_personal.personal_id = micro_like.like_object_id,micro_personal.commend_member_id=member.member_id';
        $result = $this->table('micro_personal,micro_like,member')->field($field)->join('left')->on($on)->where($condition)->page($page)->order($order)->select();
        return $result;
    }

    /**
     * 喜欢店铺列表
     */
    public function getStoreList($condition,$page=null,$order='',$field='*') {
        $result = $this->table('micro_like')->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
    }
	
	/**
	 * 用户点赞
	 * add by lizh 11:28 2016/7/13
	 */
	public function setUsersPointLike($member_id, $personal_id) {
		
		$data = $this -> getOne(array(like_member_id => $member_id, like_object_id => $personal_id, like_type => 2));
		
		if(!empty($data)) {
			
                    return $status = 2;
			
		}
		
		$insert_array['like_type'] = 2;
		$insert_array['like_object_id'] = $personal_id;
		$insert_array['like_member_id'] = $member_id;
		$insert_array['like_time'] = time();
		
		$this -> beginTransaction();
		$result = $this -> save($insert_array);
		if(!$result) {
			
			$this -> rollback();
			return $status = 0;
		}
		
		$micro_personal = Model('micro_personal');
		$micro_personal_data = $micro_personal -> getOne(array(personal_id => $personal_id));
                
		$like_count = $micro_personal_data['like_count'];
		$like_count += 1;
		$micro_personal_result = $micro_personal -> modify(array(like_count => $like_count),array(personal_id => $personal_id));
		
		if(!$micro_personal_result) {
			
			$this -> rollback();
			return $status = 0;
			
		} else {
			
			$this -> commit();
			return $status = 1;
			
		}
		
		
	}

    /**
	 * 读取单条记录
	 * @param array $condition
	 *
	 */
    public function getOne($condition){
        $result = $this->where($condition)->find();
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
	
}
