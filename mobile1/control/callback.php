<?php

/**
 * 回调
 *
 *
 *
 * * */
defined('InShopNC') or exit('Access Invalid!');

class callbackControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        //订阅成功后，收到首次推送信息是在5~10分钟之间，在能被5分钟整除的时间点上，0分..5分..10分..15分....

        $param = htmlspecialchars_decode($_POST['param']);
        $param = json_decode($param, true);

        $model_order = Model('order');
        $model_refund_return = Model('refund_return');
        $condition = array();
        $condition['shipping_code'] = $param['lastResult']['nu'];
//        $order_info = $model_order ->getOrderInfo(array('shipping_code'=>$param['lastResult']['nu']));
        $data = array();
        $data['express_inf'] = serialize($param['lastResult']);
//       $fp = fopen("log.txt","a");
//	flock($fp, LOCK_EX) ;
//	fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n"."更改成功".var_export($condition['shipping_code'], true)."\n");
//	flock($fp, LOCK_UN);
//	fclose($fp);
        $order_info = $model_order->getOrderInfo(array(shipping_code => $param['lastResult']['nu']));
        if (empty($order_info)) {
            $condition['invoice_no'] = $param['lastResult']['nu'];
            $express = $model_refund_return->editRefundReturn($condition, $data);
            //买家发货
        } else {
            $express = $model_order->editOrder($data, $condition);
            //卖家发货
        }
        if ($express) {
            try {
                //$param包含了文档指定的信息，...这里保存您的快递信息,$param的格式与订阅时指定的格式一致

                echo '{"result":true,	"returnCode":"200","message":"成功"}';
                //要返回成功（格式与订阅时指定的格式一致），不返回成功就代表失败，没有这个30分钟以后会重推
            } catch (Exception $e) {
                echo '{"result":false,	"returnCode":"500","message":"失败"}';
                //保存失败，返回失败信息，30分钟以后会重推
            }
        }
    }

//    public function testOp(){
//        $model_order = Model('order');
//        $data = array();
//        $data['express_inf'] =htmlspecialchars_decode('{&quot;status&quot;:&quot;shutdown&quot;,&quot;billstatus&quot;:&quot;check&quot;,&quot;message&quot;:&quot;&quot;,&quot;autoCheck&quot;:&quot;0&quot;,&quot;comOld&quot;:&quot;&quot;,&quot;comNew&quot;:&quot;&quot;,&quot;lastResult&quot;:{&quot;message&quot;:&quot;ok&quot;,&quot;nu&quot;:&quot;70348503000364&quot;,&quot;ischeck&quot;:&quot;1&quot;,&quot;condition&quot;:&quot;F00&quot;,&quot;com&quot;:&quot;huitongkuaidi&quot;,&quot;status&quot;:&quot;200&quot;,&quot;state&quot;:&quot;3&quot;,&quot;data&quot;:[{&quot;time&quot;:&quot;2016-03-16 13:29:21&quot;,&quot;ftime&quot;:&quot;2016-03-16 13:29:21&quot;,&quot;context&quot;:&quot;广州市|签收|广州市【BEX广州番禺区一部】，李国器 已签收&quot;},{&quot;time&quot;:&quot;2016-03-16 10:37:02&quot;,&quot;ftime&quot;:&quot;2016-03-16 10:37:02&quot;,&quot;context&quot;:&quot;广州市|派件|广州市【BEX广州番禺区一部】，【郭世华/18011757516】正在派件&quot;},{&quot;time&quot;:&quot;2016-03-16 09:59:19&quot;,&quot;ftime&quot;:&quot;2016-03-16 09:59:19&quot;,&quot;context&quot;:&quot;广州市|到件|到广州市【BEX广州番禺区一部】&quot;},{&quot;time&quot;:&quot;2016-03-15 18:06:49&quot;,&quot;ftime&quot;:&quot;2016-03-15 18:06:49&quot;,&quot;context&quot;:&quot;广州市|发件|广州市【广州番禺集散中心】，正发往【BEX广州番禺区一部】&quot;},{&quot;time&quot;:&quot;2016-03-15 16:09:44&quot;,&quot;ftime&quot;:&quot;2016-03-15 16:09:44&quot;,&quot;context&quot;:&quot;广州市|到件|到广州市【广州番禺集散中心】&quot;},{&quot;time&quot;:&quot;2016-03-15 12:42:48&quot;,&quot;ftime&quot;:&quot;2016-03-15 12:42:48&quot;,&quot;context&quot;:&quot;广州市|发件|广州市【广州夏良转运中心】，正发往【广州番禺集散中心】&quot;},{&quot;time&quot;:&quot;2016-03-15 09:03:24&quot;,&quot;ftime&quot;:&quot;2016-03-15 09:03:24&quot;,&quot;context&quot;:&quot;广州市|到件|到广州市【广州夏良转运中心】&quot;},{&quot;time&quot;:&quot;2016-03-15 05:29:29&quot;,&quot;ftime&quot;:&quot;2016-03-15 05:29:29&quot;,&quot;context&quot;:&quot;东莞市|发件|东莞市【虎门转运中心】，正发往【广州夏良转运中心】&quot;},{&quot;time&quot;:&quot;2016-03-15 05:27:32&quot;,&quot;ftime&quot;:&quot;2016-03-15 05:27:32&quot;,&quot;context&quot;:&quot;东莞市|到件|到东莞市【虎门转运中心】&quot;},{&quot;time&quot;:&quot;2016-03-14 03:54:32&quot;,&quot;ftime&quot;:&quot;2016-03-14 03:54:32&quot;,&quot;context&quot;:&quot;昆明市|发件|昆明市【昆明转运中心】，正发往【虎门转运中心】&quot;},{&quot;time&quot;:&quot;2016-03-13 20:31:16&quot;,&quot;ftime&quot;:&quot;2016-03-13 20:31:16&quot;,&quot;context&quot;:&quot;昆明市|到件|到昆明市【昆明转运中心】&quot;},{&quot;time&quot;:&quot;2016-03-13 19:19:58&quot;,&quot;ftime&quot;:&quot;2016-03-13 19:19:58&quot;,&quot;context&quot;:&quot;昆明市|发件|昆明市【昆明五华五部】，正发往【昆明转运中心】&quot;},{&quot;time&quot;:&quot;2016-03-13 19:18:40&quot;,&quot;ftime&quot;:&quot;2016-03-13 19:18:40&quot;,&quot;context&quot;:&quot;昆明市|收件|昆明市【昆明五华五部】，【2号终端18860795571】已揽收&quot;}]}}');
//        $condition = array();
//        $condition['shipping_code'] = json_decode($data['express_inf'],true);
//        $condition['shipping_code'] = $condition['shipping_code']['lastResult'];
////        $express = $model_order->editOrder($data, $condition);
//        print_r($condition['shipping_code']);
//    }
}
