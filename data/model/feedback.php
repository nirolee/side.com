<?php

defined('InShopNC') or exit('Access Invalid!');

class feedbackControl {

	/**
	 * @帮助页接口
	 * add by lizh 10:41 2016/7/8
	 */
    public function bangzhu_1_5Op() {
        
		$article_class = Model('article_class');
		$article_class_data = $article_class -> getClassList(array(ac_type => 'wap'));
        output_data(array('article_class' => $article_class_data));
    }

}
