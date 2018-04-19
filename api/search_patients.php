<?php
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
$asArray = isset($_REQUEST['asArray']) ? TRUE : FALSE;
$medical = isset($_REQUEST['medical']) ? TRUE : FALSE;
$patients = [];
$patient = null;
if (isset($_REQUEST['q'])) {
	$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : null;

	if (isset($_REQUEST['sex'])) {
		$q = (object)null;
		$q->text = $_REQUEST['q'];
		$q->sex = $_REQUEST['sex'];// can be used to filter our patients based on gender
	} else {
		$q = $_REQUEST['q'];
	}
	$patients = (new PatientDemographDAO())->searchPatientNames($q, $limit, $asArray, $medical);
} else if (isset($_REQUEST['pid'])) {
	$full = FALSE;
	if ($asArray) {
		if ($medical) {
			$patient = (new PatientDemographDAO())->getPatientAsArray($_REQUEST['pid'], FALSE, TRUE);
		} else {
			$patient = (new PatientDemographDAO())->getPatientAsArray($_REQUEST['pid'], FALSE);
		}
	} else {
		if ($medical) {
			$patient = (new PatientDemographDAO())->getPatientMedical($_REQUEST['pid'], $full);
		} else {
			$patient = (new PatientDemographDAO())->getPatient($_REQUEST['pid'], $full);
		}
	}
} else {
	$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : null;
	$patients = (new PatientDemographDAO())->searchPatientNames("", $limit, $asArray, $medical);
}

if (!isset($_GET['suppress']) && isset($_REQUEST['q'])) {
	$data = json_encode($patients, JSON_PARTIAL_OUTPUT_ON_ERROR);
	exit($data);
}
if (!isset($_GET['suppress']) && isset($_REQUEST['pid'])) {
	$data = json_encode($patient, JSON_PARTIAL_OUTPUT_ON_ERROR);
	exit($data);
}
