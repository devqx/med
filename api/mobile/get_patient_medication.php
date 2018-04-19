<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 4/24/17
 * Time: 4:09 PM
 */

if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $patActiveMedication = null;
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['patient_id'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
        $patActiveMedication = (new PrescriptionDAO())->getPatientPrescriptions($_POST['patient_id']);

    }
    echo  json_encode($patActiveMedication, JSON_PARTIAL_OUTPUT_ON_ERROR);
}