<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/7/17
 * Time: 3:03 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $data = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['patient_id']) && isset($_POST['inpatient_id']) && isset($_POST['v_name'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
//        error_log("get anme ". $_POST['v_name']);
        $data = (new VitalSignDAO())->getPatientLastVitalSign($_POST['patient_id'], $_POST['v_name'], $_POST['inpatient_id']);
    }

    echo  json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR);
}