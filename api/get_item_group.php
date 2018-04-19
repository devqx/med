<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/20/17
 * Time: 12:23 PM
 */

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';
$items = [];
$item = NULL;
$lastItemId = isset($_REQUEST['last_item_id'])?$_REQUEST['last_item_id']:NULL;
if(isset($_REQUEST['iid'])){
	$item = (new ItemGroupDAO())->getItemGroup($_REQUEST['iid']);
}else if(isset($_REQUEST['c_id'])) {
	$items = (new ItemGroupDAO())->getGroupsByServiceCenter($_REQUEST['c_id']);
}else if( isset($_REQUEST['search']) ){
	$items = (new ItemGroupDAO())->find($_REQUEST['search']);
}else{
	$items = (new ItemGroupDAO())->getItemGroups($lastItemId);
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if(isset($_REQUEST['iid'])) {
		$data = json_encode($item, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}else if( isset($_REQUEST['search']) ){
		$data = json_encode($items, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}else{
		$data = json_encode($items, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}
	if (!isset($_GET['suppress'])) {
		echo $data;
	}
}

