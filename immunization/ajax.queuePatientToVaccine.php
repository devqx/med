<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/4/14
 * Time: 12:54 PM
 */

$return = (object)null;
$pdo = null;

try {
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
} catch (PDOException $e) {
	errorLog($e);
	$return->status = "error";
	$return->message = "Database connectivity error";
	exit(json_encode($return));
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVaccineDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';


$patient_id = $_POST['patient'];
$patient = (new PatientDemographDAO())->getPatient($patient_id, false, $pdo, null);

$price = 0;
$description = array();
$charge = array();
if(is_blank($_POST['service_centre_id'])){
	exit('error:Service center is Required');
}
foreach ($_POST['vaccine'] as $vac_data) {
	//we want to charge the patient
	//$vac_data: id from the patient_vaccine table
	//mark these vaccines as billed
	$pv = (new PatientVaccineDAO())->getPatientVaccine($vac_data, true, $pdo);
	
	$pv->setBilled(true);
	$vv = (new PatientVaccineDAO())->updatePatientVaccineProps($pv, $pdo);
	
	
	$description = $pv->getVaccine()->getName();
	$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($pv->getVaccine()->getCode(), $patient_id, true, $pdo);
	
	
	$bill = new Bill();
	$bill->setPatient($patient);
	$bill->setDescription("Vaccine Charges: " . $description);
	$bill->setAmount($price);
	$bill->setBilledTo($patient->getScheme());
	$bill->setItem($pv->getVaccine());
	$bill->setSource((new BillSourceDAO())->findSourceById(6, $pdo));
	
	$bill->setTransactionType("credit");
	$bill->setDiscounted("no");
	$bill->setDiscountedBy(null);
	$bill->setCostCentre( (new ServiceCenterDAO())->get($_POST['service_centre_id'], $pdo)->getCostCentre());
	$clinic = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo)->getClinic();
	$bill->setClinic($clinic);
	$charge[] = (new BillDAO())->addBill($bill, 1, $pdo, null);
	
}

if (!is_blank($_POST['nursing_service_id'])) {
	$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
	
	$service = (new NursingServiceDAO())->get($_POST['nursing_service_id'], $pdo);
	$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($service->getCode(), $patient_id, true, $pdo);
	
	$bil = new Bill();
	$bil->setPatient($patient);
	$bil->setDescription($service->getName() . " [Used in Vaccine]");
	$bil->setItem($service);
	$bil->setSource((new BillSourceDAO())->findSourceById(16, $pdo));
	$bil->setTransactionType("credit");
	$bil->setAmount($price);
	$bil->setDiscounted(null);
	$bil->setDiscountedBy(null);
	$bil->setCostCentre( (new ServiceCenterDAO())->get($_POST['service_centre_id'], $pdo)->getCostCentre());
	$bil->setClinic($staff->getClinic());
	$bil->setBilledTo($patient->getScheme());
	$charge[] = (new BillDAO())->addBill($bil, 1, $pdo, (isset($_POST['aid']) && trim($_POST['aid']) !== "") ? ($_POST['aid']) : null);
}

$q = null;
try {
	$queue = new PatientQueue();
	$queue->setType("Vaccination");
	$queue->setPatient($patient);
	$q = (new PatientQueueDAO())->addPatientQueue($queue, $pdo);
} catch (PDOException $e) {
	error_log("Error adding to billing queue");
}

if ($q !== null && !in_array(null, $charge)) {
	$pdo->commit();
	// error_log("Things are supposed to be OK");
	$return->status = "success";
} else {
	$pdo->rollBack();
	$return->status = "error";
	$return->message = "Failed to complete transaction";
}

exit(json_encode($return));