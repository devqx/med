<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/7/14
 * Time: 4:51 PM
 */

if(isset($_POST['action']) && $_POST['action']=='save'){
//    exit('error'.print_r($_POST));
    exit("ok");
}

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
$config = new MainConfig();
echo json_encode($config->listlmpPmtctLinkOptions());exit;