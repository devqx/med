<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 6:06 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/MessageDispatch.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientMedicalReportDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/MessageDispatchDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/class.config.sms.php';
@session_start();
if(!isset($_SESSION['staffID'])){
	exit('error');
}

$report=(new PatientMedicalReportDAO())->get($_POST['id'])
	->setApproved(TRUE)
	->setApprovedBy( (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE) )
	->setApprovedDate(date("Y-m-d H:i:s"))->update();

if($report !== null){
	ob_end_clean();
	exit("ok");
}
exit("error");

//no sending of sms for this service yet
if($s !=null ){
	$phone = ($report->getPatient() != null)? $report->getPatient()->getPhoneNumber() : '';
	$msg = SmsConfig::$approvedScanSms;
	$dispatch = new MessageDispatch();
	$dispatch->setMessage($msg);
	$dispatch->setSmsChannelAddress($phone);
	(new MessageDispatchDAO())->sendSMS($dispatch);
}