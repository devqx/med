<?php

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
$type = (isset($_REQUEST['type']) ? $_REQUEST['type'] : null);
if(isset($_GET['id'])) {
	$resources = (new ResourceDAO())->getResource($_GET['id']);
} else if ($type === null) {
	$resources = (new ResourceDAO())->getResources();
} else {
	$resources = (new ResourceDAO())->getResourcesByType(explode("|", $_REQUEST['type']));
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
	header('Content-Type: application/json');
	$data = json_encode($resources, JSON_PARTIAL_OUTPUT_ON_ERROR);
	if (!isset($_GET['suppress'])) {
		echo $data;
	}
}
//    echo json_encode($resources);