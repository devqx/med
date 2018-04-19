<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/7/14
 * Time: 5:31 PM
 */
sleep(0.5);
$action = $_GET['action'];
header("Access-Control-Allow-Origin: *");
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';

if(isset($action) && $action=="incomplete"){
    $press=(new PrescriptionDAO())->getOpenPrescriptions(TRUE);
    echo json_encode($press);
}else if(isset($action) && $action == "filled"){
    $press = (new PrescriptionDAO())->getFilledPrescriptions(TRUE);
    echo json_encode($press);
}else if(isset($action) && $action == "search" && isset($_GET['q'])){
    $press = (new PrescriptionDAO())->findPrescriptions($_GET['q'], TRUE);
    echo json_encode($press);
}else if(isset($action) && $action == "single" && isset($_GET['id'])){
    $press = (new PrescriptionDAO())->getPrescription($_GET['id'], TRUE);
    echo json_encode($press);
}else {
    echo json_encode([]);
}

exit;