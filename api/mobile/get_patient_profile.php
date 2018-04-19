<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 4/24/17
 * Time: 12:13 PM
 */

if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $patient = null;
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['patient_id'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
        $patient = (new PatientDemographDAO())->getPatient($_POST['patient_id']);

    }
//    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
//        header('Content-Type: application/json;charset=UTF-8');



    echo  json_encode($patient, JSON_PARTIAL_OUTPUT_ON_ERROR);
//}
}