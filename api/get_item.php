<?php

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemBatchDAO.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';
$items = [];
$item = NULL;
if (isset($_REQUEST['iid'])) {
	$item = (new ItemDAO())->getItem($_REQUEST['iid']);
} else if (isset($_REQUEST['itc'])) {
	$items = (new ItemDAO())->getItemByCode($_REQUEST['itc']);
} else if (isset($_REQUEST['gid'])) {
	$items = (new ItemDAO())->getItemsByGeneric($_REQUEST['gid']);
}else if(isset($_REQUEST['c_id'])) {
	$items = (new ItemGroupDAO())->getGroupsByServiceCenter($_REQUEST['c_id']);
} else if(isset($_REQUEST['gr_id'])) {
	$items = (new ItemGroupDAO())->getGenericByGroup($_REQUEST['gr_id']);
} else if(isset($_REQUEST['sc_id'])) {
	$items = (new ItemDAO())->getItemByServiceCenter($_REQUEST['sc_id']);
} else if (isset($_REQUEST['i_id'])){
	$items = (new ItemBatchDAO())->getItemBatches($_REQUEST['i_id']);
}else {
	$items = (new ItemDAO())->getItems();
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
	header('Content-Type: application/json;charset=UTF-8');
	if (isset($_REQUEST['gid'])) {
		$data = json_encode($items, JSON_PARTIAL_OUTPUT_ON_ERROR);
	} else if (isset($_REQUEST['iid'])) {
		$data = json_encode($item, JSON_PARTIAL_OUTPUT_ON_ERROR);
	} else {
		$data = json_encode($items, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}
	if (!isset($_GET['suppress'])) {
		echo $data;
	}
}

