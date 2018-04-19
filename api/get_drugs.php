<?php
@ob_end_clean();

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.pharmacy.php';
$drugs = null;
$drug = null;
$activeGenericsOnly = (isset($activeGenericsOnly) ? $activeGenericsOnly : true);
@ob_end_clean();

if (isset($_REQUEST['did'])) {
	$drug = (new DrugDAO())->getDrug($_REQUEST['did'], TRUE);
	exit(json_encode($drug, JSON_PARTIAL_OUTPUT_ON_ERROR));
} else if (isset($_REQUEST['gid'])) {
	$drugs = (new DrugDAO())->getDrugsByGeneric($_REQUEST['gid'], $activeGenericsOnly);
} else if (isset($_REQUEST['q'])) {
	$drugs = (new DrugDAO())->findDrugs($_REQUEST['q'], $activeGenericsOnly);
} else {
	$drugs = (new DrugDAO())->getDrugs($activeGenericsOnly);
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
	@ob_end_clean();
	//header('Content-Type: application/json');
	if (isset($_REQUEST['did'])) {
		$data = json_encode($drug, JSON_PARTIAL_OUTPUT_ON_ERROR);
	} else {
		$data = json_encode($drugs, JSON_PARTIAL_OUTPUT_ON_ERROR);
	}
	if (!isset($_GET['suppress'])) {
		@ob_end_clean();
		echo $data;
	}
}

