<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 6/23/17
 * Time: 11:50 AM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $routeData = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['type'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FluidRouteDAO.php';
        $routeData = (new FluidRouteDAO())->getByType($_POST['type'], null);
    }
    echo json_encode($routeData, JSON_PARTIAL_OUTPUT_ON_ERROR);
}