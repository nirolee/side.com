<?php
/**
 * 文章 
 **/

defined('InShopNC') or exit('Access Invalid!');
class newsControl extends mobileHomeControl{

	public function __construct() {
        parent::__construct();
    }

    /**
     * 文章列表
     */
    public function news_listOp() {
        if(!empty($_GET['ac_id']) && intval($_GET['ac_id']) > 0) {
			$news_class_model	= Model('news_class');
			$news_model	= Model('news');
			$condition	= array();
			
			$child_class_list = $news_class_model->getChildClass(intval($_GET['ac_id']));
			$ac_ids	= array();
			if(!empty($child_class_list) && is_array($child_class_list)){
				foreach ($child_class_list as $v){
					$ac_ids[]	= $v['ac_id'];
				}
			}
			$ac_ids	= implode(',',$ac_ids);
			$condition['ac_ids']	= $ac_ids;
			$condition['news_show']	= '1';
			$news_list = $news_model->getnewsList($condition);			
			$news_type_name = $this->news_type_name($ac_ids);
			output_data(array('news_list' => $news_list, 'news_type_name'=> $news_type_name));		
		}
		else {
			output_error('缺少参数:文章类别编号');
		}    	
    }

    /**
     * 根据类别编号获取文章类别信息
     */
    private function news_type_name() {
    	if(!empty($_GET['ac_id']) && intval($_GET['ac_id']) > 0) {
			$news_class_model = Model('news_class');
			$news_class = $news_class_model->getOneClass(intval($_GET['ac_id']));
			return ($news_class['ac_name']);
		}
		else {
			return ('缺少参数:文章类别编号');			
		}    	
    }
    
    /**
     * 单篇文章显示
     */
    public function news_showOp() {
		$news_model	= Model('news');

        if(!empty($_GET['news_id']) && intval($_GET['news_id']) > 0) {
			$news	= $news_model->getOneNews(intval($_GET['news_id']));
                        
			if (empty($news)) {
				output_error('文章不存在');
			}
			else {
                            $news['news_content'] = html_entity_decode($news['news_content']) ;
//                            output_data($news);exit;
                            tpl::output('news',$news);
                            tpl::showpage('news');
			}
        } 
        else {
			output_error('缺少参数:文章编号');
        }
    }
}
