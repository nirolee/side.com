<?php

/**
 * 微信接口
 * add by lizh 2016/8/12 15:01
 */
defined('InShopNC') or exit('Access Invalid!');

class wx_interfaceControl extends mobileHomeControl {
	
	public $addId = "";
	public $appsecret = ""; 
	
    public function __construct() {
		
        parent::__construct();
        $this -> addId = "wx80efdffe7df441b2";
        $this -> appsecret = "a8d6a3d92f41fd9a8b5843f6c065d5fd";
 	
    }
	
	//获取微信分享接口
    public function get_wx_share_interfaceOp() {
	
        $url = $_GET['url'];
        $jssdk = new JSSDK($this -> addId, $this -> appsecret);
        $signPackage = $jssdk->GetSignPackage($url);
        output_data(array(signPackage => $signPackage));
		
    }
    
}
