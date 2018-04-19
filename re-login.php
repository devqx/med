<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/25/16
 * Time: 12:33 PM
 */
session_start();
$session_id_to_destroy = $_POST['id'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
$sql = "DELETE FROM onlinestatus WHERE session_id = '".$session_id_to_destroy."'";
$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$chk = $stmt->execute();

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.staff.php';
$staff  = new StaffManager();

$staff->doRoomCleanUp($_POST['u']);

// hijack, then destroy session specified.
session_id($session_id_to_destroy);
session_destroy();
session_commit();
session_start();
session_regenerate_id();
session_commit();
