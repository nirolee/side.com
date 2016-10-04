<?php
/**
 * 买家收藏
 *
 *
 *
 * 
 */
defined('InShopNC') or exit('Access Invalid!');
class favoritesModel extends Model{

    /**
     * 收藏列表
     *
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getFavoritesList($condition, $field = '*', $page = 0 , $order = 'log_id desc', $limit = 0) {
        return $this->table('favorites')->field($field)->where($condition)->order($order)->page($page)->limit($limit)->select();
    }

    /**
     * 收藏商品列表
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getGoodsFavoritesList($condition, $field = '*', $page = 0, $order = 'log_id desc') {
        $condition['fav_type'] = 'goods';
        return $this->getFavoritesList($condition, $field, $page, $order);
    }

    /**
     * 收藏店铺列表
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getStoreFavoritesList($condition, $field = '*', $page = 0, $order = 'log_id desc', $limit = 0) {
        $condition['fav_type'] = 'store';
        return $this->getFavoritesList($condition, $field, $page, $order, $limit);
    }
        /**
     * 收藏品牌列表
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getBrandFavoritesList($condition, $field = '*', $page = 0, $order = 'log_id desc', $limit = 0) {
        $condition['fav_type'] = 'brand';
        return $this->getFavoritesList($condition, $field, $page, $order, $limit);
    }
        /**
     * 收藏橱窗列表
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getClassFavoritesList($condition, $field = '*', $page = 0, $order = 'log_id desc', $limit = 0) {
        $condition['fav_type'] = 'showcase';
        return $this->getFavoritesList($condition, $field, $page, $order, $limit);
    }
    /**
     * 取单个收藏的内容
     *
     * @param array $condition 查询条件
     * @return array 数组类型的返回结果
     */
    public function getOneFavorites($condition) {
        return $this->table('favorites')->where($condition)->find();
    }

    /**
     * 获取店铺收藏数
     *
     * @param int $storeId
     *
     * @return int
     */
    public function getStoreFavoritesCountByStoreId($storeId, $memberId)
    {
        $where = array(
            'fav_type' => 'store',
            'fav_id' => $storeId,
        );

        if ($memberId > 0) {
            $where['member_id'] = $memberId;
        }

        return  $this->table('favorites')->where($where)->count();
    }
    
    public function getStoreFavoritesCount($memberId)
    {
        $where = array(
            'fav_type' => 'store',
        );

        if ($memberId > 0) {
            $where['member_id'] = $memberId;
        }

        return  $this->table('favorites')->where($where)->count();
    }

