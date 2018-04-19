<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.admissions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

if (!AdmissionSetting::$ipMedicationTaskRealTimeDeduct) {
	$pat = (new InPatientDAO())->partialDischarge($_POST['aid'], $_POST['appointment_id'], $_POST['nextMedication'], $_POST['reason']);
} else {
	$pat = (new InPatientDAO())->discharge($_POST['aid'],  $_POST['appointment_id'], $_POST['nextMedication'], $_POST['reason']);
	
	//$pdo->commit();
	
}
if ($pat === null) {
	exit("error:Oops! something went went wrong");
} else {
	exit("ok:Patient discharged");
}
