<?php

require_once "jssdk.php";
    $jssdk = new JSSDK("wx80efdffe7df441b2", "a8d6a3d92f41fd9a8b5843f6c065d5fd");
    $signPackage = $jssdk->GetSignPackage();
    echo json_encode($signPackage);
//        $this->output_data($signPackage);



