<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 4/25/17
 * Time: 7:40 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $patProblem = null;
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['patient_id'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
        $patProblem = (new PatientDiagnosisDAO())->getPatientDiagnoses($_POST['patient_id'], null, null, '');
    }
    echo  json_encode($patProblem, JSON_PARTIAL_OUTPUT_ON_ERROR);
}