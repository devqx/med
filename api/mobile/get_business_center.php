<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 6/24/17
 * Time: 2:50 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $data = null;
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['service_center'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
            $data = (new ServiceCenterDAO())->all($_POST['service_center']);

    }
    echo  json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR);
}