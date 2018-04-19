<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';

if(isset($_REQUEST['appointableTypes'])){
    include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
        $appointableTypes=getTypeOptions('type', 'appointment_group');
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
        header('Content-Type: application/json');
        $data = json_encode($appointableTypes);
        if(!isset($_GET['suppress'])){
            echo $data;
        }
    }
//    echo json_encode($appointableTypes);
}
if(isset($_REQUEST['resourceTypes'])){
    include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
        $resourceTypes=getTypeOptions('type', 'resource');
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
        header('Content-Type: application/json');
        $data = json_encode($resourceTypes);
        if(!isset($_GET['suppress'])){
            echo $data;
        }
    }
//    echo json_encode($resourceTypes);
}
    ?>
