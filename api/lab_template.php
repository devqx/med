<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDAO.php';

$full = isset($_REQUEST['full']) ? true : false;
$search = $_REQUEST['q'];
$labTemps = (new LabTemplateDAO())->findLabTemplates($search);
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
	header('Content-Type: application/json');
	$data = json_encode($labTemps, JSON_PARTIAL_OUTPUT_ON_ERROR);
	if (!isset($_GET['suppress'])) {
		exit($data);
	}
}
$data=(json_encode($labTemps, JSON_PARTIAL_OUTPUT_ON_ERROR));//, JSON_PARTIAL_OUTPUT_ON_ERROR
//error_log(var_export(json_last_error(), true).'.....');
exit($data);

