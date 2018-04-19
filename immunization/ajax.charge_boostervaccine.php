<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/25/16
 * Time: 1:17 PM
 */

$return = array();

require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/PatientVaccineBooster.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/VaccineBoosterHistory.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/VaccineBoosterDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/PatientVaccineBoosterDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/VaccineBoosterHistoryDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/BillDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/Bill.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/PatientQueue.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/PatientDemograph.php';

$patient_vb_id =  isset($_POST['bv_id']) ? $_POST['bv_id'] : '';
if($patient_vb_id == ''){
	$return['status'] = "error";
	$return['message'] = "No vaccine selected";
} else {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$vaccineBoosterItem = (new PatientVaccineBoosterDAO())->getPatientVaccineBooster($patient_vb_id, TRUE, $pdo);
	$vaccineBoosterItem->setCharged(TRUE)->update($pdo);
	$vaccine_code = $vaccineBoosterItem->getVaccineBooster()->getVaccine()->getCode();
	$vaccine_name = $vaccineBoosterItem->getVaccineBooster()->getVaccine()->getName();
	$patient_id = (new PatientVaccineBoosterDAO())->getPatientVaccineBooster($patient_vb_id, FALSE, $pdo)->getPatient()->getId();
	$patientFull = (new PatientDemographDAO())->getPatient($patient_id, FALSE, $pdo);

	$amount = (new InsuranceItemsCostDAO())->getItemPriceByCode($vaccine_code, $patient_id, TRUE, $pdo);

	$bil = new Bill();
	$bil->setPatient($patientFull);
	$bil->setDescription("Vaccine: ".$vaccine_name);

	$bil->setItem($vaccineBoosterItem->getVaccineBooster()->getVaccine());
	$bil->setSource( (new BillSourceDAO())->findSourceById(6, $pdo) );
	$bil->setTransactionType("credit");
	$bil->setAmount($amount);
	$bil->setInPatient( $patientFull );
	$bil->setDiscounted(NULL);
	$bil->setDiscountedBy(NULL);
	$bil->setClinic(new Clinic(1));
	$bil->setBilledTo($patientFull->getScheme());
	$bil->setReferral(NULL);
	$bil->setCostCentre( NULL );
	
	$pq = (new PatientQueue())->setPatient($patientFull)->setAmount($amount)->setType("Vaccination")->add($pdo);

	if($pq == null) {
		$pdo->rollBack();
		$return['status'] = "error";
		$return['message'] = "We couldn't post to the vaccination queue, sorry about that";
		exit(json_encode($return));
	}

	$charged = (new BillDAO())->addBill($bil, 1, $pdo);

	if($charged){
		$pdo->commit();
		$return['status'] = "success";
		$return['message'] = "$vaccine_name Vaccine successfully charged";
	}	else {
		$pdo->rollBack();
		$return['status'] = "error";
		$return['message'] = "An error occurred, please try again";
	}
}
exit(json_encode($return));