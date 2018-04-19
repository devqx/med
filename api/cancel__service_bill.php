<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/12/18
 * Time: 11:41 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
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
if ($_POST['type'] == 'rewrite') {
	if($parentBill == null || $patient == null) {
		$bil = (new Bill())->setParent($bill)->setPatient($bill->getPatient())->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setDescription("Reversal: " . $bill->getDescription())->setItem(getItem($bill->getItemCode(), $pdo))->setSource($bill->getSource())->setSubSource($bill->getSubSource())->setTransactionType("reversal")->setTransactionDate(date("Y-m-d H:i:s"))->setDueDate($bill->getTransactionDate())->setAmount( - $bill->getAmount())->setDiscounted(null)->setDiscountedBy(null)->setClinic(new Clinic(1))->setBilledTo($patient->getScheme())->setActiveBill('not_active')->add($bill->getQuantity(), $bill->getInPatient() ? $bill->getInPatient()->getId() : null, $pdo);
		$c = $bill->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setActiveBill('not_active')->update($pdo);
		
	}else{
		$pdo->rollBack();
		exit('error1');
	}
}else {
	$bil = (new Bill())->setPatient($bill->getPatient())->setDescription("Reversal: " . $bill->getDescription())->setParent($bill)->setItem(getItem($bill->getItemCode(), $pdo))->setSource($bill->getSource())->setSubSource($bill->getSubSource())->setTransactionType("reversal")->setTransactionDate(date("Y-m-d H:i:s"))->setDueDate($bill->getTransactionDate())->setAmount(0 - $bill->getAmount())->setDiscounted(null)->setDiscountedBy(null)->setClinic(new Clinic(1))->setBilledTo($patient->getScheme())->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setActiveBill('not_active')->add($bill->getQuantity(), $bill->getInPatient() ? $bill->getInPatient()->getId() : null, $pdo);
	$c = $bill->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setActiveBill('not_active')->update($pdo);
}
ob_end_clean();
if ($bil != null || $c != null) {
	$pdo->commit();
	exit('success');
}
$pdo->rollBack();
exit('error');