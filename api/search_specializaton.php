<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/19/18
 * Time: 5:30 PM
 */
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
$specializations = [];
$specialization = null;

if (isset($_REQUEST['q'])) {
	$specializations = (new StaffSpecializationDAO())->searchStaffSpecialization($_REQUEST['q']);
	
}else if (isset($_REQUEST['id'])) {
	$specialization = (new StaffSpecializationDAO())->searchStaffSpecializationById($_REQUEST['id']);
}else{
	$specializations = (new StaffSpecializationDAO())->getSpecializations();
	
}

if (isset($_REQUEST['q'])) {
	$data = json_encode($specializations, JSON_PARTIAL_OUTPUT_ON_ERROR);
	exit($data);
}

if(isset($_REQUEST['id'])){
	$data = json_encode($specialization, JSON_PARTIAL_OUTPUT_ON_ERROR);
	exit($data);
}
