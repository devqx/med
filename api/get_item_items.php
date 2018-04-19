<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/23/17
 * Time: 5:31 PM
 */

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
$items = NULL;
$lastItemId = isset($_REQUEST['last_item_id'])?$_REQUEST['last_item_id']:NULL;
 if(isset($_REQUEST['search'])){
	$items = (new ItemDAO())->find($_REQUEST['search']);
}else{
	$items = (new ItemDAO())->getItems($lastItemId);
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
	header('Content-Type: application/json;charset=UTF-8');
if(isset($_REQUEST['search'])){
		$data = json_encode($items, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}else{
		$data = json_encode($items, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}
	if (!isset($_GET['suppress'])) {
		echo $data;
	}
}

