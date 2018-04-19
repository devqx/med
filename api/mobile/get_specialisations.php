<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/14/17
 * Time: 12:53 AM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $specs = null;
    header("Access-Control-Allow-Origin:*");
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
    if (isset($_POST['inpatient_id']) && !isset($_POST['temp_id'])) {
        $specs = (new StaffSpecializationDAO())->getSpecializations();

    }

    echo  json_encode($specs, JSON_PARTIAL_OUTPUT_ON_ERROR);
}