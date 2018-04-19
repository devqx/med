<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/14/14
 * Time: 5:06 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/MessageDispatch.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MessageDispatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientCareMemberDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.sms.php';
$access = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false);

if (!$this_user->hasRole($access->radiologyApproval)) {
	exit('error:No access');
}

$scan = (new PatientScanDAO())->getScan($_POST['id']);
$scan->setApprovedBy($this_user);
$scan->setApprovedDate(date("Y-m-d H:i:s"));
$s = (new PatientScanDAO())->approveScan($scan);

$dispatchMessage = (new MessageDispatchDAO());
//
if ($s != null) {
	$phone = ($scan->getPatient() != null) ? $scan->getPatient()->getPhoneNumber() : '';
	$msg = SmsConfig::$approvedScanSms;
	$dispatch = new MessageDispatch();
	$dispatch->setMessage($msg);
	$dispatch->setSmsChannelAddress($phone);
	$dispatchMessage->sendSMS($dispatch); // sms for the patient
	// Send Email and SMS to Requested Dr
	$r_phone = ($scan->getRequestedBy() != null) ? $scan->getRequestedBy()->getPhone() : '';
	$typ = [];
	foreach ($scan->getScan() as $rq) {
		$typ[] = $rq->getName();
	}
	$msg_ = sprintf(SmsConfig::$approvedScanSms, implode(", ", $typ), date(MainConfig::$dateTimeFormat, strtotime($scan->getRequestDate())), $scan->getPatient()->getFullName(), $scan->getPatient()->getId());
	$dispatch->setMessage($msg_);
	$dispatch->setSmsChannelAddress($r_phone);
	$dispatchMessage->sendSMS($dispatch); // sms for the requested Dr
	
	/*$dispatch->setSubject("Scan Request Status");
    $dispatch->setEmailChannelAddress($scan->getRequestedBy()->getEmail());
    // Message to care members
    $inpatient = (new InPatientDAO())->getActiveInPatient($scan->getPatient()->getId());
    $care_member = (new PatientCareMemberDAO())->getPatientCareMembersByInPatient($inpatient->getId());
    if ($care_member){
        $emails = [];
        $pones = [];
        $dispatch->setSmsDeliveryStatus(0);
        $dispatch->setEmailDeliveryStatus(1);
        $dispatch->setVoiceDeliveryStatus(0);
        $dispatch->setPatient($scan->getPatient());
        $dispatch->setMessage($msg_);
        foreach ($care_member as $care){
            $phones[] = $care->getCareMember()->getPhone();
            $emails[] = $care->getCareMember()->getEmail();

        }
        $emails[] = $scan->getRequestedBy()->getEmail();
//        $dispatch->setSmsChannelAddress($phones);
//        $dispatchMessage->sendSMS($dispatch);
        $dispatch->setEmailChannelAddress($emails);
        $dispatchMessage->sendEmail($dispatch); // dispatch the email to care members

    }else{
        $dispatch->setSmsDeliveryStatus(0);
        $dispatch->setEmailDeliveryStatus(1);
        $dispatch->setVoiceDeliveryStatus(0);
        $dispatch->setPatient($scan->getPatient());
        $dispatchMessage->sendEmail($dispatch); //
    }*/
	
}
//exit(json_encode($s));