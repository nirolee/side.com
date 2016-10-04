<?php
/**
 * 我的收藏橱窗
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('InShopNC') or exit('Access Invalid!');

class member_favorites_classControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
		
    }

    /**
     * 橱窗列表
     * add by niro 17:47 2016.7.20
     */
    public function favorites_listOp() {
        
        $model_class = Model('favorites_class');

        $favorites_list = $model_class->getFavoritesList(array(
            'member_id'=>$this->member_info['member_id'],favorites_class_type => 'showcase'
        ), '*', 0);
		
        //$page_count = $model_class->gettotalpage();
        output_data(array('favorites_list' => $favorites_list));
    }

    /**
     * 添加瞬间收藏
     */
    public function favorites_addOp() {
		
        $fav_id = intval($_GET['personal_id']);
        $favorites_class_id = intval($_GET['favorites_class_id']);
        
        if ($fav_id <= 0){

            output_data(array(status => 0, message => '参数错误'));
        }

        $favorites_model = Model('favorites');

        //判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array(
            'fav_id'=>$fav_id,
            'favorites_class_id'=>$favorites_class_id,
            'fav_type'=>'showcase',
            'member_id'=>$this->member_info['member_id'],
        ));
        
        if(!empty($favorites_info)){

            output_data(array(status => 0, message => '您已经收藏了该瞬间'));
        }
        
        //添加收藏
        $insert_arr = array();
        $insert_arr['member_id'] = $this->member_info['member_id'];
        $insert_arr['member_name'] = $this->member_info['member_name'];
        $insert_arr['fav_id'] = $fav_id;
        $insert_arr['fav_type'] = 'showcase';
        $insert_arr['fav_time'] = time();
        $insert_arr['favorites_class_id'] = $favorites_class_id;
        $result = $favorites_model->addFavorites($insert_arr);

        if ($result) {
			
            output_data(array(status => 1, message => '收藏成功'));
        
        } else {

            output_data(array(status => 0, message => '收藏失败'));

        }
		
    }
	
    /**
     * 添加橱窗收藏
     * add by lizh 11:39 2016/8/4
     */
    public function favorites_class_addOp() {
		
        $fav_id = intval($_GET['favorites_class_id']);

        if ($fav_id <= 0){

            output_data(array(status => 0, message => '参数错误'));
        }
        
        $favorites_class = Model('favorites_class');
        $rs = $favorites_class -> isExist(array('favorites_class_id' => $fav_id, 'member_id'=>$this->member_info['member_id']));
        if($rs) {
            
           output_data(array(status => 0, message => '不能关注自己的橱窗'));
            
        }

        $favorites_model = Model('favorites');

        //判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array(
            'fav_id'=>$fav_id,
            'fav_type'=>'showcase_class',
            'member_id'=>$this->member_info['member_id'],
        ));
        
        if(!empty($favorites_info)){

            output_data(array(status => 0, message => '您已经收藏了该橱窗'));
        }

        //添加收藏
        $insert_arr = array();
        $insert_arr['member_id'] = $this->member_info['member_id'];
        $insert_arr['member_name'] = $this->member_info['member_name'];
        $insert_arr['fav_id'] = $fav_id;
        $insert_arr['fav_type'] = 'showcase_class';
        $insert_arr['fav_time'] = time();
        $result = $favorites_model->addFavorites($insert_arr);
		
        $favorites_class = Model('favorites_class');
        $insert_favorites_class_arr['favorites_count'] = array('exp','favorites_count+1');
        $where_array['favorites_class_id'] = $fav_id;
        $favorites_class -> editFavorites($where_array,$insert_favorites_class_arr);
		
        if ($result) {
			
            output_data(array(status => 1, message => '收藏成功'));
        
        } else {

            output_data(array(status => 0, message => '收藏失败'));

        }
		
    }

    /**
     * 删除收藏
     */
    public function favorites_delOp() {
        $fav_id = intval($_POST['store_id']);
        if ($fav_id <= 0) {
            output_error('参数错误');
        }

        $model_favorites = Model('favorites');
        $model_store = Model('store');

        $condition = array();
        $condition['fav_type'] = 'store';
        $condition['fav_id'] = $fav_id;
        $condition['member_id'] = $this->member_info['member_id'];

        //判断是否已经收藏
        $favorites_info = $model_favorites->getOneFavorites($condition);
        if(empty($favorites_info)){
            output_error('收藏删除失败');
        }

        $model_favorites->delFavorites($condition);

        $model_store->editStore(array(
            'store_collect' => array('exp', 'store_collect - 1'),
        ), array(
            'store_id' => $fav_id,
            'store_collect' => array('gt', 0),
        ));

        output_data('1');
    }
	
    /**
     * 创建橱窗
     * add by lizh 17:38 2016/7/21
     */
    public function create_favoritesOp() {

        $fav_id = $_POST['personal_id'];

        /* if ($fav_id <= 0){

                output_data(array(status => 0, message => '参数错误'));

        } */

        //获取瞬间图片；当封面

        $commend_image = null;
        if(!empty($fav_id)) {
            $fav_id_array = explode(',', $fav_id);
            $micro_personal = Model('micro_personal');
            $micro_personal_data = $micro_personal -> getOneData(array(personal_id => $fav_id_array[0]),'commend_image,commend_member_id');
            $commend_image = $micro_personal_data['commend_member_id'].DS.$micro_personal_data['commend_image'];
            $favorites_class_insert['favorites_img'] = $commend_image;
        }

        //添加橱窗
        $favorites_class = Model('favorites_class');
        $favorites_class_insert['favorites_class_name'] = $_POST['favorites_class_name'];
        $favorites_class_insert['favorites_class_type'] = 'showcase';
        $favorites_class_insert['favorites_content'] = $_POST['favorites_content'];


        if(empty($_POST['visible_state'])) {

            $_POST['visible_state'] = 0;

        }
        $favorites_class_insert['visible_state'] = $_POST['visible_state'];
        $favorites_class_insert['member_id'] =  $this->member_info['member_id'];
        $favorites_class_insert['create_time'] =  time();
        //print_r($favorites_class_insert);
        $favorites_class_id = $favorites_class -> addFavorites($favorites_class_insert);
        //p();
        //添加商品收藏
        $favorites_model = Model('favorites');

        if(!empty($fav_id)) {

            //判断是否已经收藏
            $favorites_info = $favorites_model->getOneFavorites(array(
                   'fav_id'=>array('in',$fav_id_array),
                   'favorites_class_id'=>$favorites_class_id,
                   'fav_type'=>'showcase',
                   'member_id'=>$this->member_info['member_id'],
            ));

            if(!empty($favorites_info)){
                   //output_error('您已经收藏了该店铺');
                   output_data(array(status => 0, message => '您已经收藏了该瞬间'));
            }

            //添加收藏
            $insert_arr = array();
            $insert_arr['member_id'] = $this->member_info['member_id'];
            $insert_arr['member_name'] = $this->member_info['member_name'];
            $insert_arr['fav_type'] = 'showcase';
            $insert_arr['fav_time'] = time();
            $insert_arr['favorites_class_id'] = $favorites_class_id;
            
            foreach($fav_id_array as $k => $v) {
                
                $insert_arr['fav_id'] = $v;
                $result = $favorites_model->addFavorites($insert_arr);
            }

            output_data(array(status => 1, message => '收藏成功'));

        } else {

            if (!empty($favorites_class_id)) {

                output_data(array(status => 1, message => '收藏成功'));

            } else {

                output_data(array(status => 0, message => '收藏失败'));

            }

        }

    }
        
        /**
	 * 编辑橱窗
	 * add by niro 17:38 2016/8/22
	 */
	public function edit_favoritesOp() {
		$fav_class_id = $_POST['fav_class_id'];
	
		
		//添加橱窗
		$favorites_class = Model('favorites_class');
		$data['favorites_class_name'] = $_POST['favorites_class_name'];
		$data['favorites_class_type'] = 'showcase';
		$data['favorites_content'] = $_POST['favorites_content'];
	
		$data['visible_state'] = 1;
		$data['member_id'] =  $this->member_info['member_id'];
		$data['create_time'] =  time();
		
		$favorites_class_id = $favorites_class -> editFavorites(array('favorites_class_id'=>$fav_class_id),$data);
		


        if ($favorites_class_id) {

            output_data(array(status => 1, message => '编辑成功'));
        } else {
            output_data(array(status => 0, message => '编辑失败'));
        }
		
	}
        
              /**
	 * 新建橱窗
	 * add by niro 17:38 2016/8/22
	 */
	public function add_favoritesOp() {
		
		//添加橱窗
		$favorites_class = Model('favorites_class');
		$data['favorites_class_name'] = $_POST['favorites_class_name'];
		$data['favorites_class_type'] = 'showcase';
		$data['favorites_content'] = $_POST['favorites_content'];
	
		$data['visible_state'] = 1;
		$data['member_id'] =  $this->member_info['member_id'];
		$data['create_time'] =  time();
		
		$favorites_class_id = $favorites_class -> addFavorites($data);
		


        if ($favorites_class_id) {

            output_data(array(status => 1, message => '新增成功'));
        } else {
            output_data(array(status => 0, message => '新增失败'));
        }
		
	}
        
    /**
     * 取消橱窗收藏
     */
    public function favorites_class_cancelOp() {
		
        $fav_id = intval($_GET['favorites_class_id']);

        if ($fav_id <= 0){

            output_data(array(status => 0, message => '参数错误'));
        }
        
        $favorites_model = Model('favorites');

        //判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array(
            'fav_id'=>$fav_id,
            'fav_type'=>'showcase_class',
            'member_id'=>$this->member_info['member_id'],
        ));
        
        if(empty($favorites_info)){

            output_data(array(status => 0, message => '您未收藏该橱窗'));
        }

        //添加收藏
        $result = $favorites_model->delFavorites(array(
            'fav_id'=>$fav_id,
            'fav_type'=>'showcase_class',
            'member_id'=>$this->member_info['member_id'],
        ));
		
        $favorites_class = Model('favorites_class');
        $insert_favorites_class_arr['favorites_count'] = array('exp','favorites_count-1');
        $where_array['favorites_class_id'] = $fav_id;
        $favorites_class -> editFavorites($where_array,$insert_favorites_class_arr);
		
        if ($result) {
			
            output_data(array(status => 1, message => '取消关注成功'));
        
        } else {

            output_data(array(status => 0, message => '取消关注失败'));

        }
		
    }
        
        

}
