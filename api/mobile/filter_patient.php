<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/14/17
 * Time: 11:21 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $patient = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['staff_id'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
        $patient = (new InPatientDAO())->getMyInPatientsMobile($_POST['staff_id'], $_POST['block'], $_POST['ward'], $_POST['room']);
    }elseif (isset($_POST['block']) || isset($_POST['ward']) || isset($_POST['room']) && !isset($_POST['staff_id'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
        error_log(':::::::::::');
        $patient = (new InPatientDAO())->getALLInPatientsMobile($_POST['block'], $_POST['ward'], $_POST['room']);
    }
    echo json_encode($patient, JSON_PARTIAL_OUTPUT_ON_ERROR);
}