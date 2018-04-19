<?php
if(!isset($_SESSION)){@session_start();}
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.staff.php';
$staff  = new StaffManager();

$staff->doRoomCleanUp($_SESSION['staffID']);

if(isset($_SESSION['staffID'])){
	require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->prepare("DELETE FROM onlinestatus WHERE staffId = ".$_SESSION['staffID']." OR session_id = '".session_id()."'", array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL))->execute();
	unset($_SESSION['staffID']);
}

session_destroy();
header('Location: home.php');