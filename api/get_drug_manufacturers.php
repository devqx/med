<?php

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugManufacturerDAO.php';
if(isset($_REQUEST['gid'])){
    $drugManufacturer = (new DrugManufacturerDAO())->getManufacturer($_REQUEST['gid']);
}else{
    $drugManufacturers = (new DrugManufacturerDAO())->getManufacturers();    
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    if(isset($_REQUEST['gid'])){
        $data = json_encode($drugManufacturer);
    }else{
        $data = json_encode($drugManufacturers);
    }
    if (!isset($_GET['suppress'])) {
        echo $data;
    }
}
//    echo json_encode($drugManufacturers);
?>
