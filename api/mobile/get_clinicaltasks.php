<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 7/16/17
 * Time: 6:57 AM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $options = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['patient_id']) && isset($_POST['inpatient_id'])){
        require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
        $vitalTypes = json_decode(json_encode((new VitalDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));
        $options = array_col($vitalTypes, 'name');
        $options[] = "Others";
    }
    echo json_encode($options, JSON_PARTIAL_OUTPUT_ON_ERROR);
}


