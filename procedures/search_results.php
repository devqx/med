<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/10/14
 * Time: 12:05 PM
 */
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';

	$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
	$pageSize = 10;

	$data = (new PatientProcedureDAO())->findProcedures($_POST['q'], $page, $pageSize);
	//(new PatientProcedureDAO())->all($start, $stop, $serviceCentreId);
	$totalSearch = $data->total;
	$pro = $data->data;
	$pager = 'list5';
	include_once 'templater.php';
	exit;
}