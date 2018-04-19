<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 7/16/17
 * Time: 3:55 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $pPres = null;
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['patient_id']) && isset($_POST['inpatient_id'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PrescriptionDAO.php";
        $temp = (new PrescriptionDAO())->getPatientPrescriptions($_POST['patient_id'], 0, 999999, TRUE);
        $pPres = $temp->data;
    }
    echo  json_encode($pPres, JSON_PARTIAL_OUTPUT_ON_ERROR);
}