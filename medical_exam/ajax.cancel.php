<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 6:06 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientMedicalReportDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MessageDispatchDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
@session_start();
if (!isset($_SESSION['staffID'])) {
	exit('error');
}
$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
$r = (new PatientMedicalReportDAO())->get($_POST['id'], $pdo);
$patient = $r->getPatient();
$report = $r->setCancelled(true)->setCancelledBy((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false, $pdo))->setCancelledDate(date("Y-m-d H:i:s"))->update($pdo);

@ob_end_clean();
if ($report !== null) {
	//do a reversal of the amount(s) of the report
	foreach ($report->getBill() as $bil_) {
		//$bill = new Bill();
		$bill = (new BillDAO())->getBill($bil_->getId(), true, $pdo);
		
		$bil = (new Bill())->setParent($bil_)->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setActiveBill('not_active')->setPatient($patient)->setDescription("" . $bil_->getDescription())->setItem($report->getExam())->setSource((new BillSourceDAO())->findSourceById(12, $pdo))->setTransactionType("reversal")->setPriceType($bil_->getPriceType())->setTransactionDate(date("Y-m-d H:i:s"))->setDueDate($bil_->getTransactionDate())->setAmount(0 - $bil_->getAmount())->setDiscounted(null)->setDiscountedBy(null)->setClinic($bil_->getClinic())->setBilledTo($bil_->getBilledTo())->setCostCentre($bil_->getCostCentre())->setItemCode($bil_->getItemCode());
		$c = $bill->setCancelledOn(date(MainConfig::$mysqlDateTimeFormat))->setCancelledBy((new StaffDirectory($_SESSION['staffID'])))->setActiveBill('not_active')->update($pdo);
		
		// You didn't do Medical Report within InPatient context
		if ($bil->add(1, null, $pdo) == null && $c == null) {
			$pdo->rollBack();
			@ob_end_clean();
			exit('error:Failed to Cancel charges');
		}
	}
	
	//todo: get the constituent requests and cancel them too
	$pdo->commit();
	@ob_end_clean();
	exit("ok");
}
//ob_end_clean();
exit("error");
