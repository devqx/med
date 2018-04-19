<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 7/6/17
 * Time: 9:10 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $temp = null;
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['staffId']) && isset($_POST['patient_id']) && isset($_POST['inpatient_id'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabGroupDAO.php';
        $temp = (new LabGroupDAO())->getPatientLabGroupsMobile($_POST['patient_id'], $page=0, $pageSize=10, false);
    }

    echo  json_encode($temp->data, JSON_PARTIAL_OUTPUT_ON_ERROR);
}