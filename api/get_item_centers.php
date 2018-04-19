<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/23/17
 * Time: 2:56 PM
 */

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$centers = [];
$center = null;
$lastItemId = isset($_REQUEST['last_item_id'])?$_REQUEST['last_item_id']:NULL;
if (isset($_REQUEST['gid'])) {
	$center = (new ServiceCenterDAO())->get($_REQUEST['gid']);
}else if(isset($_REQUEST['search'])) {
	$centers = (new ServiceCenterDAO())->find($_REQUEST['search'], 'item');
}else {
	$centers = (new ServiceCenterDAO())->all('item', $lastItemId);
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
	header('Content-Type: application/json');
	if (isset($_REQUEST['gid'])) {
		$data = json_encode($center, JSON_PARTIAL_OUTPUT_ON_ERROR);
	} else if(isset($_REQUEST['search'])){
		$data = json_encode($centers, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}else{
		$data = json_encode($centers, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}
	if (!isset($_GET['suppress'])) {
		echo $data;
	}
}