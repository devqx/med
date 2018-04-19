<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/6/17
 * Time: 11:25 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

$instance = (new InPatientDAO())->getInPatient($_REQUEST['aid'], FALSE, $pdo);
$patient = (new PatientDemographDAO())->getPatient($instance->getPatient()->getId(), FALSE, $pdo, null);
$rechargeBills = (new BillDAO())->getUnCompletedDischargeBills($instance->getId(), TRUE, $pdo);
$amount = 0;
foreach ($rechargeBills as $b){
	$amount += $b->getSource()->getId()!=8 ? $b->getAmount() : 0;
}


$_POST['amount_all'] = $amount * ($patient->getScheme()->getClinicalServicesRate() / 100);
$patInsurance = (new InsuranceDAO())->getPatientInsuranceSlim($instance->getPatient()->getId(), $pdo);
//if it has expired, charge the patient to him/herself
$newBill = (new Bill())
	->setPatient($instance->getPatient())
	->setInPatient($instance->getId())
	->setAmount($_POST['amount_all'])
	->setSource((new BillSourceDAO())->findSourceById(5, $pdo))
	->setCostCentre($instance->getWard() ? $instance->getWard()->getCostCentre() : null)
	->setTransactionType("credit")
	->setDescription("Clinical Services Charge")
	->setDiscounted('no')
	->setDiscountedBy(null)
	->setReviewed(TRUE)
	->setBilledTo(!(bool)$patInsurance->active && $patInsurance->pay_type == "insurance" ? new InsuranceScheme(1) : $patient->getScheme())
	//if patient insurance has expired, do not charge to the scheme; to the patient instead
	->setItem(null);
$charge = (new BillDAO())->addBill($newBill, 1, $pdo, $instance->getId());
$discharge = (new InPatientDAO())->discharge($_POST['aid'],  $_POST['appointment_id'], $_POST['nextMedication'], $_POST['reason'], $pdo);
$pdo->commit();


