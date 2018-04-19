<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/27/16
 * Time: 4:48 PM
 */


require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabourEnrollment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/antenatal_vars.php';

@session_start();
$return = (object)null;
if (!isset($_SESSION['staffID'])) {
	$return->message = "Sorry, no active session found";
	$return->status = "error";
	exit(json_encode($return));
}


$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
//$enrollment = (object) $enrollment;

$patientId = $_GET['patient_id'];
$url = ($_SERVER['HTTPS'] ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/api/get_antenatal_patients.php?q=$patientId&sex=female&mode=single";
//make a post to this file and get the results
$ch = curl_init();
// Disable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL, $url);
// Execute
$result = curl_exec($ch);
// Closing
curl_close($ch);
$date = date_create(date("Y-m-d", time()));
date_sub($date, date_interval_create_from_date_string("40 weeks"));
$guessed_lmp = date_format($date, "Y-m-d");

$data = json_decode($result);
$demo = $data[0];
$antenatal_details = $demo->antenatal ? $demo->antenatal : null;

$lmp = ($antenatal_details) ? $antenatal_details->lmpDate : $guessed_lmp;
$babyFatherName = $antenatal_details && $antenatal_details->babyFatherName ? $antenatal_details->babyFatherName : null;
$babyFatherPhone = $antenatal_details && $antenatal_details->babyFatherPhone ? $antenatal_details->babyFatherPhone : null;
$babyFatherBloodGroup = $antenatal_details && $antenatal_details->babyFatherBloodGroup ? $antenatal_details->babyFatherBloodGroup : null;
$gra = $antenatal_details && $antenatal_details->gravida ? $antenatal_details->gravida : null;
$para = $antenatal_details && $antenatal_details->para ? $antenatal_details->para : null;
$alive = $antenatal_details && $antenatal_details->gravida ? $antenatal_details->alive : null;
$abortions = $antenatal_details && $antenatal_details->gravida ? $antenatal_details->abortions : null;
$presentPregnancy = "Single"; //a risky assumption?

$labour = (new LabourEnrollment())->setPatient(new PatientDemograph($demo->patient_ID))->setEnrolledOn(date('Y-m-d H:i:s'))->setEnrolledBy(new StaffDirectory($_SESSION['staffID']))->setDateClosed(null)->setLmpDate($lmp)->setBabyFatherName($babyFatherName)->setGravida(get_index_by_value($gra, $gravida))->setPara(get_index_by_value($para, $parity))->setAlive(get_index_by_value($alive, $general_))->setAbortions(get_index_by_value($abortions, $general_))->setCurrentPregnancy(get_index_by_value($presentPregnancy, $pregnancies))->add($pdo);
//
if ($labour == null) {
	$pdo->rollBack();
	$return->message = "Sorry, there was an error. Please try again later";
	$return->status = "error";
	exit(json_encode($return));
}
if((new InPatientDAO())->getInPatient($_GET['aid'], TRUE, $pdo)->setLabourInstance($labour)->update($pdo)){
	$pdo->commit();
	$return->message = "Labour management instance created";
	$return->status = "success";
	exit(json_encode($return));
}

$pdo->rollBack();
$return->message = "Sorry, there was an error. Please try again later?";
$return->status = "error";
exit(json_encode($return));




