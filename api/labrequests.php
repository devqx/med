<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/4/14
 * Time: 10:24 AM
 */

if (!isset($_SESSION)) {
	@session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/StaffDirectory.php";

if (isset($_POST['action']) && $_POST['action'] == "cancel") {
	$PLDAO = new PatientLabDAO();
	$pl = $PLDAO->getLab($_POST['id']);

	$status = $PLDAO->cancelLab($pl);
	ob_clean();

	
	if ($status === true) {
		exit("ok");
	} else {
		exit('error');
	}//shouldn't we return the result from cancelLab?
}
if (isset($_POST['action']) && $_POST['action'] == "receive") {
	$PLDAO = new PatientLabDAO();
	$pl = new PatientLab($_POST['id']);
	$pl->setReceivedBy(new StaffDirectory($_SESSION['staffID']));
	$status = $PLDAO->receiveLab($pl);
	if ($status) {
		exit("ok");
	}
}

if (isset($_POST['action']) && $_POST['action'] == "re-order") {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	$pl_id = $_POST['id'];
	$this_user = new StaffDirectory($_SESSION['staffID']);

	$pl = (new PatientLabDAO())->getLab($pl_id);
	$patient_id = $pl->getLabGroup()->getPatient()->getId();

	$specimens = $pl->getLabGroup()->getPreferredSpecimens();
	$lab_data = $pl->getTest();
	$service_centre = $pl->getLabGroup()->getServiceCentre();
	$referral = $pl->getLabGroup()->getReferral();

	$request = new LabGroup();
	$request->setPatient((new PatientDemographDAO())->getPatient($patient_id, FALSE));

	$request->setInPatient($pl->getLabGroup()->getInPatient());
	$request->setRequestedBy($this_user);

	$request->setPreferredSpecimens($specimens);


	$request->setRequestData([$lab_data]);
	$request->setServiceCentre($service_centre);
	$request->setReferral($referral);
	$data = (new PatientLabDAO())->newPatientLabRequest($request, false);
	if ($data) {
		exit("ok");
	} else {

	}
}
exit("error");