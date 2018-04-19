<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/18/14
 * Time: 10:14 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabResultDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabResult.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/MessageDispatch.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MessageDispatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientCareMemberDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabGroupDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.sms.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InPatientDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";

$data = new stdClass();

if (!isset($_SESSION)) {
	session_start();
}

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
$result = (new LabResultDAO())->getLabResult($_POST['id'], true, $pdo);
if (isset($_POST['action']) && $_POST['action'] === "approve") {
	$dispatchMsg = (new MessageDispatchDAO());
	$result->setApprovedBy(new StaffDirectory($_SESSION['staffID']));
	$status = (new LabResultDAO())->approveResult($result, $pdo);
	$data = new stdClass();
	if ($status === null) {
		$data->status = "error";
		$data->message = "No access";
	} else if ($status) {
		$lab = $result; //(new LabResultDAO())->getLabResult($result->getId(), TRUE, $pdo);
		
		$labTest = (new LabDAO())->getLab($lab->getPatientLab()->getTest()->getId(), false, $pdo);
		
		$patient = (new PatientDemographDAO())->getPatient($lab->getPatientLab()->getLabGroup()->getPatient()->getId(), false, $pdo);
		
		if (SmsConfig::$sendSMS == 1) {
			$phone = ($lab->getPatientLab()->getLabGroup()->getPatient() != null) ? $lab->getPatientLab()->getLabGroup()->getPatient()->getPhoneNumber() : '';
			$msg = sprintf(SmsConfig::$approvedLabSms, $labTest->getName());
			//error_log($msg);
			$dispatch = new MessageDispatch();
			$dispatch->setMessage($msg);
			$dispatch->setSmsChannelAddress(in8nPhone($phone));
			$dispatchMsg->sendSMS($dispatch, $pdo); // SMS for the patient
		}
		
		if (SmsConfig::$sendEmail == 1) {
			$message = sprintf(SmsConfig::$approvedLabEmail, $labTest->getName());
			$requestId = $result->getPatientLab()->getLabGroup()->getGroupName();
			
			$messageIntro = $labTest->getName(). " Lab Result for ". $patient->getFullname(). " (".$patient->getId(). ") with Request id: $requestId is Ready";
			//can we get the result of this lab? yessss!!
			$resultText = "<p></p><p></p><div><table border='1' cellspacing='0' cellpadding='5' width='400'><tr><td>Field</td><td>Value</td></tr>";
			foreach ($result->getData() as $data) {
				$resultText .= "<tr><td>" . htmlspecialchars($data->getLabTemplateData()->getMethod()->getName());
				if ($data->getLabTemplateData()->getReference() != '') {
					$resultText .= "<br>(Reference: " . htmlspecialchars($data->getLabTemplateData()->getReference()) . ")";
				}
				$resultText .= "</td>";
				$resultText .= "<td>" . htmlspecialchars($data->getValue()) . "</td></tr>";
			}
			if ($result->getAbnormalValue()) {
				$resultText .= "<tr><td colspan='2' class='alert-error'><span><i class='icon-exclamation-sign'></i> Attention Required</span></td></tr>";
			}
			$resultText .= "</table></div>";
			
			$email = [];
			
			if (!is_blank($patient->getEmail()) && !$patient->isAdmitted()) {
				//if patient is not currently admitted, you should send him/her an email, if it exists
				//do not email the result to the patient
				//$email[] = $patient->getEmail();
			}
			$requesterEmail = $lab->getPatientLab()->getLabGroup()->getRequestedBy()->getEmail();
			//$referralEmail = $lab->getPatientLab()->getLabGroup()->getReferral()->getEmail();
			if (!is_blank($requesterEmail)) {
				$email[] = $requesterEmail;
			}
			
			if ($patient->isAdmitted()) {
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientCareMemberDAO.php';
				$ip = (new InPatientDAO())->getActiveInPatient($patient->getId(), true);
				
				$care_member = (new PatientCareMemberDAO())->getPatientCareMembersByInPatient($ip->getId());
				foreach ($care_member as $care) {
					$email[] = $care->getCareMember()->getEmail();
				}
			}
			
			$d = (new MessageDispatch())->setSubject('Lab Result Ready')->setMessage($messageIntro.$resultText)->setPatient($patient)->setEmailChannelAddress($email)->setEmailDeliveryStatus(false)->setSmsChannelAddress('')->setSmsDeliveryStatus(false)->setVoiceChannelAddress('')->setVoiceDeliveryStatus(false)->add($pdo);
			(new MessageDispatchDAO())->sendItem($d, $type = 2, 'others', $pdo);
		}
		
		/*
				// send Notification to the requested Dr. and Care Team
				$r_phone = ($lab->getPatientLab()->getLabGroup()->getRequestedBy() != null) ? $lab->getPatientLab()->getLabGroup()->getRequestedBy()->getPhone() : '';
				$msg_ = sprintf(SmsConfig::$approvedLabResultSms, $labTest->getName(), date(MainConfig::$dateTimeFormat, strtotime($lab->getPatientLab()->getLabGroup()->getRequestTime())), $lab->getPatientLab()->getLabGroup()->getPatient()->getFullName(), $lab->getPatientLab()->getLabGroup()->getPatient()->getId());
				$dispatch->setMessage($msg_);
				$dispatch->setSmsChannelAddress($r_phone);
				$dispatchMsg->sendSMS($dispatch); // SMS for the requested Dr
				$dispatch->setSubject("Lab Result Status");
			// Check if patient is admitted
				$inpatient = (new InPatientDAO())->getActiveInPatient($lab->getPatientLab()->getLabGroup()->getPatient()->getId());
				$care_member = (new PatientCareMemberDAO())->getPatientCareMembersByInPatient($inpatient->getId());
				if($care_member){
					$emails = [];
					$pones = [];
					$dispatch->setSmsDeliveryStatus(0);
					$dispatch->setEmailDeliveryStatus(1);
					$dispatch->setVoiceDeliveryStatus(0);
					$dispatch->setPatient($lab->getPatientLab()->getLabGroup()->getPatient());
					$dispatch->setMessage($msg_);
					foreach ($care_member as $care){
						$phones[] = $care->getCareMember()->getPhone();
						$emails[] = $care->getCareMember()->getEmail();
					}
					$emails[] = $lab->getPatientLab()->getLabGroup()->getRequestedBy()->getEmail();
					//        $dispatch->setSmsChannelAddress($phones);
					//        $dispatchMessage->sendSMS($dispatch);
					$dispatch->setEmailChannelAddress($emails);
					$dispatchMsg->sendEmail($dispatch); // Email for patient Care members
		
				}else{
					$dispatch->setSmsDeliveryStatus(0);
					$dispatch->setEmailDeliveryStatus(1);
					$dispatch->setVoiceDeliveryStatus(0);
					$dispatch->setPatient($lab->getPatientLab()->getLabGroup()->getPatient());
					$dispatch->setEmailChannelAddress($lab->getPatientLab()->getLabGroup()->getRequestedBy()->getEmail());
					$dispatchMsg->sendEmail($dispatch); // Email for the requested Dr.
				}*/
		
		$data->status = "ok";
		$data->message = "Result approved successfully";
	} else {
		$data->status = "error";
		$data->message = "Failed to approve result. It might have been already approved";
	}
} else if (isset($_POST['action']) && $_POST['action'] === "disapprove") {
	$status = (new LabResultDAO())->disApproveResult($result);
	
	if ($status === null) {
		$data->status = "error";
		$data->message = "No access";
	} else if ($status) {
		$data->status = "ok";
		$data->message = "Result reset";
	} else {
		$data->status = "error";
		$data->message = "Failed to reset result. It might have been already reset";
	}
} else if (isset($_POST['action']) && $_POST['action'] === "reject") {
	$status = (new LabResultDAO())->rejectResult($result);
	$data = new stdClass();
	if ($status === null) {
		$data->status = "error";
		$data->message = "No access";
	} else if ($status) {
		$data->status = "ok";
		$data->message = "Result rejected ";
	} else {
		$data->status = "error";
		$data->message = "Action failed";
	}
} else if (isset($_POST['action']) && $_POST['action'] === "abnormal") {
	$result->setAbnormalValue($_POST['a']);
	$status = (new LabResultDAO())->setAbnormalValue($result);
	$data = new stdClass();
	if ($status) {
		$data->status = "ok";
		$data->message = "Abnormal value set";
	} else {
		$data->status = "error";
		$data->message = "Abnormal value not set";
	}
}
//error_log(json_encode($data));
exit(json_encode($data));