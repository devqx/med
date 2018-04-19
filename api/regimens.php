<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/28/14
 * Time: 3:59 PM
 */
@session_start();
header("Access-Control-Allow-Origin: *");
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
if (isset($_GET['action']) && $_GET['action'] == 'listdrugs') {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
	$drugs = (new DrugDAO())->getDrugs();
	exit(json_encode($drugs));
} else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'cancel') {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
	$status = false;
	$pr_data = (new PrescriptionDataDAO())->getPrescriptionDatum($_REQUEST['id'], true);
	$pr_data->setCancelNote($_REQUEST['reason']);
	if (!isset($_SESSION['staffID']) && !isset($_GET['staffID'])) {
		
	} else if (!isset($_SESSION['staffID']) && isset($_REQUEST['staffID'])) {
		$pr_data->setCancelledBy((new StaffDirectoryDAO())->getStaff($_REQUEST['staffID']));
		$status = (new PrescriptionDataDAO())->cancelPrescription($pr_data);
		$bill = (new BillDAO())->getBill($_POST['id'], true);
		$c = $bill->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setActiveBill('not_active')->update($pdo);
		
	} else if (isset($_SESSION['staffID'])) {
		$pr_data->setCancelledBy((new StaffDirectoryDAO())->getStaff($_SESSION['staffID']));
		$status = (new PrescriptionDataDAO())->cancelPrescription($pr_data);
	}
	$pdo->commit();
	ob_clean();
	exit(json_encode($status));
} else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'fill') {
	//make sure a post data exists, then forward it to the "processing page"
	$_POST['drug_id'] = array();
	foreach ($_REQUEST['drug_'] as $rawD) {
		$_POST['drug_id'][] = json_decode($rawD)->id;
	}
	$_SESSION['staffID'] = $_REQUEST['staffID'];
	//discard the json object that was submitted
	unset($_POST['drug_']);
	include_once $_SERVER['DOCUMENT_ROOT'] . '/pharmaceuticals/boxy_fillBatch.php';
	
} else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'complete') {
	//make sure a post data exists, then forward it to the "processing page"
	$_SESSION['staffID'] = $_REQUEST['staffID'];
	include_once $_SERVER['DOCUMENT_ROOT'] . '/pharmaceuticals/boxy_fillBatch.php';
	
} else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'new') {
	//make sure a post data exists, then forward it to the "processing page"
	$_SESSION['staffID'] = $_REQUEST['staffID'];
	$_REQUEST['prescription'] = json_encode($_REQUEST['prescription']);
	include_once $_SERVER['DOCUMENT_ROOT'] . '/boxy.addRegimen.php';
} else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'refill') {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
	
	$pres_data = (new PrescriptionDataDAO())->getPrescriptionDatum($_REQUEST['id']);
	if (!(bool)$pres_data->getRefillable()) {
		exit('error:Prescription is not refillable');
	}
	if ((bool)$pres_data->getRefillable() && date('Y-m-d', strtotime($pres_data->getRefillDate())) <= date('Y-m-d') && $pres_data->getStatus() == 'filled' && !is_null($pres_data->getRefillDate())) {
		$code = $pres_data->getCode();
		$oldPres = (new PrescriptionDAO())->getRefillPrescriptionByCode($code);
		
		$originalRefillNumber = $pres_data->getRefillNumber();
		$originalRefillDate = $pres_data->getRefillDate();
		
		$oldPres->setRequestedBy(new StaffDirectory($_SESSION['staffID']));
		$pres_data->setRefillNumber($pres_data->getRefillNumber() - 1)->setStatus('open')->setRefillDate(null);
		$oldPres->setData([$pres_data]);
		
		if ((new PrescriptionDAO())->addPrescription($oldPres, $pdo)) {
			$pres_data->setRefillable(false)->setRefillNumber($originalRefillNumber)->setRefillDate($originalRefillDate)->update($pdo);
			//update this prescription data to have been refilled
		} else {
			ob_clean();
			$pdo->rollBack();
			exit('error:Refill action failed');
		}
		$pq = new PatientQueue();
		$pq->setType('Pharmacy');
		$pq->setPatient($oldPres->getPatient());
		(new PatientQueueDAO())->addPatientQueue($pq, $pdo);
		$pdo->commit();
		ob_clean();
		exit('success:true');
	} else {
		exit('error:Refill date has not reached');
	}
	///////////////////////////end of refill
} else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'transfer') {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
	if (empty($_POST['pharmacy_id'])) {
		exit("error:Please select the pharmacy to transfer to");
	}
	$presc = new Prescription();
	$presc->setId($_POST['cid']);
	$presc->setServiceCentre(new ServiceCenter($_POST['pharmacy_id']));
	$pps = (new PrescriptionDAO())->updateServiceCenter($presc);
	if ($pps === null) {
		ob_clean();
		exit("error:Sorry, prescription cannot be transferred");
	} else {
		ob_clean();
		exit("ok:true");
	}
}