<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/28/16
 * Time: 11:15 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Death.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DiagnosisDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DeathDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';

@session_start();
$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
error_log("InpatientId".json_encode($_POST['aid']));
$death = (new Death())->setPatient((new PatientDemographDAO())->getPatient($_POST['patient_id'], false, $pdo, true))->setTimeOfDeath($_POST['datetime_of_death'])->setDeathCausePrimary(null)->setDeathCauseSecondary(null)->setInPatient(new InPatient($_POST['aid']))->setCreateUser((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false, $pdo));
(new PatientDemographDAO())->getPatient($_POST['patient_id'], false, $pdo, true)->setDeceased(TRUE)->update($pdo);
$newDeath = (new DeathDAO())->add($death, $pdo);

if ($newDeath !== null) {
	$pdo->commit();
	exit(json_encode(true));
}
$pdo->rollBack();
exit(json_encode(false));
