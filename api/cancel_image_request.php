<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/2/15
 * Time: 12:21 PM
 */
if(!isset($_SESSION)){@session_start();}
if(!isset($_SESSION['staffID'])){exit(json_encode(false));}
if (isset($_POST['id'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PatientScanDAO.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/AppointmentDAO.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/StaffDirectory.php";
	$request = (new PatientScanDAO())->getScan($_POST['id']);

	$request->setCanceledBy(new StaffDirectory($_SESSION['staffID']));
	// check if not cancelled already
	if (!boolval($request->getCancelled())) {
		if(!$request->getAppointment() == null){
			// release the scheduled resources on the appointment
			 $app = (new AppointmentDAO())->cancelProcedureAppointment($request->getAppointment());
			}
		
		ob_clean();
		exit(json_encode((new PatientScanDAO())->cancelScanRequest($request)));
	} else {
		ob_clean();
		exit(json_encode(false));
	}
}
ob_clean();
exit(json_encode(false));