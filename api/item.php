<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/14/17
 * Time: 4:33 PM
 */
@session_start();
header("Access-Control-Allow-Origin: *");
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'cancel') {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDataDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	$status = false;
	$pr_data = (new PatientItemRequestDataDAO())->getRequestDatumById($_REQUEST['id'], true);
	
	if($pr_data->getQuantity() )
	$pr_data->setCancelledNote($_REQUEST['reason']);
	if (!isset($_SESSION['staffID']) && !isset($_GET['staffID'])) {
		$pr_data->setCancelledBy((new StaffDirectoryDAO())->getStaff($_REQUEST['staffID']));

	} else if (!isset($_SESSION['staffID']) && isset($_REQUEST['staffID'])) {
		$pr_data->setCancelledBy((new StaffDirectoryDAO())->getStaff($_REQUEST['staffID']));
		$status = (new PatientItemRequestDataDAO())->cancelRequestData($pr_data);
	} else if (isset($_SESSION['staffID'])) {
		$pr_data->setCancelledBy((new StaffDirectoryDAO())->getStaff($_SESSION['staffID']));
		$status = (new PatientItemRequestDataDAO())->cancelRequestData($pr_data);
	}
	
	ob_clean();
	exit(json_encode($status));
}else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'complete'){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDataDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	$status = false;
	$pr_data = (new PatientItemRequestDataDAO())->getRequestDatumByCode($_REQUEST['code'], true);

	if (!isset($_SESSION['staffID']) && !isset($_GET['staffID'])) {
		$pr_data->setCompletedBy((new StaffDirectoryDAO())->getStaff($_REQUEST['staffID']));
	} else if (!isset($_SESSION['staffID']) && isset($_REQUEST['staffID'])) {
		$pr_data->setCompletedBy((new StaffDirectoryDAO())->getStaff($_REQUEST['staffID']));
		$status = (new PatientItemRequestDataDAO())->completeRequestData($pr_data);
	} else if (isset($_SESSION['staffID'])) {
		$pr_data->setCompletedBy((new StaffDirectoryDAO())->getStaff($_SESSION['staffID']));
		$status = (new PatientItemRequestDataDAO())->completeRequestData($pr_data);
	}
	ob_clean();
	exit(json_encode($status));
}

