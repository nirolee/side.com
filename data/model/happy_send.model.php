<?php
/**
 * 属性模型
 *
 * 
 *
 *
 
 */
defined('InShopNC') or exit('Access Invalid!');

class happy_sendModel extends Model {
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 属性列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getAttribute($condition, $field = '*') {
        return $this->table('happy_send')->where($condition)->field($field)->find();
    }

    /**
     * 属性列表
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getAttributeList($condition, $field = '*') {
         return $this->table('happy_send')->where($condition)->field($field)->order('hps_id desc')->limit(1000)->select();
    }
    
    /**
     * 属性值列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
     public function getAttributeValueList($condition, $field = '*') {
        return $this->table('happy_send_value')->where($condition)->field($field)->order('hps_value_sort asc')->select();
    }
    
    public function getAttributeValue($condition, $field = '*') {
        return $this->table('happy_send_value')->where($condition)->field($field)->find();
    }
    
    /**
     * 保存属性值
     * @param array $insert
     * @return boolean
     */
    public function addAttributeValueAll($insert) {
        return $this->table('happy_send_value')->insertAll($insert);
    }
    
    /**
     * 保存属性值
     * @param array $insert
     * @return boolean
     */
    public function addAttribute($insert) {
        return $this->table('happy_send')->insert($insert);
    }
    public function addAttributeValue($insert) {
        return $this->table('happy_send_value')->insert($insert);
    }
    
    /**
     * 编辑属性值
     * @param array $update
     * @param array $condition
     * @return boolean
     */
   public function editAttribute($update, $condition) {
        return $this->table('happy_send')->where($condition)->update($update);
    }
    public function editAttributeValue($update, $condition) {
        return $this->table('happy_send_value')->where($condition)->update($update);
    }
}