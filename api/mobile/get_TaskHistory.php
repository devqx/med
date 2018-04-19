<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/15/17
 * Time: 1:17 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $tasks = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['patient_id']) && isset($_POST['inpatient_id'])){
        require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
        $tasks = (new ClinicalTaskDataDAO())->getPatientTaskData($_POST['patient_id'], ['Discharged', 'Ended', 'Cancelled'], true, $_POST['inpatient_id']);

    }

    echo json_encode($tasks, JSON_PARTIAL_OUTPUT_ON_ERROR);
}