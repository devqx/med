<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/4/17
 * Time: 11:20 AM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $patients = [];
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['staffId'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
        $patients = (new InPatientDAO())->getALLInPatientsMobile();
    }

    echo  json_encode($patients);
}