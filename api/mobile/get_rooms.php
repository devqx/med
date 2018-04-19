<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/15/17
 * Time: 12:01 AM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $rooms = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['staffId'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RoomDAO.php';
        $rooms = (new RoomDAO())->getRooms();

    }
    echo json_encode($rooms, JSON_PARTIAL_OUTPUT_ON_ERROR);
}