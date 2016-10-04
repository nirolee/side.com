<?php

/**
 * 快递模型
 *
 *
 *
 *

 */
defined('InShopNC') or exit('Access Invalid!');

class expressModel extends Model {

    public function __construct() {
        parent::__construct('express');
    }

    /**
     * 查询快递列表
     *
     * @param string $id 指定快递编号
     * @return array
     */
    public function getExpressList() {
        return rkcache('express', true);
    }

    /**
     * 根据编号查询快递列表
     */
    public function getExpressListByID($id = null) {
        $express_list = rkcache('express', true);

        if (!empty($id)) {
            $id_array = explode(',', $id);
            foreach ($express_list as $key => $value) {
                if (!in_array($key, $id_array)) {
                    unset($express_list[$key]);
                }
            }
            return $express_list;
        } else {
            return array();
        }
    }

    /**
     * 查询详细信息
     */
    public function getExpressInfo($id) {
        $express_list = $this->getExpressList();
        return $express_list[$id];
    }

    /**
     * 根据快递公司ecode获得快递公司信息
     * @param $ecode string 快递公司编号
     * @return array 快递公司详情
     */
    public function getExpressInfoByECode($ecode) {
        $ecode = trim($ecode);
        if (!$ecode) {
            return array('state' => false, 'msg' => '参数错误');
        }
        $express_list = $this->getExpressList();
        $express_info = array();
        if ($express_list) {
            foreach ($express_list as $v) {
                if ($v['e_code'] == $ecode) {
                    $express_info = $v;
                }
            }
        }
        if (!$express_info) {
            return array('state' => false, 'msg' => '快递公司信息错误');
        } else {
            return array('state' => true, 'data' => array('express_info' => $express_info));
        }
    }

    /**
     * 查询物流信息
     * @param unknown $e_code
     * @param unknown $shipping_code
     * @return multitype:
     */
    function get_express($e_code, $shipping_code) {

        $url = 'http://www.kuaidi100.com/poll';
        $post_data = array();
        $post_data["param"]["company"] = $e_code;
        $post_data["param"]["number"] = $shipping_code;
        $post_data["param"]["key"] = "sXvWShCW9041";
        $post_data["param"]["parameters"]["callbackurl"] = "http://www.wantease.com/mobile1/index.php?act=callback&op=index";
        $post_data["param"] = json_encode($post_data["param"],JSON_UNESCAPED_SLASHES);
//        import('function.ftp');
//        $content = dfsockopen($url,0,$post_data);
//        $content = json_decode($content, true);

//        if ($content['status'] != 200 || !is_array($content['data'])) {
//            return array();
//        }
        $o=""; 
foreach ($post_data as $k=>$v)
{
    $o.= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
}

$post_data=substr($o,0,-1);

$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
$result = curl_exec($ch);		//返回提交结果，格式与指定的格式一致（result=true代表成功）

    }

}