    /**
     * 获取品牌收藏数
     *
     * @param int $storeId
     *
     * @return int
     */
    public function getBrandsFavoritesCountByBrandsId($goodsId, $memberId)
    {
        $where = array(
            'fav_type' => 'brands',
            'fav_id' => $brandsId,
        );

        if ($memberId > 0) {
            $where['member_id'] = $memberId;
        }

        return  $this->table('favorites')->where($where)->count();
    }
 public function getBrandsFavoritesCount($memberId)
    {
        $where = array(
            'fav_type' => 'brands',
        );

        if ($memberId > 0) {
            $where['member_id'] = $memberId;
        }

        return  $this->table('favorites')->where($where)->count();
    }
    /**
     * 获取商品收藏数
     *
     * @param int $storeId
     *
     * @return int
     */
    public function getGoodsFavoritesCountByGoodsId($goodsId, $memberId)
    {
        $where = array(
            'fav_type' => 'goods',
            'fav_id' => $goodsId,
        );

        if ($memberId > 0) {
            $where['member_id'] = $memberId;
        }

        return  $this->table('favorites')->where($where)->count();
    }
 public function getGoodsFavoritesCount($memberId)
    {
        $where = array(
            'fav_type' => 'goods',
        );

        if ($memberId > 0) {
            $where['member_id'] = $memberId;
        }

        return  $this->table('favorites')->where($where)->count();
    }
	
	
    /**
     * 获取橱窗收藏数
     *
     * @param int $storeId
     *
     * @return int
     */
    public function getShowcase_classFavoritesCountByBrandsId($memberId)
    {
        $where = array(
            'fav_type' => 'showcase_class',
        );

        if ($memberId > 0) {
            $where['member_id'] = $memberId;
        }

        return  $this->table('favorites')->where($where)->count();
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
        if ($param['fav_type'] == 'store') {
            $store_id = intval($param['fav_id']);
            $model_store = Model('store');
            $store = $model_store->getStoreInfoByID($store_id);
            $param['store_name'] = $store['store_name'];
            $param['store_id'] = $store['store_id'];
            $param['sc_id'] = $store['sc_id'];
        }
        if ($param['fav_type'] == 'goods') {
            $goods_id = intval($param['fav_id']);
            $model_goods = Model('goods');
            $fields = 'goods_id,store_id,goods_name,goods_image,goods_price,goods_promotion_price';
            $goods = $model_goods->getGoodsInfoByID($goods_id,$fields);
            $param['goods_name'] = $goods['goods_name'];
            $param['goods_image'] = $goods['goods_image'];
            $param['log_price'] = $goods['goods_promotion_price'];//商品收藏时价格
            $param['log_msg'] = $goods['goods_promotion_price'];//收藏备注，默认为收藏时价格，可修改
            $param['gc_id'] = $goods['gc_id'];

            $store_id = intval($goods['store_id']);
            $model_store = Model('store');
            $store = $model_store->getStoreInfoByID($store_id);
            $param['store_name'] = $store['store_name'];
            $param['store_id'] = $store['store_id'];
            $param['sc_id'] = $store['sc_id'];
        }
        if($param['fav_type'] == 'brands'){
            $brands_id = intval($param['fav_id']);
            $model_brands = Model('brands');
            $brands = $model_brands->getBrandInfoByID($brands_id);
            $param['brand_name'] = $brands['brand_name'];
            $param['brand_id'] = $brands['brand_id'];
            $param['sc_id'] = $brands['sc_id'];
        }
        return $this->table('favorites')->insert($param);
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
            $result = $this->table('favorites')->where($condition)->update($data);
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
        return $this->table('favorites')->where($condition)->delete();
    }
	
	/**
	 * 获取我的橱窗模块
	 * add by lizh 2016/7/21 10:50
	 */
	public function getShowcaseList($array,$limit_row=4,$field='personal_id,commend_member_id,commend_image') {
		
		$micro_personal = Model('micro_personal');
		if(!empty($array)) {
			
			foreach($array as $k => $v) {
		
				$data = array();
				$class_id = $v['favorites_class_id'];
				$count = $this -> table('favorites')-> where(array(favorites_class_id => $class_id,fav_type => 'showcase')) -> count();
				$data = $this -> table('favorites')-> where(array(favorites_class_id => $class_id,fav_type => 'showcase')) -> field('fav_id') -> limit($limit_row) -> select();
				
				$micro_personal_all_data = array();
				$num = 0;
				foreach($data as $k2 => $v2) {
					
					$fav_id = $v2['fav_id'];
					
					$micro_personal_data = $micro_personal -> getOneData(array(personal_id => $fav_id),$field);
					$commend_image = $micro_personal_data['commend_image'];
					$commend_image_array = explode('.',$commend_image);
					$micro_personal_data['commend_image'] = $commend_image_array[0].'_list.'.$commend_image_array[1];
					//p();
					$micro_personal_data['commend_image'] = UPLOAD_SITE_URL.DS.ATTACH_MICROSHOP.DS.$micro_personal_data['commend_member_id'].'/'.$micro_personal_data['commend_image'];
					$micro_personal_all_data[$num] = $micro_personal_data;
					$num++;
					
				}
						   
				$array[$k]['micro_personal'] = $micro_personal_all_data;
				$array[$k]['micro_personal_count'] = $count;
				
				//
			}
			
		}
		
		if(empty($array)) {
			
			$array = array();
			
		}
		
		return $array;
		
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
        } else {
            return TRUE;
        }
    }
	
}
