<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BedDAO.php';

$full=isset($_REQUEST['full'])? TRUE:FALSE;

if(isset($_REQUEST['bedId'])){
    $bed = (new BedDAO())->getBed($_REQUEST['bedId'], $full);
}else if(isset($_REQUEST['ward_id'])){
    $ward = $_REQUEST['ward_id'];
    $beds = (new BedDAO())->getFreeBeds(TRUE, $ward);
}else{
    $beds=(new BedDAO())->getBeds($full);
}

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {  //for Ajax
    header('Content-Type: application/json');
    
    if(isset($_REQUEST['bedId'])){
        $data = json_encode($bed);
    }else{
        $data = json_encode($beds);        
    }
    
    if (!isset($_GET['suppress'])) {
        echo $data;exit;
    }
    
}
echo json_encode($beds);