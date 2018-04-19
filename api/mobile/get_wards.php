<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/14/17
 * Time: 11:56 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $wards = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['staffId'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';
        $wards = (new WardDAO())->getWards();

    }
    echo json_encode($wards, JSON_PARTIAL_OUTPUT_ON_ERROR);
}