<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/15/17
 * Time: 10:44 AM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $patient = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['staff_id'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
        $patient = (new InPatientDAO())->getALLInPatientsMobile($_POST['block'], $_POST['ward'], $_POST['room']);
    }
    echo json_encode($patient, JSON_PARTIAL_OUTPUT_ON_ERROR);
}