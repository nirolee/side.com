<?php
/**
 *
 * QQ,新浪微博登陆
 *

 * @since      File available since Release v1.1
 */
defined('InShopNC') or exit('Access Invalid!');
class connectControl extends mobileHomeControl{
	
	
	/**
     * 新浪微博登陆
     */
    public function get_sina_oauth2Op() {
		$code_url = BASE_SITE_URL.'/api.php?act=tosina';
		@header("location:$code_url");
	}

	/**
     * QQ登陆
     */
    public function get_qq_oauth2Op() {
		$code_url = BASE_SITE_URL.'/api.php?act=toqq';
		@header("location:$code_url");
	}
        
    
}
