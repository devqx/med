<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/5/14
 * Time: 10:40 AM
 */

//require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
//$pat = new Manager();

//remove this patient from the queue and redirect to the calling app
$pid = $_GET['pid'];
$qid = $_GET['qid'];
$type = $_GET['type'];

if (!isset($_SESSION)) {
	@session_start();
}

$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';

$pq = (new PatientQueueDAO())->getPatientQueue($qid, TRUE);

if (isset($_GET['remove']) && $_GET['remove'] == "true") {
	if (!is_null($pq->getSpecialization())) {
		$specialtyCode = $pq->getSpecialization()->getCode();
		if(boolval($pq->getFollowUp())){
			$price = (new InsuranceItemsCostDAO())->getItemFollowUpPriceByCode($specialtyCode, $pq->getPatient()->getId(), TRUE, null);
		} else {
			$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialtyCode, $pq->getPatient()->getId(), TRUE, null);
		}
		$pq->setAmount($price);
	} else {
		$pq->setAmount(0);
	}
	$pq->setStatus("Attended");
	$pq->setSeenBy(new StaffDirectory($_SESSION['staffID']));

} else {
//    if($pq->getBlockedBy() !== NULL){
	$pq->setStatus("Blocked");
	$pq->setBlockedBy((new StaffDirectory($_SESSION['staffID'])));
//    }
}
(new PatientQueueDAO())->changeQueueStatus($pq);

//pass the filter along so that we can narrow down to the patient directly
$filterText = $_GET['fText'];

//redirect based on the type, if nursing, ... this is already ok, else ...
if ($type == "Nursing") {
	$location = '/patient_profile.php?id=' . $pid . '&startnewvisit=true';
} else if ($type == "Vaccination") {
	$location = '/immunization/patient_immunization_profile.php?id=' . $pid;
} else if ($type == "Doctors") {
	$location = '/patient_profile.php?id=' . $pid . '&startnewvisit=false';
} else if ($type == "Lab") {
	@session_start();
	$location = '/labs/';//?fText='.$filterText.'&pid='.$pq->getPatient()->getId();
	$_SESSION['pid'] = $pq->getPatient()->getId();
	// You complained again about it redirecting to the patient profile.
	// let's continue doing and undoing, no wahala
	//$_SESSION['lab_url'] = "http://$host$uri/";
//    $location ='/patient_profile.php?id='.$pid;
} else if ($type == "Ophthalmology") {
	@session_start();
	$location = '/ophthalmology/';//?fText='.$filterText.'&pid='.$pq->getPatient()->getId();
	$_SESSION['pid'] = $pq->getPatient()->getId();
	//$_SESSION['lab_url'] = "http://$host$uri/";
//    $location ='/patient_profile.php?id='.$pid;
} else if($type == "Dentistry"){
    @session_start();
    $location ='/dentistry/';//?fText='.$filterText.'&pid='.$pq->getPatient()->getId();
    $_SESSION['pid']=$pq->getPatient()->getId();
    //$_SESSION['lab_url'] = "http://$host$uri/";
} else if($type == "Pharmacy"){
    @session_start();
    $location ='/pharmaceuticals/';
    $_SESSION['pid']=$pq->getPatient()->getId();
//    $location ='/pharmaceuticals/?fText='.$filterText;
//    $_SESSION['pharm_url'] = "http://$host$uri/";
//    $location ='/patient_profile.php?id='.$pid;
} else if ($type == "Billing") {
//    $location ='/billing/?outstanding=true&fText='.$filterText;
	$_SESSION['bill_url'] = "http://$host$uri/";
	$location = '/patient_profile.php?id=' . $pid;
} else if ($type == "Imaging") {
//    $location ='/imaging/?fText='.$filterText;
	$_SESSION['scan_url'] = "http://$host$uri/";
	$location = '/patient_profile.php?id=' . $pid;
} else if ($type == "Procedure") {
//    $location ='/procedures/?fText='.$filterText;
	$_SESSION['proc_url'] = "http://$host$uri/";
	$location = '/patient_profile.php?id=' . $pid;
} else if ($type == "Bed") {
	//get the latest admission instance without discharge
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
	$iP = (new InPatientDAO())->getActiveInPatient($pid, TRUE);
	if ($iP !== null) {
		$location = '/admissions/?action=bed&id=' . $pid . '&aid=' . $iP->getId();
	} else {
		$location = '/admissions/';
	}
} else if ($type == "Antenatal") {
	//get the latest and active antenatal instance
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
	$A_E = (new AntenatalEnrollmentDAO())->getActiveInstance($pid, TRUE);
	if ($A_E !== null) {
		$location = '/antenatal/patient_antenatal_profile.php?id=' . $pid . '&aid=' . $A_E->getId() . '&startnewvisit=true';
	} else {
		$location = '/antenatal/';
	}
} else {
	$location = '/patient_profile.php?id=' . $pid . '&startnewvisit=false';
}

header("Location:" . $location);


