<?php 
//积分中心
defined('InShopNC') or exit('Access Invalid!');
class pointshopControl extends mobileHomeControl {
    public function __construct() {
         parent::__construct();
    }
    
    public function indexOp() {
         $recommend_voucher = Model('voucher')->getRecommendTemplate(6);
         output_data($recommend_voucher);
    }
    
    public function pointprodOp() {
        $model_pointprod = Model('pointprod');
        $pointprod_list = $model_pointprod->getOnlinePointProdList('', 'pgoods_id,pgoods_name,pgoods_points,pgoods_image,pgoods_endtime', '','',20);
        
        output_data($pointprod_list);
    }
}