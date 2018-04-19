<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';

$full=isset($_REQUEST['full'])? TRUE:FALSE;

if(isset($_REQUEST['wardId'])){
    $ward = (new WardDAO())->getWard($_REQUEST['wardId'], $full);
}else{
    $wards=(new WardDAO())->getWards($full);
}

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {  //for Ajax
    header('Content-Type: application/json');
    
    if(isset($_REQUEST['wardId'])){
        $data = json_encode($ward);
    }else{
        $data = json_encode($wards);        
    }
    
    if (!isset($_GET['suppress'])) {
        echo $data;exit;
    }
    
}
