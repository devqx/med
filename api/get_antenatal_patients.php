<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/7/16
 * Time: 11:01 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if (is_blank($_REQUEST['q'])) {
	exit(json_encode([]));
}
$patients = [];
$AEdao = (new AntenatalEnrollmentDAO());
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == "single") {
	$patient = (new PatientDemographDAO())->getPatient($_REQUEST['q']);
	if($patient){
		$q = (object)null;
		$q->text = $patient->getId();
		$q->sex = "female";
		$search = (new PatientDemographDAO())->searchPatientNames($q);
		foreach ($search as $patient) {
			$patient = (object)$patient;
			if ($AEdao->isEnrolled($patient->patientId)) {
				$patient->antenatal = $AEdao->getActiveInstance($patient->patientId, FALSE);
			}
			$patients[] = $patient;
		}
	}

	exit(json_encode($patients));

} else {
	$q = (object)null;
	$q->text = $_REQUEST['q'];
	$q->sex = "female";

	$search = (new PatientDemographDAO())->searchPatientNames($q);

	foreach ($search as $patient) {
		$patient = (object)$patient;
		if ($AEdao->isEnrolled($patient->patientId)) {
			$patient->antenatal = $AEdao->getActiveInstance($patient->patientId, FALSE);
		}
		$patients[] = $patient;
	}
	exit(json_encode($patients));
}

