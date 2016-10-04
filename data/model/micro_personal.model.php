<?php
/**
 * 微商城推荐商品模型 
 *
 * 
 *
 *
 
 */
defined('InShopNC') or exit('Access Invalid!');
class micro_personalModel extends Model{

    const TABLE_NAME = 'micro_personal';
    const PK = 'personal_id';

    public function __construct(){
        parent::__construct('micro_personal');
    }

    /**
     * 读取推荐商品列表 
     *
     */
    public function getList($condition,$page=null,$order='',$field='*',$limit=''){
        $result = $this->table(self::TABLE_NAME)->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
        return $result;
    }

    /**
     * 读取推荐商品列表和用户信息
     * update by lizh 11:39 2016/7/19
     */
    public function getListWithUserInfo($condition,$page='',$order='',$field='*',$limit='',$group=''){
        $on = 'micro_personal.commend_member_id = member.member_id';
        $result = $this->table('micro_personal,member')->field($field)->join('left')->on($on)->where($condition)->page($page)-> order($order)->limit($limit)->select();	
        return $result;
    }

    /**
     * 读取推荐商品列表和用户信息分组
     * update by lizh 18:11 2016/7/19
     */
    public function getGroupListWithUserInfo($condition,$page='',$order='',$field='*',$limit='',$group=''){

        $on = 'micro_personal.commend_member_id = member.member_id';

        $result = $this->table('micro_personal,member')->field($field)->join('left')->on($on)->where($condition)->page($page)-> group($group) -> order($order)->limit($limit)->select();

        return $result;
    }

    /**
     * 读取不存在于审核的且未认领的瞬间信息和用户名信息
     * add by lizh 14:39 2016/7/16
     */
    public function getNoExistByCheckWithUserInfo($condition,$page=null,$order='',$field='*',$limit=''){

            $sql = "select wp.personal_id, wp.commend_member_id, wp.commend_image, wp.commend_message, wm.member_name 
                                    from wantease_micro_personal wp
                                    left join wantease_micro_personal_check wpc on wp.personal_id = wpc.personal_id
                                    left join wantease_member wm on wp.commend_member_id = wm.member_id
                                    where wpc.check_id is NULL and wp.flag_state = 0
                                    ORDER BY wp.personal_id desc";

            $on = 'micro_personal.personal_id = micro_personal_check.personal_id, micro_personal.commend_member_id = member.member_id';
            $result = $this->table('micro_personal,micro_personal_check,member')->field($field)->join('left')->on($on)->where($condition)->page($page)->order($order)->limit($limit)->select();		
            //$result = $this -> query($sql);
            return $result;		
    }


    /**
    * 根据编号获取单个内容
    *
    */
    public function getOne($param){
        $result = $this->where($param)->find();
        return $result;
    }
	
    /**
     * 根据编号获取单个内容
     * add by lizh 11:22 2016/7/21
     */
    public function getOneData($param,$field="*"){
        $result = $this->field($field) -> where($param)->find();
        return $result;
    }

    /**
    * 根据编号获取单个内容
    *
    */
    public function getOneWithUserInfo($param){
        $on = 'micro_personal.commend_member_id = member.member_id';
        $result = $this->table('micro_personal,member')->join('left')->on($on)->where($param)->find();
        return $result;
    }

    /**
     * add by niro 2016.07.16
     * 我的认领
     *
     */
    public function getMyCheck($condition,$page=null,$order='',$field='*'){
        $on = 'micro_personal.personal_id = micro_personal_check.personal_id';
        $result = $this->table('micro_personal,micro_personal_check')->field($field)->join('left')->on($on)->where($condition)->page($page)->order($order)->select();
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
	
    /**
     * 获取热门话题里瞬间的数据
     * add by lizh 16:52 2016/7/12
     */
    public function getHotList($array,$limit_row=3) {

            foreach($array as $k => $v) {

                    $data = array();
                    $class_id = $v['class_id'];
                    $count = $this -> where(array(class_id => $class_id)) -> field('personal_id,commend_image,commend_member_id') -> count();
                    $data = $this -> where(array(class_id => $class_id)) -> field('personal_id,commend_image,commend_member_id') -> limit($limit_row) -> select();

                    foreach($data as $k2 => $v2) {
                            $data[$k2]['commend_image'] = UPLOAD_SITE_URL.DS.ATTACH_MICROSHOP.DS.$v2['commend_member_id'].'/'.$v2['commend_image'];
                    }
                    $array[$k]['micro_personal'] = $data;
                    $array[$k]['micro_personal_count'] = $count;

            }

            return $array;

    }

    /**
     * 获取橱窗里瞬间的数据
     * add by lizh 16:52 2016/7/12
     */
    public function getShowcaseList($array,$limit_row=3) {

        foreach($array as $k => $v) {

            $data = array();
            $class_id = $v['class_id'];
            $count = $this -> where(array(class_id => $class_id)) -> field('personal_id,commend_image,commend_member_id') -> count();
            $data = $this -> where(array(class_id => $class_id)) -> field('personal_id,commend_image,commend_member_id') -> limit($limit_row) -> select();

            foreach($data as $k2 => $v2) {
                    $data[$k2]['commend_image'] = UPLOAD_SITE_URL.DS.ATTACH_MICROSHOP.DS.$v2['commend_member_id'].'/'.$v2['commend_image'];
            }
            $array[$k]['micro_personal'] = $data;
            $array[$k]['micro_personal_count'] = $count;

        }

        return $array;

    }
     
}
