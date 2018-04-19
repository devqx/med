<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/28/17
 * Time: 4:48 PM
 */

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
$generics = [];
$generic = null;
$lastItemId = isset($_REQUEST['last_item_id']) ? $_REQUEST['last_item_id'] : null;
if (isset($_REQUEST['id'])) {
	$generic = (new ItemGenericDAO())->get($_REQUEST['id']);
} else if (isset($_REQUEST['search'])) {
	$generics = (new ItemGenericDAO())->find($_REQUEST['search']);
} else if (isset($_REQUEST['g_id'])) {
	$generics = (new ItemGenericDAO())->getByGroup($_REQUEST['g_id']);
}else if (isset($_REQUEST['c_id'])) {
	$generics = (new ItemGenericDAO())->getGenericByServiceCenter($_REQUEST['c_id']);
} else {
	$generics = (new ItemGenericDAO())->list_($lastItemId);
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	header('Content-Type: application/json;charset=UTF-8');
	if (isset($_REQUEST['id'])) {
		$data = json_encode($generic, JSON_PARTIAL_OUTPUT_ON_ERROR);
	} else {
		$data = json_encode($generics, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}
	if (!isset($_GET['suppress'])) {
		echo $data;
	}
}

