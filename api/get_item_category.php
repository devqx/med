<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/3/17
 * Time: 3:21 PM
 */

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemCategoryDAO.php';
$itemCategories = [];
$itemCategory = null;
$lastItemId = isset($_REQUEST['last_item_id'])?$_REQUEST['last_item_id']:NULL;
if (isset($_REQUEST['gid'])) {
	$itemCategory = (new ItemCategoryDAO())->get($_REQUEST['gid']);
}else if(isset($_REQUEST['search'])) {
	$itemCategories = (new ItemCategoryDAO())->find($_REQUEST['search']);

}else {
	$itemCategories = (new ItemCategoryDAO())->getCategories($lastItemId);
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
	header('Content-Type: application/json');
	if (isset($_REQUEST['gid'])) {
		$data = json_encode($itemCategory, JSON_PARTIAL_OUTPUT_ON_ERROR);
	} else {
		$data = json_encode($itemCategories, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}
	if (!isset($_GET['suppress'])) {
		echo $data;
	}
}