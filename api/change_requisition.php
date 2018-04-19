<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/30/16
 * Time: 12:43 PM
 */
if(!isset($_SESSION)){@session_start();}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugRequisitionDAO.php';
if($_POST['action']=="Receive"){
	$_SESSION['service_centre_id'] = $_POST['service_centre_id'];
}
$req = (new DrugRequisitionDAO())->get($_POST['id'])->setStatus($_POST['action'].'d')->update();

if($req){
	exit(json_encode(true));
}
exit(json_encode(false));
