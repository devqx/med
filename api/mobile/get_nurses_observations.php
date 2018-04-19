<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/7/17
 * Time: 4:17 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $observe = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['patient_id']) && isset($_POST['inpatient_id'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InpatientObservationDAO.php';
        $observe = (new InpatientObservationDAO())->forIpInstance($_POST['inpatient_id'], $page=0, $pageSize=10);

    }
    echo json_encode($observe, JSON_PARTIAL_OUTPUT_ON_ERROR);
}