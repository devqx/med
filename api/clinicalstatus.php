<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/28/14
 * Time: 9:40 AM
 */


require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
$config = new MainConfig();

if(isset($_GET['action']) && $_GET['action'] == 'list_tb'){
    echo json_encode($config->listTBStatuses());exit;
}
if(isset($_GET['action']) && $_GET['action'] == 'list_fnctl'){
    echo json_encode($config->listFunctionalStatuses());exit;
}
if(isset($_POST['action']) && $_POST['action'] == 'save'){
    $data = $_POST;

    require_once $_SERVER['DOCUMENT_ROOT']."/classes/class.clinicalstatus.php";
    $C_STATUS = new ClinicalStatus();
    exit($C_STATUS->savePatientClinicalStatus($data));
}