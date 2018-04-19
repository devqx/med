<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/12/18
 * Time: 11:41 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedureRegimen.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugBatch.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureRegimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$bil = null;
$c = null;
$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

$bill = (new BillDAO())->getBill($_POST['id'], true, $pdo);
 // check if bill is already cancelled
$patient = (new PatientDemographDAO())->getPatient($bill->getPatient()->getId(), false, $pdo);
$parentBill = (new BillDAO())->checkBill($_POST['id'], true, $pdo);
if ($_POST['location_'] == 'procedure') {
	if($parentBill == null) {
		
		$regimen = new PatientProcedureRegimen();
		$regimen_obj = (new PatientProcedureRegimenDAO())->getProcedureRegimen($_POST['ppid'], $pdo);
		
		$regimen->setId($regimen_obj->getId());
		$regimen->setBillLine($_POST['id']);
		$regimen->setStatus('cancelled');
		
		(new PatientProcedureRegimenDAO())->updateStatus($regimen, $pdo);
		$bil = (new Bill())->setParent($bill)->setPatient($bill->getPatient())->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setDescription("Reversal: " . $bill->getDescription())->setItem(getItem($bill->getItemCode(), $pdo))->setSource($bill->getSource())->setSubSource($bill->getSubSource())->setTransactionType("reversal")->setTransactionDate(date("Y-m-d H:i:s"))->setDueDate($bill->getTransactionDate())->setAmount(0 - $bill->getAmount())->setDiscounted(null)->setDiscountedBy(null)->setClinic(new Clinic(1))->setBilledTo($patient->getScheme())->setActiveBill('not_active')->add($bill->getQuantity(), $bill->getInPatient() ? $bill->getInPatient()->getId() : null, $pdo);
		$c = $bill->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setActiveBill('not_active')->update($pdo);
		
		// Adjust stock inventory
		$drug_batchDao = new DrugBatchDAO();
		$drug_batch = new DrugBatch();
		$batch = (new DrugBatchDAO())->getBatch($regimen_obj->getBatch()->getId(), $pdo);
		$s_center = (new ServiceCenterDAO())->get($regimen_obj->getBatch()->getServiceCentre()->getId(), $pdo);
		
		$rem_quantity = $batch->getQuantity() + $regimen_obj->getQuantity();
		$drug_batch->setQuantity($rem_quantity);
		$drug_batch->setServiceCentre($s_center);
		$drug_batch->setId($regimen_obj->getBatch()->getId());
		$batch_updte = $drug_batchDao->stockAdjust($drug_batch, $pdo);
		if ($batch_updte == null) {
			exit("error:Failed to update inventory");
		}
		
	}else{
		$pdo->rollBack();
		exit('error1');
	}
}
ob_end_clean();
if ($bil != null || $c != null) {
	$pdo->commit();
	exit('success');
}
$pdo->rollBack();
exit('error');