<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
//error_log("*********************************************************".$_REQUEST['q']);
if (isset($_REQUEST['q'])) {
	$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : null;
	$asArray = isset($_REQUEST['asArray']) ? TRUE : FALSE;
	$staffs = (new StaffDirectoryDAO())->searchStaffNames($_REQUEST['q'], $limit, $asArray);
} else {
	$staffs = [];
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
	header('Content-Type: application/json');
	$data = json_encode($staffs);
	if (!isset($_GET['suppress'])) {
		echo $data;
	}
}
//echo json_encode($staffs)    ;
