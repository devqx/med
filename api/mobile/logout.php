<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/22/17
 * Time: 11:38 PM
 * Staff logout api
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));
    $status = "";
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['staffId'])) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.staff.php';
        $staff = new StaffManager();
        $status = require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/MyDBConnector.php';
        $pdo = (new MyDBConnector())->getPDO();
        $status =  $pdo->prepare("DELETE FROM onlinestatus WHERE staffId = ".$_POST['staffId']." OR session_id = '".$_POST['staffId']."'", array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL))->execute();

    }
    echo $status;
}

