<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/11/17
 * Time: 1:18 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $services = null;
    header("Access-Control-Allow-Origin:*");
//    if(isset($_POST['patient_id']) && isset($_POST['inpatient_id'])){
        require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
        $services = (new NursingServiceDAO())->all();
//    }

    echo json_encode($services, JSON_PARTIAL_OUTPUT_ON_ERROR);
}