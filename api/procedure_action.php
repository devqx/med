<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/12/14
 * Time: 6:28 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Appointment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';

//use this file to process the status passed to a patient procedure object
if (isset($_POST['status']) && $_POST['status'] == "start") {
	$ret = (new PatientProcedureDAO())->changeProcedureStatus(new PatientProcedure($_POST['id']), "started");
	ob_end_clean();
	if ($ret) {
		exit("success:Procedure is now Started");
	} else {
		exit("error:Failed to modify property");
	}
} else if (isset($_POST['status']) && $_POST['status'] == "close") {
    $ret = (new PatientProcedureDAO())->changeProcedureStatus(new PatientProcedure($_POST['id']), "closed", $_POST['message']);
    ob_end_clean();
    if ($ret) {
        exit("success:Procedure is now concluded");
    } else {
        exit("error:Failed to modify property");
    }
} else if (isset($_POST['status']) && $_POST['status'] == "charge") {
	$ret = (new PatientProcedureDAO())->chargeProcedure($_POST['id'], "charge");

	ob_end_clean();
	if ($ret) {
		exit("success:Procedure is now billed");
	} else {
		exit("error:Failed to bill this procedure");
	}
} else if (isset($_POST['status']) && $_POST['status'] == "cancel") {
	
	$pr = (new PatientProcedureDAO())->get($_POST['id']);
	
	
	if($pr->getStatus()=='scheduled'){
		// get procedure appointment property
		$proce = (new PatientProcedureDAO())->get($_POST['id']);
		if(!$proce->getAppointmentId() == null){
			 // release the scheduled resources on the appointment
	
			 if(!(new AppointmentDAO())->cancelProcedureAppointment($proce->getAppointmentId()) == TRUE){
	
				 exit('error:Could not cancel the procedure appointment');
			 }
		}else{
			exit('error:Please Contact the Vendor for Help'); // For back end cancellations
	
		}
	
	}
	
	if(in_array($pr->getStatus(), ['closed',  'started', 'cancelled'])){
		exit("error:Cancellation not allowed");
	}
	$ret = (new PatientProcedureDAO())->changeProcedureStatus(new PatientProcedure($_POST['id']), "cancelled");
	ob_end_clean();
	if ($ret) {
		exit("success:Procedure is now cancelled");
	} else {
		exit("error:Failed to modify property");
	}
	
} else if(isset($_POST['action']) && $_POST['action']=="re-order"){
	$procedure = (new PatientProcedureDAO())->get($_POST['id']);
	require_once $_SERVER['DOCUMENT_ROOT']. '/classes/StaffDirectory.php';
	$procedure->setRequestedBy(new StaffDirectory($_SESSION['staffID']));
	$conditions_ = $procedure->getConditions();
	$conditions = [];
	foreach ($conditions_ as $item) {
		$conditions[] = $item->getId();
	}
	$procedure->setConditions( $conditions);
	
	$reOrder = (new PatientProcedureDAO())->add($procedure, !$procedure->getBilled());
	if($reOrder != null){
		exit("ok");//to mark success
	}
	exit("error:Failed to re-order request");

}
exit("error:Unknown option");