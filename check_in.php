<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/20/14
 * Time: 5:14 PM
 */

$appointment_id = $_POST['qid'];

$patient_id = $_POST['pid'];

$department_id = @$_POST['did'];

$clinic_flag = "General";

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Department.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
if (!isset($_SESSION)) {
	@session_start();
}
if(isset($_SESSION['staffID'])){
	$staffId = $_SESSION['staffID'];
}else if(isset($_POST['staff_id'])){
	$staffId = $_POST['staff_id'];
} else {
	exit("error:Sorry, An authentication error occurred");
}
$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

//make this appointment active
$appoint = (new AppointmentDAO())->setStatus($appointment_id, 'Active', $staffId, $pdo);
$apt = (new AppointmentDAO())->getAppointment($appointment_id, TRUE, $pdo);

$patient = new PatientDemograph($patient_id);
$que = new PatientQueue();
$que->setStatus('Active');
$que->setClinic(new AptClinic($apt->getGroup()->getClinic()->getId()));

if(isset($_POST['type'])){
	$que->setType($_POST['type']);
	$que->setDepartment(NULL);
}else {
	if ($clinic_flag === "private") {
		$que->setType('Optometry-OPD');// or vaccination depending on type
	} else {
		$que->setType('Nursing');
	}
	$que->setDepartment(new Department($department_id));
}

$que->setPatient($patient);

$st = (new PatientQueueDAO())->addPatientQueue($que, $pdo);

if (isset($_POST['room_id']) && !is_blank($_POST['room_id'])) {
	$b = NULL;
	$encounterStart = date(MainConfig::$mysqlDateTimeFormat);
	$price = 0;
	$specialty = (new StaffSpecializationDAO())->get($_POST['room_id'], $pdo);
	$staff = (new StaffDirectoryDAO())->getStaff($staffId, false, $pdo);
	if($_POST['checkin_type']!=='review') {
		if ($_POST['checkin_type']=='followUp') {
			$price = (new InsuranceItemsCostDAO())->getItemFollowUpPriceByCode($specialty->getCode(), $_POST['pid'], true, $pdo);
		} else {
			$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialty->getCode(), $_POST['pid'], true, $pdo);
		}
		$pat = (new PatientDemographDAO())->getPatient($_POST['pid'], false, $pdo, null);
		
		
		$bil = new Bill();
		$bil->setPatient($pat);
		$bil->setDescription( ($_POST['checkin_type']=='followUp' ? "FollowUp " : "") . "Consultancy charges: " . $specialty->getName());
		$bil->setItem($specialty);
		$bil->setSource((new BillSourceDAO())->findSourceById(3, $pdo));
		$bil->setTransactionType("credit");
		$bil->setTransactionDate($encounterStart);
		$bil->setAmount($price);
		$bil->setPriceType($_POST['checkin_type']=='followUp' ? "followUpPrice" : "selling_price");
		$bil->setDiscounted('no');
		$bil->setDiscountedBy(null);
		$bil->setReceiver($staff);
		$bil->setClinic($staff->getClinic());
		$bil->setCostCentre((new DepartmentDAO())->get($department_id, $pdo)->getCostCentre());
		$bil->setBilledTo($pat->getScheme());
		$b = (new BillDAO())->addBill($bil, 1, $pdo);
	}
	// TODO: send this doctor a notification ...?
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
	$pat = (new PatientDemographDAO())->getPatient($_POST['pid'], FALSE, $pdo);
	$pq = new PatientQueue();
	$pq->setType("Doctors");
	$pq->setSpecialization($specialty);
	$pq->setClinic(new AptClinic($apt->getGroup()->getClinic()->getId()));
	$pq->setPatient($pat);
	$pq->setDepartment(new Department($_POST['did']));
	$pq->setFollowUp($_POST['checkin_type'] == 'followUp' ? 1 : 0);
	$pq->setReview($_POST['checkin_type'] == 'review' ? 1 : 0);
	$pq->setAmount($price);
	//push to doctors' queue
	if ((new PatientQueueDAO())->inQueue($pq->getPatient()->getId(), $pq->getType(), $pq->getSpecialization(), FALSE, $pdo)) {
		ob_end_clean();
		$pdo->rollBack();
		exit("error:Sorry, Patient is already Active on the {$specialty->getName()} Doctors queue");
	}
	$referrer = !is_blank($_POST['referrer_id']) ? (new ReferralDAO())->get($_POST['referrer_id'], $pdo) : NULL;
	
	if($_POST['checkin_type']!=='review') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
		$encounter = (new Encounter())->setBill($b!=null && $b->getId()!=null ? $b : NULL)->setStartDate($encounterStart)->setFollowUp($_POST['checkin_type']=='followUp' ? true : false)->setPatient($pat)->setDepartment(new Department($_POST['did']))->setInitiator($staff)->setSpecialization($specialty)->setScheme($pat->getScheme())->setReferrer($referrer);
		
		$newEncounter = (new EncounterDAO())->add($encounter, $pdo);
		if ($newEncounter == null) {
			ob_end_clean();
			$pdo->rollBack();
			exit("error:Sorry, An encounter failed to start");
		}
		
		$pq->setEncounter($newEncounter);
	}
	(new PatientQueueDAO())->addPatientQueue($pq, $pdo);
}
ob_start();
//exit($st);
if ($st !== null) {
	$pdo->commit();
} else {
	$pdo->rollBack();
	exit("error:Failed to add to Nursing queue");
}
echo json_encode(array('status' => $appoint));