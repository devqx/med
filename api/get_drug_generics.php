<?php

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
$drugGeneric = (object)null;
$drugGenerics = [];
$lastItemId = isset($_REQUEST['last_item_id']) ? $_REQUEST['last_item_id'] : null;
$activeGenericsOnly = (isset($activeGenericsOnly) ? $activeGenericsOnly : FALSE);
if (isset($_REQUEST['gid'])) {
	$drugGeneric = (new DrugGenericDAO())->getGeneric($_REQUEST['gid']);
} else if (isset($_REQUEST['search'])) {
	$drugGenerics = (new DrugGenericDAO())->find($_REQUEST['search']);
} else {
	$drugGenerics = (new DrugGenericDAO())->getSlim($lastItemId, $activeGenericsOnly);
}
@ob_end_clean();
//header("Access-Control-Allow-Origin: *");
//header('Content-Type: application/json');
if (isset($_REQUEST['gid'])) {
	$data = json_encode($drugGeneric, JSON_PARTIAL_OUTPUT_ON_ERROR);
} else {
	$data = json_encode($drugGenerics, JSON_PARTIAL_OUTPUT_ON_ERROR);
}
if (!isset($_GET['suppress'])) {
	echo $data;
}
 
