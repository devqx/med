<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/8/16
 * Time: 10:23 AM
 */
$return = (object)null;
$measurements = @$_POST['measurements'];
$assessments = @$_POST['assessments'];
$enrollments = @$_POST['enrollments'];
$deliveries = @$_POST['deliveries'];

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabourEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabourEnrollment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/antenatal_vars.php';

@session_start();

if(!isset($_SESSION['staffID'])){
	$return->message = "Sorry, no active session found";
	$return->status  = "error";
	exit(json_encode($return));
}

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

foreach (@$_POST['enrollments'] as $enrollment) {
	$enrollment = (object) $enrollment;
	$labour = (new LabourEnrollment())->setPatient( new PatientDemograph($enrollment->patientID) )->setEnrolledOn($enrollment->dateEnrolled)->setEnrolledBy(new StaffDirectory($_SESSION['staffID']))->setDateClosed($enrollment->dateClosed ? $enrollment->dateClosed : NULL)->setLmpDate($enrollment->patientLMP)->setBabyFatherName($enrollment->babyFatherName)->setGravida( get_index_by_value($enrollment->gravida, $gravida) )->setPara( get_index_by_value($enrollment->parity, $parity) )->setAlive(get_index_by_value($enrollment->alive, $general_))->setAbortions(get_index_by_value($enrollment->abortions, $general_))->setCurrentPregnancy( get_index_by_value($enrollment->presentPregnancy, $pregnancies) )->add($pdo);

	if($labour == null){
		$pdo->rollBack();
		$return->message = "Sorry, there was an error. Please try again later";
		$return->status  = "error";
		exit(json_encode($return));
	}
}
$pdo->commit();
$return->message = "Data successfully saved to the server";
$return->status  = "success";
exit(json_encode($return));