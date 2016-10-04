<?php
/**
 * 我的收藏
 *
 *
 *
 *
 */


defined('InShopNC') or exit('Access Invalid!');

class member_favoritesControl extends mobileMemberControl {

	public function __construct(){
		parent::__construct();
	}

    /**
     * 收藏列表
     */
    public function favorites_listOp() {
		$model_favorites = Model('favorites');

        $favorites_list = $model_favorites->getGoodsFavoritesList(array('member_id'=>$this->member_info['member_id']), '*', $this->page);
        $page_count = $model_favorites->gettotalpage();
        $favorites_id = '';
        foreach ($favorites_list as $value){
            $favorites_id .= $value['fav_id'] . ',';
        }
        $favorites_id = rtrim($favorites_id, ',');

        $model_goods = Model('goods');
        $field = 'goods_id,goods_name,goods_price,goods_image,store_id';
        $goods_list = $model_goods->getGoodsList(array('goods_id' => array('in', $favorites_id)), $field);
        foreach ($goods_list as $key=>$value) {
            $goods_list[$key]['fav_id'] = $value['goods_id'];
            $goods_list[$key]['goods_image_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
        }

        output_data(array('favorites_list' => $goods_list), mobile_page($page_count));
    }
       public function store_listOp() {
		$model_favorites = Model('favorites');
        $favorites_list = $model_favorites->getStoreFavoritesList(array('member_id'=>$this->member_info['member_id']), '*', $this->page);
        
        $page_count = $model_favorites->gettotalpage();
        $favorites_id = '';
        foreach ($favorites_list as $value){
            $favorites_id .= $value['fav_id'] . ',';
        }
        $favorites_id = rtrim($favorites_id, ',');

        $model_store = Model('store');
        $field = 'store_id,store_name,store_avatar';
        $store_list = $model_store->getStoreList(array('store_id' => array('in', $favorites_id)), $field);
        foreach ($store_list as $key=>$value) {
            $store_list[$key]['fav_id'] = $value['store_id'];
            $store_list[$key]['store_avatar'] = UPLOAD_SITE_URL.'/'.ATTACH_STORE.'/'.$store_list[$key]['store_avatar'];
        }

       output_data(array('favorites_list' => $store_list), mobile_page($page_count));
    }

    /**
     * 添加收藏
     */
    public function store_addOp() {
		$store_id = intval($_POST['store_id']);
		if ($store_id <= 0){
            output_error('参数错误');
		}

		$favorites_model = Model('favorites');

		//判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array('fav_id'=>$store_id,'fav_type'=>'store','member_id'=>$this->member_info['member_id']));
		if(!empty($favorites_info)) {
            output_error('您已经收藏了该商铺');
		}

		//判断商品是否为当前商铺所有
		$store_model = Model('store');
		$store_info = $store_model->getStoreInfoByID($store_id);
		$seller_info = Model('seller')->getSellerInfo(array('member_id'=>$this->member_info['member_id']));
		if ($store_info['store_id'] == $seller_info['store_id']) {
            output_error('您不能收藏自己商铺');
		}

		//添加收藏
		$insert_arr = array();
		$insert_arr['member_id'] = $this->member_info['member_id'];
		$insert_arr['fav_id'] = $store_id;
		$insert_arr['fav_type'] = 'store';
		$insert_arr['fav_time'] = TIMESTAMP;
		$result = $favorites_model->addFavorites($insert_arr);
		if ($result){
			//增加收藏数量
			$store_model->editStoreById(array('store_collect' => array('exp', 'store_collect + 1')), $store_id);
            output_data('1');
		}else{
            output_error('收藏失败');
		}
    }

    /**
     * 删除收藏店铺
     */
    public function store_delOp() {
		$fav_id = intval($_POST['store_id']);
		if ($fav_id <= 0){
            output_error('参数错误');
		}

		$model_favorites = Model('favorites');

        $condition = array();
        $condition['fav_id'] = $fav_id;
        $condition['member_id'] = $this->member_info['member_id'];
        $model_favorites->delFavorites($condition);
        output_data('1');
    }
	
    public function favorites_addOp() {
		$goods_id = intval($_POST['goods_id']);
		if ($goods_id <= 0){
            output_error('参数错误');
		}

		$favorites_model = Model('favorites');

		//判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array('fav_id'=>$goods_id,'fav_type'=>'goods','member_id'=>$this->member_info['member_id']));
		if(!empty($favorites_info)) {
            output_error('您已经收藏了该商品');
		}

		//判断商品是否为当前会员所有
		$goods_model = Model('goods');
		$goods_info = $goods_model->getGoodsInfoByID($goods_id);
		$seller_info = Model('seller')->getSellerInfo(array('member_id'=>$this->member_info['member_id']));
		if ($goods_info['store_id'] == $seller_info['store_id']) {
            output_error('您不能收藏自己发布的商品');
		}

		//添加收藏
		$insert_arr = array();
		$insert_arr['member_id'] = $this->member_info['member_id'];
		$insert_arr['fav_id'] = $goods_id;
		$insert_arr['fav_type'] = 'goods';
		$insert_arr['fav_time'] = TIMESTAMP;
		$result = $favorites_model->addFavorites($insert_arr);

		if ($result){
			//增加收藏数量
			$goods_model->editGoodsById(array('goods_collect' => array('exp', 'goods_collect + 1')), $goods_id);
            output_data('1');
		}else{
            output_error('收藏失败');
		}
    }

    /**
     * 删除收藏商品
     */
    public function favorites_delOp() {
		$fav_id = $_POST['fav_id'];
		//var_dump($_POST);
		$fav_id_array = explode(',',$fav_id);
		if (empty($fav_id)){
            output_error('参数错误');
		}

		$model_favorites = Model('favorites');

        $condition = array();
        $condition['fav_id'] = array('in',$fav_id_array);
        $condition['member_id'] = $this->member_info['member_id'];
        $model_favorites->delFavorites($condition);
		//p();
        output_data('1');
    }

}
